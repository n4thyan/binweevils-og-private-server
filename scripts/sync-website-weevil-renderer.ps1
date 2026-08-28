param(
    [string]$SourceRoot = "C:\Users\pc\Desktop\project-binweevils-html5\reference\demo-html5\BWR_HTML5_the_peel_room_layers_and_auth_fix\public\weevil-creator"
)

$ErrorActionPreference = "Stop"
$RepoRoot = Split-Path -Parent $PSScriptRoot
$Destination = Join-Path $RepoRoot "game-full\weevil-creator"

if (-not (Test-Path $SourceRoot)) {
    throw "Weevil renderer source folder was not found: $SourceRoot"
}

$required = @(
    "src\runtime\WeevilDef.js",
    "src\runtime\WeevilCanvasRenderer.js",
    "src\runtime\canvasAtlasLoader.js",
    "assets\atlases\manifest.json",
    "assets\raw\misc\upper_leg.png",
    "assets\raw\misc\lower_leg.png",
    "assets\raw\misc\lower_leg_stripy.png"
)

foreach ($relative in $required) {
    $candidate = Join-Path $SourceRoot $relative
    if (-not (Test-Path $candidate)) {
        throw "Required renderer file is missing: $candidate"
    }
}

$pngFiles = Get-ChildItem -Path (Join-Path $SourceRoot "assets") -Recurse -Filter *.png -File
foreach ($png in $pngFiles) {
    $head = [System.IO.File]::ReadAllBytes($png.FullName)
    if ($head.Length -lt 8 -or $head[0] -ne 137 -or $head[1] -ne 80 -or $head[2] -ne 78 -or $head[3] -ne 71) {
        throw "Renderer PNG is not materialised (Git LFS pointer or invalid image): $($png.FullName)"
    }
}

if (Test-Path $Destination) {
    Remove-Item $Destination -Recurse -Force
}

New-Item -ItemType Directory -Path $Destination -Force | Out-Null
Copy-Item -Path (Join-Path $SourceRoot "src") -Destination $Destination -Recurse -Force
Copy-Item -Path (Join-Path $SourceRoot "assets") -Destination $Destination -Recurse -Force

Write-Host "Website Weevil renderer synced successfully."
Write-Host "Source:      $SourceRoot"
Write-Host "Destination: $Destination"
Write-Host "The website will now render the OG users.def value through the verified HTML5 renderer."
