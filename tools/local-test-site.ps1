param(
    [ValidateSet("sync", "up", "down", "restart", "logs", "status", "wp")]
    [string] $Action = "status",

    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $Args
)

$ErrorActionPreference = "Stop"

$repoRoot = Split-Path -Parent $PSScriptRoot
$composeFile = Join-Path $repoRoot "docker\compose.local.yml"
$envFile = Join-Path $repoRoot ".local\.env"
$localRoot = Join-Path $repoRoot ".local"
$localWpContent = Join-Path $localRoot "wp-content"
$localDbRoot = Join-Path $localRoot "db"
$repoWpContent = Join-Path $repoRoot "wp-content"
$liveWpContent = "Z:\docker\destever\wp-content"
$liveDbRoot = "Z:\docker\destever\db"

function Ensure-LocalEnv {
    if (-not (Test-Path $localRoot)) {
        New-Item -ItemType Directory -Force -Path $localRoot | Out-Null
    }

    if (-not (Test-Path $envFile)) {
        @(
            "WORDPRESS_DB_NAME=destever_db"
            "WORDPRESS_DB_USER=wordpress"
            "WORDPRESS_DB_PASSWORD=change-me"
            "MYSQL_ROOT_PASSWORD=change-me-too"
            "WORDPRESS_DEBUG=0"
        ) | Set-Content -Path $envFile -Encoding ascii
    }
}

function Sync-LiveWpContent {
    if (-not (Test-Path $liveWpContent)) {
        throw "Live wp-content path not found: $liveWpContent"
    }

    New-Item -ItemType Directory -Force -Path $localWpContent | Out-Null

    & robocopy $liveWpContent $localWpContent /MIR /XD "uploads" "cache" "upgrade" "upgrade-temp-backup" | Out-Null
    if ($LASTEXITCODE -gt 7) {
        throw "wp-content sync failed with exit code $LASTEXITCODE"
    }
}

function Sync-RepoOverrides {
    if (-not (Test-Path $repoWpContent)) {
        throw "Repo wp-content path not found: $repoWpContent"
    }

    & robocopy $repoWpContent $localWpContent /E /R:1 /W:1 | Out-Null
    if ($LASTEXITCODE -gt 7) {
        throw "repo override sync failed with exit code $LASTEXITCODE"
    }
}

function Sync-LiveDbData {
    if (-not (Test-Path $liveDbRoot)) {
        throw "Live db path not found: $liveDbRoot"
    }

    New-Item -ItemType Directory -Force -Path $localDbRoot | Out-Null

    & robocopy $liveDbRoot $localDbRoot /MIR | Out-Null
    if ($LASTEXITCODE -gt 7) {
        throw "db sync failed with exit code $LASTEXITCODE"
    }
}

function Invoke-Compose {
    param(
        [string[]] $ComposeArgs
    )

    Ensure-LocalEnv
    docker compose --env-file $envFile -f $composeFile @ComposeArgs
}

switch ($Action) {
    "sync" {
        Ensure-LocalEnv
        Sync-LiveWpContent
        Sync-RepoOverrides
        Sync-LiveDbData
        Write-Host "Local test wp-content and db synced from live mount."
    }
    "up" {
        Ensure-LocalEnv
        Invoke-Compose -ComposeArgs @("down")
        Sync-LiveWpContent
        Sync-RepoOverrides
        Sync-LiveDbData
        Invoke-Compose -ComposeArgs @("up", "-d")
        Write-Host "Local test site started at http://localhost:8160"
    }
    "down" {
        Invoke-Compose -ComposeArgs @("down")
    }
    "restart" {
        Invoke-Compose -ComposeArgs @("restart")
    }
    "logs" {
        Invoke-Compose -ComposeArgs @("logs", "-f")
    }
    "status" {
        Invoke-Compose -ComposeArgs @("ps")
    }
    "wp" {
        if (-not $Args -or $Args.Count -eq 0) {
            throw "Usage: .\tools\local-test-site.ps1 wp plugin list"
        }

        docker exec -i destever-local-wordpress wp --allow-root @Args
    }
}
