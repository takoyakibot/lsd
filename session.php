<?php
session_start();

require("./setting.php");

// not post : goto index.php
if ($_SERVER["REQUEST_METHOD"] != "POST" || isset($_SESSION["GId"]) == false) {
    Mapiromahamadiromat();
}

if (isset($_POST["SId"])) $_SESSION["SId"] = h($_POST["SId"]);

$gId = $_SESSION["GId"];
$sId = $_SESSION["SId"];
$sTitle = "";
$DDName = "";
$PCId = "";

$mysqli = GetMysqli();

// セッションタイトルの取得
$result = GetQueryResult(sprintf("select STitle, DDName from SessionList where GId = '%s' and SId = %s;", $gId, $sId));
$row = $result->fetch_assoc();
$sTitle = $row["STitle"];
$DDName = $row["DDName"];

$pcList = GetQueryResult(sprintf("select PCId, PCName,
        case when trim(Tripper) = '' then '未入力' else Tripper end as Tripper
    from PC where GId = '%s';", $gId)
);
while ($row = $pcList->fetch_assoc()) {
    $pcArray[] = array('PCId' => $row['PCId'], 'PCName' => $row['PCName'], 'Tripper' => $row['Tripper']);
}
$partyList = GetQueryResult(
    sprintf("select p.SId, sl.STitle, p.PCId, pc.PCName,
            case when trim(pc.Tripper) = '' then '未入力' else pc.Tripper end as Tripper
        from Party p
        inner join SessionList sl on p.SId = sl.SId
        inner join PC pc on p.PCId = pc.PCId
        where p.GId = '%s' and p.SId = %s and p.GId = sl.GId and p.GId = pc.GId;",
        $gId,
        $sId
    )
);
while ($row = $partyList->fetch_assoc()) {
    $partyArray[] = array('Id' => $row['SId'],
        'STitle' => $row['STitle'],
        'PCId' => $row['PCId'],
        'PCName' => $row['PCName'],
        'Tripper' => $row['Tripper']
    );
}

CloseMysqliArray($mysqli);

function optionPC(){
    global $pcArray;

    if (count($pcArray) == 0) return;
    foreach ($pcArray as $row) {
        print('<option value="'.$row['PCId'].'">'.$row['PCId'].' : '.$row['PCName'].' / '.$row['Tripper']);
    }
}

function listParty(){
    global $partyArray;

    if (count($partyArray) == 0) return;
    foreach ($partyArray as $row) {
        print('<li>'.$row['PCId'].' : '.$row['PCName'].' / '.$row['Tripper']);
        print('<input type="radio" name="PCId" value="'.$row['PCId'].'" />');
    }
}


?>

<html>
<head>
    <title>lsd/session</title>
</head>
<body>
<img src="logo.png">
<br />
<br />
<form name="back" action="guild.php" method="post" />
<a href="#" onclick="document.back.submit()">back</a>
</form>
<br />
<br />
<form action="./speakeasy.php" method="post">
    <input type="submit" value="Take It (Speak)Easy!" />
</form>
セッションタイトル： <?php print($sTitle); ?><br />
DD： <?php print($DDName); ?><br />
<br />
<br />
<br />
<form action="./db/index.php" method="post">
    <input type="hidden" name="mode" value="PartyAdd" />
    <input type="hidden" name="from" value="session.php" />
    ボンクラを追加します（登録は前のページです）：<br />
    <select name="PCId"><?php optionPC(); ?></select> <input type="submit" value="PC Add" />
</form>
<br />
<br />
ボンクラリスト：<br />
<form action="./db/index.php" method="post">
    <ul><?php listParty(); ?></ul>
    <input type="hidden" name="mode" value="PartyDelete" />
    <input type="hidden" name="from" value="session.php" />
    <input type="submit" value="死ぬがよい" />
</form>

</body>
</html>
