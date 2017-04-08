<?php
session_start();

require(dirname(__FILE__)."/../setting.php");
$backPage = "../";

// not post : goto index.php
#if ($_SERVER["REQUEST_METHOD"] != "POST" || !(isset($_SESSION["GId"]) || isset($_GET["GId"])) ) {
#    Mapiromahamadiromat();
#}

// POSTのみ実行
$mysqli = GetMysqli();

switch ($_POST["mode"]) {
    // index.php
    case "Login":
        Login();
        break;
    case "GuildAdd":
        GuildAdd();
        break;
    // guild.php
    case "SessionAdd":
        SessionAdd();
        break;
    case "PCAdd":
        PCAdd();
        break;
    // pc.php
    case "PCUpdate":
        PCUpdate();
        break;
    case "PCDelete":

        break;
    // session.php
    case "PartyAdd":
        PartyAdd();
        break;
    case "PartyDelete":
        PartyDelete();
        break;
    case "SessionUpdate":
        break;
    case "SessionDelete":
        break;
    // speakeasy.php
    case "SpeakEasyResist":
        SpeakEasyResist();
        break;
}

CloseMysqli($mysqli);

$backPage .= $_POST["from"];




function Login() {
    $LoginId = htmlspecialchars($_POST["LoginId"], ENT_QUOTES);

    $query = sprintf("SELECT GId FROM Guild WHERE LoginId = '%s';", $LoginId);

    $result = GetQueryResult($query);
    $row = $result->fetch_assoc();

    $_POST["from"] .= "?GId=" . $row["GId"];
}

function GuildAdd() {
    $GName = htmlspecialchars($_POST["GName"], ENT_QUOTES);
    $LoginId = htmlspecialchars($_POST["LoginId"], ENT_QUOTES);

    // 名前が被っていたらエラーにしよう
    $checkQuery = "select 1 from Guild where GName = '%s' or LoginId = '%s';";
    $row = GetQueryResult(sprintf($checkQuery, $GName, $LoginId));
    if ($row->num_rows == 0) {

        $GId = md5(uniqid(rand(), 1));

        $query[] = sprintf("insert into Guild values ('%s', '%s', '%s');", $GId, $GName, $LoginId);

        ExecQuerys($query);

        $_POST["from"] .= "?GId=" . $GId;
    } else {

        $_POST["from"] = "";
    }
}

function SessionAdd() {
    $gId = htmlspecialchars($_SESSION["GId"], ENT_QUOTES);
    $sTitle = htmlspecialchars($_POST["STitle"], ENT_QUOTES);
    $DDName = htmlspecialchars($_POST["DDName"], ENT_QUOTES);
    if ($sTitle == "") return;

    $count = GetQueryResult(sprintf("select coalesce(max(SId), 0) + 1 newId from SessionList where GId = '%s';", $gId));
    $row = $count->fetch_assoc();

    $query = sprintf("insert into SessionList values ('%s', %s, '%s', '%s', now());",
        $gId, $row["newId"], $sTitle, $DDName
    );

    ExecQuery($query);
}

function PCAdd() {
    $gId = htmlspecialchars($_SESSION["GId"], ENT_QUOTES);
    $Name = htmlspecialchars($_POST["PCName"], ENT_QUOTES);
    $Tripper = htmlspecialchars($_POST["Tripper"], ENT_QUOTES);
    $Memo = htmlspecialchars($_POST["PCMemo"], ENT_QUOTES);
    $Url = htmlspecialchars($_POST["PCUrl"], ENT_QUOTES);

    $count = GetQueryResult(sprintf("select coalesce(max(PCId), 0) + 1 newId from PC where GId = '%s';", $gId));
    $row = $count->fetch_assoc();

    $query[] = sprintf("insert into PC values ('%s', %s, '%s', '%s', '%s', '%s')",
        $gId, $row['newId'], $Name, $Tripper, $Memo, $Url);

    ExecQuerys($query);
}

function PCUpdate() {
    $gId = htmlspecialchars($_SESSION["GId"], ENT_QUOTES);
    $PCId = htmlspecialchars($_POST["PCId"], ENT_QUOTES);
    $PCName = htmlspecialchars($_POST["PCName"], ENT_QUOTES);
    $Tripper = htmlspecialchars($_POST["Tripper"], ENT_QUOTES);
    $PCMemo = htmlspecialchars($_POST["PCMemo"], ENT_QUOTES);
    $PCUrl = htmlspecialchars($_POST["PCUrl"], ENT_QUOTES);

    $query = sprintf("update PC set "
        ."PCName = '%s', Tripper = '%s', PCMemo = '%s', PCUrl = '%s' "
        ."where GId = '%s' and PCId = %s;",
        $PCName,
        $Tripper,
        $PCMemo,
        $PCUrl,
        $gId,
        $PCId
    );

    ExecQuery($query);
}

function PartyAdd() {
    $gId = htmlspecialchars($_SESSION["GId"], ENT_QUOTES);
    $sId = htmlspecialchars($_SESSION["SId"], ENT_QUOTES);
    $PCId = htmlspecialchars($_POST["PCId"], ENT_QUOTES);

    // パーティへのPCの追加
    ExecQuery(sprintf("insert into Party values ('%s', %s, %s);", $gId, $sId, $PCId));
}

function PartyDelete() {
    $gId = htmlspecialchars($_SESSION["GId"], ENT_QUOTES);
    $sId = htmlspecialchars($_SESSION["SId"], ENT_QUOTES);
    $PCId = htmlspecialchars($_POST["PCId"], ENT_QUOTES);

    // パーティからのPCの削除
    ExecQuery(sprintf("delete from Party where GId = '%s' and SId = %s and PCId = %s;",
        $gId, $sId, $PCId)
    );
}

function SessionUpdate() {

}


function SpeakEasyResist() {
    // 変数の初期化
    $pcArray = array();
    $gId = htmlspecialchars($_SESSION["GId"], ENT_QUOTES);
    $sId = htmlspecialchars($_SESSION["SId"], ENT_QUOTES);
    $vId = htmlspecialchars($_SESSION["VId"], ENT_QUOTES);

    // PC配列の作成
    $result = GetQueryResult(sprintf("select p.PCId from Party p where p.GId = '%s' and p.SId = %s;", $gId, $sId));
    while ($row = $result->fetch_assoc()) {
        $pcArray[] = array('Id' => $row['PCId']);
    }

    // 投票結果の更新
    //// SEIdの取得
    $seCount = GetQueryResult(
        sprintf("select SEId from SpeakEasy where GId = '%s' and SId = %s and Writor = %s;", $gId, $sId, $vId)
    );

    // SpeakEasy, vid, mvp
    $queryArray = array();
    if ($row = $seCount->fetch_assoc()) {
        $SEId = $row["SEId"];

        // Update
        $queryArray[] = sprintf("update SpeakEasy "
            ."set MVP = %s, MVPMemo = '%s', SMemo = '%s' "
            ."where GId = '%s' and SEId = %s;",
            $_POST["MVP"], '', '', $gId, $SEId
        );
    } else {

        $seCount = GetQueryResult(
            sprintf("select coalesce(max(SEId), 0) + 1 newId from SpeakEasy where GId = '%s';", $gId)
        );
        $row = $seCount->fetch_assoc();

        $SEId = $row["newId"];

        // Insert
        $queryArray[] = sprintf("insert into SpeakEasy values('%s', %s, %s, %s, %s, '%s', '%s');",
            $gId, $sId, $SEId, $vId, $_POST["MVP"], '', ''
        );
    }

    // SpeakEasyCarma sid, seid, vid, pcid
    //// Delete all
    $queryArray[] = sprintf("delete from SpeakEasyCarma where GId = '%s' and SEId = %s;", $gId, $SEId);

    // each PCs insert
    foreach ($pcArray as $pc) {
        // Insert
        $queryArray[] = sprintf("insert into SpeakEasyCarma values('%s', %s, %s, %s, '%s');",
            $gId, $SEId, $pc["Id"], $_POST["carma".$pc["Id"]], ''
        );
    }

    ExecQuerys($queryArray);
}

?>
<html>
<head><title>リロード対策</title></head>
<?php

if($debug) {
    print('<body>');
    print('<a href="#" onclick="document.back.submit()">back</a>');
} else {
    print('<body onload="document.back.submit();">');
}

?>

<form name="back" action="<?php print($backPage); ?>" method="post">
    <input type="hidden" name="error" value="<?php if($_POST["from"] == "") print("error"); ?>" />
</form>

</body>
</html>
