#!/usr/bin/env python3
"""Converte as imagens do Gemini (jpeg/png) para o formato do app.

O que faz, para cada item do manifest.json:
  1. procura <id>.jpeg / .jpg / .png na pasta de imagens;
  2. redimensiona para caber em 640x480 (sem cortar o objeto);
  3. completa para 4:3 com a cor do fundo da própria foto;
  4. salva como WebP em <pasta>/<categoria>/<id>.webp, abaixo de ~60 KB.

Uso (Windows):
  pip install pillow
  python prepare_images.py "C:\\xampp\\htdocs\\site\\vocabulary\\manifest.json" "C:\\xampp\\htdocs\\site\\vocabulary\\img"

Os arquivos originais NÃO são alterados. Antes de publicar, apague (ou mova
para fora da pasta do site) os .jpeg para não subi-los ao GitHub Pages.
"""
import io
import json
import os
import sys

from PIL import Image

W, H = 640, 480
MAX_KB = 60
QUALITIES = (82, 76, 70, 64, 58, 50, 42)
EXTS = (".jpeg", ".jpg", ".png", ".webp")


def find_source(img_dir, item_id):
    """Acha o arquivo de origem ignorando maiúsculas/minúsculas."""
    wanted = {item_id.lower() + e for e in EXTS}
    for name in os.listdir(img_dir):
        full = os.path.join(img_dir, name)
        if os.path.isfile(full) and name.lower() in wanted:
            return full
    return None


def corner_color(im):
    """Cor média dos 4 cantos: é o fundo que usamos para completar o 4:3."""
    w, h = im.size
    n = max(2, min(w, h) // 40)
    boxes = [(0, 0, n, n), (w - n, 0, w, n), (0, h - n, n, h), (w - n, h - n, w, h)]
    r = g = b = 0
    cnt = 0
    for box in boxes:
        px = im.crop(box).resize((1, 1), Image.BILINEAR).getpixel((0, 0))
        r += px[0]; g += px[1]; b += px[2]; cnt += 1
    return (r // cnt, g // cnt, b // cnt)


def to_canvas(src_path):
    im = Image.open(src_path)
    im = im.convert("RGB")
    bg = corner_color(im)
    im.thumbnail((W, H), Image.LANCZOS)  # mantém proporção, nunca corta
    canvas = Image.new("RGB", (W, H), bg)
    canvas.paste(im, ((W - im.width) // 2, (H - im.height) // 2))
    return canvas


def encode(canvas):
    """Tenta qualidades decrescentes até ficar abaixo do limite."""
    data = b""
    q = QUALITIES[0]
    for q in QUALITIES:
        buf = io.BytesIO()
        canvas.save(buf, "WEBP", quality=q, method=6)
        data = buf.getvalue()
        if len(data) <= MAX_KB * 1024:
            break
    return data, q


def main():
    if len(sys.argv) < 3:
        print(__doc__)
        sys.exit(1)
    manifest_path, img_dir = sys.argv[1], sys.argv[2]
    with open(manifest_path, encoding="utf-8") as f:
        manifest = json.load(f)

    used = set()
    converted = missing = too_big = 0

    for cat in manifest["categories"]:
        for it in cat["items"]:
            src = find_source(img_dir, it["id"])
            if src is None:
                print(f"  FALTA   {it['id']}  (não achei {it['id']}.jpeg/.jpg/.png em {img_dir})")
                missing += 1
                continue
            used.add(os.path.basename(src).lower())

            data, q = encode(to_canvas(src))
            out = os.path.join(img_dir, *it["file"].split("/"))
            os.makedirs(os.path.dirname(out), exist_ok=True)
            with open(out, "wb") as f:
                f.write(data)

            old_kb = os.path.getsize(src) / 1024
            new_kb = len(data) / 1024
            flag = ""
            if new_kb > MAX_KB:
                flag = "  (acima de 60 KB, mesmo na menor qualidade)"
                too_big += 1
            print(f"  OK      {it['file']:<28} {old_kb:6.0f} KB -> {new_kb:5.1f} KB  (q={q}){flag}")
            converted += 1

    # imagens na pasta que não pertencem a nenhum item do manifest
    extras = [
        n for n in sorted(os.listdir(img_dir))
        if os.path.isfile(os.path.join(img_dir, n))
        and n.lower().endswith(EXTS)
        and n.lower() not in used
    ]
    if extras:
        print("\nImagens sem item no manifest (ainda não foram convertidas):")
        for n in extras:
            print("  -", n)

    print(f"\n{converted} convertida(s), {missing} faltando, {too_big} acima do limite.")
    sys.exit(1 if missing else 0)


if __name__ == "__main__":
    main()