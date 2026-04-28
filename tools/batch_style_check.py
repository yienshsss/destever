from __future__ import annotations

import argparse
import csv
import json
import re
import sys
from collections import Counter, defaultdict
from dataclasses import dataclass, asdict
from pathlib import Path
from typing import Iterable


ADVERBS = [
    "정말",
    "진짜",
    "아주",
    "너무",
    "조금",
    "살짝",
    "문득",
    "괜히",
    "어쩐지",
    "차라리",
    "이미",
    "다시",
    "계속",
    "이내",
    "겨우",
    "도로",
    "훨씬",
    "곧",
    "막",
]

CONNECTORS = [
    "그리고",
    "하지만",
    "그러나",
    "그래서",
    "그러자",
    "그런데",
    "그러면서",
    "게다가",
    "대신",
    "반면",
    "한편",
]

CLICHES = [
    "이를 악물",
    "숨을 삼켰",
    "입술을 깨물",
    "눈을 질끈 감",
    "말끝을 흐렸",
    "차갑게 식은",
    "피식 웃",
    "쓴웃음을 지",
    "한숨을 내쉬",
    "숨을 몰아쉬",
    "마른침을 삼켰",
    "어깨를 으쓱",
    "어깨를 움찔",
    "입꼬리를 올렸",
    "입꼬리를 비틀",
]

ENDING_PATTERNS = ["했다", "였다", "것이었다"]


@dataclass
class Issue:
    file_name: str
    category: str
    severity: str
    phrase: str
    suggestion: str
    context: str
    count: int = 1


def read_text(path: Path) -> str:
    for encoding in ("utf-8", "utf-8-sig", "cp949"):
        try:
            return path.read_text(encoding=encoding)
        except UnicodeDecodeError:
            continue
    return path.read_text(encoding="utf-8", errors="replace")


def normalize_spaces(text: str) -> str:
    return re.sub(r"\s+", " ", text).strip()


def sentence_split(text: str) -> list[str]:
    parts = re.split(r"(?:[.!?…]+|\n+)", text)
    return [normalize_spaces(part) for part in parts if normalize_spaces(part)]


def build_context(text: str, phrase: str, radius: int = 60) -> str:
    idx = text.find(phrase)
    if idx < 0:
        return text[: radius * 2]
    start = max(0, idx - radius)
    end = min(len(text), idx + len(phrase) + radius)
    return normalize_spaces(text[start:end])


def find_phrase_counts(text: str, phrases: list[str]) -> Counter:
    counts = Counter()
    for phrase in phrases:
        count = len(re.findall(re.escape(phrase), text))
        if count:
            counts[phrase] = count
    return counts


def detect_repeated_words(file_name: str, text: str) -> list[Issue]:
    issues: list[Issue] = []
    for match in re.finditer(r"\b([가-힣A-Za-z]{2,})\b(?:\s+\1\b)+", text):
        phrase = match.group(0)
        word = match.group(1)
        issues.append(
            Issue(
                file_name=file_name,
                category="연속 반복 단어",
                severity="medium",
                phrase=phrase,
                suggestion=f"'{word}' 반복 여부 확인",
                context=build_context(text, phrase),
            )
        )
    return issues


def detect_long_sentences(file_name: str, text: str, limit: int) -> list[Issue]:
    issues: list[Issue] = []
    for sentence in sentence_split(text):
        if len(sentence) >= limit:
            issues.append(
                Issue(
                    file_name=file_name,
                    category="장문 주의",
                    severity="medium",
                    phrase=sentence[:80] + ("..." if len(sentence) > 80 else ""),
                    suggestion=f"{limit}자 이상 문장입니다. 분리 검토",
                    context=sentence[:220] + ("..." if len(sentence) > 220 else ""),
                    count=len(sentence),
                )
            )
    return issues


def detect_ending_runs(file_name: str, text: str) -> list[Issue]:
    issues: list[Issue] = []
    sentences = sentence_split(text)
    for ending in ENDING_PATTERNS:
        streak: list[str] = []
        for sentence in sentences:
            if sentence.endswith(ending):
                streak.append(sentence)
            else:
                if len(streak) >= 3:
                    issues.append(
                        Issue(
                            file_name=file_name,
                            category="어미 반복",
                            severity="medium",
                            phrase=ending,
                            suggestion=f"'{ending}' 어미가 {len(streak)}문장 연속 반복",
                            context=" / ".join(s[:50] for s in streak[:3]),
                            count=len(streak),
                        )
                    )
                streak = []
        if len(streak) >= 3:
            issues.append(
                Issue(
                    file_name=file_name,
                    category="어미 반복",
                    severity="medium",
                    phrase=ending,
                    suggestion=f"'{ending}' 어미가 {len(streak)}문장 연속 반복",
                    context=" / ".join(s[:50] for s in streak[:3]),
                    count=len(streak),
                )
            )
    return issues


def detect_obvious_typos(file_name: str, text: str) -> list[Issue]:
    issues: list[Issue] = []
    patterns = [
        (r"ㅋㅋㅋㅋ{3,}", "감탄/웃음 표현 과다"),
        (r"ㅎㅎㅎㅎ{3,}", "웃음 표현 과다"),
        (r"\.\.\.\.+", "말줄임표 과다"),
        (r"[!?]{3,}", "감탄 부호 과다"),
        (r"[가-힣]{1}\s+[가-힣]{1}\s+[가-힣]{1}", "한글 자소/띄어쓰기 깨짐 의심"),
        (r"[^\x00-\x7F가-힣0-9\s\.\,\!\?\-…'\"“”‘’:;()\[\]<>/\\]", "문자 인코딩/특수문자 이상 의심"),
    ]
    for pattern, label in patterns:
        for match in re.finditer(pattern, text):
            phrase = match.group(0)
            issues.append(
                Issue(
                    file_name=file_name,
                    category="명백한 오탈자 후보",
                    severity="high",
                    phrase=phrase,
                    suggestion=label,
                    context=build_context(text, phrase),
                )
            )
    return issues


def detect_continuity_candidates(file_name: str, text: str) -> list[Issue]:
    issues: list[Issue] = []
    lines = [normalize_spaces(line) for line in text.splitlines() if normalize_spaces(line)]
    for line in lines:
        if "설정" in line or "복선" in line or "떡밥" in line:
            issues.append(
                Issue(
                    file_name=file_name,
                    category="설정/복선 검토 후보",
                    severity="low",
                    phrase=line[:80],
                    suggestion="자동 판정보다 수동 검토 권장",
                    context=line[:180],
                )
            )
    return issues[:20]


def analyze_file(path: Path, long_sentence_limit: int) -> tuple[list[Issue], dict]:
    text = read_text(path)
    issues: list[Issue] = []

    adverb_counts = find_phrase_counts(text, ADVERBS)
    connector_counts = find_phrase_counts(text, CONNECTORS)
    cliche_counts = find_phrase_counts(text, CLICHES)

    for phrase, count in adverb_counts.items():
        if count >= 8:
            issues.append(
                Issue(
                    file_name=path.name,
                    category="자주 쓰는 부사",
                    severity="low",
                    phrase=phrase,
                    suggestion="반복 사용 빈도 검토",
                    context=build_context(text, phrase),
                    count=count,
                )
            )

    for phrase, count in connector_counts.items():
        if count >= 8:
            issues.append(
                Issue(
                    file_name=path.name,
                    category="반복 접속어",
                    severity="low",
                    phrase=phrase,
                    suggestion="문단 연결 방식 다양화 검토",
                    context=build_context(text, phrase),
                    count=count,
                )
            )

    for phrase, count in cliche_counts.items():
        if count >= 4:
            issues.append(
                Issue(
                    file_name=path.name,
                    category="상투 표현",
                    severity="low",
                    phrase=phrase,
                    suggestion="상투 표현 반복 사용 검토",
                    context=build_context(text, phrase),
                    count=count,
                )
            )

    issues.extend(detect_repeated_words(path.name, text))
    issues.extend(detect_long_sentences(path.name, text, long_sentence_limit))
    issues.extend(detect_ending_runs(path.name, text))
    issues.extend(detect_obvious_typos(path.name, text))
    issues.extend(detect_continuity_candidates(path.name, text))

    summary = {
        "file_name": path.name,
        "adverbs": dict(adverb_counts.most_common()),
        "connectors": dict(connector_counts.most_common()),
        "cliches": dict(cliche_counts.most_common()),
        "char_count": len(text),
    }
    return issues, summary


def write_csv(path: Path, rows: list[Issue]) -> None:
    with path.open("w", newline="", encoding="utf-8-sig") as fp:
        writer = csv.writer(fp)
        writer.writerow(["파일명", "유형", "심각도", "표현", "횟수", "제안", "문맥"])
        for row in rows:
            writer.writerow(
                [
                    row.file_name,
                    row.category,
                    row.severity,
                    row.phrase,
                    row.count,
                    row.suggestion,
                    row.context,
                ]
            )


def write_habit_csv(path: Path, summaries: list[dict]) -> None:
    with path.open("w", newline="", encoding="utf-8-sig") as fp:
        writer = csv.writer(fp)
        writer.writerow(["파일명", "구분", "표현", "횟수"])
        for summary in summaries:
            for kind, label in (("adverbs", "자주 쓰는 부사"), ("connectors", "반복 접속어"), ("cliches", "상투 표현")):
                for phrase, count in summary[kind].items():
                    writer.writerow([summary["file_name"], label, phrase, count])


def write_json(path: Path, data: object) -> None:
    path.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")


def merge_with_spelling(style_rows: list[Issue], source_dir: Path) -> list[dict]:
    merged = [
        {
            "file_name": row.file_name,
            "type": row.category,
            "severity": row.severity,
            "text": row.phrase,
            "suggestion": row.suggestion,
            "context": row.context,
            "count": row.count,
        }
        for row in style_rows
    ]

    spelling_csv = source_dir / "spelling_report.csv"
    if spelling_csv.exists():
        with spelling_csv.open("r", encoding="utf-8-sig", newline="") as fp:
            reader = csv.DictReader(fp)
            for item in reader:
                merged.append(
                    {
                        "file_name": Path(item.get("file_path", "")).name,
                        "type": "맞춤법",
                        "severity": "high",
                        "text": item.get("original_text", ""),
                        "suggestion": item.get("candidates", ""),
                        "context": item.get("context", ""),
                        "count": 1,
                    }
                )
    return merged


def main() -> int:
    if hasattr(sys.stdout, "reconfigure"):
        try:
            sys.stdout.reconfigure(encoding="utf-8")
        except Exception:
            pass

    parser = argparse.ArgumentParser(description="Batch style checker for Korean novel markdown files.")
    parser.add_argument("source", help="Folder containing .md files")
    parser.add_argument("--long-sentence-limit", type=int, default=250)
    parser.add_argument("--output-prefix", default="style_review")
    args = parser.parse_args()

    source = Path(args.source)
    files = sorted(source.rglob("*.md"))
    if not files:
        print("No markdown files found.")
        return 1

    all_issues: list[Issue] = []
    summaries: list[dict] = []

    for path in files:
        print(f"[분석] {path.name}")
        issues, summary = analyze_file(path, args.long_sentence_limit)
        all_issues.extend(issues)
        summaries.append(summary)

    issue_csv = source / f"{args.output_prefix}_issues.csv"
    issue_json = source / f"{args.output_prefix}_issues.json"
    habit_csv = source / f"{args.output_prefix}_habits.csv"
    habit_json = source / f"{args.output_prefix}_habits.json"
    merged_csv = source / "combined_review.csv"

    write_csv(issue_csv, all_issues)
    write_json(issue_json, [asdict(row) for row in all_issues])
    write_habit_csv(habit_csv, summaries)
    write_json(habit_json, summaries)

    merged = merge_with_spelling(all_issues, source)
    with merged_csv.open("w", newline="", encoding="utf-8-sig") as fp:
        writer = csv.writer(fp)
        writer.writerow(["파일명", "유형", "심각도", "문제표현", "제안", "문맥", "횟수"])
        for item in merged:
            writer.writerow(
                [
                    item["file_name"],
                    item["type"],
                    item["severity"],
                    item["text"],
                    item["suggestion"],
                    item["context"],
                    item["count"],
                ]
            )

    print(f"완료: {len(all_issues)}개 이슈")
    print(issue_csv)
    print(habit_csv)
    print(merged_csv)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
