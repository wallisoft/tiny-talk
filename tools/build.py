#!/usr/bin/env python3
"""Build tiny-talk's dictionary from the plain-text sources, with nothing but Python itself.

    python3 tools/build.py        writes dist/dictionary.tsv and dist/stats.json

A word is written with the pictures of the basic characters it is built from, following recipes.txt down to
core/pictures.txt. The grammar markers of the parts are dropped (teacher = person + giving + knowledge, not
"+ to give"), as Bliss itself does. A word in edge/pictures.txt has a picture of its own instead.

dist/dictionary.tsv, one word to a line:
    BCI number <tab> its pictures <tab> its parts <tab> its words in each language, in core/languages.json order
Pictures are joined by U+2060 (word joiner) so a word never breaks across lines; words in one language by ';'.
"""
import json, os, re, sys

HERE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
WJ = "⁠"


def entries(path):
    """'key = value  # note' lines, comments and blanks skipped."""
    out = {}
    for line in open(os.path.join(HERE, path), encoding="utf-8"):
        line = line.rstrip("\n")
        if not line.strip() or line.lstrip().startswith("#"):
            continue
        body = re.split(r"\s+#\s", line, maxsplit=1)[0] if "  # " in line or " # " in line else line
        key, _, value = body.partition("=")
        out[key.strip()] = value.strip()
    return out


def main():
    pictures = entries("core/pictures.txt")
    edge = entries("edge/pictures.txt")
    markers = set(entries("core/markers.txt")) | {l.split("#")[0].strip() for l in open(os.path.join(HERE, "core/markers.txt"), encoding="utf-8")
                                                  if l.strip() and not l.startswith("#")}
    recipes = {k: v.split() for k, v in entries("recipes.txt").items()}
    langs = [c for c, _ in json.load(open(os.path.join(HERE, "core/languages.json"), encoding="utf-8"))]
    words = {c: entries(f"words/{c}.txt") for c in langs}

    def write(s, depth=0):
        if s in edge and depth == 0: return [edge[s]]
        if s in pictures: return [pictures[s]]
        if s.startswith("["): return []                       # a drawn part belongs to its character
        if s == "?" or depth > 10 or s not in recipes: return None
        out = []
        for p in recipes[s]:
            if depth > 0 and p in markers: continue
            w = write(p, depth + 1)
            if w is None: return None
            out += w
        return out

    ids = sorted({s for c in langs for s in words[c]}, key=lambda s: int(s) if s.isdigit() else 0)
    lines, missing = [], 0
    for s in ids:
        w = write(s)
        if not w or "".join(w) in (".", ","):
            missing += 1
            continue
        cells = [words[c].get(s, "") for c in langs]
        if not any(cells): continue
        parts = " ".join(recipes.get(s, []))
        lines.append("\t".join([s, WJ.join(w), parts] + cells))
    os.makedirs(os.path.join(HERE, "dist"), exist_ok=True)
    with open(os.path.join(HERE, "dist", "dictionary.tsv"), "w", encoding="utf-8") as f:
        f.write("\n".join(lines) + "\n")
    stats = {"words": len(lines), "not_yet": missing, "pictures": len(pictures), "edge": len(edge), "languages": langs,
             "per_language": {c: sum(1 for l in lines if l.split("\t")[3 + i]) for i, c in enumerate(langs)}}
    json.dump(stats, open(os.path.join(HERE, "dist", "stats.json"), "w"), indent=1)
    print(f"{len(lines)} words written in {len(pictures)} pictures; {missing} waiting for a picture")


if __name__ == "__main__":
    main()
