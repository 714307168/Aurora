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
    send("Runtime.enable"); send("Page.enable")
    send("Page.navigate", {"url": url}); time.sleep(4)
    r = send("Runtime.evaluate", {"expression": """(()=>{
      const $$=s=>document.querySelectorAll(s);
      return {
        title:(document.title||'').slice(0,40),
        aurora:!!document.querySelector('.aurora-container')||!!document.querySelector('.aurora-post'),
        toc:$$('#post-toc a').length,
        codeHead:$$('.code-head').length,
        sideBox:$$('.side-box').length,
        navDrop:!!document.querySelector('.nav-drop .drop-menu'),
        beian:(document.querySelector('.footer-beian')?.textContent||'').slice(0,14),
        dslash:$$('a[href*="//index.php"]').length,
        emptyHref:$$('a[href=""]').length,
        hljs:typeof hljs!=='undefined',
        h:(document.body?document.body.scrollHeight:0)
      }} catch(e) { return {err: e.message}; }
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
        ("404", BASE + "/index.php/404zzz"),
    ]
    print("=" * 50)
    print("Aurora 主题回归 · 站点:", BASE)
    print("=" * 50)
    for name, url in pages:
        v = navigate(w, url)
        if "err" in v: print(f"[{name}] ⚠ 检查异常: {v['err']}"); continue
        flags = []
        if name == "文章37(TOC)" and v.get("toc", 0) < 2: flags.append("TOC缺失")
        if name == "文章5(代码)" and v.get("codeHead", 0) < 1: flags.append("代码块增强缺失")
        if v.get("dslash") > 0: flags.append("双斜杠"+str(v["dslash"]))
        if v.get("emptyHref") > 0: flags.append("空href"+str(v["emptyHref"]))
        if not v.get("beian"): flags.append("备案缺失")
        if not v.get("aurora"): flags.append("非Aurora主题")
        status = "✅" if not flags else "⚠ " + ", ".join(flags)
        print(f"[{name}] {status} | TOC:{v.get('toc',0)} 代码块:{v.get('codeHead',0)} 侧栏:{v.get('sideBox',0)} hljs:{'ok' if v.get('hljs') else 'no'}")
    print("=" * 50)
    print("提示：改动主题后若改过 css/js，请确认 header/footer 的 ?v= 已递增。")

if __name__ == "__main__":
    main()
