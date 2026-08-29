/* Aurora — 交互脚本（导航 + 代码块增强 + 回到顶部） */
(function () {
  "use strict";

  // 移动端菜单开合
  var toggle = document.getElementById("nav-toggle");
  var nav = document.getElementById("aurora-nav");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      nav.classList.toggle("open");
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
      if (pre.querySelector(".code-head")) continue;
      var code = pre.querySelector("code");
      if (!code) continue;

      var lang = "";
      var m = (code.className || "").match(/language-([\w+-]+)/);
      if (m) lang = m[1].toLowerCase();
      if (!lang) lang = detectLang(code.textContent || "");

      var head = document.createElement("div");
      head.className = "code-head";
      var tag = document.createElement("span");
      tag.className = "code-lang";
      tag.textContent = LANG_NAMES[lang] || "代码";
      var btn = document.createElement("button");
      btn.className = "code-copy";
      btn.type = "button";
      btn.textContent = "复制";
      btn.addEventListener("click", function () {
        var text = (code.textContent || "").replace(/\n$/, "");
        var done = function () {
          btn.textContent = "已复制";
          setTimeout(function () { btn.textContent = "复制"; }, 1600);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(done, function () { fallback(text, done); });
        } else { fallback(text, done); }
      });
      head.appendChild(tag);
      head.appendChild(btn);
      pre.appendChild(head);
    }
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

  function init() { enhanceCode(); buildToc(); }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else { init(); }
})();
