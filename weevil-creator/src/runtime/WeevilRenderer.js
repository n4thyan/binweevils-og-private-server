import * as PIXI from "https://cdn.jsdelivr.net/npm/pixi.js@8.x/dist/pixi.mjs";
import { Vec3, RAD_TO_DEG, DEG_TO_RAD, clamp, snapAngles, wrap360 } from "./math.js";
import { createCamera, projectPoint } from "./projection.js";
import { getFrame } from "./atlasLoader.js";

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

const LEG_POINTS = [
  { colorSlot: "front", frontLeg: true, j1: new Vec3(55, 170, 55), j2: new Vec3(115, 185, 70), j3: new Vec3(100, 0, 68) },
  { colorSlot: "mid", frontLeg: false, j1: new Vec3(68, 170, 10), j2: new Vec3(128, 185, 10), j3: new Vec3(113, 0, 10) },
  { colorSlot: "rear", frontLeg: false, j1: new Vec3(63, 170, -35), j2: new Vec3(123, 185, -60), j3: new Vec3(108, 0, -58) },
  { colorSlot: "front", frontLeg: true, j1: new Vec3(-55, 170, 55), j2: new Vec3(-115, 185, 70), j3: new Vec3(-100, 0, 68) },
  { colorSlot: "mid", frontLeg: false, j1: new Vec3(-68, 170, 10), j2: new Vec3(-128, 185, 10), j3: new Vec3(-113, 0, 10) },
  { colorSlot: "rear", frontLeg: false, j1: new Vec3(-63, 170, -35), j2: new Vec3(-123, 185, -60), j3: new Vec3(-108, 0, -58) },
];

const BASIC_ANTENNA_LAYOUTS = {
  0: [],
  1: [[new Vec3(0, 0, 0), new Vec3(0, 50, -30), new Vec3(0, 20, 0)]],
  2: [[new Vec3(0, 0, 0), new Vec3(0, 95, -46), new Vec3(0, 40, 0)]],
  3: [[new Vec3(0, 0, 0), new Vec3(0, 60, -110), new Vec3(0, 135, -40)]],
  4: [
    [new Vec3(0, 0, 0), new Vec3(30, 60, -26), new Vec3(12, 20, 3)],
    [new Vec3(0, 0, 0), new Vec3(-30, 60, -26), new Vec3(-12, 20, 3)],
  ],
  5: [
    [new Vec3(0, 0, 0), new Vec3(50, 100, -46), new Vec3(10, 45, 0)],
    [new Vec3(0, 0, 0), new Vec3(-50, 100, -46), new Vec3(-10, 45, 0)],
  ],
  6: [
    [new Vec3(0, 0, 0), new Vec3(65, 50, -71), new Vec3(40, 125, -25)],
    [new Vec3(0, 0, 0), new Vec3(-65, 50, -71), new Vec3(-40, 125, -25)],
  ],
  7: [
    [new Vec3(0, 0, 0), new Vec3(0, 60, -30), new Vec3(0, 20, 0)],
    [new Vec3(0, 0, 0), new Vec3(-40, 50, -25), new Vec3(-20, 20, 0)],
    [new Vec3(0, 0, 0), new Vec3(40, 50, -25), new Vec3(20, 20, 0)],
  ],
  8: [
    [new Vec3(0, 0, 0), new Vec3(0, 94, -35), new Vec3(0, 40, 0)],
    [new Vec3(0, 0, 0), new Vec3(-54, 76, -30), new Vec3(-20, 40, 0)],
    [new Vec3(0, 0, 0), new Vec3(54, 76, -30), new Vec3(20, 40, 0)],
  ],
  9: [
    [new Vec3(0, 0, 0), new Vec3(0, 60, -110), new Vec3(0, 135, -40)],
    [new Vec3(0, 0, 0), new Vec3(-100, 50, -55), new Vec3(-50, 125, -25)],
    [new Vec3(0, 0, 0), new Vec3(100, 50, -55), new Vec3(50, 125, -25)],
  ],
};

const SUPER_ANTENNA_LAYOUT = [
  [new Vec3(0, 0, 0), new Vec3(0, 180, -110), new Vec3(0, 255, -40)],
  [new Vec3(0, 0, 0), new Vec3(-80, 150, -55), new Vec3(-40, 225, -25)],
  [new Vec3(0, 0, 0), new Vec3(80, 150, -55), new Vec3(40, 225, -25)],
  [new Vec3(0, 0, 0), new Vec3(-100, 50, -55), new Vec3(-50, 125, -25)],
  [new Vec3(0, 0, 0), new Vec3(100, 50, -55), new Vec3(50, 125, -25)],
];

function frameName(frameNumber) {
  return `${frameNumber}.png`;
}

function getAtlas(atlases, key) {
  const sheet = atlases.get(key.replaceAll("/", "_")) ?? atlases.get(key);
  if (!sheet) {
    throw new Error(`Missing atlas: ${key}`);
  }
  return sheet;
}

function setAnchor(sprite, x = 0.5, y = 0.5) {
  if (sprite.anchor) sprite.anchor.set(x, y);
}

function pickFrame(details, pitchDeg, yawDeg) {
  const res = 1 / details.rIncr;
  let rx = clamp(pitchDeg, details.rxMin, details.rxMax);
  let ry = wrap360(yawDeg);
  ry = clamp(ry, details.ryMin, details.ryMax);

  rx -= details.rxMin;
  const rowBase = details.framesY * Math.round(res * rx);
  ry -= details.ryMin;

  let local = 1 + Math.round(res * ry);
  let flipX = 1;

  if (details.symAxes > 0) {
    if (details.symAxes === 180) {
      local = 1;
    } else {
      const sector = Math.floor((local - 1) / (details.framesY - 1));
      local = (local - 1) % (details.framesY - 1);
      if ((sector & 1) === 0) {
        local += 1;
        flipX = 1;
      } else {
        local = details.framesY - local;
        flipX = -1;
      }
    }
  }

  return { frame: rowBase + local, flipX };
}

function applyTint(displayObject, color) {
  displayObject.tint = color >>> 0;
}

function projectLocal(point, snapped, sf = 1) {
  const camera = createCamera(snapped.pitch, snapped.yaw);
  return projectPoint(point, camera, { sf });
}

function faceFrameFromYaw(yawIndex) {
  let frame = yawIndex + 1;
  if (frame > 37) frame = 74 - frame;
  return frame;
}

function angleFromTransformedDirection(dirProjection) {
  return -Math.atan2(dirProjection.y, dirProjection.x) * RAD_TO_DEG;
}

function average(a, b) {
  return 0.5 * (a + b);
}

function length3(a, b) {
  return a.subtract(b).length();
}

function assignSegment(sprite, p1, p2, baseLength, thicknessScale) {
  const dx = p2.x - p1.x;
  const dy = p2.y - p1.y;
  const angle = -Math.atan2(dy, dx) * RAD_TO_DEG;
  const dist = Math.hypot(dx, dy);
  sprite.x = p1.x;
  sprite.y = p1.y;
  sprite.rotation = angle;
  sprite.scale.x = dist / baseLength;
  sprite.scale.y = thicknessScale;
}

function legPalette(baseLegColor, legType) {
  const palette = { front: baseLegColor, mid: baseLegColor, rear: baseLegColor };
  switch (Number(legType)) {
    case 1:
      return palette;
    case 3:
      return { front: 0xffff00, mid: 0xffa000, rear: 0xff4400 };
    case 4:
      return { front: 0xff00ff, mid: 0xffff00, rear: 0x00ffff };
    case 6:
      return { front: 0x000000, mid: 0xffffff, rear: 0xffffff };
    case 8:
      return { front: 0xff0000, mid: 0xffaa33, rear: 0xffdd55 };
    case 9:
      return { front: 0x3366ff, mid: 0x00ffff, rear: 0x66ccff };
    case 39:
      return { front: 0xeeee00, mid: 0xccaa00, rear: 0xb8860b };
    case 40:
      return { front: 0xffffff, mid: 0x9cff, rear: 0x9cff };
    default:
      return palette;
  }
}

export class WeevilRenderer extends PIXI.Container {
  constructor(atlases) {
    super();
    this.atlases = atlases;
    this.currentDef = null;
    this.expressionIndex = 0;
    this.pitch = 15;
    this.yaw = 20;
    this.autoRotate = true;

    this.creature = new PIXI.Container();
    this.creature.sortableChildren = true;
    this.addChild(this.creature);

    this.shadow = new PIXI.Graphics();
    this.shadow.zIndex = -1000;
    this.creature.addChild(this.shadow);

    this.body = new PIXI.Sprite();
    setAnchor(this.body);
    this.creature.addChild(this.body);

    this.headRoot = new PIXI.Container();
    this.headRoot.sortableChildren = true;
    this.creature.addChild(this.headRoot);

    this.head = new PIXI.Sprite();
    setAnchor(this.head);
    this.headRoot.addChild(this.head);

    this.prob = new PIXI.Sprite();
    setAnchor(this.prob);
    this.headRoot.addChild(this.prob);

    this.maskSprite = new PIXI.Sprite();
    setAnchor(this.maskSprite);
    this.maskSprite.visible = false;
    this.headRoot.addChild(this.maskSprite);

    this.mouth = new PIXI.Sprite();
    setAnchor(this.mouth);
    this.mouth.mask = this.maskSprite;
    this.headRoot.addChild(this.mouth);

    this.eyes = [this.createEye(), this.createEye()];
    this.eyes.forEach((eye) => this.headRoot.addChild(eye.container));

    this.antennaLayers = [];
    for (let i = 0; i < 5; i += 1) {
      this.antennaLayers.push(this.createAntenna());
      this.headRoot.addChild(this.antennaLayers[i].container);
    }

    this.legs = [];
    for (let i = 0; i < 6; i += 1) {
      const upper = new PIXI.Sprite();
      const lower = new PIXI.Sprite();
      upper.zIndex = 5 + i;
      lower.zIndex = 6 + i;
      setAnchor(upper, 0, 0.5);
      setAnchor(lower, 0, 0.5);
      this.creature.addChild(upper);
      this.creature.addChild(lower);
      this.legs.push({ upper, lower });
    }

    this.baseUpperLength = 169;
    this.baseLowerLength = 285;
  }

  createEye() {
    const container = new PIXI.Container();
    const white = new PIXI.Sprite();
    const iris = new PIXI.Sprite();
    const lid = new PIXI.Sprite();
    [white, iris, lid].forEach((sprite) => {
      setAnchor(sprite);
      container.addChild(sprite);
    });
    return { container, white, iris, lid };
  }

  createAntenna() {
    const container = new PIXI.Container();
    const stalk = new PIXI.Graphics();
    const ball = new PIXI.Sprite();
    setAnchor(ball);
    container.addChild(stalk);
    container.addChild(ball);
    return { container, stalk, ball };
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
      lc: Number(defObj.lc) || 0xffffff,
      lt: Number(defObj.lt) || 0,
    };
    this.expressionIndex = clamp(expressionIndex, 0, 6);
    this.refreshStaticTextures();
    this.updatePose();
  }

  setView(pitch, yaw) {
    this.pitch = pitch;
    this.yaw = yaw;
    this.updatePose();
  }

  refreshStaticTextures() {
    if (!this.currentDef) return;
    this.upperLegTexture = getFrame(getAtlas(this.atlases, "misc"), "upper_leg.png");
    this.lowerLegTexture = getFrame(
      getAtlas(this.atlases, "misc"),
      Number(this.currentDef.lt) === 1 ? "lower_leg_stripy.png" : "lower_leg.png",
    );
    this.ballTexture = getFrame(getAtlas(this.atlases, "misc"), "ABall_mc.png");
    this.shadowTexture = getFrame(getAtlas(this.atlases, "misc"), "shadow.png");
    this.legs.forEach((leg) => {
      leg.upper.texture = this.upperLegTexture;
      leg.lower.texture = this.lowerLegTexture;
    });
    this.antennaLayers.forEach((ant) => {
      ant.ball.texture = this.ballTexture;
    });
  }

  update(delta = 1) {
    if (this.autoRotate) {
      this.yaw += 0.3 * delta;
      if (this.yaw >= 360) this.yaw -= 360;
      this.updatePose();
    }
  }

  updatePose() {
    if (!this.currentDef) return;

    const snapped = snapAngles(this.pitch, this.yaw);
    const bodyConfig = BODY_CONFIG[this.currentDef.bt] ?? BODY_CONFIG[1];
    const headShape = HEAD_SHAPES[this.currentDef.ht] ?? HEAD_SHAPES[1];
    const eyeConfig =
      (EYE_POSITIONS[this.currentDef.ht] && EYE_POSITIONS[this.currentDef.ht][this.currentDef.et]) ||
      EYE_POSITIONS[1][1];
    const bodyPick = pickFrame(bodyConfig.render, snapped.pitch, snapped.yaw);
    const headPick = pickFrame(HEAD_RENDER, snapped.pitch, snapped.yaw);

    const bodySheet = getAtlas(this.atlases, bodyConfig.atlas);
    const headSheet = getAtlas(this.atlases, headShape.atlas);
    const maskSheet = getAtlas(this.atlases, headShape.maskAtlas);
    const probSheet = getAtlas(this.atlases, "misc_Prob1_mc");

    this.body.texture = getFrame(bodySheet, frameName(bodyPick.frame));
    this.head.texture = getFrame(headSheet, frameName(headPick.frame));
    this.maskSprite.texture = getFrame(maskSheet, frameName(headPick.frame));
    this.prob.texture = getFrame(probSheet, frameName(headPick.frame));

    applyTint(this.body, this.currentDef.bc);
    applyTint(this.head, this.currentDef.hc);
    applyTint(this.prob, this.currentDef.hc);

    const bodyRootProjection = projectLocal(bodyConfig.bodyRoot, snapped, 1);
    this.body.position.set(bodyRootProjection.x, bodyRootProjection.y);
    this.body.scale.set(bodyRootProjection.scale * bodyPick.flipX, bodyRootProjection.scale);
    this.body.zIndex = bodyRootProjection.depth;

    const headRootProjection = projectLocal(bodyConfig.headRoot, snapped, 1);
    this.headRoot.position.set(headRootProjection.x, headRootProjection.y);
    this.headRoot.scale.set(headRootProjection.scale, headRootProjection.scale);
    this.headRoot.zIndex = headRootProjection.depth + 10;

    this.head.position.set(0, 0);
    this.head.scale.set(headPick.flipX, 1);
    this.maskSprite.position.set(0, 0);
    this.maskSprite.scale.set(headPick.flipX, 1);

    const probProjection = projectLocal(new Vec3(0, eyeConfig.probY, eyeConfig.probZ), snapped, 2000 / 600);
    this.prob.position.set(probProjection.x, probProjection.y);
    this.prob.scale.set(probProjection.scale * headPick.flipX, probProjection.scale);

    this.updateMouth(snapped, headShape, eyeConfig);
    this.updateEyes(snapped, eyeConfig);
    this.updateAntennae(snapped, headShape);
    this.updateLegs(snapped, bodyConfig);
    this.updateShadow(snapped);
  }

  updateShadow(snapped) {
    const scaleY = Math.sin(Math.max(10, snapped.pitch) * DEG_TO_RAD);
    this.shadow.clear();
    this.shadow.beginFill(0x000000, 0.15);
    this.shadow.drawEllipse(0, 0, 135, 36 * scaleY);
    this.shadow.endFill();
    this.shadow.position.set(0, 190);
  }

  updateMouth(snapped, headShape, eyeConfig) {
    const mouthName = MOUTH_ORDER[this.expressionIndex] ?? MOUTH_ORDER[0];
    const mouthAtlas = getAtlas(this.atlases, `mouth_${mouthName}`);
    const localFrame = faceFrameFromYaw(snapped.yawIndex);
    this.mouth.texture = getFrame(mouthAtlas, frameName(localFrame));
    this.mouth.zIndex = 5;

    const proj = projectLocal(new Vec3(0, headShape.mouthY, headShape.mouthZ), snapped, 2000 / 600);
    this.mouth.position.set(proj.x, proj.y);

    const mirror = snapped.yawIndex + 1 > 37 ? -1 : 1;
    const pitchScale = Math.cos((snapped.pitch + 15) * DEG_TO_RAD);
    this.mouth.scale.set(proj.scale * Math.sqrt(Math.max(0.05, pitchScale)) * mirror, proj.scale * pitchScale);
    this.mouth.rotation = 0;

    this.maskSprite.position.set(0, 0);
    this.maskSprite.scale.set((pickFrame(HEAD_RENDER, snapped.pitch, snapped.yaw).flipX), 1);
  }

  updateEyes(snapped, eyeConfig) {
    const usesSet2 = this.currentDef.et > 3;
    const whiteSheet = usesSet2 ? null : getAtlas(this.atlases, "eyes_Eye_white1_mc");
    const irisSheet = getAtlas(this.atlases, usesSet2 ? "eyes_Eye_iris2_mc" : "eyes_Eye_iris1_mc");
    const lidSheet = getAtlas(this.atlases, usesSet2 ? "eyes_Eye_lid2_mc" : "eyes_Eye_lid1_mc");
    const eyeWhite2Atlas = getAtlas(this.atlases, "eyes");

    [eyeConfig.left, eyeConfig.right].forEach((cfg, index) => {
      const eye = this.eyes[index];
      const proj = projectLocal(cfg.pos, snapped, 2000 / 600);
      const camera = createCamera(snapped.pitch, snapped.yaw);
      const dirProjection = projectPoint(cfg.dir, camera, { sf: 1 }).transformed;
      const whiteFrameAngle = angleFromTransformedDirection(dirProjection);
      const dirAdjusted = dirProjection.clone();
      dirAdjusted.z -= 2000 - 600;
      const whiteFramePitch =
        90 - Math.atan2(-dirAdjusted.z, Math.hypot(dirAdjusted.x, dirAdjusted.y)) * RAD_TO_DEG;
      const eyeFrame = Math.abs(Math.round(0.2 * Math.abs(whiteFramePitch))) + 1;

      const baseRot = Math.atan2(cfg.dir.y, cfg.dir.z) * RAD_TO_DEG;
      const yawRad = snapped.yaw * DEG_TO_RAD;
      const lidRotation = -Math.sin(yawRad) * baseRot;
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

      eye.container.position.set(proj.x, proj.y);
      eye.container.scale.set(proj.scale, proj.scale);
      eye.container.zIndex = proj.depth + 20;

      if (usesSet2) {
        eye.white.texture = getFrame(eyeWhite2Atlas, "Eye_white2_mc.png");
      } else {
        eye.white.texture = getFrame(whiteSheet, frameName(eyeFrame));
      }
      eye.iris.texture = getFrame(irisSheet, frameName(eyeFrame));
      eye.lid.texture = getFrame(lidSheet, frameName(lidFrame));

      eye.white.rotation = whiteFrameAngle;
      eye.iris.rotation = whiteFrameAngle;
      eye.lid.rotation = lidRotation;
      eye.lid.scale.set(lidScaleX, 1);

      applyTint(eye.iris, this.currentDef.ec);
      applyTint(eye.lid, this.currentDef.hc);
      eye.lid.visible = false;
    });
  }

  updateAntennae(snapped, headShape) {
    const base = new Vec3(0, headShape.antennaBaseY, -19);
    const type = Number(this.currentDef.at);
    const layout = BASIC_ANTENNA_LAYOUTS[type] ?? SUPER_ANTENNA_LAYOUT;
    const palette = [this.currentDef.ac, this.currentDef.ac, this.currentDef.ac, this.currentDef.ac, this.currentDef.ac];

    this.antennaLayers.forEach((ant, index) => {
      const def = layout[index];
      ant.container.visible = Boolean(def);
      if (!def) return;

      const [p1, p2, crv] = def;
      const proj1 = projectLocal(base.add(p1), snapped, 2000 / 600);
      const proj2 = projectLocal(base.add(p2), snapped, 2000 / 600);
      const projC = projectLocal(base.add(crv), snapped, 2000 / 600);

      ant.stalk.clear();
      ant.stalk.moveTo(proj1.x, proj1.y);
      ant.stalk.quadraticCurveTo(projC.x, projC.y, proj2.x, proj2.y);
      ant.stalk.stroke({ width: 9, color: palette[index] });

      ant.ball.position.set(proj2.x, proj2.y);
      ant.ball.scale.set(proj2.scale, proj2.scale);
      ant.ball.zIndex = proj2.depth + 30;
      applyTint(ant.ball, palette[index]);
    });
  }

  updateLegs(snapped, bodyConfig) {
    const palette = legPalette(this.currentDef.lc, this.currentDef.lt);
    const camera = createCamera(snapped.pitch, snapped.yaw);

    LEG_POINTS.forEach((data, index) => {
      const leg = this.legs[index];
      const signAdjusted = {
        j1: data.j1.clone(),
        j2: data.j2.clone(),
        j3: data.j3.clone(),
      };

      const spread = bodyConfig.legSpread;
      if (signAdjusted.j1.x > 0) {
        signAdjusted.j1.x += spread;
        signAdjusted.j2.x += spread;
        signAdjusted.j3.x += 0.4 * spread;
      } else {
        signAdjusted.j1.x -= spread;
        signAdjusted.j2.x -= spread;
        signAdjusted.j3.x -= 0.4 * spread;
      }

      const pj1 = projectPoint(signAdjusted.j1, camera, { sf: 1 });
      const pj2 = projectPoint(signAdjusted.j2, camera, { sf: 1 });
      const pj3 = projectPoint(signAdjusted.j3, camera, { sf: 1 });

      const upperThickness = average(pj1.scale, pj2.scale);
      const lowerThickness = average(pj2.scale, pj3.scale);

      leg.upper.texture = this.upperLegTexture;
      leg.lower.texture = this.lowerLegTexture;

      applyTint(leg.upper, palette[data.colorSlot]);
      applyTint(leg.lower, palette[data.colorSlot]);

      assignSegment(leg.upper, pj1, pj2, this.baseUpperLength / 1.2, Math.abs(upperThickness));
      assignSegment(leg.lower, pj2, pj3, this.baseLowerLength, Math.abs(lowerThickness));

      leg.upper.zIndex = pj2.depth;
      leg.lower.zIndex = pj2.depth + 0.1;
    });
  }
}
