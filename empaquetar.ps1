# Arma dist\tnlcomputer-app.zip listo para subir al hosting (con vendor y assets compilados).
# Uso (PowerShell, desde la raíz del proyecto):  .\empaquetar.ps1
$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

Write-Host 'Compilando CSS/JS...'
npm run build
if ($LASTEXITCODE) { throw 'Falló npm run build' }

Write-Host 'Preparando copia limpia...'
$tmp = Join-Path $env:TEMP 'tnl-dist'
if (Test-Path $tmp) { Remove-Item $tmp -Recurse -Force }
New-Item -ItemType Directory $tmp | Out-Null

robocopy . $tmp /E /NFL /NDL /NJH /NJS /XD node_modules vendor dist tests .git storage uploads _para_eliminar /XF .env database.sqlite empaquetar.ps1 *.log .phpunit.result.cache | Out-Null

# storage: solo la estructura de carpetas, sin logs, sesiones ni caché locales
robocopy storage "$tmp\storage" /E /NFL /NDL /NJH /NJS /XF *.log *.php-* | Out-Null
Get-ChildItem "$tmp\storage\framework" -Recurse -File | Where-Object Name -ne '.gitignore' | Remove-Item -Force
# Caché de arranque generada en esta PC (lista paquetes de desarrollo): Laravel la regenera en el servidor
Get-ChildItem "$tmp\bootstrap\cache" -File -Filter *.php | Remove-Item -Force

Write-Host 'Instalando dependencias de producción...'
Push-Location $tmp
composer install --no-dev --optimize-autoloader --no-interaction --quiet
if ($LASTEXITCODE) { Pop-Location; throw 'Falló composer install' }
Pop-Location

New-Item -ItemType Directory dist -Force | Out-Null
$zip = Join-Path (Resolve-Path dist) 'tnlcomputer-app.zip'
if (Test-Path $zip) { Remove-Item $zip }
& "$env:SystemRoot\System32\tar.exe" -a -c -f $zip -C $tmp .
Remove-Item $tmp -Recurse -Force

$mb = [math]::Round((Get-Item $zip).Length / 1MB, 1)
Write-Host "Listo: $zip ($mb MB)"
