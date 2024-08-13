<p align="center">
    <br> <a href="README.md">English</a> | 中文
</p>
<p align="center">
    <em>百分百鲜橙汁 统计数据</em>
</p>

# 百分百鲜橙汁 统计数据
本项目的起点是 [一个远古时期的个人统计查询项目](https://gitlab.com/gabuch2/orange-juice-stats)，旧项目缺乏维护，已经难以满足其功能。   
本项目是它的增强版本。   

羽希在 2021 年初将其重新整理并拓展到全球统计，部署到 100oj.com 使其成为众多橙汁工具的一部分。

# 部署

1. PHP 7.0 或更高版本。   
2. 在 ``config.php`` 填入你的 SteamAPI Key 。你可以在 [这里](https://steamcommunity.com/dev) 获取。   
3. (可选) 如果你要使用 renders/ 下的内容来出图，请为 PHP 安装 [ImageMagick 拓展](https://www.php.net/manual/en/imagick.setup.php)。

# 公共实例

全球统计的入口是 [global.php](https://interface.100oj.com/stat/global.php) 。

个人统计的入口是 [player.php](https://interface.100oj.com/stat/player.php) 。

## License
沿用旧项目的 [GNU Affero General Public License v3.0](https://www.gnu.org/licenses/agpl-3.0.en.html)
