import * as PIXI from "https://cdn.jsdelivr.net/npm/pixi.js@8.x/dist/pixi.mjs";

export async function loadAtlasMap(basePath, manifestPath = "assets/atlases/manifest.json") {
  const manifestUrl = `${basePath.replace(/\/$/, "")}/${manifestPath}`;
  const manifest = await fetch(manifestUrl).then((r) => r.json());

  const atlasMap = new Map();

  for (const [folder, entry] of Object.entries(manifest)) {
    const jsonUrl = `${basePath.replace(/\/$/, "")}/assets/atlases/${entry.json}`;
    const pngUrl = `${basePath.replace(/\/$/, "")}/assets/atlases/${entry.png}`;

    const data = await fetch(jsonUrl).then((r) => r.json());
    const texture = await PIXI.Assets.load(pngUrl);
    const sheet = new PIXI.Spritesheet(texture.baseTexture ?? texture.source ?? texture, data);
    await sheet.parse();
    atlasMap.set(folder, sheet);
    atlasMap.set(folder.replaceAll("/", "_"), sheet);
  }

  return atlasMap;
}

export function getFrame(sheet, frameName) {
  if (!sheet) {
    throw new Error("Missing spritesheet");
  }
  const texture = sheet.textures[frameName];
  if (!texture) {
    throw new Error(`Missing frame ${frameName}`);
  }
  return texture;
}
