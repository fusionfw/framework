# Fix double backslashes in all PHP files
$files = Get-ChildItem -Path "src" -Recurse -Filter "*.php"
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $content = $content -replace 'Fusion\\\\\\\\', 'Fusion\\'
    Set-Content -Path $file.FullName -Value $content -NoNewline
    Write-Host "Fixed: $($file.Name)"
}
