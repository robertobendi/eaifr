# EAIFR Thesis Template

Three versions of the same LaTeX thesis template, themed for the
**East African Institute for Fundamental Research (EAIFR)**.

The visual language is drawn directly from the official EAIFR brochure:
deep navy, warm gold accent, serif title with the "E A I F R" capitals
highlighted in gold, and "Advancing basic science in Africa" as a
subtle italic tagline.

All three versions are **EAIFR-only** (no partner institution logos on
the cover) and share the same chapter/section structure, so you can
swap between them just by replacing `main.tex`.

## The three versions

### `v1_minimal/` — Classic, black ink only
Traditional LaTeX `book` look. Brochure-style stacked wordmark with
bold first letters (E A I F R), gentle rules, no color. Best for
faculties that expect a conservative thesis.

### `v2_light/` — Tasteful light branding
Clean white background, navy serif title, gold-highlighted "E A I F R"
capitals and gold "basic science" accent. Navy chapter headings under a
gold hairline. Restrained but recognisably EAIFR.

### `v3_moderate/` — Brochure-style rich cover
Full-bleed navy cover with a subtle black-and-white silhouette of the
Virunga volcano cone behind the title, echoing the brochure. Gold
capitals, gold italic tagline, thin gold rules. Inside, the same navy
+ gold chapter headings as `v2`.

## How to use on Overleaf

1. Zip the version folder you want (e.g. `v2_light/`) and upload it as
   a new Overleaf project, or copy its contents into an existing
   project.
2. Open `main.tex` and edit the metadata block near the top:
   ```latex
   \newcommand{\thesistitle}{...}
   \newcommand{\thesisauthor}{...}
   \newcommand{\thesisdegree}{...}
   \newcommand{\thesissupervisor}{...}
   ```
3. Compile with **pdfLaTeX** (default on Overleaf).

## Brand palette
- Navy    `#0F2A4A`
- Gold    `#D4A04B`
- Cream   `#F5F1E8`

## Notes
- `commath` is pre-installed on Overleaf; if your local TeX install
  doesn't have it, leave it commented out.
- `subfigure` was dropped because it clashes with `subcaption`. Use
  `\begin{subfigure}` from the `subcaption` package.
- Greek-in-text support was removed from the default preamble.
  Uncomment the two commented lines in the preamble if you need Greek
  outside math mode.
- The `figures/` folder contains the darkened Virunga-cone background
  for the `v3` cover.
