<?php
/**
 * Created by PhpStorm.
 * User: aokiyuuta
 * Date: 2016/09/08
 * Time: 16:35
 */
session_start();

require("./setting.php");

// GETがあれば受け取る
if (isset($_GET["a"]) && isset($_GET["b"])) {
    $_SESSION["GId"] = h($_GET["a"]);
    $_SESSION["SId"] = h($_GET["b"]);
}

// not post and not get : goto index.php
if (($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION["GId"]))
    && !isset($_GET["a"])
    && !isset($_GET["b"])) {
    Mapiromahamadiromat();
}

// POSTをSESSIONにぶちこんでから削除
$_SESSION["IsVote"] = $_POST["IsVote"];
$_SESSION["VId"] = $_POST["VId"];
unset($_POST["IsVote"]);
unset($_POST["VId"]);

// 固定値の宣言
$_Default = "Default";
$_VoteStart = "VoteStart";
$_VoteEnd = "VoteEnd";
$isVote = $_SESSION["IsVote"];

// 変数の初期化
$sId = "";
$sTitle = "";
$vId = "";    // 投票者

$pcArray = array();
$userArray = array();
$carmaArray = array();
$voteArray = array();

// postが来たらmodeを判断する
$gId = $_SESSION["GId"];
$sId = $_SESSION["SId"];
$vId = $_SESSION["VId"];

$mysqli = GetMysqli();

$result = GetQueryResult(sprintf("select STitle from SessionList where GId = '%s' and SId = %s", $gId, $sId));
$row = $result->fetch_assoc();
$sTitle = $row["STitle"];


// パーティの詳細情報を適当にJOINして取得する
$result = GetQueryResult(sprintf("select p.PCId, pc.PCName,
        case when trim(pc.Tripper) = '' then pc.PCName else pc.Tripper end as Tripper
    from Party p
    inner join PC pc on p.PCId = pc.PCId and p.GId = pc.GId
    where p.GId = '%s' and p.SId = %s;",
    $gId, $sId)
);

// ユーザ配列、PC配列の作成
$userArray[] = array('Id'=>'0', 'Name'=>'DD'); // 添字0でDD
while ($row = $result->fetch_assoc()) {
    $pcArray[] = array('Id' => $row['PCId'], 'Name' => $row['PCName']);
    $userArray[] = array('Id' => $row['PCId'], 'Name' => $row['Tripper']);
}

// カルマの一覧の取得
$carma = GetQueryResult("select * from CarmaMaster;");
// 投票用のカルマの選択欄を作成
$carmaArray[] = array('Id' => "0", 'Name' => "");
while ($row = $carma->fetch_assoc()) {
    $carmaArray[] = array('Id' => $row['CId'], 'Name' => $row['CName']);
}

// モードによる追加処理
if ($isVote == true) {
    // Get SpeakEasyResult (VoteStart only)
    $query = "select se.Writor, sec.SEId, pc.PCId, pc.PCName, cm.CId, cm.CName, se.MVP, mvp.PCName MVPName,
              se.MVPMemo, sec.CMemo
          from SpeakEasy se
              inner join SpeakEasyCarma sec on se.SEId = sec.SEId and se.GId = sec.GId
              left join PC pc on pc.PCId = sec.PCId and pc.GId = sec.GId
              left join CarmaMaster cm on sec.CId = cm.CId
              left join PC mvp on se.MVP = mvp.PCId and se.GId = mvp.GId
          where se.SId = %s and se.GId = '%s'";
    if ($vId == 0) {
        $query = sprintf($query . " order by se.Writor, sec.PCId;", $sId, $gId);
    } else {
        $query = sprintf($query . " and se.Writor = %s order by se.Writor, sec.PCId;", $sId, $gId, $vId);
    }

    // Create Vote Array
    $carmaVoteResult = GetQueryResult($query);
    while ($row = $carmaVoteResult->fetch_assoc()) {
        $voteArray[] = array(
            'Writor' => $row['Writor'],
            'PCId' => $row['PCId'],
            'PCName' => $row['PCName'],
            'CId' => $row['CId'],
            'CName' => $row['CName'],
            'MVP' => $row['MVP'],
            'MVPName' => $row['MVPName']
        );
    }
}

CloseMysqliArray($mysqli);

// 表示用URL作成
if (!$debug) $directAccessUrl = explode("?", "http://命を洗濯を.xyz".$_SERVER["REQUEST_URI"])[0];
else $directAccessUrl = explode("?", "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"])[0];
$directAccessUrl .= sprintf("?a=%s&b=%s", $gId, $sId);

// 呼ぶとtableタグの中身がいっぺんにできる関数
function printTable(){
    global $vId;
    global $isVote;
    global $pcArray;
    global $userArray;
    global $voteArray;
/*
    foreach ($voteArray as $vote){
        print_r($vote);
        br();
    }
*/
    print('<table border="1" cellspacing="0" cellpadding="5">');

    // header
    printSpeakEasyHeaderRow();

    // rows 2~
    for ($i = 0; $i < count($pcArray); $i++){
        // 1st column
        print('<tr><th>'.$pcArray[$i]['Name'].'</th>');

        // 2nd column ~~
        for ($j = 0; $j < count($userArray); $j++){
            print('<td>');
            if ($userArray[$j]['Id'] == $vId){
                print('<select name="carma'.$pcArray[$i]['Id'].'">');
                // PLの場合は問題ないはずだがDDで投票が揃っていない場合の考慮が面倒だ
                if ($userArray[$j]['Id'] == $voteArray[$i]['Writor']){
                    print(carmaOptions($voteArray[$i]['CId']));
                } else {
                    print(carmaOptions(0));
                }
                print('</select>');
                print('<br /><textarea rows="1" cols="20" name="carmaMemo'.$pcArray[$i]['Id'].'"></textarea>');
            } elseif ($vId == 0){
                // DDの場合。投票が揃っていない場合があり、そのときの制御ができなくはないが面倒すぎるのでぐるぐる回す。
                // 未投票の場合は0で一致するPC番号が存在しないため、ifに入らず何も出力されない
                foreach ($voteArray as $vote){
                    if ($vote['PCId'] == $pcArray[$i]['Id']
                        && $vote['Writor'] == $userArray[$j]['Id']) {
                        print($vote['CName']);
                    }
                }
            }
            print('</td>');
        }
        // for DD. ADD sum column
        if ($isVote && $vId == 0) {
            $resultArray = array();
            foreach ($voteArray as $vote) {
                if ($vote['PCId'] == $pcArray[$i]['Id']
                    && $vote['CId'] > 0
                ) {
                    $resultArray = sumVote($resultArray, $vote['CName']);
                }
            }

            print('<th width="100" align="center">');
            foreach ($resultArray as $result){
                print($result['Name'].'：'.$result['Count']);
                br();
            }
            print('</th>');
        }
        print('</tr>');
    }

    // last row (MVP)
    print('<tr><th>MVP</th>');
    for ($j = 0; $j < count($userArray); $j++){
        print('<td>');
        if ($userArray[$j]['Id'] == $vId){
            print('<select name="MVP">');

            // default row
            print('<option value="0">');
            foreach ($pcArray as $pc){
                print('<option value="'.$pc['Id'].'"');
                if ($voteArray[0]['MVP'] == $pc['Id']){
                    print(' Selected');
                }
                print('>'.$pc['Name'].'');
            }
            print('</select>');
            print('<br /><textarea rows="1" cols="20" name="MVPMemo"></textarea>');
        } elseif ($vId == 0) {
            // DDの場合。投票が揃っていない場合があり、そのときの制御ができなくはないが面倒すぎるのでぐるぐる回す。
            foreach ($voteArray as $vote){
                if ($vote['Writor'] == $userArray[$j]['Id']) {
                    print($vote['MVPName']);
                    break;
                }
            }
        }
        print('</td>');
    }
    // for DD. ADD sum column
    if ($isVote && $vId == 0) {
        $resultArray = array();
        for ($j = 0; $j < count($voteArray); $j += count($pcArray)) {
            if ($voteArray[$j]['MVP'] > 0) {
                $resultArray = sumVote($resultArray, $voteArray[$j]['MVPName']);
            }
        }

        print('<th width="100" align="center">');
        foreach ($resultArray as $result){
            print($result['Name'].'：'.$result['Count']);
            br();
        }
        print('</th>');
    }
    print('</tr>');

    print('</table>');
}

// １行目出力
function printSpeakEasyHeaderRow()
{
    global $isVote;

    // １列目は飾り
    print('<tr><th width="100" align="center">');
    if ($isVote == true) {
        print('<input type="submit" value="SpeakEasy" />');
    } else {
        print('<input type="submit" value="SpeakEasy" hidden onclick="return confirmSubmit();" />');
        // ここはなにもしない
        ;
    }

    print('</th>');
    // ２列目以降は関数を呼ぶ
    printEveryUserHeader();
    // 行末
    print('</tr>');
}

// １行目２列目以降の出力
function printEveryUserHeader()
{
    global $isVote;
    global $vId;
    global $userArray;

    // PLごとに出力。まだ足す。
    foreach ($userArray as $user) {
        // 出力する文字列を判定する。投票モードの時、submit用のaタグをつける。
        $PCName = $user['Name'];
        if ($isVote == null)
        {
            $PCName = '<a href="#" onclick="return PCNameClick('.$user['Id'].');">'.$PCName.'</a>';
        }

        print('<th width="100" align="center">'.$PCName.'</th>');
    }

    // DDの場合、合計列を追加する
    if ($isVote && $vId == 0) {
        print('<th width="100" align="center">合計</th>');
    }
}

// カルマオプションの中身
/**
 * @param $paramCarma
 * @return string
 */
function carmaOptions($paramCarma)
{
    global $carmaArray;
    $carmaComboBoxOptions = "";

    foreach ($carmaArray as $carma) {
        $carmaComboBoxOptions = $carmaComboBoxOptions . '<option value="' . $carma['Id'] . '"';
        if ($paramCarma == $carma['Id']) {
            $carmaComboBoxOptions = $carmaComboBoxOptions . ' Selected ';
        }
        $carmaComboBoxOptions = $carmaComboBoxOptions . '>' . $carma['Name'] . '</option>';
    }

    return $carmaComboBoxOptions;
}

// 投票結果合計用の関数
function sumVote($resultArray, $voteName)
{
    for ($i = 0; $i < count($resultArray); $i++) {
        if ($resultArray[$i]['Name'] == $voteName) {
            $resultArray[$i]['Count']++;
            return $resultArray;
        }
    }
    $resultArray[] = array('Name' => $voteName, 'Count' => 1);
    return $resultArray;
}

?>

<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>lsd/speakeasy</title>
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/clipboard.js/1.5.3/clipboard.min.js"></script>
    <script>
        $(function () {
            var clipboard = new Clipboard('.btn');
        });
    </script>
    <script type="text/javascript">
        <!--
        function confirmSubmit() {
            return confirm('OKを押すと、選択した人の投票結果が表示されます。\nよろしいですか？');
        }

        function PCNameClick(VId) {
            if (!confirmSubmit()) return;
            document.getElementById('VId').value = VId;
            document.vote.submit();
        }
        //-->
    </script>
</head>
<body>
    <img src="logo.png">
    <br />
    <br />
    <br />
    <form name="back" action="session.php" method="post" />
        <a href="#" onclick="document.back.submit()">back</a>
    </form>
    <br />
    <br />
    <?php
        // Goto db/index.php Only Vote is True.
        if ($isVote) {
            print('<form name="vote" action="./db/" method="post">');
            print('<input type="hidden" name="mode" value="SpeakEasyResist" />');
            print('<input type="hidden" name="from" value="speakeasy.php" />');
        // Else is PostBack.
        } else {
            print('<form name="vote" action="#" method="post">');
        }
    ?>
        <input type="hidden" id="VId" name="VId" value="" />

        <?php
            // テーブルの表示
            printTable();

            // 投票がnullの場合のみ、POSTにIsVoteをセットする
            if ($isVote == null) {
                print('<input type="hidden" name="IsVote" value=true />');
            }
        ?>
    </form>
    <br />
    <br />
    名前をクリックすると、その人の投票結果の確認と変更ができるます。<br />
    投票結果の変更後、表の左上に表示されるボタンを押すことで投票結果が保存されます。<br />
    また、DDの投票画面のみ、全員の投票内容と集計結果が表示されます。<br />
    <br />
    <br />
    以下のURLを利用すると、このページに直接アクセスできます。<br />
    <input id="daurl" type="text" style="width: 550px" readonly value="<?=$directAccessUrl?>" />
    <button class="btn" data-clipboard-target="#daurl">URLをコピー</button>

</body>
</html>
