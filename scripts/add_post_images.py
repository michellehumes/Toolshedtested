#!/usr/bin/env python3
import os
import re
from pathlib import Path

SITE_URL = "https://toolshedtested.com"
POSTS_DIR = Path("/Users/michellehumes/Toolshedtested/posts")
ASSETS_DIR = Path("/Users/michellehumes/Toolshedtested/wp-content/themes/toolshed-tested/assets/images")
ASSETS_DIR.mkdir(parents=True, exist_ok=True)

IMAGES = {
    "angle-grinders.md": {
        "title": "DeWalt DWE402 angle grinder",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DWE402_1.jpg",
    },
    "battery-chainsaws.md": {
        "title": "EGO Power+ CS1600 chainsaw",
        "url": "https://egopowerplus.com/media/catalog/product/cache/74b89dcd5fc31fcc1dc1b1791eeb3daa/c/s/cs1600_1.jpg",
    },
    "best-battery-powered-lawn-mowers-2025.md": {
        "title": "EGO Power+ 21-inch mower",
        "url": "https://egopowerplus.com/media/catalog/product/cache/5f1d4c0ffcf3c0236d41820a59fb2c82/l/m/lm2100sp__1_4.jpg",
    },
    "best-battery-powered-lawn-mowers.md": {
        "title": "EGO Power+ 21-inch mower",
        "url": "https://egopowerplus.com/media/catalog/product/cache/5f1d4c0ffcf3c0236d41820a59fb2c82/l/m/lm2100sp__1_4.jpg",
    },
    "best-cordless-hedge-trimmers.md": {
        "title": "EGO Power+ HT2400 hedge trimmer",
        "url": "https://egopowerplus.com/media/catalog/product/cache/5f1d4c0ffcf3c0236d41820a59fb2c82/h/t/ht2400_1_1.jpg",
    },
    "best-portable-air-compressors.md": {
        "title": "Makita MAC2400 air compressor",
        "url": None,
    },
    "chainsaws.md": {
        "title": "Stihl MS 250 chainsaw",
        "url": None,
    },
    "circular-saws.md": {
        "title": "DeWalt DWE575 circular saw",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DWE575_1.jpg",
    },
    "cordless-drills.md": {
        "title": "DeWalt DCD771C2 cordless drill",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DCD771C2_2.jpg",
    },
    "cordless-leaf-blowers.md": {
        "title": "EGO Power+ LB6500 leaf blower",
        "url": "https://egopowerplus.com/media/catalog/product/cache/74b89dcd5fc31fcc1dc1b1791eeb3daa/l/b/lb6500_1.jpg",
    },
    "electric-pressure-washers.md": {
        "title": "Sun Joe SPX3000 pressure washer",
        "url": "https://shopjoe.com/cdn/shop/products/Sun-Joe-SPX3000_1_1000x1000.jpg?v=1631038984",
    },
    "electric-snow-blowers.md": {
        "title": "Snow Joe SJ627E snow blower",
        "url": "https://shopjoe.com/cdn/shop/files/SJ627E_Image01_700x700.png?v=1707428173",
    },
    "impact-drivers.md": {
        "title": "DeWalt DCF887 impact driver",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DCF887D2_1.jpg",
    },
    "inverter-generators.md": {
        "title": "Honda EU2200i inverter generator",
        "url": "https://powerequipment.honda.com/-/media/products/Power-Equipment/Generators/Model-Page/Portable/EU2200IT/eu2200it_240.ashx",
    },
    "jigsaws.md": {
        "title": "Bosch JS470E jigsaw",
        "url": "https://media.boschtools.com/us/en/ocsmedia/optimized/full/18469_JS470E_Front.png",
    },
    "lawn-mowers.md": {
        "title": "EGO Power+ 21-inch mower",
        "url": "https://egopowerplus.com/media/catalog/product/cache/5f1d4c0ffcf3c0236d41820a59fb2c82/l/m/lm2100sp__1_4.jpg",
    },
    "leaf-blowers.md": {
        "title": "EGO Power+ LB6500 leaf blower",
        "url": "https://egopowerplus.com/media/catalog/product/cache/74b89dcd5fc31fcc1dc1b1791eeb3daa/l/b/lb6500_1.jpg",
    },
    "miter-saws.md": {
        "title": "DeWalt DWS780 miter saw",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DWS780_1.jpg",
    },
    "oscillating-multi-tools.md": {
        "title": "DeWalt DCS356 oscillating multi-tool",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DCS356C1_1.jpg",
    },
    "portable-generators.md": {
        "title": "Honda EU3000iS inverter generator",
        "url": None,
    },
    "pressure-washers.md": {
        "title": "Sun Joe SPX3000 pressure washer",
        "url": "https://shopjoe.com/cdn/shop/products/Sun-Joe-SPX3000_1_1000x1000.jpg?v=1631038984",
    },
    "random-orbital-sanders.md": {
        "title": "Bosch ROS20VSC random orbital sander",
        "url": "https://media.boschtools.com/us/en/ocsmedia/optimized/full/18433_ROS20VSC_Front.png",
    },
    "reciprocating-saws.md": {
        "title": "Milwaukee M18 Fuel Sawzall",
        "url": None,
    },
    "snow-blowers.md": {
        "title": "Snow Joe SJ627E snow blower",
        "url": "https://shopjoe.com/cdn/shop/files/SJ627E_Image01_700x700.png?v=1707428173",
    },
    "table-saws.md": {
        "title": "DeWalt DWE7491RS table saw",
        "url": "https://www.dewalt.com/NAG/PRODUCT/IMAGES/HIRES/Ecomm_Large-DWE7491RS_1.jpg",
    },
}

PLACEHOLDER_BG = "#f2efe9"
PLACEHOLDER_FG = "#1f1f1f"
PLACEHOLDER_ACCENT = "#1b5e20"


def slugify(text):
    return re.sub(r"[^a-z0-9]+", "-", text.lower()).strip("-")


def write_placeholder(title):
    filename = ASSETS_DIR / f"placeholder-{slugify(title)}.svg"
    if filename.exists():
        return filename
    svg = f"""<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"1200\" height=\"800\" viewBox=\"0 0 1200 800\">
  <defs>
    <linearGradient id=\"bg\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\">
      <stop offset=\"0%\" stop-color=\"{PLACEHOLDER_BG}\" />
      <stop offset=\"100%\" stop-color=\"#e8e1d5\" />
    </linearGradient>
  </defs>
  <rect width=\"1200\" height=\"800\" fill=\"url(#bg)\"/>
  <rect x=\"80\" y=\"80\" width=\"1040\" height=\"640\" fill=\"#ffffff\" stroke=\"#d6d0c6\" stroke-width=\"4\" rx=\"32\"/>
  <text x=\"120\" y=\"220\" font-family=\"Georgia, serif\" font-size=\"44\" fill=\"{PLACEHOLDER_FG}\">Toolshed Tested</text>
  <text x=\"120\" y=\"310\" font-family=\"Georgia, serif\" font-size=\"56\" font-weight=\"700\" fill=\"{PLACEHOLDER_ACCENT}\">{title}</text>
  <text x=\"120\" y=\"390\" font-family=\"Arial, sans-serif\" font-size=\"28\" fill=\"{PLACEHOLDER_FG}\">Official product image pending</text>
  <rect x=\"120\" y=\"450\" width=\"360\" height=\"24\" fill=\"#e0dbd1\"/>
  <rect x=\"120\" y=\"490\" width=\"520\" height=\"24\" fill=\"#e0dbd1\"/>
  <rect x=\"120\" y=\"530\" width=\"420\" height=\"24\" fill=\"#e0dbd1\"/>
</svg>"""
    filename.write_text(svg, encoding="utf-8")
    return filename


def insert_image_markdown(content, image_url, title):
    if re.search(r"!\[.+?\]\(.+?\)", content):
        return content, False
    patterns = [r"^### 1\)\s+.+$", r"^## 1\.\s+.+$", r"^## 1\)\s+.+$"]
    for pat in patterns:
        m = re.search(pat, content, flags=re.M)
        if m:
            insert_at = m.end()
            snippet = f"\n\n![{title}]({image_url})\n"
            return content[:insert_at] + snippet + content[insert_at:], True
    paras = re.split(r"\n\n+", content, maxsplit=1)
    if len(paras) == 2:
        return paras[0] + f"\n\n![{title}]({image_url})\n\n" + paras[1], True
    return content, False


def main():
    changed = []
    for filename, info in IMAGES.items():
        path = POSTS_DIR / filename
        if not path.exists():
            continue
        title = info["title"]
        if info["url"]:
            image_url = info["url"]
        else:
            placeholder = write_placeholder(title)
            image_url = f"{SITE_URL}/wp-content/themes/toolshed-tested/assets/images/{placeholder.name}"
        content = path.read_text(encoding="utf-8")
        new_content, did_insert = insert_image_markdown(content, image_url, title)
        if did_insert:
            path.write_text(new_content, encoding="utf-8")
            changed.append(str(path))
    if changed:
        print("\n".join(changed))

if __name__ == "__main__":
    main()
