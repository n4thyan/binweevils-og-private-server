import { Vec3, RAD_TO_DEG, DEG_TO_RAD, clamp, snapAngles, wrap360 } from "./math.js";
import { createCamera, projectPoint } from "./projection.js";
import { getFrame } from "./canvasAtlasLoader.js";

const BODY_CONFIG = {
  1: { atlas: "body_spheroid", render: { rxMin: 0, rxMax: 0, ryMin: 0, ryMax: 360, framesY: 37, symAxes: 1, rIncr: 5 }, bodyRoot: new Vec3(0, 191, 1), bodyYOffset: 197, legSpread: 20, headRoot: new Vec3(0, 282, 111) },
  2: { atlas: "body_cone", render: { rxMin: 0, rxMax: 50, ryMin: 0, ryMax: 360, framesY: 1, symAxes: 180, rIncr: 5 }, bodyRoot: new Vec3(0, 191, 1), bodyYOffset: 224, legSpread: 25, headRoot: new Vec3(0, 273, 162) },
  3: { atlas: "body_cone_narrow_inv", render: { rxMin: 0, rxMax: 50, ryMin: 0, ryMax: 360, framesY: 37, symAxes: 1, rIncr: 5 }, bodyRoot: new Vec3(0, 191, 1), bodyYOffset: 184, legSpread: 0, headRoot: new Vec3(0, 290, 132) },
  4: { atlas: "body_cuboid", render: { rxMin: 0, rxMax: 50, ryMin: 0, ryMax: 360, framesY: 10, symAxes: 3, rIncr: 5 }, bodyRoot: new Vec3(0, 191, 1), bodyYOffset: 212, legSpread: 20, headRoot: new Vec3(0, 290, 146) },
};

const HEAD_RENDER = { rxMin: 0, rxMax: 50, ryMin: 0, ryMax: 360, framesY: 37, symAxes: 1, rIncr: 5 };

const HEAD_SHAPES = {
  1: { atlas: "head_spheroid", maskAtlas: "head_spheroid_mask", faceYOffset: 70, faceZOffset: -20, antennaBaseY: 90, mouthY: -35, mouthZ: 64 },
  2: { atlas: "head_cone", maskAtlas: "head_spheroid_mask", faceYOffset: 80, faceZOffset: -20, antennaBaseY: 105, mouthY: -35, mouthZ: 69 },
  3: { atlas: "head_cone_inv", maskAtlas: "head_spheroid_mask", faceYOffset: 82, faceZOffset: -29, antennaBaseY: 90, mouthY: -35, mouthZ: 69 },
  4: { atlas: "head_cuboid", maskAtlas: "head_spheroid_mask", faceYOffset: 85, faceZOffset: -25, antennaBaseY: 90, mouthY: -35, mouthZ: 69 },
};

const EYE_POSITIONS = {
  1: {
    1: { probY: 0, probZ: 66, left: { pos: new Vec3(27, 15, 41), dir: new Vec3(0.38, 0.23, 1) }, right: { pos: new Vec3(-27, 15, 41), dir: new Vec3(-0.38, 0.23, 1) } },
    2: { probY: 0, probZ: 66, left: { pos: new Vec3(40, 13, 32), dir: new Vec3(0.8, 0.2, 1) }, right: { pos: new Vec3(-40, 13, 32), dir: new Vec3(-0.8, 0.2, 1) } },
    3: { probY: 30, probZ: 66, left: { pos: new Vec3(25, 40, 30), dir: new Vec3(0.42, 0.55, 1) }, right: { pos: new Vec3(-25, 40, 30), dir: new Vec3(-0.42, 0.55, 1) } },
    4: { probY: 74, probZ: 44, left: { pos: new Vec3(30, 98, 38), dir: new Vec3(0.15, 0.2, 1) }, right: { pos: new Vec3(-30, 98, 38), dir: new Vec3(-0.15, 0.2, 1) } },
    5: { probY: 74, probZ: 44, left: { pos: new Vec3(60, 100, 1), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-60, 100, 1), dir: new Vec3(0, 0.2, 1) } },
    6: { probY: 0, probZ: 66, left: { pos: new Vec3(95, 5, 38), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-95, 5, 38), dir: new Vec3(0, 0.2, 1) } },
  },
  2: {
    1: { probY: 0, probZ: 66, left: { pos: new Vec3(27, 15, 41), dir: new Vec3(0.38, 0.23, 1) }, right: { pos: new Vec3(-27, 15, 41), dir: new Vec3(-0.38, 0.23, 1) } },
    2: { probY: 0, probZ: 66, left: { pos: new Vec3(40, 13, 32), dir: new Vec3(0.8, 0.2, 1) }, right: { pos: new Vec3(-40, 13, 32), dir: new Vec3(-0.8, 0.2, 1) } },
    3: { probY: 30, probZ: 66, left: { pos: new Vec3(25, 40, 30), dir: new Vec3(0.42, 0.5, 1) }, right: { pos: new Vec3(-25, 40, 30), dir: new Vec3(-0.42, 0.5, 1) } },
    4: { probY: 68, probZ: 56, left: { pos: new Vec3(30, 95, 52), dir: new Vec3(0.15, 0.2, 1) }, right: { pos: new Vec3(-30, 95, 52), dir: new Vec3(-0.15, 0.2, 1) } },
    5: { probY: 60, probZ: 47, left: { pos: new Vec3(70, 96, 1), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-70, 96, 1), dir: new Vec3(0, 0.2, 1) } },
    6: { probY: 0, probZ: 66, left: { pos: new Vec3(95, 5, 38), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-95, 5, 38), dir: new Vec3(0, 0.2, 1) } },
  },
  3: {
    1: { probY: 0, probZ: 66, left: { pos: new Vec3(27, 15, 41), dir: new Vec3(0.38, 0.23, 1) }, right: { pos: new Vec3(-27, 15, 41), dir: new Vec3(-0.38, 0.23, 1) } },
    2: { probY: 0, probZ: 66, left: { pos: new Vec3(48, 12, 29), dir: new Vec3(0.6, 0, 0.9) }, right: { pos: new Vec3(-48, 12, 29), dir: new Vec3(-0.6, 0, 0.9) } },
    3: { probY: 30, probZ: 66, left: { pos: new Vec3(27, 40, 36), dir: new Vec3(0.35, 0.4, 1) }, right: { pos: new Vec3(-27, 40, 36), dir: new Vec3(-0.35, 0.4, 1) } },
    4: { probY: 70, probZ: 58, left: { pos: new Vec3(30, 96, 54), dir: new Vec3(0.15, 0.2, 1) }, right: { pos: new Vec3(-30, 96, 54), dir: new Vec3(-0.15, 0.2, 1) } },
    5: { probY: 46, probZ: 62, left: { pos: new Vec3(60, 92, 48), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-60, 92, 48), dir: new Vec3(0, 0.2, 1) } },
    6: { probY: 0, probZ: 66, left: { pos: new Vec3(100, 5, 38), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-100, 5, 38), dir: new Vec3(0, 0.2, 1) } },
  },
  4: {
    1: { probY: 0, probZ: 66, left: { pos: new Vec3(27, 15, 41), dir: new Vec3(0.38, 0.23, 1) }, right: { pos: new Vec3(-27, 15, 41), dir: new Vec3(-0.38, 0.23, 1) } },
    2: { probY: 0, probZ: 66, left: { pos: new Vec3(62, 13, 0), dir: new Vec3(1, 0, 0.05) }, right: { pos: new Vec3(-62, 13, 0), dir: new Vec3(-1, 0, 0.05) } },
    3: { probY: 22, probZ: 66, left: { pos: new Vec3(26, 50, 28), dir: new Vec3(0.28, 0.3, 1) }, right: { pos: new Vec3(-26, 50, 28), dir: new Vec3(-0.28, 0.3, 1) } },
    4: { probY: 50, probZ: 63, left: { pos: new Vec3(30, 84, 63), dir: new Vec3(0.15, 0.2, 1) }, right: { pos: new Vec3(-30, 84, 63), dir: new Vec3(-0.15, 0.2, 1) } },
    5: { probY: 46, probZ: 57, left: { pos: new Vec3(92, 98, 1), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-92, 98, 1), dir: new Vec3(0, 0.2, 1) } },
    6: { probY: 0, probZ: 66, left: { pos: new Vec3(100, 5, 38), dir: new Vec3(0, 0.2, 1) }, right: { pos: new Vec3(-100, 5, 38), dir: new Vec3(0, 0.2, 1) } },
  },
};

const MOUTH_ORDER = ["Mouth2_mc", "Mouth1_mc", "Mouth3_mc", "Mouth4_mc", "Mouth5_mc", "Mouth6_mc", "Mouth7_mc"];
const BASIC_ANTENNA_LAYOUTS = {
  0: [],
  1: [[new Vec3(0, 0, 0), new Vec3(0, 50, -30), new Vec3(0, 20, 0)]],
  2: [[new Vec3(0, 0, 0), new Vec3(0, 95, -46), new Vec3(0, 40, 0)]],
  3: [[new Vec3(0, 0, 0), new Vec3(0, 60, -110), new Vec3(0, 135, -40)]],
  4: [[new Vec3(0, 0, 0), new Vec3(30, 60, -26), new Vec3(12, 20, 3)], [new Vec3(0, 0, 0), new Vec3(-30, 60, -26), new Vec3(-12, 20, 3)]],
  5: [[new Vec3(0, 0, 0), new Vec3(50, 100, -46), new Vec3(10, 45, 0)], [new Vec3(0, 0, 0), new Vec3(-50, 100, -46), new Vec3(-10, 45, 0)]],
  6: [[new Vec3(0, 0, 0), new Vec3(65, 50, -71), new Vec3(40, 125, -25)], [new Vec3(0, 0, 0), new Vec3(-65, 50, -71), new Vec3(-40, 125, -25)]],
  7: [[new Vec3(0, 0, 0), new Vec3(0, 60, -30), new Vec3(0, 20, 0)], [new Vec3(0, 0, 0), new Vec3(-40, 50, -25), new Vec3(-20, 20, 0)], [new Vec3(0, 0, 0), new Vec3(40, 50, -25), new Vec3(20, 20, 0)]],
  8: [[new Vec3(0, 0, 0), new Vec3(0, 94, -35), new Vec3(0, 40, 0)], [new Vec3(0, 0, 0), new Vec3(-54, 76, -30), new Vec3(-20, 40, 0)], [new Vec3(0, 0, 0), new Vec3(54, 76, -30), new Vec3(20, 40, 0)]],
  9: [[new Vec3(0, 0, 0), new Vec3(0, 60, -110), new Vec3(0, 135, -40)], [new Vec3(0, 0, 0), new Vec3(-100, 50, -55), new Vec3(-50, 125, -25)], [new Vec3(0, 0, 0), new Vec3(100, 50, -55), new Vec3(50, 125, -25)]],
};

const SUPER_ANTENNA_LAYOUT = [
  [new Vec3(0, 0, 0), new Vec3(0, 180, -110), new Vec3(0, 255, -40)],
  [new Vec3(0, 0, 0), new Vec3(-80, 150, -55), new Vec3(-40, 225, -25)],
  [new Vec3(0, 0, 0), new Vec3(80, 150, -55), new Vec3(40, 225, -25)],
  [new Vec3(0, 0, 0), new Vec3(-100, 50, -55), new Vec3(-50, 125, -25)],
  [new Vec3(0, 0, 0), new Vec3(100, 50, -55), new Vec3(50, 125, -25)],
];



const REGISTRATION_POINTS = {
  body_spheroid: { originX: 94, originY: 94 },
  body_cone: { originX: 96, originY: 102 },
  body_cone_narrow_inv: { originX: 88, originY: 92 },
  body_cuboid: { originX: 92, originY: 96 },
  head_spheroid: { originX: 86, originY: 96 },
  head_cone: { originX: 90, originY: 104 },
  head_cone_inv: { originX: 88, originY: 92 },
  head_cuboid: { originX: 88, originY: 96 },
  misc_Prob1_mc: { originX: 14, originY: 58 },
  eyes_Eye_white1_mc: { originX: 32.5, originY: 30 },
  eyes: { originX: 27.5, originY: 27.5 },
  eyes_Eye_iris1_mc: { originX: 22, originY: 11.5 },
  eyes_Eye_iris2_mc: { originX: 18.5, originY: 10.5 },
  eyes_Eye_lid1_mc: { originX: 33.5, originY: 18 },
  eyes_Eye_lid2_mc: { originX: 30.5, originY: 17 },
  mouth_Mouth1_mc: { originX: 64, originY: 32.5 },
  mouth_Mouth2_mc: { originX: 55, originY: 25 },
  mouth_Mouth3_mc: { originX: 33, originY: 14.5 },
  mouth_Mouth4_mc: { originX: 22.5, originY: 31.5 },
  mouth_Mouth5_mc: { originX: 51, originY: 19.5 },
  mouth_Mouth6_mc: { originX: 59.5, originY: 20 },
  mouth_Mouth7_mc: { originX: 24, originY: 13 },
  misc: { originX: 10, originY: 10 },
};

function registrationFor(atlasKey) {
  return REGISTRATION_POINTS[atlasKey] ?? null;
}

const EYE_COMPONENT_ORIGINS = {
  1: { x: 32.5, y: 30 },
  2: { x: 27.5, y: 27.5 },
};

const HEAD_FACE_OFFSETS = {
  1: { x: 0, y: 0 },
  2: { x: 13, y: 12 },
  3: { x: 14, y: 19 },
  4: { x: 9, y: 26 },
};

const HAT_RENDER = { pitchMin: 0, pitchMax: 50, pitchStep: 5, yawStep: 20, columns: 19, rows: 11 };

const HAT_ATTACH_POINTS = {
  1: { x: 0, y: 90, z: -18, scale: 1.02 },
  2: { x: 3, y: 96, z: -18, scale: 1.05 },
  3: { x: 4, y: 98, z: -26, scale: 1.04 },
  4: { x: 1, y: 92, z: -22, scale: 1.02 },
};

const HAT_EYE_Y_OFFSETS = {
  1: 0,
  2: 0,
  3: 3,
  4: 8,
  5: 6,
  6: 0,
};

function pickHatFrame(pitchDeg, yawDeg) {
  const row = clamp(Math.round((clamp(pitchDeg, HAT_RENDER.pitchMin, HAT_RENDER.pitchMax) - HAT_RENDER.pitchMin) / HAT_RENDER.pitchStep), 0, HAT_RENDER.rows - 1);
  const yaw = wrap360(yawDeg);
  let col = Math.round(yaw / HAT_RENDER.yawStep);
  if (col >= HAT_RENDER.columns) col = HAT_RENDER.columns - 1;
  return frameName(row * HAT_RENDER.columns + col + 1);
}

const LEG_POINTS = [
  { colorSlot: "front", j1: new Vec3(55, 170, 55), j2: new Vec3(115, 185, 70), j3: new Vec3(100, 0, 68) },
  { colorSlot: "mid", j1: new Vec3(68, 170, 10), j2: new Vec3(128, 185, 10), j3: new Vec3(113, 0, 10) },
  { colorSlot: "rear", j1: new Vec3(63, 170, -35), j2: new Vec3(123, 185, -60), j3: new Vec3(108, 0, -58) },
  { colorSlot: "front", j1: new Vec3(-55, 170, 55), j2: new Vec3(-115, 185, 70), j3: new Vec3(-100, 0, 68) },
  { colorSlot: "mid", j1: new Vec3(-68, 170, 10), j2: new Vec3(-128, 185, 10), j3: new Vec3(-113, 0, 10) },
  { colorSlot: "rear", j1: new Vec3(-63, 170, -35), j2: new Vec3(-123, 185, -60), j3: new Vec3(-108, 0, -58) },
];

function frameName(frameNumber) {
  return `${frameNumber}.png`;
}

function getAtlas(atlases, key) {
  return atlases.get(key.replaceAll("/", "_")) ?? atlases.get(key);
}

function pickFrame(details, pitchDeg, yawDeg) {
  const res = 1 / details.rIncr;
  let rx = clamp(pitchDeg, details.rxMin, details.rxMax);
  let ry = clamp(wrap360(yawDeg), details.ryMin, details.ryMax);
  rx -= details.rxMin;
  let local = 1 + Math.round(res * (ry - details.ryMin));
  const rowBase = details.framesY * Math.round(res * rx);
  let flipX = 1;
  if (details.symAxes > 0) {
    if (details.symAxes === 180) {
      local = 1;
    } else {
      const sector = Math.floor((local - 1) / (details.framesY - 1));
      local = (local - 1) % (details.framesY - 1);
      if ((sector & 1) === 0) {
        local += 1;
      } else {
        local = details.framesY - local;
        flipX = -1;
      }
    }
  }
  return { frame: rowBase + local, flipX };
}

function faceFrameFromYaw(yawIndex) {
  let frame = yawIndex + 1;
  if (frame > 37) frame = 74 - frame;
  return frame;
}

function projectLocal(point, snapped, sf = 1) {
  const camera = createCamera(snapped.pitch, snapped.yaw);
  return projectPoint(point, camera, { sf });
}

function angleFromTransformedDirection(dirProjection) {
  return -Math.atan2(dirProjection.y, dirProjection.x);
}

function average(a, b) {
  return 0.5 * (a + b);
}

function multiplyFlashMatrices(m1, m2) {
  return {
    a: m1.a * m2.a + m1.c * m2.b,
    b: m1.b * m2.a + m1.d * m2.b,
    c: m1.a * m2.c + m1.c * m2.d,
    d: m1.b * m2.c + m1.d * m2.d,
    tx: m1.a * m2.tx + m1.c * m2.ty + m1.tx,
    ty: m1.b * m2.tx + m1.d * m2.ty + m1.ty,
  };
}

function mouthTransformFromAngles(localPos, snapped, mouthScale = 1) {
  const sf = 2000 / 600;
  const proj = projectLocal(localPos, snapped, sf);
  const yawIndex = snapped.yawIndex;
  const pitchDeg = snapped.pitch;
  const yawDeg = snapped.yaw;

  let x = proj.x;
  let y = proj.y;

  if (yawIndex > 15 && yawIndex < 37) {
    const ref16 = projectLocal(localPos, { pitch: snapped.pitch, yaw: 80 }, sf);
    y = ref16.y - 0.2 * (yawIndex - 15);
  }
  if (yawIndex > 19 && yawIndex < 37) {
    const ref19 = projectLocal(localPos, { pitch: snapped.pitch, yaw: 95 }, sf);
    x = ref19.x + 0.5 * (yawIndex - 19);
  }
  if (yawIndex > 37 && yawIndex < 59) {
    const ref16 = projectLocal(localPos, { pitch: snapped.pitch, yaw: 80 }, sf);
    y = ref16.y + 0.2 * (yawIndex - 59);
  }
  if (yawIndex > 37 && yawIndex < 55) {
    const ref19 = projectLocal(localPos, { pitch: snapped.pitch, yaw: 95 }, sf);
    x = -ref19.x + 0.5 * (yawIndex - 55);
  }

  const baseScale = proj.scale * mouthScale;
  const cosPitch = Math.max(0.001, Math.cos(pitchDeg * DEG_TO_RAD));
  let shearAngle = -Math.sin(yawDeg * DEG_TO_RAD) * pitchDeg;
  let shear = Math.sin(shearAngle * DEG_TO_RAD) / cosPitch;

  if (yawDeg > 60 && yawDeg < 180) {
    const offsetDeg = yawDeg - 60;
    shearAngle = -Math.sin(offsetDeg * DEG_TO_RAD) * pitchDeg;
    shear += Math.sin(shearAngle * DEG_TO_RAD) / cosPitch;
  }
  if (yawDeg > 180 && yawDeg < 300) {
    const offsetDeg = yawDeg - 300;
    shearAngle = -Math.sin(offsetDeg * DEG_TO_RAD) * pitchDeg;
    shear += Math.sin(shearAngle * DEG_TO_RAD) / cosPitch;
  }

  const yawFrame = yawIndex + 1;
  const signedScaleX = yawFrame > 37 ? -baseScale : baseScale;
  const pitchScale = Math.max(0, Math.cos((pitchDeg + 15) * DEG_TO_RAD));
  const scaleX = signedScaleX * Math.sqrt(Math.max(0.001, pitchScale));
  const scaleY = baseScale * pitchScale;

  let matrix = { a: scaleX, b: 0, c: 0, d: scaleY, tx: 0, ty: 0 };
  matrix = multiplyFlashMatrices(matrix, { a: 1, b: shear, c: 0, d: 1, tx: 0, ty: 0 });
  matrix = multiplyFlashMatrices(matrix, { a: 1, b: 0, c: 0, d: 1, tx: x, ty: y });

  return { matrix, depth: proj.depth };
}

const SUPER_LEG_PALETTES = {
  2: { front: 13369344, mid: 16777215, rear: 13369344 },
  3: { front: 16776960, mid: 16729088, rear: 16759552 },
  4: { front: 16711935, mid: 65535, rear: 16776960 },
  5: { front: 16737843, mid: 0, rear: 16737843 },
  6: { front: 0, mid: 16777215, rear: 0 },
  7: { front: 250, mid: 150, rear: 0 },
  8: { front: 16711680, mid: 16737843, rear: 16763955 },
  9: { front: 3368703, mid: 6737151, rear: 65535 },
  10: { front: 6436289, mid: 12806905, rear: 6644479 },
  11: { front: 16711680, mid: 25600, rear: 0 },
  12: { front: 43520, mid: 15597568, rear: 43520 },
  13: { front: 3329330, mid: 15631086, rear: 65535 },
  14: { front: 0, mid: 16711680, rear: 0 },
  15: { front: 25600, mid: 3329330, rear: 25600 },
  16: { front: 16732865, mid: 65535, rear: 16732865 },
  17: { front: 8388736, mid: 13148872, rear: 16758465 },
  18: { front: 36096, mid: 2631720, rear: 36096 },
  19: { front: 153, mid: 16771473, rear: 153 },
  20: { front: 15160448, mid: 16761035, rear: 16756695 },
  21: { front: 139, mid: 4251856, rear: 8837628 },
  22: { front: 13882323, mid: 8421504, rear: 0 },
  23: { front: 0, mid: 8421504, rear: 13882323 },
  24: { front: 11393254, mid: 16753920, rear: 139 },
  25: { front: 65535, mid: 255, rear: 8388736 },
  26: { front: 65535, mid: 255, rear: 139 },
  27: { front: 16776943, mid: 16775812, rear: 16774656 },
  28: { front: 0, mid: 16776960, rear: 0 },
  29: { front: 0, mid: 65280, rear: 0 },
  30: { front: 0, mid: 26367, rear: 0 },
  31: { front: 260075, mid: 45717, rear: 1643798 },
  32: { front: 7303023, mid: 4013373, rear: 2631720 },
  33: { front: 419832, mid: 43520, rear: 15395850 },
  34: { front: 26367, mid: 16777215, rear: 26367 },
  35: { front: 16776960, mid: 0, rear: 16711680 },
  36: { front: 16777215, mid: 16510725, rear: 14745828 },
  37: { front: 16715182, mid: 16715239, rear: 6163967 },
  38: { front: 0, mid: 25219, rear: 0 },
  39: { front: 15658496, mid: 13421568, rear: 15658496 },
  40: { front: 10289151, mid: 16777215, rear: 10289151 },
};

function legPalette(baseLegColor, legType) {
  const palette = { front: baseLegColor, mid: baseLegColor, rear: baseLegColor, lowerFrame: "lower_leg.png" };
  if (Number(legType) === 1) {
    return { ...palette, lowerFrame: "lower_leg_stripy.png" };
  }
  const superPalette = SUPER_LEG_PALETTES[Number(legType)];
  return superPalette ? { ...superPalette, lowerFrame: "lower_leg.png" } : palette;
}

function colorToCss(color) {
  return `#${(color >>> 0).toString(16).padStart(6, "0")}`;
}

export class WeevilCanvasRenderer {
  constructor(canvas, atlases, legImages = new Map()) {
    this.canvas = canvas;
    this.ctx = canvas.getContext("2d");
    this.atlases = atlases;
    this.legImages = legImages;
    this.tintCache = new Map();
    this.currentDef = null;
    this.expressionIndex = 0;
    this.pitch = 15;
    this.yaw = 20;
    this.autoRotate = true;
    this.centerX = 0;
    this.centerY = 0;
  }

  resize(width, height) {
    this.canvas.width = width;
    this.canvas.height = height;
    this.centerX = width * 0.5;
    this.centerY = height * 0.68;
  }

  setDefinition(defObj, { expressionIndex = 0 } = {}) {
    this.currentDef = {
      ht: Number(defObj.ht) || 1,
      hc: Number(defObj.hc) || 0xffffff,
      bt: Number(defObj.bt) || 1,
      bc: Number(defObj.bc) || 0xffffff,
      et: Number(defObj.et) || 1,
      ec: Number(defObj.ec) || 0x000000,
      lids: 0,
      at: Number(defObj.at) || 0,
      ac: Number(defObj.ac) || 0xffffff,
      hat: Number(defObj.hat) || 0,
      hatc: Number(defObj.hatc) || 0x6b4a12,
      lc: Number(defObj.lc) || 0xffffff,
      lt: Number(defObj.lt) || 0,
    };
    this.expressionIndex = clamp(expressionIndex, 0, 6);
  }

  setView(pitch, yaw) {
    this.pitch = pitch;
    this.yaw = yaw;
  }

  update(delta = 1) {
    if (this.autoRotate) {
      this.yaw += 0.3 * delta;
      if (this.yaw >= 360) this.yaw -= 360;
    }
    this.render();
  }

  render() {
    const ctx = this.ctx;
    ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
    if (!this.currentDef) return;

    const snapped = snapAngles(this.pitch, this.yaw);
    const bodyConfig = BODY_CONFIG[this.currentDef.bt] ?? BODY_CONFIG[1];
    const headShape = HEAD_SHAPES[this.currentDef.ht] ?? HEAD_SHAPES[1];
    const eyeConfig =
      (EYE_POSITIONS[this.currentDef.ht] && EYE_POSITIONS[this.currentDef.ht][this.currentDef.et]) ||
      EYE_POSITIONS[1][1];
    const bodyPick = pickFrame(bodyConfig.render, snapped.pitch, snapped.yaw);
    const headPick = pickFrame(HEAD_RENDER, snapped.pitch, snapped.yaw);
    const rawFaceOffset = HEAD_FACE_OFFSETS[this.currentDef.ht] ?? HEAD_FACE_OFFSETS[1];
    const faceOffset = { x: rawFaceOffset.x * headPick.flipX, y: rawFaceOffset.y };
    const eyeOrigin = EYE_COMPONENT_ORIGINS[this.currentDef.et > 3 ? 2 : 1];
    const commands = [];

    commands.push({ type: "shadow", depth: -9999, x: 0, y: 190, scaleY: Math.sin(Math.max(10, snapped.pitch) * DEG_TO_RAD) });

    const bodyProjection = projectLocal(bodyConfig.bodyRoot, snapped, 1);
    commands.push({
      type: "image",
      depth: bodyProjection.depth,
      atlas: bodyConfig.atlas,
      frame: frameName(bodyPick.frame),
      x: bodyProjection.x,
      y: bodyProjection.y,
      scaleX: bodyProjection.scale * bodyPick.flipX,
      scaleY: bodyProjection.scale,
      rotation: 0,
      tint: this.currentDef.bc,
      originX: registrationFor(bodyConfig.atlas)?.originX,
      originY: registrationFor(bodyConfig.atlas)?.originY,
    });

    const headRoot = projectLocal(bodyConfig.headRoot, snapped, 1);
    const headScale = headRoot.scale;

    const addHeadLocal = (cmd) => {
      const offsetX = (cmd.applyFaceOffset ? faceOffset.x : 0) * headScale;
      const offsetY = (cmd.applyFaceOffset ? faceOffset.y : 0) * headScale;
      const next = {
        ...cmd,
        x: headRoot.x + (cmd.x ?? 0) * headScale + offsetX,
        y: headRoot.y + (cmd.y ?? 0) * headScale + offsetY,
        scaleX: (cmd.scaleX ?? 1) * headScale,
        scaleY: (cmd.scaleY ?? 1) * headScale,
        depth: headRoot.depth + cmd.depth,
      };
      if (cmd.matrix) {
        next.matrix = {
          a: cmd.matrix.a * headScale,
          b: cmd.matrix.b * headScale,
          c: cmd.matrix.c * headScale,
          d: cmd.matrix.d * headScale,
          tx: headRoot.x + cmd.matrix.tx * headScale + offsetX,
          ty: headRoot.y + cmd.matrix.ty * headScale + offsetY,
        };
      }
      commands.push(next);
    };

    addHeadLocal({
      type: "image",
      depth: 10,
      atlas: headShape.atlas,
      frame: frameName(headPick.frame),
      x: 0,
      y: 0,
      scaleX: headPick.flipX,
      scaleY: 1,
      rotation: 0,
      tint: this.currentDef.hc,
      originX: registrationFor(headShape.atlas)?.originX,
      originY: registrationFor(headShape.atlas)?.originY,
    });

    const hatType = Number(this.currentDef.hat || 0);
    if (hatType > 0) {
      const hatAttach = HAT_ATTACH_POINTS[this.currentDef.ht] ?? HAT_ATTACH_POINTS[1];
      const hatEyeLift = HAT_EYE_Y_OFFSETS[this.currentDef.et] ?? 0;
      const hatFrame = pickHatFrame(snapped.pitch, snapped.yaw);
      const hatProjection = projectLocal(
        new Vec3(hatAttach.x ?? 0, hatAttach.y + hatEyeLift, hatAttach.z),
        snapped,
        2000 / 600,
      );
      addHeadLocal({
        type: "image",
        depth: hatProjection.depth + 18,
        atlas: "hats_tophat",
        frame: hatFrame,
        x: hatProjection.x,
        y: hatProjection.y,
        scaleX: hatAttach.scale,
        scaleY: hatAttach.scale,
        rotation: 0,
        tint: this.currentDef.hatc ?? 0x6b4a12,
        originX: 96,
        originY: 150,
      });
    }

    const probProjection = projectLocal(new Vec3(0, eyeConfig.probY, eyeConfig.probZ), snapped, 2000 / 600);
    addHeadLocal({
      type: "image",
      depth: probProjection.depth + 26,
      atlas: "misc_Prob1_mc",
      frame: frameName(headPick.frame),
      x: probProjection.x,
      y: probProjection.y,
      scaleX: probProjection.scale * headPick.flipX,
      scaleY: probProjection.scale,
      rotation: 0,
      tint: this.currentDef.hc,
      originX: registrationFor("misc_Prob1_mc")?.originX,
      originY: registrationFor("misc_Prob1_mc")?.originY,
      applyFaceOffset: true,
    });

    const mouthKey = `mouth_${MOUTH_ORDER[this.expressionIndex] ?? MOUTH_ORDER[0]}`;
    const mouthFrame = faceFrameFromYaw(snapped.yawIndex);
    const mouthProjection = projectLocal(new Vec3(0, headShape.mouthY, headShape.mouthZ), snapped, 2000 / 600);
    const mouthMirror = snapped.yawIndex + 1 > 37 ? -1 : 1;
    const mouthPitchScale = Math.max(0.05, Math.cos((snapped.pitch + 15) * DEG_TO_RAD));
    addHeadLocal({
      type: "image",
      depth: Math.min(mouthProjection.depth + 6, probProjection.depth + 12),
      atlas: mouthKey,
      frame: frameName(mouthFrame),
      x: mouthProjection.x,
      y: mouthProjection.y,
      scaleX: mouthProjection.scale * Math.sqrt(mouthPitchScale) * mouthMirror,
      scaleY: mouthProjection.scale * mouthPitchScale,
      rotation: 0,
      tint: null,
      originX: registrationFor(mouthKey)?.originX,
      originY: registrationFor(mouthKey)?.originY,
      applyFaceOffset: true,
    });

    const camera = createCamera(snapped.pitch, snapped.yaw);
    const usesSet2 = this.currentDef.et > 3;

    [eyeConfig.left, eyeConfig.right].forEach((cfg) => {
      const proj = projectLocal(cfg.pos, snapped, 2000 / 600);
      const dirProjection = projectPoint(cfg.dir, camera, { sf: 1 }).transformed;
      const whiteFrameAngle = angleFromTransformedDirection(dirProjection);
      const dirAdjusted = dirProjection.clone();
      dirAdjusted.z -= 2000 - 600;
      const whiteFramePitch = 90 - Math.atan2(-dirAdjusted.z, Math.hypot(dirAdjusted.x, dirAdjusted.y)) * RAD_TO_DEG;
      const eyeFrame = Math.abs(Math.round(0.2 * Math.abs(whiteFramePitch))) + 1;

      const baseRot = Math.atan2(cfg.dir.y, cfg.dir.z) * RAD_TO_DEG;
      const yawRad = snapped.yaw * DEG_TO_RAD;
      const lidRotation = -Math.sin(yawRad) * baseRot * DEG_TO_RAD;
      const lidPitch = snapped.pitch - Math.cos(yawRad) * baseRot;
      let facingYaw = -Math.atan2(-dirProjection.x, -dirProjection.z) * RAD_TO_DEG;
      if (facingYaw < 0) facingYaw += 360;
      const row = clamp(Math.round(0.2 * lidPitch), 0, 10) * 37;
      let col = Math.round(0.2 * facingYaw) + 1;
      let lidScaleX = 1;
      if (col > 37) {
        col = 74 - col;
        lidScaleX = -1;
      }
      const lidFrame = row + col;

      const eyeDepth = proj.depth + 20;

      addHeadLocal({
        type: "image",
        depth: eyeDepth,
        atlas: usesSet2 ? "eyes" : "eyes_Eye_white1_mc",
        frame: usesSet2 ? "Eye_white2_mc.png" : frameName(eyeFrame),
        x: proj.x,
        y: proj.y,
        scaleX: proj.scale,
        scaleY: proj.scale,
        rotation: whiteFrameAngle,
        tint: null,
        originX: eyeOrigin.x,
        originY: eyeOrigin.y,
        applyFaceOffset: true,
      });

      addHeadLocal({
        type: "image",
        depth: eyeDepth + 0.001,
        atlas: usesSet2 ? "eyes_Eye_iris2_mc" : "eyes_Eye_iris1_mc",
        frame: frameName(eyeFrame),
        x: proj.x,
        y: proj.y,
        scaleX: proj.scale,
        scaleY: proj.scale,
        rotation: whiteFrameAngle,
        tint: this.currentDef.ec,
        originX: registrationFor(usesSet2 ? "eyes_Eye_iris2_mc" : "eyes_Eye_iris1_mc")?.originX,
        originY: registrationFor(usesSet2 ? "eyes_Eye_iris2_mc" : "eyes_Eye_iris1_mc")?.originY,
        applyFaceOffset: true,
      });

      // Eyelids intentionally disabled per latest request.

    });

    if (hatType === 0) {
      const antennaBase = new Vec3(0, headShape.antennaBaseY, -19);
      const antennaLayout = BASIC_ANTENNA_LAYOUTS[Number(this.currentDef.at)] ?? SUPER_ANTENNA_LAYOUT;
      antennaLayout.forEach(([p1, p2, crv], index) => {
        const proj1 = projectLocal(antennaBase.add(p1), snapped, 2000 / 600);
        const proj2 = projectLocal(antennaBase.add(p2), snapped, 2000 / 600);
        const projC = projectLocal(antennaBase.add(crv), snapped, 2000 / 600);

        commands.push({
          type: "curve",
          depth: headRoot.depth + proj2.depth + 28,
          x1: headRoot.x + proj1.x * headScale + faceOffset.x * headScale,
          y1: headRoot.y + proj1.y * headScale + faceOffset.y * headScale,
          cx: headRoot.x + projC.x * headScale + faceOffset.x * headScale,
          cy: headRoot.y + projC.y * headScale + faceOffset.y * headScale,
          x2: headRoot.x + proj2.x * headScale + faceOffset.x * headScale,
          y2: headRoot.y + proj2.y * headScale + faceOffset.y * headScale,
          width: 9 * headScale,
          color: this.currentDef.ac,
        });

        commands.push({
          type: "image",
          depth: headRoot.depth + proj2.depth + 29,
          atlas: "misc",
          frame: "ABall_mc.png",
          x: headRoot.x + proj2.x * headScale + faceOffset.x * headScale,
          y: headRoot.y + proj2.y * headScale + faceOffset.y * headScale,
          scaleX: proj2.scale * headScale,
          scaleY: proj2.scale * headScale,
          rotation: 0,
          tint: this.currentDef.ac,
          originX: 10,
          originY: 10,
        });
      });
    }

    const legColors = legPalette(this.currentDef.lc, this.currentDef.lt);
    LEG_POINTS.forEach((data) => {
      const p = { j1: data.j1.clone(), j2: data.j2.clone(), j3: data.j3.clone() };
      const spread = bodyConfig.legSpread;
      if (p.j1.x > 0) {
        p.j1.x += spread;
        p.j2.x += spread;
        p.j3.x += 0.4 * spread;
      } else {
        p.j1.x -= spread;
        p.j2.x -= spread;
        p.j3.x -= 0.4 * spread;
      }

      const pj1 = projectPoint(p.j1, camera, { sf: 1 });
      const pj2 = projectPoint(p.j2, camera, { sf: 1 });
      const pj3 = projectPoint(p.j3, camera, { sf: 1 });
      const paletteColor = legColors[data.colorSlot];

      commands.push({
        type: "segment",
        depth: pj2.depth,
        atlas: "misc",
        frame: "upper_leg.png",
        x1: pj1.x,
        y1: pj1.y,
        x2: pj2.x,
        y2: pj2.y,
        thickness: Math.abs(average(pj1.scale, pj2.scale)),
        stretchFactor: 1.2,
        tint: paletteColor,
        tintMode: "flashOffset",
      });

      commands.push({
        type: "segment",
        depth: pj2.depth + 0.1,
        atlas: "misc",
        frame: legColors.lowerFrame,
        x1: pj2.x,
        y1: pj2.y,
        x2: pj3.x,
        y2: pj3.y,
        thickness: Math.abs(average(pj2.scale, pj3.scale)),
        stretchFactor: 1,
        tint: paletteColor,
        tintMode: "flashOffset",
      });
    });

    commands.sort((a, b) => a.depth - b.depth);

    ctx.save();
    ctx.translate(this.centerX, this.centerY);
    for (const cmd of commands) {
      if (cmd.type === "shadow") this.drawShadow(cmd);
      else if (cmd.type === "curve") this.drawCurve(cmd);
      else if (cmd.type === "segment") this.drawSegment(cmd);
      else if (cmd.type === "image") this.drawImageCommand(cmd);
    }
    ctx.restore();
  }

  drawShadow(cmd) {
    const ctx = this.ctx;
    ctx.save();
    ctx.fillStyle = "rgba(0,0,0,0.15)";
    ctx.beginPath();
    ctx.ellipse(cmd.x, cmd.y, 135, 36 * cmd.scaleY, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.restore();
  }

  drawCurve(cmd) {
    const ctx = this.ctx;
    ctx.save();
    ctx.strokeStyle = colorToCss(cmd.color);
    ctx.lineWidth = cmd.width;
    ctx.lineCap = "round";
    ctx.beginPath();
    ctx.moveTo(cmd.x1, cmd.y1);
    ctx.quadraticCurveTo(cmd.cx, cmd.cy, cmd.x2, cmd.y2);
    ctx.stroke();
    ctx.restore();
  }

  drawSegment(cmd) {
    const sourceImage = this.legImages.get(cmd.frame);
    const dx = cmd.x2 - cmd.x1;
    const dy = cmd.y2 - cmd.y1;
    const angle = Math.atan2(dy, dx);
    const dist = Math.hypot(dx, dy);

    if (sourceImage) {
      const baseW = sourceImage.width;
      const scaleX = (dist / baseW) * cmd.stretchFactor;
      const scaleY = cmd.thickness;
      const image = cmd.tint != null ? this.getTintedLegImage(cmd.frame, cmd.tint, cmd.tintMode) : sourceImage;
      const ctx = this.ctx;
      ctx.save();
      ctx.translate(cmd.x1, cmd.y1);
      ctx.rotate(angle);
      ctx.scale(scaleX, scaleY);
      ctx.drawImage(image, 0, -image.height * 0.5);
      ctx.restore();
      return;
    }

    const atlas = getAtlas(this.atlases, cmd.atlas);
    const frame = getFrame(atlas, cmd.frame);
    const baseW = frame.w;
    const scaleX = (dist / baseW) * cmd.stretchFactor;
    const scaleY = cmd.thickness;
    this.drawAtlasFrame(atlas, cmd.frame, {
      x: cmd.x1,
      y: cmd.y1,
      scaleX,
      scaleY,
      rotation: angle,
      tint: cmd.tint,
      anchorX: 0,
      anchorY: 0.5,
    });
  }

  getTintedLegImage(frameName, tint, tintMode = "flashOffset") {
    const key = `leg:${frameName}:${tint}:${tintMode}`;
    if (this.tintCache.has(key)) {
      return this.tintCache.get(key);
    }
    const sourceImage = this.legImages.get(frameName);
    if (!sourceImage) {
      throw new Error(`Missing leg image ${frameName}`);
    }
    const canvas = document.createElement("canvas");
    canvas.width = sourceImage.width;
    canvas.height = sourceImage.height;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(sourceImage, 0, 0);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imageData.data;
    const r = (tint >> 16) & 0xff;
    const g = (tint >> 8) & 0xff;
    const b = tint & 0xff;
    for (let index = 0; index < data.length; index += 4) {
      if (data[index + 3] === 0) continue;
      if (tintMode === "flashOffset") {
        data[index] = Math.min(255, data[index] + r);
        data[index + 1] = Math.min(255, data[index + 1] + g);
        data[index + 2] = Math.min(255, data[index + 2] + b);
      } else {
        data[index] = Math.round((data[index] * r) / 255);
        data[index + 1] = Math.round((data[index + 1] * g) / 255);
        data[index + 2] = Math.round((data[index + 2] * b) / 255);
      }
    }
    ctx.putImageData(imageData, 0, 0);
    this.tintCache.set(key, canvas);
    return canvas;
  }

  drawImageCommand(cmd) {
    const atlas = getAtlas(this.atlases, cmd.atlas);
    this.drawAtlasFrame(atlas, cmd.frame, cmd);
  }

  drawAtlasFrame(atlas, frameName, options) {
    const frame = getFrame(atlas, frameName);
    const ctx = this.ctx;
    const image = options.tint != null ? this.getTintedFrame(atlas, frameName, options.tint, options.tintMode) : atlas.image;

    ctx.save();
    if (options.matrix) {
      ctx.transform(options.matrix.a, options.matrix.b, options.matrix.c, options.matrix.d, options.matrix.tx, options.matrix.ty);
    } else {
      ctx.translate(options.x, options.y);
      ctx.rotate(options.rotation || 0);
      ctx.scale(options.scaleX, options.scaleY);
    }

    const sx = image === atlas.image ? frame.x : 0;
    const sy = image === atlas.image ? frame.y : 0;
    const sw = frame.w;
    const sh = frame.h;
    const dx = options.originX != null ? -options.originX : -sw * (options.anchorX ?? 0.5);
    const dy = options.originY != null ? -options.originY : -sh * (options.anchorY ?? 0.5);

    ctx.drawImage(image, sx, sy, sw, sh, dx, dy, sw, sh);
    ctx.restore();
  }

  getTintedFrame(atlas, frameName, tint, tintMode = "multiply") {
    const key = `${atlas.key}:${frameName}:${tint}:${tintMode}`;
    if (this.tintCache.has(key)) {
      return this.tintCache.get(key);
    }
    const frame = getFrame(atlas, frameName);
    const canvas = document.createElement("canvas");
    canvas.width = frame.w;
    canvas.height = frame.h;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(atlas.image, frame.x, frame.y, frame.w, frame.h, 0, 0, frame.w, frame.h);
    if (tintMode === "flashOffset") {
      const imageData = ctx.getImageData(0, 0, frame.w, frame.h);
      const data = imageData.data;
      const r = (tint >> 16) & 0xff;
      const g = (tint >> 8) & 0xff;
      const b = tint & 0xff;
      for (let index = 0; index < data.length; index += 4) {
        if (data[index + 3] === 0) continue;
        data[index] = Math.min(255, data[index] + r);
        data[index + 1] = Math.min(255, data[index + 1] + g);
        data[index + 2] = Math.min(255, data[index + 2] + b);
      }
      ctx.putImageData(imageData, 0, 0);
    } else {
      ctx.globalCompositeOperation = "source-in";
      ctx.fillStyle = colorToCss(tint);
      ctx.fillRect(0, 0, frame.w, frame.h);
      ctx.globalCompositeOperation = "multiply";
      ctx.drawImage(atlas.image, frame.x, frame.y, frame.w, frame.h, 0, 0, frame.w, frame.h);
      ctx.globalCompositeOperation = "destination-in";
      ctx.drawImage(atlas.image, frame.x, frame.y, frame.w, frame.h, 0, 0, frame.w, frame.h);
      ctx.globalCompositeOperation = "source-over";
    }
    this.tintCache.set(key, canvas);
    return canvas;
  }
}
