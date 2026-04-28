from __future__ import annotations

import argparse
import csv
import json
import re
import sys
import time
import urllib.error
import urllib.request
import zipfile
from dataclasses import dataclass
from html import unescape
from pathlib import Path
from typing import Iterable
from xml.etree import ElementTree as ET


WORD_NS = {"w": "http://schemas.openxmlformats.org/wordprocessingml/2006/main"}


@dataclass
class Suggestion:
    file_path: str
    chunk_index: int
    chunk_start: int
    chunk_end: int
    issue_start: int
    issue_end: int
    original_text: str
    candidates: str
    description: str
    context: str


def read_markdown(path: Path) -> str:
    return path.read_text(encoding="utf-8")


def read_docx(path: Path) -> str:
    paragraphs: list[str] = []
    with zipfile.ZipFile(path) as docx_zip:
        with docx_zip.open("word/document.xml") as doc_xml:
            root = ET.parse(doc_xml).getroot()

    for paragraph in root.findall(".//w:p", WORD_NS):
        texts: list[str] = []
        for node in paragraph.findall(".//w:t", WORD_NS):
            texts.append(node.text or "")
        line = "".join(texts).strip()
        if line:
            paragraphs.append(line)

    return "\n\n".join(paragraphs)


def load_text(path: Path) -> str:
    if path.suffix.lower() == ".md":
        return read_markdown(path)
    if path.suffix.lower() == ".docx":
        return read_docx(path)
    raise ValueError(f"Unsupported file type: {path.suffix}")


def split_chunks(text: str, max_chars: int) -> list[tuple[int, int, str]]:
    paragraphs = [segment.strip() for segment in re.split(r"\n\s*\n", text) if segment.strip()]
    chunks: list[tuple[int, int, str]] = []
    cursor = 0
    current = ""
    chunk_start = 0

    for paragraph in paragraphs:
        paragraph_start = text.find(paragraph, cursor)
        if paragraph_start < 0:
            paragraph_start = cursor
        paragraph_end = paragraph_start + len(paragraph)
        cursor = paragraph_end

        candidate = paragraph if not current else f"{current}\n\n{paragraph}"
        if current and len(candidate) > max_chars:
            chunks.append((chunk_start, chunk_start + len(current), current))
            current = paragraph
            chunk_start = paragraph_start
        else:
            if not current:
                chunk_start = paragraph_start
            current = candidate

    if current:
        chunks.append((chunk_start, chunk_start + len(current), current))

    if not chunks and text.strip():
        trimmed = text.strip()
        start = text.find(trimmed)
        chunks.append((start, start + len(trimmed), trimmed))

    return chunks


def request_suggestions(api_url: str, text: str, timeout: int) -> list[dict]:
    payload = json.dumps({"text": text}).encode("utf-8")
    req = urllib.request.Request(
        api_url,
        data=payload,
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=timeout) as response:
        body = response.read().decode("utf-8")
    data = json.loads(body)
    return data.get("suggestions", [])


def build_context(chunk_text: str, start: int, end: int, radius: int = 36) -> str:
    left = max(0, start - radius)
    right = min(len(chunk_text), end + radius)
    snippet = chunk_text[left:right].replace("\r", " ").replace("\n", " ")
    return unescape(snippet)


def iter_supported_files(root: Path) -> Iterable[Path]:
    for path in sorted(root.rglob("*")):
        if path.is_file() and path.suffix.lower() in {".md", ".docx"}:
            yield path


def load_existing_suggestions(path: Path) -> list[Suggestion]:
    if not path.exists():
        return []

    loaded: list[Suggestion] = []
    with path.open("r", newline="", encoding="utf-8-sig") as fp:
        reader = csv.DictReader(fp)
        for item in reader:
            loaded.append(
                Suggestion(
                    file_path=item["file_path"],
                    chunk_index=int(item["chunk_index"]),
                    chunk_start=int(item["chunk_start"]),
                    chunk_end=int(item["chunk_end"]),
                    issue_start=int(item["issue_start"]),
                    issue_end=int(item["issue_end"]),
                    original_text=item["original_text"],
                    candidates=item["candidates"],
                    description=item["description"],
                    context=item["context"],
                )
            )
    return loaded


def write_csv(path: Path, suggestions: list[Suggestion]) -> None:
    with path.open("w", newline="", encoding="utf-8-sig") as fp:
        writer = csv.writer(fp)
        writer.writerow(
            [
                "file_path",
                "chunk_index",
                "chunk_start",
                "chunk_end",
                "issue_start",
                "issue_end",
                "original_text",
                "candidates",
                "description",
                "context",
            ]
        )
        for item in suggestions:
            writer.writerow(
                [
                    item.file_path,
                    item.chunk_index,
                    item.chunk_start,
                    item.chunk_end,
                    item.issue_start,
                    item.issue_end,
                    item.original_text,
                    item.candidates,
                    item.description,
                    item.context,
                ]
            )


def write_json(path: Path, suggestions: list[Suggestion]) -> None:
    data = [item.__dict__ for item in suggestions]
    path.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")


def collect_suggestions(
    root: Path,
    api_url: str,
    delay_seconds: float,
    timeout: int,
    max_chars: int,
    csv_path: Path,
    json_path: Path,
) -> list[Suggestion]:
    results = load_existing_suggestions(csv_path)
    completed_files = {item.file_path for item in results}

    for file_path in iter_supported_files(root):
        file_key = str(file_path)
        if file_key in completed_files:
            print(f"[skip] {file_path.name} 이미 저장돼 있어 건너뜁니다.", flush=True)
            continue

        text = load_text(file_path)
        chunks = split_chunks(text, max_chars=max_chars)
        file_results: list[Suggestion] = []

        for chunk_index, (chunk_start, chunk_end, chunk_text) in enumerate(chunks, start=1):
            print(f"[{file_path.name}] chunk {chunk_index}/{len(chunks)} 검사 중...", flush=True)
            try:
                suggestions = request_suggestions(api_url, chunk_text, timeout)
            except urllib.error.HTTPError as exc:
                print(f"HTTP error for {file_path}: {exc.code} {exc.reason}", file=sys.stderr)
                suggestions = []
            except urllib.error.URLError as exc:
                raise RuntimeError(
                    f"맞춤법 API 연결 실패: {api_url} ({exc.reason})"
                ) from exc

            for item in suggestions:
                start = int(item.get("start", 0))
                end = int(item.get("end", 0))
                file_results.append(
                    Suggestion(
                        file_path=file_key,
                        chunk_index=chunk_index,
                        chunk_start=chunk_start,
                        chunk_end=chunk_end,
                        issue_start=chunk_start + start,
                        issue_end=chunk_start + end,
                        original_text=item.get("text", ""),
                        candidates=" | ".join(item.get("candidates", [])),
                        description=item.get("description", ""),
                        context=build_context(chunk_text, start, end),
                    )
                )

            time.sleep(delay_seconds)

        results.extend(file_results)
        completed_files.add(file_key)
        write_csv(csv_path, results)
        write_json(json_path, results)
        print(f"[save] {file_path.name} 저장 완료", flush=True)

    return results


def main() -> int:
    if hasattr(sys.stdout, "reconfigure"):
        try:
            sys.stdout.reconfigure(encoding="utf-8")
        except Exception:
            pass

    parser = argparse.ArgumentParser(
        description="Batch spelling-check .md and .docx files through a local Speller API."
    )
    parser.add_argument("source", help="Source folder containing .md or .docx files")
    parser.add_argument(
        "--api-url",
        default="http://localhost:3000",
        help="Speller API endpoint (default: http://localhost:3000)",
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=6.5,
        help="Delay between API calls in seconds (default: 6.5)",
    )
    parser.add_argument(
        "--timeout",
        type=int,
        default=30,
        help="HTTP timeout in seconds (default: 30)",
    )
    parser.add_argument(
        "--max-chars",
        type=int,
        default=1200,
        help="Maximum characters per request chunk (default: 1200)",
    )
    parser.add_argument(
        "--output-prefix",
        default="spelling_report",
        help="Output filename prefix placed in the source folder",
    )
    args = parser.parse_args()

    root = Path(args.source).expanduser()
    if not root.exists():
        print(f"Source folder not found: {root}", file=sys.stderr)
        return 1

    csv_path = root / f"{args.output_prefix}.csv"
    json_path = root / f"{args.output_prefix}.json"

    suggestions = collect_suggestions(
        root=root,
        api_url=args.api_url,
        delay_seconds=args.delay,
        timeout=args.timeout,
        max_chars=args.max_chars,
        csv_path=csv_path,
        json_path=json_path,
    )

    write_csv(csv_path, suggestions)
    write_json(json_path, suggestions)

    print(f"완료: {len(suggestions)}개 교정 제안을 찾았습니다.")
    print(f"CSV:  {csv_path}")
    print(f"JSON: {json_path}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
