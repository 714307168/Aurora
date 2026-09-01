/* Aurora — 交互脚本（导航 + 代码块增强 + 回到顶部） */
(function () {
  "use strict";

  var UI = document.documentElement.lang === "en-US" ? {
    code: "Code", copy: "Copy", copied: "Copied", expand: "Expand %d lines",
    reading: "About %d words · %d min"
  } : {
    code: "代码", copy: "复制", copied: "已复制", expand: "展开 %d 行",
    reading: "约 %d 字 · %d 分钟"
  };

  // 移动端菜单开合
  var toggle = document.getElementById("nav-toggle");
  var nav = document.getElementById("aurora-nav");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", nav.classList.contains("open") ? "true" : "false");
    });
  }

  // 回到顶部
  var toTop = document.getElementById("to-top");
  if (toTop) {
    toTop.addEventListener("click", function () {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  // 代码块：语言标签 + 一键复制（highlight.js 负责着色）
  var LANG_NAMES = {
    c: "C", cpp: "C++", csharp: "C#", java: "Java", python: "Python", py: "Python",
    javascript: "JavaScript", js: "JavaScript", typescript: "TypeScript", php: "PHP",
    go: "Go", rust: "Rust", sql: "SQL", html: "HTML", xml: "XML", css: "CSS",
    json: "JSON", bash: "Shell", sh: "Shell", shell: "Shell", batch: "Batch",
    bat: "Batch", yaml: "YAML", yml: "YAML", markdown: "Markdown", md: "Markdown",
    dockerfile: "Dockerfile", ini: "INI", conf: "Config", text: "Text"
  };
  var HINTS = [
    ["batch", ["@echo off", "setlocal", "goto ", "pause", "错误"]],
    ["php", ["<?php", "$_GET", "$_POST", "$_SERVER"]],
    ["java", ["import java.", "public static void main", "System.out.print", "public class"]],
    ["cpp", ["#include", "int main(", "cout <<", "using namespace std"]],
    ["go", ["package main", "import \"fmt\"", "func main(", "fmt.Println"]],
    ["python", ["def ", "import ", "from ", "print(", "elif"]],
    ["sql", ["SELECT", "FROM", "INSERT INTO", "CREATE TABLE", "WHERE"]],
    ["bash", ["#!/bin/bash", "#!/bin/sh", "apt install", "sudo ", "systemctl"]],
    ["javascript", ["=>", "const ", "let ", "console.log", "document."]],
    ["html", ["<!DOCTYPE html", "<html", "<meta charset", "<div class"]],
    ["css", ["margin:", "padding:", "display:", "background:", "color:"]],
    ["json", [": ", "null", "true", "false"]],
    ["xml", ["<?xml", "<root", "</root>"]],
    ["dockerfile", ["FROM ", "RUN ", "COPY ", "CMD "]]
  ];

  function detectLang(c) {
    var t = c.slice(0, 900), best = "", bestScore = 0;
    for (var i = 0; i < HINTS.length; i++) {
      var name = HINTS[i][0], kws = HINTS[i][1], score = 0;
      for (var j = 0; j < kws.length; j++) if (t.indexOf(kws[j]) !== -1) score++;
      if (score > bestScore) { bestScore = score; best = name; }
    }
    return best;
  }

  function enhanceCode() {
    var pres = document.querySelectorAll(".post-body pre");
    for (var i = 0; i < pres.length; i++) {
      var pre = pres[i];
      var code = pre.querySelector("code");
      if (!code) continue;

      // 语言标签 + 一键复制（只加一次）
      if (!pre.querySelector(".code-head")) {
        var lang = "";
        var m = (code.className || "").match(/language-([\w+-]+)/);
        if (m) lang = m[1].toLowerCase();
        if (!lang) lang = detectLang(code.textContent || "");
        var head = document.createElement("div");
        head.className = "code-head";
        var tag = document.createElement("span");
        tag.className = "code-lang";
        tag.textContent = LANG_NAMES[lang] || UI.code;
        var btn = document.createElement("button");
        btn.className = "code-copy";
        btn.type = "button";
        btn.textContent = UI.copy;
        (function (codeRef, btnRef) {
          btnRef.addEventListener("click", function () {
            var text = (codeRef.textContent || "").replace(/\n$/, "");
            var done = function () {
              btnRef.textContent = UI.copied;
              setTimeout(function () { btnRef.textContent = UI.copy; }, 1600);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
              navigator.clipboard.writeText(text).then(done, function () { fallback(text, done); });
            } else { fallback(text, done); }
          });
        })(code, btn);
        head.appendChild(tag);
        head.appendChild(btn);
        pre.appendChild(head);
      }

      // 行号 + 长代码折叠（只加一次）
      if (!pre.querySelector(".line-nums")) {
        var codeText = (code.textContent || "").replace(/\n$/, "");
        var lines = codeText ? codeText.split("\n").length : 1;
        if (lines < 1) lines = 1;
        var ln = document.createElement("div");
        ln.className = "line-nums";
        for (var n = 1; n <= lines; n++) {
          var s = document.createElement("span");
          s.textContent = n;
          ln.appendChild(s);
        }
        pre.insertBefore(ln, code);
        pre.classList.add("code-lines");
        if (lines > 18) {
          pre.classList.add("code-collapsed");
          var head2 = pre.querySelector(".code-head");
          if (head2 && !head2.querySelector(".code-fold")) {
            var fold = document.createElement("button");
            fold.className = "code-fold";
            fold.type = "button";
            fold.textContent = UI.expand.replace("%d", lines);
            (function (preRef, foldRef) {
              foldRef.addEventListener("click", function () {
                preRef.classList.remove("code-collapsed");
                foldRef.remove();
              });
            })(pre, fold);
            head2.appendChild(fold);
          }
        }
      }
    }
  }

  // 阅读进度条（仅文章页有 #read-progress 时）
  function readProgress() {
    var bar = document.getElementById("read-progress");
    if (!bar) return;
    var update = function () {
      var doc = document.documentElement;
      var max = (doc.scrollHeight - window.innerHeight) || 1;
      var p = Math.min(100, Math.max(0, (window.scrollY / max) * 100));
      bar.style.width = p + "%";
    };
    window.addEventListener("scroll", update, { passive: true });
    update();
  }

  function fallback(text, done) {
    var ta = document.createElement("textarea");
    ta.value = text; ta.style.position = "fixed"; ta.style.opacity = "0";
    document.body.appendChild(ta); ta.select();
    try { document.execCommand("copy"); done(); } catch (e) {}
    document.body.removeChild(ta);
  }

  // 文章目录（TOC）：提取正文 h2/h3 生成导航，点击平滑跳转，滚动高亮
  function buildToc() {
    var postBody = document.querySelector(".post-body");
    var tocBox = document.getElementById("post-toc");
    if (!postBody || !tocBox) return;
    var heads = postBody.querySelectorAll("h1, h2, h3");
    if (heads.length < 2) return; // 章节太少不显示

    var list = document.createElement("ol");
    list.className = "toc-list";
    for (var i = 0; i < heads.length; i++) {
      var h = heads[i];
      var id = "toc-sec-" + i;
      h.id = id;
      var li = document.createElement("li");
      li.className = h.tagName === "H3" ? "toc-h3" : (h.tagName === "H1" ? "toc-h2" : "toc-h2");
      var a = document.createElement("a");
      a.href = "#" + id;
      a.textContent = h.textContent.trim();
      a.addEventListener("click", function (e) {
        e.preventDefault();
        var t = document.getElementById(this.getAttribute("href").slice(1));
        if (t) window.scrollTo({ top: t.offsetTop - 76, behavior: "smooth" });
      });
      li.appendChild(a);
      list.appendChild(li);
    }
    tocBox.appendChild(list);

    // scrollspy：滚动时高亮当前章节
    var links = Array.prototype.slice.call(tocBox.querySelectorAll("a"));
    function spy() {
      var pos = window.scrollY + 90;
      var cur = -1;
      for (var i = 0; i < heads.length; i++) {
        if (heads[i].offsetTop <= pos) cur = i;
      }
      links.forEach(function (a, idx) { a.classList.toggle("active", idx === cur); });
    }
    window.addEventListener("scroll", spy, { passive: true });
    spy();
  }

  // 亮/暗主题切换：localStorage 记录；新访客固定默认深色，点击后记住选择
  function themeToggle() {
    var btn = document.getElementById("theme-toggle");
    if (!btn) return;
    var apply = function (t) {
      document.documentElement.setAttribute("data-theme", t);
      btn.textContent = t === "light" ? "☀" : "☾";
      btn.setAttribute("aria-pressed", t === "light" ? "true" : "false");
      try { localStorage.setItem("aurora-theme", t); } catch (e) {}
    };
    var saved = null;
    try { saved = localStorage.getItem("aurora-theme"); } catch (e) {}
    var t = saved === "light" || saved === "dark" ? saved : "dark";
    apply(t);
    btn.addEventListener("click", function () {
      var cur = document.documentElement.getAttribute("data-theme");
      apply(cur === "light" ? "dark" : "light");
    });
  }

  // 正文图片：懒加载 + 点击灯箱放大
  function enhanceImages() {
    var imgs = document.querySelectorAll(".post-body img");
    for (var i = 0; i < imgs.length; i++) {
      var img = imgs[i];
      img.setAttribute("loading", "lazy");
      img.setAttribute("decoding", "async");
      img.style.cursor = "zoom-in";
      img.addEventListener("click", function () {
        openLightbox(this.getAttribute("src") || this.currentSrc);
      });
    }
  }
  function openLightbox(src) {
    if (!src) return;
    var exist = document.querySelector(".aurora-lightbox");
    if (exist) exist.remove();
    var box = document.createElement("div");
    box.className = "aurora-lightbox";
    var big = document.createElement("img");
    big.src = src; big.className = "lightbox-img"; big.alt = "";
    box.appendChild(big);
    document.body.appendChild(box);
    var close = function (e) {
      if (!e.key || e.key === "Escape") {
        box.remove();
        document.removeEventListener("keydown", close);
      }
    };
    document.addEventListener("keydown", close);
    box.addEventListener("click", close);
  }

  // 正文字数与阅读时长：中文按字、英文/数字按词，约 400 字词/分钟。
  function readingMeta() {
    var body = document.querySelector(".post-body");
    var target = document.getElementById("post-reading-meta");
    if (!body || !target) return;
    var content = (body.textContent || "").trim();
    var han = content.match(/[\u3400-\u9fff]/g) || [];
    var latin = content.replace(/[\u3400-\u9fff]/g, " ").match(/[A-Za-z0-9]+(?:[-_.'][A-Za-z0-9]+)*/g) || [];
    var words = han.length + latin.length;
    var minutes = Math.max(1, Math.ceil(words / 400));
    var template = target.getAttribute("data-template") || UI.reading;
    target.textContent = template.replace("%d", words).replace("%d", minutes);
  }

  // 免登录喜欢/收藏：仅保存在当前浏览器，不伪造全站计数。
  function postReactions() {
    var box = document.querySelector(".post-reactions");
    if (!box) return;
    var postId = box.getAttribute("data-post-id") || "unknown";
    var buttons = box.querySelectorAll("[data-action]");
    for (var i = 0; i < buttons.length; i++) {
      (function (button) {
        var action = button.getAttribute("data-action");
        var key = "aurora-post-" + action + "-" + postId;
        var apply = function (active) {
          button.classList.toggle("active", active);
          button.setAttribute("aria-pressed", active ? "true" : "false");
          var icon = action === "like" ? (active ? "♥" : "♡") : (active ? "★" : "☆");
          button.firstChild.nodeValue = icon + " ";
        };
        var active = false;
        try { active = localStorage.getItem(key) === "1"; } catch (e) {}
        apply(active);
        button.addEventListener("click", function () {
          active = !active;
          try {
            if (active) localStorage.setItem(key, "1");
            else localStorage.removeItem(key);
          } catch (e) {}
          apply(active);
        });
      })(buttons[i]);
    }
  }

  function init() {
    enhanceCode();
    buildToc();
    readProgress();
    themeToggle();
    enhanceImages();
    readingMeta();
    postReactions();
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else { init(); }
})();
