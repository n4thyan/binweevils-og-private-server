import { Vec3, DEG_TO_RAD } from "./math.js";

export const CAMERA_DISTANCE = 2000;
export const FOCAL_LENGTH = 600;
export const DEFAULT_DEPTH_OFFSET = CAMERA_DISTANCE - FOCAL_LENGTH;

export function createCamera(pitchDeg, yawDeg, distance = CAMERA_DISTANCE) {
  const pitch = pitchDeg * DEG_TO_RAD;
  const yaw = yawDeg * DEG_TO_RAD;

  const pos = new Vec3(
    Math.sin(yaw) * Math.cos(pitch) * distance,
    Math.sin(pitch) * distance,
    Math.cos(yaw) * Math.cos(pitch) * distance,
  );

  const out = new Vec3(-pos.x, -pos.y, -pos.z).normalize();
  let side = out.cross(new Vec3(0, 1, 0)).normalize().scale(-1);
  const up = side.cross(out).normalize();

  return { pos, side, up, out, distance };
}

export function transformPoint(point, camera, focal = FOCAL_LENGTH) {
  const rel = point.subtract(camera.pos);
  return new Vec3(
    camera.side.dot(rel),
    camera.up.dot(rel),
    camera.out.dot(rel) - focal,
  );
}

export function projectPoint(point, camera, { focal = FOCAL_LENGTH, sf = 1 } = {}) {
  const transformed = transformPoint(point, camera, focal);
  const ratio = focal / (focal + transformed.z);
  return {
    x: transformed.x * ratio * sf,
    y: transformed.y * ratio * sf,
    scale: ratio * sf,
    depth: -transformed.z + DEFAULT_DEPTH_OFFSET,
    transformed,
  };
}
