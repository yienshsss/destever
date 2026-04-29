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
LOCAL_SITE_URL="http://localhost:$PROJECT_B_TEST_PORT"

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
  echo "Overlaying Git-managed wp-content from $REPO_WP_CONTENT"
  mkdir -p "$LOCAL_WP_CONTENT"
  rsync -rlt "$REPO_WP_CONTENT/" "$LOCAL_WP_CONTENT/"

  # Keep the test runtime independent from any live Redis drop-in.
  rm -f "$LOCAL_WP_CONTENT/object-cache.php"
}

compose() {
  ensure_env
  echo "Running docker compose $*"
  PROJECT_B_TEST_ROOT="$TEST_ROOT" PROJECT_B_TEST_PORT="$PROJECT_B_TEST_PORT" \
    docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

wp_cli() {
  echo "The wp action is not supported in this local Docker workflow yet." >&2
  exit 1
}

wordpress_php() {
  local php_code="$1"
  docker exec -i destever-mac-test-wordpress php <<PHP
<?php
require '/var/www/html/wp-load.php';
$php_code
PHP
}

update_local_site_urls() {
  local source_site_url
  echo "Detecting source site URL from copied DB"
  source_site_url="$(wordpress_php "echo (string) get_option('home');" | tr -d '\r')"

  if [[ -z "$source_site_url" ]]; then
    echo "Could not detect source site URL from local DB." >&2
    exit 1
  fi

  if [[ "$source_site_url" == "$LOCAL_SITE_URL" ]]; then
    echo "Local site URLs already point to $LOCAL_SITE_URL"
    return
  fi

  echo "Rewriting serialized site URLs to $LOCAL_SITE_URL"
  local escaped_source="${source_site_url//\\/\\\\}"
  escaped_source="${escaped_source//\'/\\\'}"
  local escaped_target="${LOCAL_SITE_URL//\\/\\\\}"
  escaped_target="${escaped_target//\'/\\\'}"

  wordpress_php "
function project_b_recursive_replace_urls(\$value, \$from, \$to) {
    if (is_array(\$value)) {
        foreach (\$value as \$key => \$item) {
            \$value[\$key] = project_b_recursive_replace_urls(\$item, \$from, \$to);
        }
        return \$value;
    }

    if (is_object(\$value)) {
        foreach (\$value as \$key => \$item) {
            \$value->\$key = project_b_recursive_replace_urls(\$item, \$from, \$to);
        }
        return \$value;
    }

    if (is_string(\$value)) {
        return str_replace(\$from, \$to, \$value);
    }

    return \$value;
}

function project_b_replace_urls_in_table(\$table, \$from, \$to) {
    global \$wpdb;

    \$columns = \$wpdb->get_results(\"SHOW COLUMNS FROM {\$table}\", ARRAY_A);
    \$text_columns = array();
    \$primary_keys = array();

    foreach (\$columns as \$column) {
        \$type = strtolower((string) \$column['Type']);

        if ('PRI' === \$column['Key']) {
            \$primary_keys[] = \$column['Field'];
        }

        if (false !== strpos(\$type, 'char') || false !== strpos(\$type, 'text')) {
            \$text_columns[] = \$column['Field'];
        }
    }

    if (empty(\$text_columns) || empty(\$primary_keys)) {
        return;
    }

    \$select_columns = array_merge(\$primary_keys, \$text_columns);
    \$rows = \$wpdb->get_results(\"SELECT \" . implode(', ', array_map(fn(\$col) => \"\\\"\`\$col\`\\\"\", \$select_columns)) . \" FROM \`{\$table}\`\", ARRAY_A);

    foreach (\$rows as \$row) {
        \$updates = array();

        foreach (\$text_columns as \$column) {
            if ('guid' === \$column && \$table === \$wpdb->posts) {
                continue;
            }

            \$current = \$row[\$column];
            \$decoded = maybe_unserialize(\$current);
            \$updated = is_string(\$decoded) || is_array(\$decoded) || is_object(\$decoded)
                ? project_b_recursive_replace_urls(\$decoded, \$from, \$to)
                : \$decoded;
            \$serialized = maybe_serialize(\$updated);

            if ((string) \$serialized !== (string) \$current) {
                \$updates[\$column] = \$serialized;
            }
        }

        if (empty(\$updates)) {
            continue;
        }

        \$where = array();
        foreach (\$primary_keys as \$primary_key) {
            \$where[\$primary_key] = \$row[\$primary_key];
        }

        \$wpdb->update(\$table, \$updates, \$where);
    }
}

\$from = '$escaped_source';
\$to   = '$escaped_target';

foreach (\$wpdb->tables('all') as \$table) {
    if (! \$wpdb->get_var(\$wpdb->prepare('SHOW TABLES LIKE %s', \$table))) {
        continue;
    }

    project_b_replace_urls_in_table(\$table, \$from, \$to);
}

update_option('home', \$to);
update_option('siteurl', \$to);
echo 'search-replace complete';
" >/dev/null

  echo "Local site URLs replaced: $source_site_url -> $LOCAL_SITE_URL"
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
    update_local_site_urls
    echo "Mac test site started at http://localhost:$PROJECT_B_TEST_PORT"
    ;;
  repair-urls)
    require_docker
    update_local_site_urls
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
    wp_cli "$@"
    ;;
  *)
    echo "Usage: $0 {sync|up|down|restart|logs|status|wp|repair-urls}" >&2
    exit 1
    ;;
esac
