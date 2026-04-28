param(
    [string] $SourceRoot = (Split-Path -Parent $PSScriptRoot),
    [string] $LiveRoot = "Z:\\docker\\destever"
)

$sourceTheme = Join-Path $SourceRoot "wp-content\\themes\\Avada-Child"
$liveTheme = Join-Path $LiveRoot "wp-content\\themes\\Avada-Child"

if (-not (Test-Path $sourceTheme)) {
    Write-Error "Source theme not found: $sourceTheme"
    exit 1
}

if (-not (Test-Path $liveTheme)) {
    New-Item -ItemType Directory -Force -Path $liveTheme | Out-Null
}

robocopy $sourceTheme $liveTheme /MIR /XD ".git"

if ($LASTEXITCODE -gt 7) {
    Write-Error "robocopy failed with exit code $LASTEXITCODE"
    exit $LASTEXITCODE
}

Write-Host "Theme synced to live WordPress folder."
