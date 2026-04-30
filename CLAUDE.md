# Destever — Claude/Codex 작업 지침

## 역할 분담

- **Claude**: 설계, 진단, 계획 수립. 코드 직접 작성 최소화.
- **Codex**: 실제 코드 작성 및 수정 실행.

Claude가 설계한 내용은 `docs/`에 기록되고, Codex는 해당 문서를 컨텍스트로 받아 실행한다.

---

## 저장소 구조 및 추적 범위

Git으로 추적하는 것:
- `wp-content/themes/Avada-Child/` — 커스텀 차일드 테마 (header.php 포함)
- `wp-content/mu-plugins/` — 필수 플러그인 (구조 보수 코드)
- `tools/` — 배포 및 테스트 스크립트
- `docs/` — 워크플로우 및 설계 문서
- `docker/` — Docker Compose 예시 설정

Git으로 추적하지 않는 것:
- WordPress 코어 (`wp-admin/`, `wp-includes/`)
- DB, 업로드, 캐시, Redis 데이터
- 시크릿 (`.env`, `wp-config.php`)
- `.local/` 폴더 (로컬 테스트 런타임)

---

## 환경 구성

### Windows (회사, 평일 08:00–16:30)
- 작업 경로: `C:\Users\nero_\OneDrive\Desktop\destever-source`
- 라이브 마운트: `Z:\docker\destever` (Synology NAS WebDAV)
- 로컬 테스트: `http://localhost:8160`
- Git remote: `git@github-personal:yienshsss/destever.git`

### Mac Mini (집, 평일 퇴근 후 + 주말)
- 작업 경로: `/Users/yien/Documents/work/destever-source`
- 로컬 테스트: `http://localhost:8161`
- 런타임 데이터: `~/.local/share/destever-test/`
- Git remote: `git@github-personal:yienshsss/destever.git`

### Synology NAS (라이브 서버)
- WordPress 컨테이너: `Destever` (포트 8159)
- DB 컨테이너: `Destever-DB` (MariaDB 11.4)
- SSH: `ssh synology` (alias 설정 완료, 양쪽 모두)
- 라이브 경로: `/volume1/docker/destever/`

---

## 표준 워크플로우

### 작업 시작
```
git pull
```

### 코드 수정 후 로컬 테스트
```
# Windows
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 up

# Mac
./tools/local-test-site-mac.sh up
```

### 라이브 반영
```
git add / commit / push
powershell -ExecutionPolicy Bypass -File .\tools\sync-theme-to-live.ps1
```

### DB 최신화 (라이브 글 변경 후)
```
# Windows
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 refresh-db

# Mac
./tools/local-test-site-mac.sh refresh-db
```

### URL 보정 (로컬 테스트 중 스타일 깨질 때)
```
# Windows
powershell -ExecutionPolicy Bypass -File .\tools\local-test-site.ps1 repair-urls

# Mac
./tools/local-test-site-mac.sh repair-urls
```

---

## 절대 규칙

1. `Z:\docker\destever` (라이브 마운트)를 Git working copy로 쓰지 않는다.
2. 라이브 파일을 직접 편집하지 않는다. 반드시 로컬 클론 → sync 경로를 따른다.
3. WordPress 코어, DB, 업로드, 시크릿을 Git에 커밋하지 않는다.
4. Avada 부모 테마(`wp-content/themes/Avada/`)를 직접 수정하지 않는다. 모든 커스텀은 Avada-Child 또는 mu-plugins에서 한다.
5. 작업 전 반드시 `git pull`.
6. 작업 후 반드시 `git add / commit / push`.

---

## 주요 파일 위치

| 목적 | 경로 |
|------|------|
| 차일드 테마 | `wp-content/themes/Avada-Child/` |
| 오버레이 메뉴 | `wp-content/themes/Avada-Child/functions.php` |
| 헤더 템플릿 | `wp-content/themes/Avada-Child/header.php` |
| 카테고리 템플릿 | `wp-content/themes/Avada-Child/category.php` |
| Windows 테스트 스크립트 | `tools/local-test-site.ps1` |
| Mac 테스트 스크립트 | `tools/local-test-site-mac.sh` |
| 라이브 동기화 스크립트 | `tools/sync-theme-to-live.ps1` |
| 워크플로우 상태 | `docs/workflow-status.md` |

---

## 현재 개발 과제 (우선순위 순)

1. PROS 오버레이 메뉴 서브메뉴 복원 (커미션/리퀘 항목)
2. 블로그 카테고리 페이지 레이아웃 개선 (카드→썸네일 목록)
3. 포스트 페이지 인라인 에디터 구현
