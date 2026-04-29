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
EOF
  fi
}

require_docker() {
  if ! command -v docker >/dev/null 2>&1; then
    echo "Docker is not installed or not on PATH. Install Docker Desktop for Mac, then retry." >&2
    exit 1
  fi
}

check_test_runtime() {
  if [[ ! -d "$LOCAL_WP_CONTENT" ]]; then
    echo "Test wp-content path not found: $LOCAL_WP_CONTENT" >&2
    echo "Create or copy a full WordPress wp-content snapshot into the external-drive test folder first." >&2
    exit 1
  fi

  if [[ ! -d "$LOCAL_DB_ROOT" ]]; then
    echo "Test DB path not found: $LOCAL_DB_ROOT" >&2
    echo "Create or copy a DB snapshot into the external-drive test folder first." >&2
    exit 1
  fi

  if [[ ! -f "$LOCAL_WP_CONTENT/themes/Avada/functions.php" ]]; then
    echo "Avada parent theme is incomplete in the external-drive test runtime." >&2
    echo "Missing: $LOCAL_WP_CONTENT/themes/Avada/functions.php" >&2
    exit 1
  fi

  if [[ ! -f "$LOCAL_WP_CONTENT/plugins/fusion-builder/inc/bootstrap.php" ]]; then
    echo "Fusion Builder plugin is incomplete in the external-drive test runtime." >&2
    echo "Missing: $LOCAL_WP_CONTENT/plugins/fusion-builder/inc/bootstrap.php" >&2
    exit 1
  fi
}

sync_repo_overrides() {
  check_test_runtime
  mkdir -p "$LOCAL_WP_CONTENT"
  rsync -rlt "$REPO_WP_CONTENT/" "$LOCAL_WP_CONTENT/"

  # Keep the test runtime independent from any live Redis drop-in.
  rm -f "$LOCAL_WP_CONTENT/object-cache.php"
}

compose() {
  ensure_env
  PROJECT_B_TEST_ROOT="$TEST_ROOT" PROJECT_B_TEST_PORT="$PROJECT_B_TEST_PORT" \
    docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

case "$ACTION" in
  sync)
    ensure_env
    sync_repo_overrides
    echo "Git-managed wp-content overrides synced into external test runtime: $TEST_ROOT"
    ;;
  up)
    require_docker
    ensure_env
    compose down
    sync_repo_overrides
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
