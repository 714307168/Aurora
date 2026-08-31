#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Aurora 主题回归脚本：可达性 + 关键 DOM + console 错误
用法: python3 scripts/qa-scan.py
需: 本机已装 google-chrome；脚本自动拉起 CDP 并逐页检查。"""
import json, os, re, subprocess, sys, time, urllib.request, urllib.parse
try:
    import websocket
except ImportError:
    subprocess.run([sys.executable, "-m", "pip", "install", "-q", "websocket-client"])
    import websocket

BASE = os.environ.get("AURORA_SITE", "https://www.liuyg.cn")
CDP = "http://127.0.0.1:9222"

def ensure_chrome():
    try:
        urllib.request.urlopen(CDP + "/json/version", timeout=3)
        return
    except Exception:
        pass
    subprocess.Popen(
        ["/usr/bin/google-chrome", "--headless", "--disable-gpu", "--no-sandbox",
         "--remote-debugging-port=9222", "--remote-allow-origins=*",
         "--user-data-dir=/tmp/aurora-qa", "about:blank"],
        stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    for _ in range(20):
        time.sleep(1)
        try:
            urllib.request.urlopen(CDP + "/json/version", timeout=2); return
        except Exception: pass
    sys.exit("无法启动 Chrome CDP")

def cdp():
    req = urllib.request.Request(CDP + "/json/new?" + urllib.parse.quote("about:blank", safe=''), method='PUT')
    return websocket.create_connection(json.loads(urllib.request.urlopen(req).read())["webSocketDebuggerUrl"], timeout=60)

def navigate(w, url):
    mid = 0
    def send(method, params=None):
        nonlocal mid; mid += 1
        w.send(json.dumps({"id": mid, "method": method, "params": params or {}}))
        while True:
            m = json.loads(w.recv())
            if m.get("id") == mid: return m.get("result", {})
    send("Runtime.enable"); send("Page.enable"); send("Network.enable")
    send("Network.setCacheDisabled", {"cacheDisabled": True})
    send("Page.addScriptToEvaluateOnNewDocument", {"source": """
      if (!window.__auroraErrors) {
        window.__auroraErrors=[];
        window.addEventListener('error',e=>window.__auroraErrors.push(String(e.message||e.error||'error')));
        window.addEventListener('unhandledrejection',e=>window.__auroraErrors.push(String(e.reason||'rejection')));
      }
    """})
    nav = send("Page.navigate", {"url": url})
    if nav.get("errorText"):
        return {"err": "导航失败: " + nav["errorText"]}
    # 不能只看 readyState：上一页可能仍是 complete。必须同时确认 location.href 已切到目标 URL。
    target = url.rstrip("/")
    loaded = False
    current = ""
    for _ in range(30):
        state = send("Runtime.evaluate", {
            "expression": "({href:location.href,state:document.readyState})",
            "returnByValue": True
        }).get("result", {}).get("value", {})
        current = (state.get("href") or "").rstrip("/")
        if current == target and state.get("state") == "complete":
            loaded = True
            break
        time.sleep(0.5)
    if not loaded:
        return {"err": "页面加载超时: target=%s current=%s" % (url, current)}
    time.sleep(1)
    r = send("Runtime.evaluate", {"expression": """(()=>{
      const $$=s=>document.querySelectorAll(s);
      const ex={};
      try{
        ex.title=(document.title||'').slice(0,40);
        ex.aurora=!!document.querySelector('.aurora-container')||!!document.querySelector('.aurora-post');
        ex.toc=$$('#post-toc a').length;
        ex.codeHead=$$('.code-head').length;
        ex.codeAligned=Array.from($$('.post-body pre.code-lines')).every(p=>{
          const n=p.querySelector('.line-nums'),c=p.querySelector('code');
          if(!n||!c)return false;
          const nr=n.getBoundingClientRect(),cr=c.getBoundingClientRect();
          return Math.abs(nr.y-cr.y)<2 && cr.x>=nr.x+nr.width-2;
        });
        ex.sideBox=$$('.side-box').length;
        ex.navDrop=!!document.querySelector('.nav-drop .drop-menu');
        ex.beian=(document.querySelector('.footer-beian')?.textContent||'').slice(0,14);
        ex.dslash=$$('a[href*="//index.php"]').length;
        ex.emptyHref=$$('a[href=""]').length;
        ex.hljs=typeof hljs!=='undefined';
        ex.canonical=!!document.querySelector('link[rel="canonical"]');
        ex.jsonld=!!document.querySelector('script[type="application/ld+json"]');
        ex.reactions=$$('.post-reactions [data-action]').length;
        ex.reading=(document.querySelector('#post-reading-meta')?.textContent||'').trim();
        ex.views=(document.querySelector('.post-meta .views')?.textContent||'').trim();
        ex.related=$$('.related-posts a').length;
        ex.errors=(window.__auroraErrors||[]).slice(0,5);
        ex.h=(document.body?document.body.scrollHeight:0);
      } catch(e) { ex.err=e.message; }
      return ex;
    })()""", "returnByValue": True})
    return r.get("result", {}).get("value", {})

def main():
    ensure_chrome()
    w = cdp()
    pages = [
        ("首页", BASE + "/"),
        ("文章37(TOC)", BASE + "/index.php/archives/37/"),
        ("文章5(代码)", BASE + "/index.php/archives/5/"),
        ("友链", BASE + "/index.php/links.html"),
        ("404", BASE + "/index.php/aurora-qa-404-" + str(int(time.time()))),
    ]
    print("=" * 50)
    print("Aurora 主题回归 · 站点:", BASE)
    print("=" * 50)
    failures = 0
    for name, url in pages:
        v = navigate(w, url)
        if "err" in v:
            failures += 1
            print(f"[{name}] ⚠ 检查异常: {v['err']}")
            continue
        flags = []
        if name == "文章37(TOC)" and (v.get("toc") or 0) < 2: flags.append("TOC缺失")
        if name == "文章5(代码)" and (v.get("codeHead") or 0) < 1: flags.append("代码块增强缺失")
        if name == "文章5(代码)" and not v.get("codeAligned"): flags.append("代码行号与正文错位")
        if (v.get("dslash") or 0) > 0: flags.append("双斜杠"+str(v.get("dslash")))
        if (v.get("emptyHref") or 0) > 0: flags.append("空href"+str(v.get("emptyHref")))
        if not v.get("beian"): flags.append("备案缺失")
        if not v.get("aurora"): flags.append("非Aurora主题")
        if not v.get("canonical"): flags.append("canonical缺失")
        if v.get("errors"): flags.append("JS错误:" + ";".join(v.get("errors")))
        if name.startswith("文章"):
            if not v.get("jsonld"): flags.append("JSON-LD缺失")
            if (v.get("reactions") or 0) != 2: flags.append("互动按钮缺失")
            if not v.get("reading"): flags.append("阅读时长缺失")
            if not v.get("views"): flags.append("浏览量缺失")
        if name == "文章5(代码)" and not v.get("hljs"): flags.append("hljs缺失")
        if flags: failures += 1
        status = "✅" if not flags else "⚠ " + ", ".join(flags)
        print(f"[{name}] {status} | TOC:{v.get('toc') or 0} 代码块:{v.get('codeHead') or 0} 侧栏:{v.get('sideBox') or 0} hljs:{'ok' if v.get('hljs') else 'no'}")
    print("=" * 50)
    print("提示：改动主题后若改过 css/js，请确认 header/footer 的 ?v= 已递增。")
    w.close()
    if failures:
        print(f"回归失败：{failures} 个页面存在问题")
        sys.exit(1)
    print("回归通过：所有页面关键契约正常")

if __name__ == "__main__":
    main()
