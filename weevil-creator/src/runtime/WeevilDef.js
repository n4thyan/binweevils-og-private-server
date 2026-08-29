export const clrs1 = [10027008,43520,153,10057472,8913032,11198463,26367,16750848,13421568,61166,13369548,16777215,16766429,11206400,16763904,15658496,16745604,2631720,10066329,16777145,15597568,26112,1184274,12733185,16736768,16425579,16767167,7620096,16771473,6394113,8899328,14548127,62720,11993014,25670,110971,61093,7011535,25219,50886,10289151,2797311,3014772,5243334,8334079,14138879,16729855,16756735,11338573,15597672,15952037,16757203];
export const clrs2 = [52224,4474077,15610675,13421568,52428,13369548,8943360,2136473,11206400,16763904,15658496,16745604,10027008,15597568,16766429,12733185,16736768,16425579,16767167,7620096,16750848,16771473,10057472,16777145,6394113,8899328,11206400,14548127,26112,43520,62720,11993014,25670,110971,61093,7011535,25219,50886,61166,10289151,153,26367,2797311,11198463,3014772,5243334,8334079,14138879,8913032,16729855,16756735,11338573,15597672,15952037,16757203,10066329,16777215,2631720];

export function getDefObj(defString) {
  return {
    ht: defString.substr(0, 1),
    hc: clrs1[Number(defString.substr(1, 2))],
    bt: defString.substr(3, 1),
    bc: clrs1[Number(defString.substr(4, 2))],
    et: defString.substr(6, 1),
    ec: clrs2[Number(defString.substr(7, 2))],
    lids: defString.substr(9, 1),
    at: defString.substr(10, 2),
    ac: clrs1[Number(defString.substr(12, 2))],
    lc: clrs1[Number(defString.substr(14, 2))],
    lt: defString.substr(16, 2),
  };
}

export function getDefStr(defObj) {
  const parts = [
    String(Number(defObj.ht) || 1),
    getClrIndex(1, Number(defObj.hc) || clrs1[0]),
    String(Number(defObj.bt) || 1),
    getClrIndex(1, Number(defObj.bc) || clrs1[0]),
    String(Number(defObj.et) || 1),
    getClrIndex(2, Number(defObj.ec) || clrs2[0]),
    String(Number(defObj.lids) || 0),
    String(Number(defObj.at) || 0).padStart(2, "0"),
    getClrIndex(1, Number(defObj.ac) || clrs1[0]),
    getClrIndex(1, Number(defObj.lc) || clrs1[0]),
    String(Number(defObj.lt) || 0).padStart(2, "0"),
  ];
  return parts.join("");
}

export function intToHex(color) {
  return `#${color.toString(16).padStart(6, "0")}`;
}

function getClrIndex(type, color) {
  const colors = type === 2 ? clrs2 : clrs1;
  const exactIndex = colors.indexOf(color);
  if (exactIndex >= 0) {
    return String(exactIndex).padStart(2, "0");
  }

  const safeColor = Number.isFinite(color) ? color : colors[0];
  let closestIndex = 0;
  let closestDistance = Number.POSITIVE_INFINITY;

  colors.forEach((candidate, index) => {
    const distance = colorDistanceSq(candidate, safeColor);
    if (distance < closestDistance) {
      closestDistance = distance;
      closestIndex = index;
    }
  });

  return String(closestIndex).padStart(2, "0");
}

function colorDistanceSq(a, b) {
  const ar = (a >> 16) & 255;
  const ag = (a >> 8) & 255;
  const ab = a & 255;
  const br = (b >> 16) & 255;
  const bg = (b >> 8) & 255;
  const bb = b & 255;
  return (ar - br) ** 2 + (ag - bg) ** 2 + (ab - bb) ** 2;
}
