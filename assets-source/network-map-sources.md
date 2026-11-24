# EAIFR network map

Checked 2026-11-24 against https://eaifr.ictp.it/about/partnerships/ and https://eaifr.ictp.it/about/our-people/.

The homepage caption records the evidence for each of eight highlighted countries. Only partnerships and visiting affiliations are included; former staff, student origins, event attendees and historical anonymous dots are excluded. Stakeholder corrections removing Ali Hassanali and moving Marcello Musso to former staff take precedence over the old site's roster. Neither is needed to support these countries.

Country polygons: Natural Earth public-domain 1:110m admin-0 countries, https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_110m_admin_0_countries.geojson (local network-world.geojson). SVG uses equirectangular projection: x=(longitude+180)*3, y=(90-latitude)*3. Callouts mark Switzerland approximately at 8.23E/46.82N and Kigali at 30.06E/1.94S; all other shading is country-level, not an asserted city location.

This replaces the unverified legacy dots entirely. Update the caption and SVG together when evidence changes.

## Dotted revision

Rebuilt with build_network_map.py using a Robinson projection and uniform land dots. Eight approximate city locations: Kigali, San Diego, Salamanca, Paris, Lausanne, Rome, Cambridge and Abu Dhabi. Each is supported by the named affiliation in the homepage source list. This is a selection, not a comprehensive network. The earlier country-shading implementation is superseded. SVG and HTML dimensions both use 1200 by 650 with preserved aspect ratio.
