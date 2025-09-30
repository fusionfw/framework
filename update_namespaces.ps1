# Update all PHP files in src/ directory
Get-ChildItem -Path "src" -Recurse -Filter "*.php" | ForEach-Object {
    $content = Get-Content $_.FullName -Raw
    $content = $content -replace 'namespace Fusion\\Core', 'namespace Fusion'
    $content = $content -replace 'use Fusion\\Core\\', 'use Fusion\\'
    Set-Content -Path $_.FullName -Value $content -NoNewline
    Write-Host "Updated: $($_.FullName)"
}
