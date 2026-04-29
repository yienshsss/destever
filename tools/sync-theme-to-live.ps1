param(
    [string] $SourceRoot = (Split-Path -Parent $PSScriptRoot),
    [string] $LiveRoot = "Z:\\docker\\destever"
)

$sourceTheme = Join-Path $SourceRoot "wp-content\\themes\\Avada-Child"
$liveTheme = Join-Path $LiveRoot "wp-content\\themes\\Avada-Child"
$sourceMuPlugins = Join-Path $SourceRoot "wp-content\\mu-plugins"
$liveMuPlugins = Join-Path $LiveRoot "wp-content\\mu-plugins"

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

if (Test-Path $sourceMuPlugins) {
    if (-not (Test-Path $liveMuPlugins)) {
        New-Item -ItemType Directory -Force -Path $liveMuPlugins | Out-Null
    }

    robocopy $sourceMuPlugins $liveMuPlugins /MIR

    if ($LASTEXITCODE -gt 7) {
        Write-Error "mu-plugins robocopy failed with exit code $LASTEXITCODE"
        exit $LASTEXITCODE
    }
}

Write-Host "Theme and mu-plugins synced to live WordPress folder."
