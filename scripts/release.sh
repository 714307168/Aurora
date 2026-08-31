#!/usr/bin/env bash
# Aurora 一键发版：本地门禁 → 原子目录切换 → 线上 QA → GitHub；任一失败恢复主题和 sitemap
# 用法: bash scripts/release.sh "本次改动描述"
set -Eeuo pipefail

cd /root/aurora-theme
MSG="${1:-update aurora theme}"
PHP=/www/server/php/80/bin/php
WEBROOT=/home/wwwroot/www.liuyg.cn
SITE="$WEBROOT/usr/themes/Aurora"
BACKUP_ROOT="$WEBROOT/usr/themes/.aurora-backups"
SSH="ssh -i ~/.ssh/aurora_github -o IdentitiesOnly=yes"
TODAY=$(date +%Y%m%d)
STAMP=$(date +%Y%m%d-%H%M%S)
STAGE="$BACKUP_ROOT/.Aurora-stage-$STAMP"
BACKUP="$BACKUP_ROOT/Aurora-$STAMP"
SITEMAP="$WEBROOT/sitemap.xml"
SITEMAP_BACKUP="$BACKUP_ROOT/sitemap-$STAMP.xml"
SITEMAP_EXISTED=0
SWAP_STARTED=0

rollback() {
  local code=$?
  trap - ERR
  echo "发布失败，执行完整回滚（exit=$code）"
  if [ "$SWAP_STARTED" -eq 1 ] && [ -d "$BACKUP" ]; then
    rm -rf "$SITE"
    mv "$BACKUP" "$SITE"
    chown -R www:www "$SITE"
  fi
  if [ "$SITEMAP_EXISTED" -eq 1 ] && [ -f "$SITEMAP_BACKUP" ]; then
    install -m 0644 -o www -g www "$SITEMAP_BACKUP" "$SITEMAP"
  elif [ "$SWAP_STARTED" -eq 1 ]; then
    rm -f "$SITEMAP"
  fi
  rm -rf "$STAGE"
  exit "$code"
}
trap rollback ERR

echo "══════════ Aurora 一键发版 ══════════"
echo "线上主题目录：$SITE"
echo "提交说明：$MSG"

echo "[1/8] 功能契约测试"
python3 -m unittest tests/test_theme_features.py -v

echo "[2/8] 语法、差异与数据库前置检查"
for file in *.php scripts/*.php; do
  "$PHP" -l "$file" >/dev/null
  echo "  ✓ $file"
done
node --check assets/aurora.js
python3 -m py_compile scripts/qa-scan.py tests/test_theme_features.py
bash -n scripts/release.sh
git diff --check
"$PHP" -r '
require "/home/wwwroot/www.liuyg.cn/config.inc.php";
$db=\Typecho\Db::get();
$rows=$db->fetchAll($db->query("SHOW COLUMNS FROM `".$db->getPrefix()."contents`"));
$fields=array_column($rows,"Field");
if(!in_array("views",$fields,true)){fwrite(STDERR,"缺少 contents.views，发布中止\n");exit(1);}
echo "  ✓ contents.views 已存在\n";
'

echo "[3/8] 静态资源版本号"
CHANGED_ASSETS=$(git status --porcelain -- assets/ 2>/dev/null || true)
if [ -n "$CHANGED_ASSETS" ]; then
  python3 - "$TODAY" <<'PY'
import re, sys
now = int(sys.argv[1]); current = 0
for path in ("header.php", "footer.php"):
    source = open(path, encoding="utf-8").read()
    match = re.search(r"\?v=(\d+)", source)
    if match:
        current = max(current, int(match.group(1)))
new = max(current + 1, now)
for path in ("header.php", "footer.php"):
    source = open(path, encoding="utf-8").read()
    updated = re.sub(r"\?v=\d+", "?v=" + str(new), source)
    if updated != source:
        open(path, "w", encoding="utf-8").write(updated)
        print(f"  ✓ {path} → ?v={new}")
PY
else
  echo "  - assets 未改，跳过"
fi

echo "[4/8] 构建完整暂存主题并同步线上站（目录切换）"
mkdir -p "$BACKUP_ROOT" "$STAGE/assets" "$STAGE/scripts"
for file in *.php style.css; do install -m 0644 -o www -g www "$file" "$STAGE/$file"; done
for file in assets/*; do install -m 0644 -o www -g www "$file" "$STAGE/$file"; done
install -m 0644 -o www -g www scripts/generate-sitemap.php "$STAGE/scripts/generate-sitemap.php"
if [ -f "$SITEMAP" ]; then
  cp -a "$SITEMAP" "$SITEMAP_BACKUP"
  SITEMAP_EXISTED=1
fi
mv "$SITE" "$BACKUP"
SWAP_STARTED=1
mv "$STAGE" "$SITE"
chown -R www:www "$SITE"
echo "  ✓ 目录级切换完成，备份：$BACKUP"

echo "[5/8] 生成并验证 sitemap"
AURORA_WEBROOT="$WEBROOT" "$PHP" scripts/generate-sitemap.php
python3 - "$SITEMAP" <<'PY'
import sys
import xml.etree.ElementTree as ElementTree
root = ElementTree.parse(sys.argv[1]).getroot()
count = len(root)
if count < 50:
    raise SystemExit(f"sitemap URL 数异常：{count}")
print(f"  ✓ sitemap XML 有效，URL={count}")
PY

echo "[6/8] 同步后线上回归与 HTTP 验收"
AURORA_SITE=https://www.liuyg.cn python3 scripts/qa-scan.py
for path in "/" "/index.php/archives/37/" "/index.php/links.html" "/sitemap.xml"; do
  code=$(curl -s --noproxy '*' -o /dev/null -w "%{http_code}" "https://www.liuyg.cn$path")
  echo "  $code  $path"
  [ "$code" = "200" ]
done
DOUBLE_SLASH=$(curl -s --noproxy '*' "https://www.liuyg.cn/" | grep -c '//index.php' || true)
[ "$DOUBLE_SLASH" = "0" ]

echo "[7/8] 提交并推送 GitHub"
git add -- .gitignore README.md style.css *.php assets docs scripts tests
if git diff --cached --quiet; then
  echo "  - 无新变更提交"
else
  git commit -m "$MSG${CHANGED_ASSETS:+（含 assets 改动，版本号已 bump）}"
fi
GIT_SSH_COMMAND="$SSH" git push
# push 已成功后不再因只读远端核验失败回滚已验证的线上版本，避免远端已更新而生产被退回。
SWAP_STARTED=0
trap - ERR

echo "[8/8] 收尾与远端核验"
LOCAL_HEAD=$(git rev-parse HEAD)
REMOTE_HEAD=$(GIT_SSH_COMMAND="$SSH" git ls-remote origin refs/heads/main | cut -f1)
[ "$LOCAL_HEAD" = "$REMOTE_HEAD" ]
rm -f "$SITEMAP_BACKUP"
python3 - "$BACKUP_ROOT" <<'PY'
import pathlib, shutil, sys
root = pathlib.Path(sys.argv[1])
backups = sorted((p for p in root.glob("Aurora-*") if p.is_dir()), key=lambda p: p.stat().st_mtime, reverse=True)
for old in backups[5:]:
    shutil.rmtree(old)
for stage in root.glob(".Aurora-stage-*"):
    shutil.rmtree(stage, ignore_errors=True)
PY
SWAP_STARTED=0
trap - ERR
echo "  ✓ 本地/远端 HEAD：$LOCAL_HEAD"
echo "══════════ 发版完成 ══════════"
