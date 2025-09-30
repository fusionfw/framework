# Update Console.php templates
$content = Get-Content "src\Console.php" -Raw
$content = $content -replace 'use Fusion\\Core\\', 'use Fusion\\'
Set-Content -Path "src\Console.php" -Value $content -NoNewline
Write-Host "Updated Console.php templates"
