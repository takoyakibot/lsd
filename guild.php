<?php
session_start();

require("./setting.php");

// not post : goto index.php
if (isset($_GET["GId"]) == false && isset($_SESSION["GId"]) == false) {
    Mapiromahamadiromat();
}

// GETにGIdがいればセッションにぶちこむ　なければ何もしない
if (isset($_GET["GId"]) == true) {
    $_SESSION["GId"] = h($_GET["GId"]);
}
$gId = $_SESSION["GId"];

$mysqli = GetMysqli();

$GuildList = GetQueryResult(sprintf("SELECT GName FROM Guild WHERE GId = '%s';", $gId));
$row = $GuildList->fetch_assoc();
$GName = $row['GName'];

$SessionList = GetQueryResult(
    sprintf("SELECT SId, STitle, DDName FROM SessionList WHERE GId = '%s' ORDER BY SId desc;", $gId)
);
while ($row = $SessionList->fetch_assoc()) {
    $SessionArray[] = array('Id' => $row['SId'],
        'Name' => $row['STitle'],
        'DDName' => $row['DDName']
    );
}
$pcList = GetQueryResult(sprintf("SELECT PCId, PCName, 
        case when trim(Tripper) = '' then '未入力' else Tripper end as Tripper, PCMemo, PCUrl
    FROM PC WHERE GId = '%s';",
    $gId)
);
while ($row = $pcList->fetch_assoc()) {
    $pcArray[] = array('Id' => $row['PCId'],
        'Name' => $row['PCName'],
        'Tripper' => $row['Tripper'],
        'Url' => $row['PCUrl']
    );
}

CloseMysqliArray($mysqli);

function optionSessionList(){
    global $SessionArray;

    if (count($SessionArray) == 0) return;
    foreach ($SessionArray as $s)
    {
        print('<option value="'.$s['Id'].'">'.$s['Id'].' : '.$s['Name']. '（' .$s['DDName'].'）');
    }
}

function listPC(){
    global $pcArray;

    if (count($pcArray) == 0) return;
    foreach ($pcArray as $p)
    {
        print('<li>'.$p['Id'].' : <a href="./pc.php?PCId='.$p['Id'].'">'.$p['Name'].'</a>'
            .'（'.$p['Tripper'].'）'
            .'<a href="'.$p['Url'].'">url</a>'
        );
    }
}

?>
<html>
<head>
    <title>lsd/guild</title>
</head>
<body>
<img src="logo.png">
<?php print($GName); ?><br />
<br />
<br />
<form action="./db/" method="post">
    <input type="hidden" name="mode" value="SessionAdd">
    <input type="hidden" name="from" value="guild.php">
    <table>
        <tr>
            <td>* アクトタイトル</td>
            <td>：<input type="text" name="STitle" id="STitle" /></td>
        </tr>
        <tr>
            <td>* DD</td>
            <td>：<input type="text" name="DDName" id="DDName" /></td>
            <td><input type="submit" value="セッション追加"
                       onclick="if (!inputCheck('STitle')) return false; if (!inputCheck('DDName')) return false;" />
            </td>
        </tr>
    </table>
</form>
<br />
<br />

<form action="session.php" method="post">
    <select name="SId" id="SId"><?php optionSessionList(); ?></select>
    <input type="SUBMIT" value="セッション編集・スピークイージーへ" <?php if (count($SessionArray) == 0) print("disabled") ?> />
</form>
<br />
<br />
<form action="./db/" method="post">
    <input type="hidden" name="mode" value="PCAdd">
    <input type="hidden" name="from" value="guild.php">
    <br />
    <table>
        <tr><td>* Name</td><td>：<input type="text" name="PCName" id="PCName" /></td></tr>
        <tr><td>Tripper</td><td>：<input type="text" name="Tripper" id="Tripper" /></td></tr>
        <tr><td>Memo</td><td>：<input type="text" name="PCMemo" /></td></tr>
        <tr><td>Url</td><td>：<input type="text" name="PCUrl" /></td>
            <td>
                <input type="SUBMIT" value="PC追加"
                       onclick="if (!inputCheck('PCName')) return false;" />
            </td>
        </tr>
    </table>
</form>
<br />
<ul>
    <?php listPC(); ?>
</ul>
<script type="text/javascript">
    <!--
    function inputCheck($Id) {
        if (document.getElementById($Id).value == null || document.getElementById($Id).value == "") {
            confirm("にゅうりょくして");
            return false;
        }
        return true;
    }
    //-->
</script>
</body>
</html>
