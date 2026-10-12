/* tiny-talk.world: the dictionary, read from the public repo (or the copy beside this page if GitHub is out of reach). */
const TT = (() => {
  const REPO = "wallisoft/tiny-talk";
  const RAW = `https://raw.githubusercontent.com/${REPO}/main/`;
  const WJ = "\u2060";
  const NAMES = { en: "English", sv: "Svenska", no: "Norsk", da: "Dansk", fi: "Suomi", is: "Íslenska", de: "Deutsch", nl: "Nederlands",
    af: "Afrikaans", fr: "Français", es: "Español", pt: "Português", it: "Italiano", pl: "Polski", hu: "Magyar", lt: "Lietuvių",
    lv: "Latviešu", ru: "Русский", eo: "Esperanto" };
  let langs = [], rows = [], byId = new Map(), byPic = new Map(), core = new Map();

  async function text(path) {
    for (const url of [RAW + path, "data/" + path.split("/").pop().replace(/\.tsv$/, ".txt")]) {
      try { const r = await fetch(url, { cache: "no-cache" }); if (r.ok) return await r.text(); } catch (e) {}
    }
    throw new Error("could not load " + path);
  }
  async function load() {
    const [dict, langList, pics] = await Promise.all([text("dist/dictionary.tsv"), text("core/languages.json"), text("core/pictures.txt")]);
    langs = JSON.parse(langList).map(x => x[0]);
    rows = dict.split("\n").filter(Boolean).map(line => {
      const f = line.split("\t");
      const words = {};
      langs.forEach((c, i) => words[c] = (f[3 + i] || "").split(";").filter(Boolean));
      return { id: f[0], pics: f[1], bare: f[1].split(WJ).join(""), parts: (f[2] || "").split(" ").filter(Boolean), words };
    });
    rows.forEach(r => { byId.set(r.id, r); if (!byPic.has(r.bare)) byPic.set(r.bare, r); });
    pics.split("\n").forEach(l => { const m = l.match(/^(\d+)=(\S+)\s+#\s*(.*)$/); if (m) core.set(m[1], { pic: m[2], gloss: m[3] }); });
    return TT;
  }
  function lang() {
    let l = ""; try { l = localStorage.getItem("tt-lang") || ""; } catch (e) {}
    if (!l) l = (navigator.language || "en").slice(0, 2).toLowerCase().replace("nb", "no").replace("nn", "no");
    return NAMES[l] ? l : "en";
  }
  function setLang(l) { try { localStorage.setItem("tt-lang", l); } catch (e) {} }
  const word = (r, l = lang()) => r && (r.words[l][0] || r.words.en[0] || "");
  function find(q, l = lang(), max = 30) {
    q = q.trim().toLowerCase(); if (!q) return [];
    const exact = [], starts = [], inside = [];
    for (const r of rows) {
      const ws = r.words[l].map(w => w.toLowerCase());
      if (ws.some(w => w === q || w === "to " + q)) exact.push(r);
      else if (ws.some(w => w.startsWith(q) || w.startsWith("to " + q))) starts.push(r);
      else if (q.length > 3 && ws.some(w => w.includes(q))) inside.push(r);
    }
    return exact.concat(starts, inside).slice(0, max);
  }
  /* pictures back into words: each group whole if it is a word, else the longest known runs */
  function read(textIn, l = lang()) {
    return textIn.split(WJ).join("").trim().split(/\s+/).filter(Boolean).map(group => {
      if (byPic.has(group)) return word(byPic.get(group), l);
      const cps = Array.from(group), parts = [];
      for (let i = 0; i < cps.length;) {
        let j = cps.length;
        for (; j > i; j--) { const bit = cps.slice(i, j).join(""); if (byPic.has(bit)) { parts.push(word(byPic.get(bit), l)); break; } }
        i = j > i ? j : i + 1;
      }
      return parts.length ? "(" + parts.join(" + ") + ")" : "?";
    }).join(" ");
  }
  const suggest = (kind, r, extra = {}) => {
    const p = new URLSearchParams({ template: kind + ".yml", title: (kind === "picture" ? "Picture: " : "Word: ") + word(r, "en") + " " + (r ? r.bare : ""),
      symbol: r ? r.id : "", meaning: word(r, lang()), current: kind === "picture" ? (r ? r.bare : "") : word(r, lang()), ...extra });
    return `https://github.com/${REPO}/issues/new?` + p.toString();
  };
  const esc = s => String(s == null ? "" : s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
  function langPicker(el, onChange) {
    el.innerHTML = langs.map(c => `<option value="${c}">${NAMES[c]}</option>`).join("");
    el.value = lang();
    el.addEventListener("change", () => { setLang(el.value); onChange && onChange(el.value); });
  }
  return { load, lang, setLang, word, find, read, suggest, esc, langPicker, NAMES, REPO, RAW,
    get rows() { return rows; }, get langs() { return langs; }, byId: id => byId.get(id), core: () => core };
})();
