#!/usr/bin/env python3
"""Developer-only structural/render contract for the definitive Flash world map."""

import argparse
import hashlib
import os
import re
import struct
import subprocess
import tempfile
import zlib
from pathlib import Path

if os.name == "nt":
    DEFAULT_JAVA = Path(r"C:\Program Files\Eclipse Adoptium\jdk-21.0.7.6-hotspot\bin\java.exe")
    DEFAULT_FFDEC = Path(r"C:\tools\ffdec\ffdec.jar")
else:
    DEFAULT_JAVA = Path("/c/Program Files/Eclipse Adoptium/jdk-21.0.7.6-hotspot/bin/java.exe")
    DEFAULT_FFDEC = Path("/c/tools/ffdec/ffdec.jar")


def run(*args: str) -> None:
    command = [str(arg) for arg in args]
    if os.name != "nt":
        # Launch Windows Java from MSYS, but give the Java process native drive paths.
        command = [command[0]] + [
            value[1].upper() + ":/" + value[3:] if len(value) > 3 and value[0] == "/" and value[2] == "/" else value
            for value in command[1:]
        ]
    completed = subprocess.run(command, stdout=subprocess.PIPE, stderr=subprocess.STDOUT, text=True)
    if completed.returncode:
        raise RuntimeError("command failed:\n" + completed.stdout)


def sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as source:
        for block in iter(lambda: source.read(1024 * 1024), b""):
            digest.update(block)
    return digest.hexdigest()


def read_rgba_png(path: Path):
    data = path.read_bytes()
    assert data[:8] == b"\x89PNG\r\n\x1a\n", f"not a PNG: {path}"
    offset = 8
    compressed = bytearray()
    width = height = None
    while offset < len(data):
        length = struct.unpack(">I", data[offset:offset + 4])[0]
        kind = data[offset + 4:offset + 8]
        payload = data[offset + 8:offset + 8 + length]
        offset += 12 + length
        if kind == b"IHDR":
            width, height, depth, colour, compression, filtering, interlace = struct.unpack(">IIBBBBB", payload)
            assert (depth, colour, compression, filtering, interlace) == (8, 6, 0, 0, 0), "expected non-interlaced RGBA PNG"
        elif kind == b"IDAT":
            compressed.extend(payload)
        elif kind == b"IEND":
            break
    assert width and height
    raw = zlib.decompress(bytes(compressed))
    stride = width * 4
    rows = []
    previous = bytearray(stride)
    position = 0
    for _ in range(height):
        filter_type = raw[position]
        position += 1
        scanline = bytearray(raw[position:position + stride])
        position += stride
        for i in range(stride):
            left = scanline[i - 4] if i >= 4 else 0
            up = previous[i]
            up_left = previous[i - 4] if i >= 4 else 0
            if filter_type == 1:
                scanline[i] = (scanline[i] + left) & 255
            elif filter_type == 2:
                scanline[i] = (scanline[i] + up) & 255
            elif filter_type == 3:
                scanline[i] = (scanline[i] + ((left + up) // 2)) & 255
            elif filter_type == 4:
                estimate = left + up - up_left
                pa, pb, pc = abs(estimate - left), abs(estimate - up), abs(estimate - up_left)
                predictor = left if pa <= pb and pa <= pc else up if pb <= pc else up_left
                scanline[i] = (scanline[i] + predictor) & 255
            elif filter_type != 0:
                raise AssertionError(f"unsupported PNG filter: {filter_type}")
        rows.append(bytes(scanline))
        previous = scanline
    return width, height, b"".join(rows)


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("candidate", type=Path, help="actual served map SWF")
    parser.add_argument("--repo-copy", type=Path, help="repository map SWF that must match candidate byte-for-byte")
    parser.add_argument("--original", type=Path)
    parser.add_argument("--java", type=Path, default=DEFAULT_JAVA)
    parser.add_argument("--ffdec", type=Path, default=DEFAULT_FFDEC)
    args = parser.parse_args()

    for required in (args.candidate, args.java, args.ffdec):
        assert required.is_file(), f"missing required file: {required}"
    if args.repo_copy:
        assert args.repo_copy.is_file(), f"missing repository map: {args.repo_copy}"
        assert sha256(args.repo_copy) == sha256(args.candidate), "repository and served map SWFs differ"

    temp_parent = None if os.name == "nt" else Path("/c/Users/pc/AppData/Local/Temp")
    with tempfile.TemporaryDirectory(prefix="bw-map-contract-", dir=temp_parent) as temporary:
        work = Path(temporary)
        scripts = work / "scripts"
        frames = work / "frames"
        scripts.mkdir()
        frames.mkdir()
        xml_path = work / "candidate.xml"

        run(args.java, "-jar", args.ffdec, "-format", "script:as", "-export", "script", scripts, args.candidate)
        run(args.java, "-jar", args.ffdec, "-export", "frame", frames, args.candidate)
        run(args.java, "-jar", args.ffdec, "-swf2xml", args.candidate, xml_path)

        map_source = (scripts / "scripts" / "com" / "binweevils" / "externalUIs" / "map" / "Map.as").read_text(encoding="utf-8")
        location_source = (scripts / "scripts" / "com" / "binweevils" / "externalUIs" / "map" / "MapLocation.as").read_text(encoding="utf-8")
        xml_text = xml_path.read_text(encoding="utf-8")

        required_contracts = {
            "Nest Street": 'new MapLocation(this,this.nestStreet_btn,196',
            "Ink's Orange Peel": 'new MapLocation(this,this.inksOrange_btn,106',
            "Peel Park": 'new MapLocation(this,this.peelPark_btn,108',
        }
        for name, contract in required_contracts.items():
            assert contract in map_source, f"missing navigation contract: {name}"

        assert "100,3" not in map_source and "101,3" not in map_source, "retired invalid Bin Pets IDs remain"
        # Preserve this map generation's original close contract. The original SWF uses
        # closeExternalInterface(UIresetMode), not loadLoc(-1).
        assert "this.bin.closeExternalInterface(this.bin.UIresetMode)" in map_source, "close handler missing"
        normalized_map_source = map_source.replace("\\'", "'")
        for label in ("NEST STREET", "INK'S ORANGE PEEL", "PEEL PARK"):
            assert label in normalized_map_source, f"missing tooltip label: {label}"
        assert "setHint" in location_source and 'gotoAndPlay("over")' in location_source, "hover/name behavior missing"
        assert 'name="inksOrange_btn"' in xml_text and 'name="peelPark_btn"' in xml_text, "inserted button instances missing"

        down_labels = len(re.findall(r'FrameLabelTag[^>]*name="down"', xml_text))
        over_labels = len(re.findall(r'FrameLabelTag[^>]*name="over"', xml_text))
        assert down_labels >= 2 and over_labels >= 2, "inserted buttons lack down/over timelines"

        candidate_width, candidate_height, candidate_pixels = read_rgba_png(frames / "1.png")
        assert (candidate_width, candidate_height) == (825, 490), f"unexpected map frame size: {(candidate_width, candidate_height)}"

        changed_fraction = None
        if args.original:
            assert args.original.is_file(), f"missing original: {args.original}"
            original_frames = work / "original-frames"
            original_frames.mkdir()
            run(args.java, "-jar", args.ffdec, "-export", "frame", original_frames, args.original)
            original_width, original_height, original_pixels = read_rgba_png(original_frames / "1.png")
            assert (original_width, original_height) == (candidate_width, candidate_height)
            changed_pixels = sum(
                original_pixels[index:index + 4] != candidate_pixels[index:index + 4]
                for index in range(0, len(candidate_pixels), 4)
            )
            changed_fraction = changed_pixels / (candidate_width * candidate_height)
            assert 0.005 < changed_fraction < 0.10, f"map artwork changed unexpectedly: {changed_fraction:.3%}"

        print("DEFINITIVE MAP CONTRACT PASS")
        print(f"candidate_sha256={sha256(args.candidate)}")
        print(f"frame_size={candidate_width}x{candidate_height}")
        print(f"frame_labels_down={down_labels} frame_labels_over={over_labels}")
        if args.repo_copy:
            print("repo_served_byte_identical=yes")
        if changed_fraction is not None:
            print(f"changed_frame_pixels={changed_fraction:.3%}")


if __name__ == "__main__":
    main()
