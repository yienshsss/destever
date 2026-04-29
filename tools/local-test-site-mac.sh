#!/usr/bin/env bash
set -euo pipefail

ACTION="${1:-status}"
shift || true

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="$REPO_ROOT/docker/compose.macos-test.yml"
TEST_ROOT="${PROJECT_B_TEST_ROOT:-/Volumes/KY 외장하드/SynologyDrive/04. 프로젝트/Codex/Destever test}"
ENV_FILE="$TEST_ROOT/.env"
LOCAL_WP_CONTENT="$TEST_ROOT/wp-content"
LOCAL_DB_ROOT="$TEST_ROOT/db"
LOCAL_REDIS_ROOT="$TEST_ROOT/redis"
REPO_WP_CONTENT="$REPO_ROOT/wp-content"
LIVE_ROOT="${PROJECT_B_LIVE_ROOT:-/Users/yien/Library/CloudStorage/CloudMounter-NAS/docker/destever}"
LIVE_WP_CONTENT="$LIVE_ROOT/wp-content"
LIVE_DB_ROOT="$LIVE_ROOT/db"
PROJECT_B_TEST_PORT="${PROJECT_B_TEST_PORT:-8161}"

ensure_env() {
  mkdir -p "$TEST_ROOT" "$LOCAL_WP_CONTENT" "$LOCAL_DB_ROOT" "$LOCAL_REDIS_ROOT"

  if [[ ! -f "$ENV_FILE" ]]; then
    cat > "$ENV_FILE" <<EOF
WORDPRESS_DB_NAME=destever_db
WORDPRESS_DB_USER=wordpress
WORDPRESS_DB_PASSWORD=change-me
MYSQL_ROOT_PASSWORD=change-me-too
WORDPRESS_DEBUG=0
PROJECT_B_TEST_PORT=$PROJECT_B_TEST_PORT
PROJECT_B_TEST_ROOT=$TEST_ROOT
PROJECT_B_LIVE_WP_CONTENT=$LIVE_WP_CONTENT
EOF
  fi
}

require_docker() {
  if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is not installed or not on PATH. Install Docker Desktop for Mac, then retry." >&2
    exit 1
  fi
}

sync_live_wp_content() {
  if [[ ! -d "$LIVE_WP_CONTENT" ]]; then
    echo "Live wp-content path not found: $LIVE_WP_CONTENT" >&2
    exit 1
  fi

  mkdir -p "$LOCAL_WP_CONTENT"
  # Do not copy plugins, languages, uploads, or the parent theme through
  # CloudMounter. The compose file mounts those live files read-only.
  mkdir -p "$LOCAL_WP_CONTENT/themes" "$LOCAL_WP_CONTENT/mu-plugins"
}

sync_repo_overrides() {
  if [[ ! -d "$REPO_WP_CONTENT" ]]; then
    echo "Repo wp-content path not found: $REPO_WP_CONTENT" >&2
    exit 1
  fi

  mkdir -p "$LOCAL_WP_CONTENT"
  rsync -rlt "$REPO_WP_CONTENT/" "$LOCAL_WP_CONTENT/"
}

sync_live_db_data() {
  if [[ ! -d "$LIVE_DB_ROOT" ]]; then
    echo "Live db path not found: $LIVE_DB_ROOT" >&2
    exit 1
  fi

  mkdir -p "$LOCAL_DB_ROOT"
  rsync -rlt --delete "$LIVE_DB_ROOT/" "$LOCAL_DB_ROOT/"
}

compose() {
  ensure_env
  PROJECT_B_TEST_ROOT="$TEST_ROOT" PROJECT_B_TEST_PORT="$PROJECT_B_TEST_PORT" PROJECT_B_LIVE_WP_CONTENT="$LIVE_WP_CONTENT" \
    docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

case "$ACTION" in
  sync)
    ensure_env
    sync_live_wp_content
    sync_repo_overrides
    sync_live_db_data
    echo "Mac test wp-content and db synced into: $TEST_ROOT"
    ;;
  up)
    require_docker
    ensure_env
    compose down
    sync_live_wp_content
    sync_repo_overrides
    sync_live_db_data
    compose up -d
    echo "Mac test site started at http://localhost:$PROJECT_B_TEST_PORT"
    ;;
  down)
    require_docker
    compose down
    ;;
  restart)
    require_docker
    compose restart
    ;;
  logs)
    require_docker
    compose logs -f
    ;;
  status)
    require_docker
    compose ps
    ;;
  wp)
    require_docker
    if [[ "$#" -eq 0 ]]; then
      echo "Usage: tools/local-test-site-mac.sh wp plugin list" >&2
      exit 1
    fi
    docker exec -i destever-mac-test-wordpress wp --allow-root "$@"
    ;;
  *)
    echo "Usage: $0 {sync|up|down|restart|logs|status|wp}" >&2
    exit 1
    ;;
esac
