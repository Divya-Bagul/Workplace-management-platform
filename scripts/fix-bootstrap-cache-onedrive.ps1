# Fixes Laravel "bootstrap/cache must be present and writable" when the project
# lives under OneDrive: PHP's is_writable() often returns false there even though
# the folder exists. This re-homes the cache to %LOCALAPPDATA% and uses a junction.

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$cacheLink = Join-Path $projectRoot 'bootstrap\cache'
$cacheTarget = Join-Path $env:LOCALAPPDATA 'zylitix-bootstrap-cache'

Write-Host "Project: $projectRoot"
Write-Host "Cache target: $cacheTarget"

if (-not (Test-Path $cacheTarget)) {
    New-Item -ItemType Directory -Path $cacheTarget | Out-Null
}

$gitignore = Join-Path $cacheTarget '.gitignore'
if (-not (Test-Path $gitignore)) {
    @'
*
!.gitignore
'@ | Set-Content -Path $gitignore -Encoding utf8
}

function Test-IsOurBootstrapCacheJunction {
    param([string]$Path, [string]$ExpectedTarget)
    if (-not (Test-Path $Path)) { return $false }
    try {
        $i = Get-Item -LiteralPath $Path -Force -ErrorAction Stop
        if ($i.LinkType -eq 'Junction' -and $i.Target -and $i.Target.Count -gt 0) {
            $t = [System.IO.Path]::GetFullPath($i.Target[0])
            $e = [System.IO.Path]::GetFullPath($ExpectedTarget)
            return ($t -ieq $e)
        }
    } catch { }
    return $false
}

if (Test-IsOurBootstrapCacheJunction -Path $cacheLink -ExpectedTarget $cacheTarget) {
    Write-Host "bootstrap\cache already points to $cacheTarget. Nothing to do."
    exit 0
}

if (Test-Path $cacheLink) {
    Write-Host "Clearing read-only flags under bootstrap\cache ..."
    attrib -R "$cacheLink\*" /S /D 2>$null
    attrib -R "$cacheLink" /D 2>$null

    Write-Host "Moving existing cache files to $cacheTarget ..."
    Get-ChildItem -Path $cacheLink -Force | ForEach-Object {
        $dest = Join-Path $cacheTarget $_.Name
        if (Test-Path $dest) { Remove-Item $dest -Force -Recurse }
        Move-Item -LiteralPath $_.FullName -Destination $dest -Force
    }
    Remove-Item -LiteralPath $cacheLink -Force
}

Write-Host "Creating junction: $cacheLink -> $cacheTarget"
cmd /c "mklink /J `"$cacheLink`" `"$cacheTarget`""

Write-Host "Done. Run: composer dump-autoload"
$phpPath = $cacheLink.Replace('\', '/')
php -r "echo (is_writable('$phpPath') ? 'PHP sees cache as writable.' : 'PHP still reports not writable; move project outside OneDrive.'), PHP_EOL;"
