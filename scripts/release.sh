#!/usr/bin/env bash
# Aurora 主题一键发版 SOP：语法检查 → 回归测试 → 同步线上 → 版本号 → 提交推送 → 线上验证
# 用法: bash scripts/release.sh "本次改动描述"
set -uo pipefail

cd /root/aurora-theme
MSG="${1:-update aurora theme}"
PHP=/www/server/php/80/bin/php
SITE=/home/wwwroot/www.liuyg.cn/usr/themes/Aurora
SSH="ssh -i ~/.ssh/aurora_github -o IdentitiesOnly=yes"
TODAY=$(date +%Y%m%d)

echo "══════════ Aurora 一键发版 ══════════"
echo "GitHub 仓库   : git@github.com:714307168/Aurora.git"
echo "线上主题目录  : $SITE"
echo "本次提交说明  : $MSG"

# ---------- Step 2 静态检查 ----------
echo ""
echo "[2/7] 语法检查"
PHP_FAIL=0
for f in *.php; do
  $PHP -l "$f" >/dev/null 2>&1 && echo "  ✓ $f" || { echo "  ✗ $f"; PHP_FAIL=1; }
done
JS_FAIL=0
node --check assets/aurora.js >/dev/null 2>&1 || JS_FAIL=1
[ $JS_FAIL -eq 0 ] && echo "  ✓ assets/aurora.js" || echo "  ✗ assets/aurora.js (node --check)"
if [ $PHP_FAIL -ne 0 ] || [ $JS_FAIL -ne 0 ]; then echo "语法错误，中止。"; exit 1; fi

# ---------- Step 3 回归测试 ----------
echo ""
echo "[3/7] 回归测试 (qa-scan.py)"
python3 scripts/qa-scan.py || { echo "测试未通过，中止。"; exit 1; }

# ---------- Step 4 版本号（涉及 css/js 则 bump 到当日）----------
echo ""
echo "[4/7] 版本号检查"
CHANGED_ASSETS=$(git status --porcelain -- assets/ 2>/dev/null)
if [ -n "$CHANGED_ASSETS" ]; then
  python3 - "$TODAY" <<'PY'
import re,sys
today=int(sys.argv[1]); cur=0
for p in ("header.php","footer.php"):
    s=open(p,encoding='utf-8').read()
    m=re.search(r"\?v=(\d+)",s)
    if m: cur=max(cur,int(m.group(1)))
new=max(cur+1,today)
for p in ("header.php","footer.php"):
    s=open(p,encoding='utf-8').read()
    s2=re.sub(r"\?v=\d+","?v="+str(new),s)
    if s2!=s: open(p,'w',encoding='utf-8').write(s2); print(f"  bump {p} → ?v={new}")
PY
  echo "  ✓ 版本号已递增（取 max(当前+1, 今日)）"
else
  echo "  - 本次未改 assets，跳过版本号"
fi

# ---------- Step 5 同步线上站 ----------
echo ""
echo "[5/7] 同步线上站"
for f in *.php style.css assets/*; do cp "$f" "$SITE/$f"; done
chown -R www:www "$SITE"
touch "$SITE"/*.php 2>/dev/null
echo "  ✓ 已同步并设置权限"

# ---------- Step 6 提交 + 推送 GitHub（含文档同步前提）----------
echo ""
echo "[6/7] 提交 + 推送 GitHub"
git add -A
git commit -m "$MSG${CHANGED_ASSETS:+（含 assets 改动，版本号已 bump）}" && echo "  ✓ commit" || echo "  - 无变更可提交"
GIT_SSH_COMMAND="$SSH" git push 2>&1 | tail -3

# ---------- Step 7 线上验证 ----------
echo ""
echo "[7/7] 线上验证"
for u in "/" "/index.php/archives/37/" "/index.php/links.html"; do
  code=$(curl -s --noproxy '*' -o /dev/null -w "%{http_code}" "https://www.liuyg.cn$u")
  echo "  $code  $u"
done
DS=$(curl -s --noproxy '*' "https://www.liuyg.cn/" | grep -c '//index.php')
echo "  双斜杠: $DS (期望 0)"

echo ""
echo "══════════ 发版完成 ══════════"
