param(
    [ValidateSet("sync", "up", "down", "restart", "logs", "status", "wp", "repair-urls", "refresh-db")]
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
$localSiteUrl = "http://localhost:8160"

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
            "SYNOLOGY_SSH_HOST="
            "SYNOLOGY_SSH_PORT=22"
            "SYNOLOGY_SSH_USER=admin"
            "SYNOLOGY_DB_CONTAINER=Destever-DB"
            "SYNOLOGY_DB_NAME=destever_db"
            "SYNOLOGY_DB_USER="
            "SYNOLOGY_DB_PASSWORD="
            "SYNOLOGY_SUDO_PASSWORD="
        ) | Set-Content -Path $envFile -Encoding ascii
        return
    }

    $defaults = [ordered]@{
        WORDPRESS_DB_NAME     = "destever_db"
        WORDPRESS_DB_USER     = "wordpress"
        WORDPRESS_DB_PASSWORD = "change-me"
        MYSQL_ROOT_PASSWORD   = "change-me-too"
        WORDPRESS_DEBUG       = "0"
        SYNOLOGY_SSH_HOST     = ""
        SYNOLOGY_SSH_PORT     = "22"
        SYNOLOGY_SSH_USER     = "admin"
        SYNOLOGY_DB_CONTAINER = "Destever-DB"
        SYNOLOGY_DB_NAME      = "destever_db"
        SYNOLOGY_DB_USER      = ""
        SYNOLOGY_DB_PASSWORD  = ""
        SYNOLOGY_SUDO_PASSWORD = ""
    }

    $existingValues = @{}
    foreach ($line in Get-Content -Path $envFile) {
        if ($line -match '^\s*#' -or $line -notmatch '=') {
            continue
        }

        $parts = $line -split '=', 2
        $key = $parts[0].Trim()
        $value = ""
        if ($parts.Count -gt 1) {
            $value = $parts[1]
        }
        $existingValues[$key] = $value
    }

    $outputLines = @()
    foreach ($entry in $defaults.GetEnumerator()) {
        $value = $entry.Value
        if ($existingValues.ContainsKey($entry.Key)) {
            $value = $existingValues[$entry.Key]
        }
        $outputLines += "$($entry.Key)=$value"
    }

    foreach ($entry in $existingValues.GetEnumerator()) {
        if ($defaults.Keys -notcontains $entry.Key) {
            $outputLines += "$($entry.Key)=$($entry.Value)"
        }
    }

    Set-Content -Path $envFile -Value $outputLines -Encoding ascii
}

function Get-EnvMap {
    Ensure-LocalEnv

    $envMap = @{}
    foreach ($line in Get-Content -Path $envFile) {
        if ($line -match '^\s*#' -or $line -notmatch '=') {
            continue
        }

        $key, $value = $line -split '=', 2
        $envMap[$key.Trim()] = $value
    }

    return $envMap
}

function Get-RequiredEnvValue {
    param(
        [hashtable] $EnvMap,
        [string] $Key
    )

    $value = ""
    if ($EnvMap.ContainsKey($Key)) {
        $value = [string] $EnvMap[$Key]
    }

    if ([string]::IsNullOrWhiteSpace($value)) {
        throw "Required setting missing in ${envFile}: $Key"
    }

    return $value.Trim()
}

function Convert-ToShellSingleQuotedValue {
    param(
        [string] $Value
    )

    return "'" + $Value.Replace("'", "'\''") + "'"
}

function Sync-LiveWpContent {
    if (-not (Test-Path $liveWpContent)) {
        throw "Live wp-content path not found: $liveWpContent"
    }

    Write-Host "Syncing live wp-content from $liveWpContent"
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

    Write-Host "Overlaying Git-managed wp-content from $repoWpContent"
    & robocopy $repoWpContent $localWpContent /E /R:1 /W:1 | Out-Null
    if ($LASTEXITCODE -gt 7) {
        throw "repo override sync failed with exit code $LASTEXITCODE"
    }
}

function Sync-LiveDbData {
    if (-not (Test-Path $liveDbRoot)) {
        throw "Live db path not found: $liveDbRoot"
    }

    Write-Host "Syncing live DB data from $liveDbRoot"
    New-Item -ItemType Directory -Force -Path $localDbRoot | Out-Null

    & robocopy $liveDbRoot $localDbRoot /MIR | Out-Null
    if ($LASTEXITCODE -gt 7) {
        throw "db sync failed with exit code $LASTEXITCODE"
    }
}

function Test-LocalDbSeeded {
    if (-not (Test-Path $localDbRoot)) {
        return $false
    }

    $entries = Get-ChildItem -Force -Path $localDbRoot -ErrorAction SilentlyContinue
    return ($entries | Measure-Object).Count -gt 0
}

function Invoke-Compose {
    param(
        [string[]] $ComposeArgs
    )

    Ensure-LocalEnv
    Write-Host "Running docker compose $($ComposeArgs -join ' ')"
    docker compose --env-file $envFile -f $composeFile @ComposeArgs
}

function Invoke-WordPressPhp {
    param(
        [string] $PhpCode
    )

    $script = @"
<?php
require '/var/www/html/wp-load.php';
$PhpCode
"@

    $script | docker exec -i destever-local-wordpress php
}

function Wait-ForLocalWordPress {
    $maxAttempts = 30

    for ($attempt = 1; $attempt -le $maxAttempts; $attempt++) {
        try {
            docker exec destever-local-wordpress sh -lc "test -f /var/www/html/wp-load.php" | Out-Null
            if ($LASTEXITCODE -eq 0) {
                return
            }
        } catch {
        }

        Start-Sleep -Seconds 2
    }

    throw "WordPress core files did not become ready inside the local container."
}

function Wait-ForLocalDb {
    $maxAttempts = 30

    for ($attempt = 1; $attempt -le $maxAttempts; $attempt++) {
        try {
            docker exec destever-local-db sh -lc "mariadb -u root -e 'SELECT 1'" | Out-Null
            if ($LASTEXITCODE -eq 0) {
                return
            }
        } catch {
        }

        Start-Sleep -Seconds 2
    }

    throw "Local MariaDB did not become ready inside the local container."
}

function Invoke-SynologyDumpToLocalImport {
    $envMap = Get-EnvMap

    $sshHost = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_SSH_HOST"
    $sshPort = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_SSH_PORT"
    $sshUser = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_SSH_USER"
    $remoteContainer = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_DB_CONTAINER"
    $remoteDbName = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_DB_NAME"
    $remoteDbUser = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_DB_USER"
    $remoteDbPassword = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_DB_PASSWORD"
    $sudoPassword = Get-RequiredEnvValue -EnvMap $envMap -Key "SYNOLOGY_SUDO_PASSWORD"
    $localDbName = Get-RequiredEnvValue -EnvMap $envMap -Key "WORDPRESS_DB_NAME"

    Write-Host "Starting local DB container for import"
    Invoke-Compose -ComposeArgs @("up", "-d", "db")
    Wait-ForLocalDb

    Write-Host "Resetting local database $localDbName"
    $resetSql = "DROP DATABASE IF EXISTS ``{0}``; CREATE DATABASE ``{0}``;" -f $localDbName
    $escapedResetSql = $resetSql.Replace("'", "''")
    docker exec destever-local-db sh -lc "mariadb -u root -e '$escapedResetSql'" | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to reset local database $localDbName"
    }

    $sudoPasswordArg = Convert-ToShellSingleQuotedValue $sudoPassword
    $remoteContainerArg = Convert-ToShellSingleQuotedValue $remoteContainer
    $remoteDbUserArg = Convert-ToShellSingleQuotedValue $remoteDbUser
    $remoteDbPasswordArg = Convert-ToShellSingleQuotedValue $remoteDbPassword
    $remoteDbNameArg = Convert-ToShellSingleQuotedValue $remoteDbName
    $remoteCommand = "printf '%s\n' $sudoPasswordArg | sudo -S -p '' /usr/local/bin/docker exec $remoteContainerArg mariadb-dump --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 -u$remoteDbUserArg -p$remoteDbPasswordArg $remoteDbNameArg"
    $sshArguments = "-p $sshPort $sshUser@$sshHost $remoteCommand"
    $dockerArguments = "exec -i destever-local-db sh -lc ""exec mariadb -u root $localDbName"""

    Write-Host "Streaming mysqldump from ${sshUser}@${sshHost}:${sshPort} ($remoteContainer) into local MariaDB"

    $sshStartInfo = New-Object System.Diagnostics.ProcessStartInfo
    $sshStartInfo.FileName = "ssh"
    $sshStartInfo.Arguments = $sshArguments
    $sshStartInfo.UseShellExecute = $false
    $sshStartInfo.RedirectStandardOutput = $true
    $sshStartInfo.RedirectStandardError = $true
    $sshProcess = New-Object System.Diagnostics.Process
    $sshProcess.StartInfo = $sshStartInfo
    $sshProcess.Start() | Out-Null

    $dockerStartInfo = New-Object System.Diagnostics.ProcessStartInfo
    $dockerStartInfo.FileName = "docker"
    $dockerStartInfo.Arguments = $dockerArguments
    $dockerStartInfo.UseShellExecute = $false
    $dockerStartInfo.RedirectStandardInput = $true
    $dockerStartInfo.RedirectStandardOutput = $true
    $dockerStartInfo.RedirectStandardError = $true
    $dockerProcess = New-Object System.Diagnostics.Process
    $dockerProcess.StartInfo = $dockerStartInfo
    $dockerProcess.Start() | Out-Null

    $sshErrorTask = $sshProcess.StandardError.ReadToEndAsync()
    $dockerErrorTask = $dockerProcess.StandardError.ReadToEndAsync()
    $copyTask = $sshProcess.StandardOutput.BaseStream.CopyToAsync($dockerProcess.StandardInput.BaseStream)
    $copyTask.GetAwaiter().GetResult()
    $dockerProcess.StandardInput.Close()

    $sshProcess.WaitForExit()
    $dockerProcess.WaitForExit()

    $sshError = $sshErrorTask.GetAwaiter().GetResult().Trim()
    $dockerError = $dockerErrorTask.GetAwaiter().GetResult().Trim()

    if ($sshProcess.ExitCode -ne 0) {
        throw "Remote mysqldump failed.`n$sshError"
    }

    if ($dockerProcess.ExitCode -ne 0) {
        throw "Local DB import failed.`n$dockerError"
    }

    Write-Host "Local DB refreshed from Synology mysqldump"
}

function Update-LocalSiteUrls {
    Wait-ForLocalWordPress
    Write-Host "Detecting source site URL from copied DB"

    $sourceSiteUrl = ""
    for ($attempt = 1; $attempt -le 20; $attempt++) {
        try {
            $sourceSiteUrl = Invoke-WordPressPhp -PhpCode "echo (string) get_option('home');"
            $sourceSiteUrl = ($sourceSiteUrl | Out-String).Trim()
            if ($sourceSiteUrl) {
                break
            }
        } catch {
        }

        Start-Sleep -Seconds 2
    }

    if (-not $sourceSiteUrl) {
        throw "Could not detect source site URL from local DB."
    }

    if ($sourceSiteUrl -notmatch '^https?://') {
        throw "Detected invalid source site URL while preparing local URLs: $sourceSiteUrl"
    }

    if ($sourceSiteUrl -eq $localSiteUrl) {
        Write-Host "Local site URLs already point to $localSiteUrl"
        return
    }

    Write-Host "Rewriting serialized site URLs to $localSiteUrl"
    $escapedSource = $sourceSiteUrl.Replace('\', '\\').Replace("'", "\'")
    $escapedTarget = $localSiteUrl.Replace('\', '\\').Replace("'", "\'")

    Invoke-WordPressPhp -PhpCode @"
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

    \$columns = \$wpdb->get_results("SHOW COLUMNS FROM {\$table}", ARRAY_A);
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
    \$rows = \$wpdb->get_results("SELECT " . implode(', ', array_map(fn(\$col) => \"`\$col`\", \$select_columns)) . " FROM `{\$table}`", ARRAY_A);

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

\$from = '$escapedSource';
\$to   = '$escapedTarget';

foreach (\$wpdb->tables('all') as \$table) {
    if (! \$wpdb->get_var(\$wpdb->prepare('SHOW TABLES LIKE %s', \$table))) {
        continue;
    }

    project_b_replace_urls_in_table(\$table, \$from, \$to);
}

update_option('home', \$to);
update_option('siteurl', \$to);
echo "search-replace complete";
"@ | Out-Null

    Write-Host "Local site URLs replaced: $sourceSiteUrl -> $localSiteUrl"
}

switch ($Action) {
    "sync" {
        Ensure-LocalEnv
        Sync-LiveWpContent
        Sync-RepoOverrides
        Invoke-SynologyDumpToLocalImport
        Write-Host "Local test wp-content synced from live mount and DB refreshed from Synology dump."
    }
    "up" {
        Ensure-LocalEnv
        Invoke-Compose -ComposeArgs @("down")
        Sync-LiveWpContent
        Sync-RepoOverrides
        if (-not (Test-LocalDbSeeded)) {
            Write-Host "Local DB is empty, importing a fresh Synology dump"
            Invoke-SynologyDumpToLocalImport
        } else {
            Write-Host "Reusing existing local DB snapshot"
        }
        Invoke-Compose -ComposeArgs @("up", "-d")
        Update-LocalSiteUrls
        Write-Host "Local test site started at http://localhost:8160"
    }
    "refresh-db" {
        Ensure-LocalEnv
        Invoke-SynologyDumpToLocalImport
    }
    "repair-urls" {
        Update-LocalSiteUrls
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

        throw "The wp action is not supported in this local Docker workflow yet. Run the equivalent check through docker exec or extend Invoke-WordPressPhp."
    }
}
