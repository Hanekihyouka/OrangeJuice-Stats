<p align="center">
    <br> English | <a href="README-CN.md">中文</a>
</p>
<p align="center">
    <em>100% Orange Juice Stats</em>
</p>

# 100% Orange Juice Stats
The starting point of this project was (a personal stats query project from the distant past)[https://gitlab.com/gabuch2/orange-juice-stats], the old project lacked maintenance and was already struggling with its functionality.

Haneki reorganises and expands it to global stats in early 2021, deploying it to 100oj.com making it part of the many Orange Juice tools available.

# Setup

* Based on PHP 7.0
* You need a proper SteamAPI Key to make this work. You can get one on the (Steam's Web API website)[https://steamcommunity.com/dev]. The API keys goes in steamauth/SteamConfig.php and util/StatJsonParser.
* If you use the content under renders/ to make images, you will need (ImageMagick)[https://www.php.net/manual/en/book.imagick.php].

global.php for global stats

player.php for player stats

## License
(GNU Affero General Public License v3.0)[https://www.gnu.org/licenses/agpl-3.0.en.html]