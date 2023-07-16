<?php
//ini_set("display_errors", "On");//打开错误提示
//ini_set("error_reporting",E_ALL);//显示所有错误
require_once ("util/GlobalDataBuilder.php");
require_once ("util/StatJsonParser.php");
//日期
$isSetDate = false;
$startdate = date("m-d-Y");
$enddate = $startdate;
$days = "全部";
if(isset($_GET['startdate'])&&isset($_GET['enddate'])){
    $isSetDate = true;
    $startdate = date("m-d-Y",$_GET['startdate']);
    $enddate = date("m-d-Y",$_GET['enddate']);
    $days = 1 + ceil(($_GET['enddate'] - $_GET['startdate'])/86400);
    //处理
    $contentObject = buildStat(requestJsonGlobal($_GET['startdate'],$_GET['enddate']),true);
    $contentJson = json_encode($contentObject);
}else{
    $contentObject = buildStat(requestJsonGlobal());
    $contentJson = json_encode($contentObject);
}

?>

<!DOCTYPE html>
<html>
<head>
    <!-- Info -->
    <meta itemprop="name" content="百分百鲜橙汁橙汁 - 全球统计">
    <meta name="description" itemprop="description" content="可选择日期的，角色的全球总场数、胜场、胜率等统计信息。">
    <meta itemprop="image" content="https://interface.100oj.com/share.png">
    <link rel="icon" href="https://interface.100oj.com/favicon.ico">
    <title>百分百鲜橙汁 - 全球统计</title>
    <!-- Info -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="./css/jquery.dataTables.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="./css/bootstrap.css">
    <!-- DateRangePicker CSS -->
    <link rel="stylesheet" type="text/css" href="./css/daterangepicker.css">
    <!-- Haneki CSS -->
    <link rel="stylesheet" type="text/css" href="./css/common.css">
    <!-- jQuery -->
    <script type="text/javascript" charset="utf8" src="./js/jquery.js"></script>
    <!-- Bootstrap -->
    <script type="text/javascript" charset="utf8" src="./js/bootstrap.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" charset="utf8" src="./js/jquery.dataTables.min.js"></script>
    <!-- Moment 重要!moment必须放在datarp之前-->
    <script type="text/javascript" charset="utf8" src="./js/moment.min.js"></script>
    <!-- DateRangePicker -->
    <script type="text/javascript" charset="utf8" src="./js/daterangepicker.min.js"></script>
</head>
<body>
<main>
    <div class="box">
        <div class="col-lg-12 col-xs-12" >
            <div class="col-md-offset-5 col-lg-offset-5 col-xl-offset-5">
                <!-- 日期选择器 -->
                <input type="text" name="daterange"/>
                <?php echo "天数:".$days; ?>
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
        </div>
    </div>
</main>
</body>
<footer>


</footer>
</html>

<script>
    $(function() {
        $('input[name="daterange"]').daterangepicker({
            "opens": 'center',
            "ranges": {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "startDate": '<?php echo $startdate; ?>',
            "endDate": '<?php echo $enddate; ?>',
            "maxDate": moment()
        }, function(start, end, label) {
            console.log("选择了一个新的日期: "+start.format('YYYY-MM-DD') +' to '+end.format('YYYY-MM-DD'));
            let date1 = new Date(start.format('YYYY-MM-DD'));
            let date2 = new Date(end.format('YYYY-MM-DD'));
            let unix1 = date1.getTime()/1000;
            let unix2 = date2.getTime()/1000;
            window.location.href = "global.php?startdate=" + unix1 + "&enddate=" + unix2;
        });
    });
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
    var datatable_chara;
    $(document).ready(init());

    function init() {
        datatable_chara = $('#table_chara').DataTable({
            "responsive": false,
            "paging": false,
            "searching": false,
            "info": false,
            "columnDefs": [
                { "width": "5%", "type": 'alt-string', "targets": 0 }
            ],
            "order": [[ 1, "desc" ]]
        });

        var content = JSON.parse(`<?php echo $contentJson; ?>`);//绷。
        console.log(content);
        var total_games = 0;
        var total_wins = 0;
        for (let contentKey in content) {
            if(content[contentKey]["wins"]==undefined){
                content[contentKey]["wins"]=0;
            }
            total_games = total_games + parseInt(content[contentKey]["games"]);
            total_wins = total_wins + parseInt(content[contentKey]["wins"]);
            datatable_chara.row.add([
                "<img class='intable-img' title='" + contentKey + "' alt='" + contentKey + "' src='" + "./images/chara/" + contentKey + ".webp" + "'>",
                "<div class='intable-text'>" + content[contentKey]["games"] + "</div>",
                "<div class='intable-text'>" + content[contentKey]["wins"] + "</div>",
                "<div class='intable-text'>" + ((content[contentKey]["games"]>10)?(100*content[contentKey]["wins"]/content[contentKey]["games"]).toFixed(3):("<abbr title='至少10场才会进行计算。'>N/A</abbr>")) + "</div>"
            ]).draw(false);
        }
        datatable_chara.row.add([
            "<img class='intable-img' title='A_Total' alt='A_Total' src='./images/chara/A_Total.webp'>",
            "<div class='intable-text'>" + total_games + "</div>",
            "<div class='intable-text'>" + total_wins + "</div>",
            "<div class='intable-text'>" + (100*total_wins/total_games).toFixed(3) + "</div>"
        ]).draw(false);
    }
</script>
