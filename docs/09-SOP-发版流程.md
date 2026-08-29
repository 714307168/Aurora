# 09 · SOP：改动 → 测试 → 发布 → 上线

> 主题每次改动的标准作业流程。**按顺序执行，每步有通过标准；不通过就停，别带问题上线。**

## 0. 三条路径（先记住）

| 环境 | 位置 | 说明 |
| --- | --- | --- |
| 本地项目 | `/root/aurora-theme/` | **源头**，改代码只在这改，git 仓库在这 |
| GitHub | `git@github.com:714307168/Aurora.git` | 开源仓库，SSH 推送 |
| 线上站 | `/home/wwwroot/www.liuyg.cn/usr/themes/Aurora/` | 实际生效的博客主题 |

原则：**改代码一律在本地项目改 → 测试通过 → 推 GitHub → 再同步线上**。不要直接在线上站改。

---

## Step 1　修改
在 `/root/aurora-theme/` 改主题文件（PHP/CSS/JS/设置）。

## Step 2　静态检查
```bash
cd /root/aurora-theme
for f in *.php; do /www/server/php/80/bin/php -l "$f" >/dev/null 2>&1 && echo "✓ $f" || echo "✗ $f"; done
python3 -c "import ast;ast.parse(open('assets/aurora.js').read())"   # JS 语法
```
**通过标准**：无 `✗`。

## Step 3　回归测试
```bash
python3 scripts/qa-scan.py     # 可达性 + TOC/代码块/侧栏/双斜杠/空href/备案 + console
```
**通过标准**：每个页面输出 `✅` 或仅合理提示（如无代码块页 codeHead=0 属正常）。出现 `双斜杠`/`空href`/`TOC缺失`/`备案缺失` 必须修。

## Step 4　版本号（改了 css/js 必做）
改了 `assets/aurora.css` 或 `assets/aurora.js`，必须让 `?v=` 递增，否则 CDN 缓存旧文件：
```bash
# header.php 的 aurora.css?v=YYYYMMDD 与 footer.php 的 aurora.js?v=YYYYMMDD 同步 bump 到当日日期
```
**通过标准**：bump 后 `curl -s http://127.0.0.1/ -H 'Host: www.liuyg.cn' | grep -o 'aurora.[a-z]*.v=[0-9]*'` 显示新值。

## Step 5　同步线上站
```bash
for f in *.php style.css assets/*; do
  cp "$f" /home/wwwroot/www.liuyg.cn/usr/themes/Aurora/"$f"
done
chown -R www:www /home/wwwroot/www.liuyg.cn/usr/themes/Aurora
touch /home/wwwroot/www.liuyg.cn/usr/themes/Aurora/*.php 2>/dev/null   # 触发 opcache 失效
```
**通过标准**：文件清单一致，无权限错误。

## Step 6　文档同步（强制）
改了什么就更新对应文档（见 [README.md](README.md) 的「文档同步规则」）：模板→01/03，CSS→02，JS→04，设置→05，坑→06/07，测试→08。

## Step 7　提交 + 推送 GitHub
```bash
cd /root/aurora-theme
git add -A
git commit -m "描述变更（含文档同步）"
GIT_SSH_COMMAND="ssh -i ~/.ssh/aurora_github -o IdentitiesOnly=yes" git push
```
**通过标准**：`git push` 显示 `main -> main`，无 403。

## Step 8　线上验证
```bash
for u in / /index.php/archives/37/ /index.php/links.html; do
  curl -s --noproxy '*' -o /dev/null -w "%{http_code} $u\n" "https://www.liuyg.cn$u"
done
curl -s --noproxy '*' "https://www.liuyg.cn/" | grep -c '//index.php'   # 期望 0
```
**通过标准**：全部 200、双斜杠 0。

## Step 9　回滚（出问题时的退路）
```bash
# 本地回退 + 重新推
cd /root/aurora-theme && git revert HEAD && git push
# 或临时把主题切回备份：后台「外观」切到 Single/Nova
```

---

## 一键脚本

`scripts/release.sh` 把 Step 2-8 串成一条命令（语法检查 → 测试 → 同步线上 → bump 版本提示 → commit+push → 线上验证）。日常改动跑它：

```bash
bash scripts/release.sh "本次改动描述"
```

> 高风险操作：脚本会自动 commit/push 并同步线上站，**执行前请确认当前分支干净、改动已就绪**。
