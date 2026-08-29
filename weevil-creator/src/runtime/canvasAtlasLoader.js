export async function loadAtlasMap(basePath, manifestPath = "assets/atlases/manifest.json") {
  const manifestUrl = `${basePath.replace(/\/$/, "")}/${manifestPath}`;
  const manifest = await fetch(manifestUrl).then((r) => r.json());
  const atlasMap = new Map();

  for (const [folder, entry] of Object.entries(manifest)) {
    const jsonUrl = `${basePath.replace(/\/$/, "")}/assets/atlases/${entry.json}`;
    const pngUrl = `${basePath.replace(/\/$/, "")}/assets/atlases/${entry.png}`;

    const data = await fetch(jsonUrl).then((r) => r.json());
    const image = await loadImage(pngUrl);
    const entryObject = {
      key: folder,
      image,
      frames: data.frames,
      meta: data.meta,
    };
    atlasMap.set(folder, entryObject);
    atlasMap.set(folder.replaceAll("/", "_"), entryObject);
  }

  return atlasMap;
}

export function loadImage(src) {
  return new Promise((resolve, reject) => {
    const image = new Image();
    image.onload = () => resolve(image);
    image.onerror = reject;
    image.src = src;
  });
}

export function getFrame(sheet, frameName) {
  if (!sheet) {
    throw new Error("Missing atlas");
  }
  const frame = sheet.frames[frameName];
  if (!frame) {
    throw new Error(`Missing frame ${frameName}`);
  }
  return frame.frame;
}
