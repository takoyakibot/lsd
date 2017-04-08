<?php
require("./setting.php");

session_start();

// not post : goto index.php
if (isset($_GET['PCId']) == false || isset($_SESSION["GId"]) == false) {
    Mapiromahamadiromat();
}

$PCId = "";
$PCName = "";
$Tripper = "";
$PCMemo = "";
$PCUrl = "";

$gId = $_SESSION["GId"];
$PCId = h($_GET['PCId']);

$mysqli = GetMysqli();

$SelectedPC = GetQueryResult(
    sprintf("SELECT PCId, PCName, Tripper, PCMemo, PCUrl FROM PC WHERE GId = '%s' AND PCId = %s;", $gId, $PCId)
);
$row = $SelectedPC->fetch_assoc();

$PCId = $row['PCId'];
$PCName = $row['PCName'];
$Tripper = $row['Tripper'];
$PCMemo = $row['PCMemo'];
$PCUrl = $row['PCUrl'];

CloseMysqliArray($mysqli);
?>

<html>
<head>
    <title>lsd/pc</title>
</head>
<body>
<img src="logo.png">
<br />
<br />
<a href="./guild.php">modoru</a>
<br />
<br />
<form action="./db/" method="post">
    <input type="hidden" name="PCId" value="<?php print($PCId); ?>" />
    <input type="hidden" name="mode" value="PCUpdate" />
    <input type="hidden" name="from" value="pc.php?PCId=<?php print($PCId); ?>" />
    PC    <input type="SUBMIT" value="Update" />
    <br />
    <table>
        <tr><td>Name</td><td><input type="text" name="PCName" value="<?php print($PCName); ?>" /></td></tr>
        <tr><td>Tripper</td><td><input type="text" name="Tripper" value="<?php print($Tripper); ?>" /></td></tr>
        <tr><td>Memo</td><td><input type="text" name="PCMemo" value="<?php print($PCMemo); ?>" /></td></tr>
        <tr><td>Url</td><td><input type="text" name="PCUrl" value="<?php print($PCUrl); ?>" /></td></tr>
    </table>
    <br />
</form>
<br />

</body>
</html>
