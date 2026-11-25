"""Rebuild uploads/svg/world-network.svg on the original dotted map.

Land dots come from uploads/svg/world-dots-teal.svg. Partner dots are the original
site's markers, recovered as longitude/latitude and placed with the land map's own
projection. Pale single-colour land, dark-green partners, gold Kigali with its name,
no arrows. Standard library only.

To add or remove a partner, edit PARTNERS and rerun: python assets-source/build_network_map.py
"""
import math
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "uploads/svg/world-dots-teal.svg"
OUT = ROOT / "uploads/svg/world-network.svg"

LAND, LAND_OPACITY = "#0d8080", "0.26"
PARTNER, GOLD, INK, HALO = "#143d28", "#d4a843", "#1b2838", "#f7f6f2"

KIGALI = (30.06, -1.94)

# (longitude, latitude, nearest city). Coordinates are the original map's markers;
# city names are inferred from the coordinates and still need confirming against
# the partner list.
PARTNERS = [
    (4.33, 50.88, "Brussels / Louvain"),
    (0.11, 52.20, "Cambridge"),
    (-2.60, 51.42, "Bristol"),
    (-3.50, 50.70, "Exeter"),
    (2.52, 48.89, "Paris"),
    (-0.56, 44.85, "Bordeaux"),
    (5.69, 45.21, "Grenoble"),
    (6.82, 46.54, "Lausanne"),
    (-5.69, 40.99, "Salamanca"),
    (11.56, 44.49, "Bologna / Modena"),
    (12.09, 45.39, "Padua"),
    (13.75, 45.63, "Trieste"),
    (12.69, 41.90, "Rome"),
    (36.80, -1.27, "Nairobi"),
    (38.76, 9.04, "Addis Ababa"),
    (54.65, 24.47, "Abu Dhabi"),
    (113.26, 23.15, "Guangzhou"),
    (-72.28, 43.70, "Hanover, New Hampshire"),
    (-79.36, 43.64, "Toronto"),
    (-87.65, 42.08, "Chicago / Evanston"),
    (-78.91, 35.99, "Durham, North Carolina"),
    (-105.20, 39.79, "Boulder / Golden, Colorado"),
    (-119.74, 34.42, "Santa Barbara"),
    (-117.25, 32.85, "San Diego"),
]


def xy(lon, lat):
    """Mercator fitted to the land dots against Natural Earth coastlines."""
    merc = math.log(math.tan(math.pi / 4 + math.radians(lat) / 2))
    return 2292 + 13.1 * lon, 1879 - 770 * merc


src = SRC.read_text(encoding="utf-8")
land_d = re.search(r'<path fill="#0d8080" d="([^"]+)"', src).group(1)

# Land bounds: every dot is "m dx dy" followed by two arcs that return to the start point.
x = y = 0.0
xs, ys = [], []
for cmd, a, b in re.findall(r"([Mm])\s*(-?\d*\.?\d+)[ ,]?(-?\d*\.?\d+)", land_d):
    a, b = float(a), float(b)
    x, y = (a, b) if cmd == "M" else (x + a, y + b)
    xs.append(x - 15.2)
    ys.append(y)
pad = 60
vx, vy = min(xs) - 15.2 - pad, min(ys) - 15.2 - pad
vw, vh = max(xs) + 15.2 + pad - vx, max(ys) + 15.2 + pad - vy

kx, ky = xy(*KIGALI)
svg = [
    f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="{vx:.0f} {vy:.0f} {vw:.0f} {vh:.0f}" '
    f'width="{vw:.0f}" height="{vh:.0f}" role="img" aria-labelledby="title desc">',
    '<title id="title">EAIFR partners worldwide</title>',
    '<desc id="desc">Dotted world map. Dark green dots mark partners; a gold dot marks EAIFR in Kigali.</desc>',
    f'<path fill="{LAND}" opacity="{LAND_OPACITY}" d="{land_d}"/>',
    f'<g fill="{PARTNER}" stroke="#fff" stroke-width="7">',
    *[f'<circle cx="{px:.0f}" cy="{py:.0f}" r="26"/>' for px, py in (xy(lon, lat) for lon, lat, _ in PARTNERS)],
    "</g>",
    f'<circle cx="{kx:.0f}" cy="{ky:.0f}" r="36" fill="{GOLD}" stroke="#fff" stroke-width="8"/>',
    f'<text x="{kx - 62:.0f}" y="{ky + 36:.0f}" text-anchor="end" font-family="Arial,sans-serif" font-size="104" '
    f'font-weight="700" fill="{INK}" stroke="{HALO}" stroke-width="18" paint-order="stroke fill" '
    f'stroke-linejoin="round">Kigali</text>',
    "</svg>",
]
OUT.write_text("\n".join(svg), encoding="utf-8")
print(f"viewBox {vx:.0f} {vy:.0f} {vw:.0f} {vh:.0f}; {len(PARTNERS)} partner dots")
