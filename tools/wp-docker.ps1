param(
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $WpArgs
)

$containerName = "destever-wordpress"

if (-not $WpArgs -or $WpArgs.Count -eq 0) {
    Write-Host "Usage: .\\tools\\wp-docker.ps1 plugin list"
    exit 1
}

docker exec -i $containerName wp --allow-root @WpArgs
