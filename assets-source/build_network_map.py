"""Rebuild the source-backed dotted map. Requires pyproj and shapely."""
import json
from pathlib import Path
from pyproj import Transformer
from shapely.geometry import shape, Point
from shapely.ops import transform, unary_union
from xml.sax.saxutils import escape
ROOT=Path(__file__).resolve().parents[1]
project=Transformer.from_crs("EPSG:4326", "+proj=robin +datum=WGS84", always_xy=True).transform
features=json.loads((ROOT/"assets-source/network-world.geojson").read_text())["features"]
land=unary_union([transform(project,shape(f["geometry"])).buffer(0) for f in features if f["properties"]["ADMIN"]!="Antarctica"])
scale=1120/34011666.66
def xy(lon,lat):
    x,y=project(lon,lat)
    return 600+x*scale,320-y*scale
svg=['<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 650" width="1200" height="650" preserveAspectRatio="xMidYMid meet" role="img" aria-labelledby="title desc"><title id="title">EAIFR selected international connections</title><desc id="desc">Muted dots form the land. Teal markers identify San Diego, Salamanca, Paris, Lausanne, Rome, Cambridge and Abu Dhabi. Gold marks EAIFR in Kigali. Locations are supported by the official EAIFR partnerships and people pages.</desc><g fill="#0d8080" opacity="0.26">']
for y in range(48,592,7):
    for x in range(40,1161,7):
        if land.contains(Point((x-600)/scale,(320-y)/scale)):
            svg.append(f'<circle cx="{x}" cy="{y}" r="1.9"/>')
svg.append('</g>')
locations=[("San Diego",-117.16,32.72,"Dave May, Scripps Institution of Oceanography"),("Salamanca",-5.66,40.97,"Mario Amado Montero, University of Salamanca"),("Paris",2.35,48.86,"Sorbonne University"),("Lausanne",6.63,46.52,"Sara Bonella, EPFL; CECAM and NCCR-MARVEL in Switzerland"),("Rome",12.50,41.90,"Andrea Marini, CNR"),("Cambridge",0.12,52.20,"University of Cambridge"),("Abu Dhabi",54.38,24.45,"Arjun Narayanan, NYU Abu Dhabi"),("Kigali",30.06,-1.94,"EAIFR, University of Rwanda")]
for name,lon,lat,evidence in locations:
    x,y=xy(lon,lat)
    color="#d4a843" if name=="Kigali" else "#0d8080"
    svg.append(f'<g data-location="{name}"><title>{escape(name+": "+evidence)}</title><circle cx="{x:.2f}" cy="{y:.2f}" r="6" fill="{color}" stroke="#fff" stroke-width="2"/></g>')
for name,lon,lat,tx,ty in [("Lausanne, Switzerland",6.63,46.52,740,105),("EAIFR, Kigali",30.06,-1.94,770,395)]:
    x,y=xy(lon,lat)
    svg.append(f'<path d="M{x:.2f},{y:.2f} L{tx-15},{ty-7}" fill="none" stroke="#526664" stroke-width="1.4"/><text x="{tx}" y="{ty}" font-family="Arial,sans-serif" font-size="28" font-weight="600" fill="#1b2838" stroke="#f8f6f1" stroke-width="5" paint-order="stroke fill" stroke-linejoin="round">{name}</text>')
svg.append('</svg>')
(ROOT/"uploads/svg/world-network.svg").write_text("\n".join(svg),encoding="utf-8")
print("Built dotted Robinson map with",len(locations),"sourced location markers")
