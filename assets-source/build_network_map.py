"""Rebuild uploads/svg/world-network.svg on the original dotted map.

Land dots come from uploads/svg/world-dots-teal.svg. Partner dots are the
research collaborations list, placed with the land map's own projection. Pale single-colour land, dark-green partners, gold Kigali with its name,
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

# (longitude, latitude, institution). Catherine's research collaborations list,
# November 2026; keep in sync with the research_collaborations seed entries.
PARTNERS = [
    (5.37, 43.30, "IRD, Marseille"),
    (36.82, -1.29, "IRD, Nairobi"),
    (11.34, 44.49, "INGV, Bologna"),
    (13.77, 45.65, "INGV, Trieste"),
    (12.51, 41.90, "CNR, Rome"),
    (6.57, 46.52, "CECAM, Lausanne"),
    (-1.26, 51.75, "Earth Sciences, University of Oxford"),
    (4.38, 50.81, "Universite Libre de Bruxelles"),
    (-0.58, 44.84, "INSERM and LOMA, Bordeaux"),
    (2.21, 48.71, "Ecole Polytechnique, Palaiseau"),
    (2.36, 48.85, "Sorbonne University, Paris"),
    (5.87, 45.64, "ISTerre, Chambery"),
    (0.12, 52.20, "University of Cambridge"),
    (-2.60, 51.46, "University of Bristol"),
    (14.51, 46.05, "Slovenian Environment Agency, Ljubljana"),
    (-78.90, 35.97, "North Carolina Central University, Durham"),
    (-105.22, 39.75, "Colorado School of Mines, Golden"),
    (73.05, 33.68, "Centre for Earthquake Studies, NCP, Islamabad"),
    (38.76, 9.03, "Addis Ababa University"),
    (27.13, -22.55, "BIUST, Palapye"),
    (-1.57, 6.67, "KNUST, Kumasi"),
    (-0.19, 5.65, "University of Ghana, Accra"),
    (32.57, 0.33, "Makerere University, Kampala"),
    (35.74, -6.17, "University of Dodoma"),
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
    '<title id="title">EAIFR research collaborations worldwide</title>',
    '<desc id="desc">Dotted world map. Dark green dots mark research collaborations; a gold dot marks EAIFR in Kigali.</desc>',
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
