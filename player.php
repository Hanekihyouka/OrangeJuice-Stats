<?php
//ini_set("display_errors", "On");//打开错误提示
//ini_set("error_reporting",E_ALL);//显示所有错误
require_once ("util/PlayerDataBuilder.php");
require_once ("util/StatJsonParser.php");
require ('steamauth/steamauth.php');
unset($_SESSION['steam_uptodate']);
if (isset($_GET["steamid"])||isset($_SESSION["steamid"])){
    if(isset($_SESSION["steamid"])){$_GET["steamid"] = $_SESSION['steamid'];}
    $responseJsonPlayer = requestJsonPlayer($_GET["steamid"]);
    if(!$responseJsonPlayer===false){
        $contentObject = buildStat($responseJsonPlayer);
    }
    $profileObejct = json_decode(requestJsonProfile($_GET["steamid"]),true)["response"]["players"][0];
}
$contentJson = json_encode($contentObject);
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Info -->
    <meta itemprop="name" content="百分百鲜橙汁橙汁 - 个人统计">
    <meta name="description" itemprop="description" content="玩家的等级、游戏场数、合作等级、角色胜场、胜率等统计信息。">
    <meta itemprop="image" content="./images/share.png">
    <link rel="icon" href="./images/favicon.ico">
    <title>百分百鲜橙汁 - 个人统计</title>
    <!-- Info -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="./css/jquery.dataTables.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="./css/bootstrap.css">
    <!-- Haneki CSS -->
    <link rel="stylesheet" type="text/css" href="./css/common.css">
    <?php
    if ((!isset($_GET["steamid"]))||($responseJsonPlayer===false)){
        echo '<link rel="stylesheet" type="text/css" href="./css/init.css">';
    }else{
        if($contentObject[3]["STAT_PLAYER_LEVEL"]["value"]>=300){
            echo '<style type="text/css">html,body{background-image: url("./images/background2.png") !important;}</style>';
        }
    }
    ?>
    <!-- jQuery -->
    <script type="text/javascript" charset="utf8" src="./js/jquery.js"></script>
    <!-- Bootstrap -->
    <script type="text/javascript" charset="utf8" src="./js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" charset="utf8" src="./js/jquery.dataTables.min.js"></script>
</head>
<body>
<main>
    <?php
    if ((!isset($_GET["steamid"]))||($responseJsonPlayer===false)){
    ?>
    <div class="init-content">
        <div class="init-box">
            <h2>百分百鲜橙汁，玩家统计</h2>
            <form>
                <div class="init-inputbox">
                    <input type="text" name="steamid" required="">
                    <label>Steam64id</label>
                </div>
                <input type="submit" name="" value="查询">
                <h4> 或者通过 steam 登录</h4>
                <?php 
                loginbutton("rectangle");
                if($responseJsonPlayer===false){
                ?>
                <div style="color:red">
                <p>I can't access this users' stats. Verify their game stats are set to public.</p>
                <p>无法获取该玩家的统计数据，请将 <b>个人资料->隐私设置->游戏详情</b>，设置为公开/所有人可见。</p>
                </div>
                <?php
                }
                ?>
            </form>
        </div>
    </div>
    <?php
    }
    ?>
    <div class="box">
        <div>
            <table class="table-player-info">
                <tr>
                    <th rowspan="5" colspan="2" class="visible-md visible-lg">
                        <img src='<?php echo $profileObejct["avatarfull"]; ?>'>
                    </th>
                    <th colspan="4">
                        <h2><?php echo $profileObejct["personaname"]; ?> 的统计数据</h2>
                    </th>
                </tr>
                <tr>
                    <td>
                        <p>等级:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_LEVEL"]["value"]; ?></p>
                    </td>
                    <td>
                        <p>经验:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_EXP"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>线上游戏场数:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_ONLINE_GAMES_PLAYED"]["value"]; ?></p>
                    </td>
                    <td>
                        <p>线上游戏胜场:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_ONLINE_GAMES_WON"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>赏金模式场数:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_ONLINE_BH_GAMES_PLAYED"]["value"]; ?></p>
                    </td>
                    <td>
                        <p>赏金模式胜场:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_ONLINE_BH_GAMES_WON"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p>合作模式场数:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_C_GAMES_PLAYED"]["value"]; ?></p>
                    </td>
                    <td>
                        <p>NPC击倒数:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_NPC_DEFEATS"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td rowspan="5" class="visible-md visible-lg">
                        <a href="player.php">
                            <img width="200" src="images/logo.png" >
                        </a>
                    </td>
                    <td align="center" class="visible-md visible-lg">
                        <img class="role_icon" title="Attacker" alt="Attacker" src="./images/role0.png">
                    </td>
                    <td>
                        <p>攻击者 等级:</p>
                    </td>
                    <td>
                        <p><?php echo floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2A']["value"]))/6); ?></p>
                    </td>
                    <td>
                        <p>经验:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_EXP2A"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td align="center" class="visible-md visible-lg">
                        <img class="role_icon" title="Guardian" alt="Guardian" src="./images/role1.png">
                    </td>
                    <td>
                        <p>守护者 等级:</p>
                    </td>
                    <td>
                        <p><?php echo floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2B']["value"]))/6); ?></p>
                    </td>
                    <td>
                        <p>经验:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_EXP2B"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td align="center" class="visible-md visible-lg">
                        <img class="role_icon" title="Support" alt="Support" src="./images/role2.png">
                    </td>
                    <td>
                        <p>支援者 等级:</p>
                    </td>
                    <td>
                        <p><?php echo floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2C']["value"]))/6); ?></p>
                    </td>
                    <td>
                        <p>经验:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_EXP2C"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td align="center" class="visible-md visible-lg">
                        <img class="role_icon" title="Avenger" alt="Avenger" src="./images/role3.png">
                    </td>
                    <td>
                        <p>复仇者 等级:</p>
                    </td>
                    <td>
                        <p><?php echo floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2D']["value"]))/6); ?></p>
                    </td>
                    <td>
                        <p>经验:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_EXP2D"]["value"]; ?></p>
                    </td>
                </tr>
                <tr>
                    <td align="center" class="visible-md visible-lg">
                        <img class="role_icon" title="Dealer" alt="Dealer" src="./images/role4.png">
                    </td>
                    <td>
                        <p>发牌者 等级:</p>
                    </td>
                    <td>
                        <p><?php echo floor((-11+sqrt(289.0+2.4*$contentObject[3]['STAT_PLAYER_EXP2E']["value"]))/6); ?></p>
                    </td>
                    <td>
                        <p>经验:</p>
                    </td>
                    <td>
                        <p><?php echo $contentObject[3]["STAT_PLAYER_EXP2E"]["value"]; ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-lg-12 col-xs-12" >
            <ul id="tab_group" class="nav nav-tabs show-700">
                <li class="nav-item">
                    <a class="nav-link active" onclick="changeTab('tab_table_chara', this)">角色统计</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="changeTab('tab_table_card', this)">卡牌携带</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="changeTab('tab_table_bh', this)">赏金击杀</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" onclick="changeTab('tab_table_other', this)">垃圾桶</a>
                </li>
                <li>
                    <a class="nav-link"><?php logoutbutton(); ?></a>
                </li>
            </ul>
            <div id="select_tab" class="hidden-700">
                <select onchange="changeTab(this, null)" class="form-control">
                    <option value=0>角色统计</option>
                    <option value=1>卡牌携带</option>
                    <option value=2>赏金击杀</option>
                    <option value=3>垃圾桶</option>
                </select>
                <br>
            </div>
            <div id="tab_table_chara" class="tab-content">
                <table id="table_chara" class="table table-striped table-bordered" style="table-layout: fixed;border-top:none !important;width:100%;">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="text-center">场数</th>
                        <th class="text-center">胜场</th>
                        <th class="text-center">胜率 %</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th class="text-center">场数</th>
                        <th class="text-center">胜场</th>
                        <th class="text-center">胜率 %</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div id="tab_table_card" class="tab-content" style="display:none">
                <table id="table_card" class="table table-striped table-bordered" style="table-layout: fixed;border-top:none !important;width:100%;">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="text-center">携带次数</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th class="text-center">携带次数</th>
                    </tr>
                    </tfoot>
                </table>
                <br>
                <p>并不是所有的卡包都有对应的统计，只有早年的几个卡包有统计项。</p>
            </div>
            <div id="tab_table_bh" class="tab-content" style="display:none">
                <table id="table_bh" class="table table-striped table-bordered" style="table-layout: fixed;border-top:none !important;width:100%;">
                    <thead>
                    <tr>
                        <th></th>
                        <th class="text-center">击杀次数</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th></th>
                        <th class="text-center">击杀次数</th>
                    </tr>
                    </tfoot>
                </table>
                <br>
                <p>击倒赏金npc的次数。</p>
            </div>
            <div id="tab_table_other" class="tab-content" style="display:none">
                <table id="table_other" class="table table-striped table-bordered" style="table-layout: fixed;border-top:none !important;width:100%;">
                    <thead>
                    <tr>
                        <th>键</th>
                        <th class="text-center">值</th>
                        <th class="text-center">备注</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>键</th>
                        <th class="text-center">值</th>
                        <th class="text-center">备注</th>
                    </tr>
                    </tfoot>
                </table>
                <br>
                <p>不在前3个表中的数据，都被丢到了这个表中。</p>
            </div>
        </div>
    </div>
</main>
</body>
<footer>


</footer>
</html>
<script>
    var datatable_definitions = {
        "responsive": false,
        "paging": false,
        "searching": false,
        "info": false,
        "columnDefs": [
            { "width": "5%", "type": 'alt-string', "targets": 0 },
        ],
        "order": [[ 1, "desc" ]]
    };
    var datatable_chara,datatable_card,datatable_bh,datatable_other;
    $(document).ready(init());

    function init() {
        datatable_chara = $('#table_chara').DataTable({
            "responsive": false,
            "paging": false,
            "searching": false,
            "info": false,
            "columnDefs": [
                { "width": "5%", "type": 'alt-string', "targets": 0 }//,
                //{ "type": "num-fmt", "targets": 3 }
            ],
            "order": [[ 1, "desc" ]]
        });
        datatable_card = $('#table_card').DataTable(datatable_definitions);
        datatable_bh = $('#table_bh').DataTable(datatable_definitions);
        datatable_other = $('#table_other').DataTable(datatable_definitions);

        var content = JSON.parse(`<?php echo $contentJson; ?>`);//绷。
        console.log(content);
        for (let contentKey in content[0]) {
            if(content[0][contentKey]["wins"]==undefined){
                content[0][contentKey]["wins"]=0;
            }
            datatable_chara.row.add([
                "<img class='intable-img' title='" + contentKey + "' alt='" + contentKey + "' src='" + "./images/chara/" + contentKey + ".webp" + "'>",
                "<div class='intable-text'>" + content[0][contentKey]["games"] + "</div>",
                "<div class='intable-text'>" + content[0][contentKey]["wins"] + "</div>",
                "<div class='intable-text'>" + ((content[0][contentKey]["games"]>10)?(100*content[0][contentKey]["wins"]/content[0][contentKey]["games"]).toFixed(3):("<abbr title='至少10场才会进行计算。'>N/A</abbr>")) + "</div>"
            ]).draw(false);
        }

        for (let contentKey in content[1]) {
            datatable_card.row.add([
                "<img class='intable-img' title='" + contentKey + "' alt='" + contentKey + "' src='" + "./images/cards/" + contentKey + ".webp" + "'>",
                "<div class='intable-text'>" + content[1][contentKey] + "</div>"
            ]).draw(false);
        }

        for (let contentKey in content[2]) {
            datatable_bh.row.add([
                "<img class='intable-img' title='" + contentKey + "' alt='" + contentKey + "' src='" + "./images/bh/" + contentKey + ".webp" + "'>",
                "<div class='intable-text'>" + content[2][contentKey] + "</div>"
            ]).draw(false);
        }

        for (let contentKey in content[3]) {
            datatable_other.row.add([
                contentKey,
                "<div class='intable-text'>" + content[3][contentKey]["value"] + "</div>",
                content[3][contentKey]["descr"]
            ]).draw(false);
        }
    }

    function changeTab(contentId, tab){
        let x = document.getElementsByClassName("tab-content");
        for (let i = 0; i < x.length; i++){
            x[i].style.display = "none";
        }

        if(!tab){
            x[contentId.value].style.display = "block";
        } else {
            x = document.getElementsByClassName("nav-link");
            for (let i = 0; i < x.length; i++){
                x[i].classList.remove("active");
            }
            tab.classList.add("active");
            document.getElementById(contentId).style.display = "block";
        }
        datatable_card.columns.adjust();
        datatable_bh.columns.adjust();
        datatable_other.adjust();
    }

</script>




