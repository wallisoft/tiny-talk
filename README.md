# tiny-talk 👄➡️⬅️

**Say it in pictures anyone can read.**

tiny-talk writes [Blissymbolics](https://www.blissymbolics.org) in emoji. Bliss is a language of meanings
rather than sounds: every one of its thousands of words is built from a few hundred basic characters. Give
each basic character one picture, and every word follows from Bliss's own recipe:

| word | pictures | built from |
|---|---|---|
| hello | 👄➡️⬅️ | words arriving |
| goodbye | 👄⬅️➡️ | words leaving |
| happy | ❤️⬆️🏷️ | feeling, up, (describing) |
| sad | ❤️⬇️🏷️ | feeling, down, (describing) |
| tomorrow | 📅🔜 | day, soon |
| teacher | 🧑🎁🎓 | person, giving, knowledge |
| peace | ❤️🌌🌍 | feeling, sky, earth |

A message written this way belongs to no language. Whoever reads it reads it in theirs: the same
👄➡️⬅️ 🧑❤️➕❗ is *bonjour ami*, *Guten Tag Freund*, *здравствуйте друг*.

- **The app:** a messenger. Type in your own language; what travels is only pictures, and it arrives in the
  reader's language. Lessons and a dictionary in 19 languages. [Android app](https://visualised.org/downloads/TinyTalk.apk),
  or [in a browser](https://tiny-talk.world/chat.html).
- **The site:** [tiny-talk.world](https://tiny-talk.world): chat in the browser, take the lessons, look up any word, vote.

## What is here

Everything is plain text, one thing to a line, so anyone can read it and every change is easy to see.

| file | what it holds | how it changes |
|---|---|---|
| `core/pictures.txt` | one picture for each basic character | slowly, in numbered editions |
| `core/markers.txt` | Bliss's grammar markers | rarely |
| `recipes.txt` | what each word is built from | as Bliss's vocabulary grows |
| `words/xx.txt` | the words for each symbol, in each language | freely: corrections and additions welcome |
| `bliss/` | the official Blissymbol for each word, by BCI number (SVG, from BCI) | with BCI's releases |
| `lessons.txt` | the lessons: pictures and the words they build, no language in them | freely |
| `edge/pictures.txt` | whole words given a picture of their own | by vote |
| `dist/` | the dictionary the app and the site read | built from the rest; don't edit |
| `site/` | the tiny-talk.world website (it reads `dist/` from here) | improvements welcome |

`python3 tools/build.py` builds `dist/` from the rest, with nothing but Python itself.

## Taking part

You don't need to write code. See [CONTRIBUTING.md](CONTRIBUTING.md): suggest a better picture or a better word
with a form, and vote with a 👍 on the suggestions you like. The popular ones rise to the top of the
[vote page](https://tiny-talk.world/vote.html). How suggestions become part of tiny-talk:
[GOVERNANCE.md](GOVERNANCE.md).

## Licence and thanks

The Blissymbol drawings, the words, recipes and their translations come from the **BCI Authorized Vocabulary** (2026-09-12) by
[Blissymbolics Communication International](https://www.blissymbolics.org), under
[CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/). tiny-talk's data, including the pictures it
gives each character, is shared under the same licence: see [LICENSE.md](LICENSE.md). The code in `tools/`
is MIT.

tiny-talk is an independent project. It is not made or endorsed by BCI; the official Blissymbols are theirs,
and tiny-talk is one way of writing them.

Made by Wallisoft, with Visualised.
