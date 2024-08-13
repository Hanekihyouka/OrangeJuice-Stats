<?php
//ini_set("display_errors", "On");//打开错误提示
//ini_set("error_reporting",E_ALL);//显示所有错误
require_once ("util/dictionary.php");
require_once ("util/StatJsonParser.php");

$schemaJson = requestJsonSchema();
$schemaObject = json_decode($schemaJson,true);

$achObject = $schemaObject["game"]["availableGameStats"]["achievements"];
$statsObject = $schemaObject["game"]["availableGameStats"]["stats"];

?>

<!DOCTYPE html>
<html>
<head>
    <!-- Info -->
    <meta itemprop="name" content="Schematic">
    <meta name="description" itemprop="description" content="Schematic">
    <meta itemprop="image" content="./images/share.png">
    <link rel="icon" href="./images/favicon.ico">
    <title>Schematic</title>

</head>
<body>
    <div id="achievements">
        <h2 id="achC" onclick="onclickAch()">成就 [点击此处展开]</h2>
        <table id="achLayout" style="text-align: left;display: none">
            <tr>
                <th>index</th>
                <th>icon</th>
                <th>iconGray</th>
                <th>name</th>
                <th>displayName</th>
                <th>description</th>
                <th>hidden</th>
            </tr>
            <?php
            for ($i = 0 ; $i < sizeof($achObject) ; $i++){
                echo "<tr><td>".$i."</td>";
                echo "<td>"."<img src='".$achObject[$i]["icon"]."'>"."</td>";
                echo "<td>"."<img src='".$achObject[$i]["icongray"]."'>"."</td>";
                echo "<td>".$achObject[$i]["name"]."</td>";
                echo "<td>".$achObject[$i]["displayName"]."</td>";
                echo "<td>".$achObject[$i]["description"]."</td>";
                echo "<td>".($achObject[$i]["hidden"]?"隐藏":"不隐藏")."</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <div id="stats">
        <h2 id="statsC" onclick="onclickStats()">统计 [点击此处展开]</h2>
        <table id="statsLayout" style="text-align: left;display: none">
            <tr>
                <th>index</th>
                <th>name</th>
                <th>value</th>
                <th>remark</th>
            </tr>
            <?php
            for ($i = 0 ; $i < sizeof($statsObject) ; $i++){
                echo "<tr><td>".$i."</td>";
                echo "<td>".$statsObject[$i]["name"]."</td>";
                echo "<td id='".$statsObject[$i]["name"]."'>[点击此处发送请求]</td>";
                echo "<td>".getDescr($statsObject[$i]["name"])."</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>

<script>
    let stats = <?php echo json_encode($statsObject);?>;
    //console.log(stats);
    let showAch = false;
    function onclickAch(){
        if (showAch){
            document.getElementById("achLayout").style.display = "none";
            document.getElementById("achC").innerText = "成就 [点击此处展开]";
        }else {
            document.getElementById("achLayout").style.display = "";
            document.getElementById("achC").innerText = "成就 [点击此处折叠]";
        }
        showAch = !showAch;
    }

    let showStats = false;
    function onclickStats(){
        if (showStats){
            document.getElementById("statsLayout").style.display = "none";
            document.getElementById("statsC").innerText = "统计 [点击此处展开]";
        }else {
            document.getElementById("statsLayout").style.display = "";
            document.getElementById("statsC").innerText = "统计 [点击此处折叠]";
        }
        showStats = !showStats;
    }

    for (let i = 0; i < stats.length; i++) {
        let stat_td = document.getElementById(stats[i]["name"]);
        //console.log(i + "\t>\t" + stats[i]["name"]);
        stat_td.onclick = (function () {
            stat_td.innerText = "请求中...";
            let Http = new XMLHttpRequest();
            let url='https://interface.100oj.com/stat/util/pipe/GetGlobalStatsForGame.php?key=' + stats[i]["name"];
            Http.open("GET", url);
            Http.onload = function (){
                stat_td.innerText = this.responseText;
            }
            Http.send();
        });
    }

</script>
