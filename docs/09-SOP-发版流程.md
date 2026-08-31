# 09 · SOP：改动 → 验证 → 发布

## 三个权威位置

| 环境 | 位置 |
| --- | --- |
| 源码/Git | `/root/aurora-theme/` |
| 线上主题 | `/home/wwwroot/www.liuyg.cn/usr/themes/Aurora/` |
| GitHub | `git@github.com:714307168/Aurora.git` |

只改源码目录，禁止直接手改线上副本。

## 一键发布

```bash
cd /root/aurora-theme
bash scripts/release.sh "feat: 变更说明"
```

脚本严格执行 8 个节点：

1. `unittest` 功能契约；
2. PHP/JS/Shell/diff 语法检查；
3. CSS/JS 改动时递增 `?v=`，只增不减；
4. 在同盘构建完整暂存主题，将现有主题目录移入 `.aurora-backups/Aurora-时间`，再用目录 rename 切换，避免逐文件混合版本；
5. 原子生成 `/home/wwwroot/www.liuyg.cn/sitemap.xml`；
6. 对**同步后的新线上版本**执行 `qa-scan.py`；失败自动恢复上一版；
7. `git commit` + SSH push GitHub；
8. 首页、文章、友链、sitemap HTTP 200 + 双斜杠 0。

## 为什么 QA 必须放在同步后

旧流程在同步前跑浏览器 QA，测到的是旧线上代码，会“假绿”。现在本地契约负责发布前拦截，真实浏览器 QA 在同步后执行，并有自动回滚兜底。

## 回滚

自动回滚：目录切换后任何 sitemap、QA、HTTP、commit 或 push 失败，脚本恢复完整旧主题目录与旧 sitemap 后退出 1。push 成功后才解除回滚 trap。

人工回滚：

```bash
BACKUP=/home/wwwroot/www.liuyg.cn/usr/themes/.aurora-backups/Aurora-时间
mv /home/wwwroot/www.liuyg.cn/usr/themes/Aurora /home/wwwroot/www.liuyg.cn/usr/themes/Aurora.failed
mv "$BACKUP" /home/wwwroot/www.liuyg.cn/usr/themes/Aurora
chown -R www:www /home/wwwroot/www.liuyg.cn/usr/themes/Aurora
```

最多保留最近 5 份发布备份。Git 侧另用 `git revert HEAD`，不要强推覆盖历史。

## 文档同步规则

- 模板/DOM → `03`；JS → `04`；设置 → `05`；测试 → `08`；流程 → `09`；规划状态 → `10/11`；
- README 只写用户可见功能和安装方法；实现坑与 SOP 留在 docs；
- 发版完成必须满足：线上运行证据 + Git 远端推送，不以“文件已写”代替上线。
