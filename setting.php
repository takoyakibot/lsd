<?php

$local = !true;
$debug = true;

$config_path = "config.php";
if (file_exists($config_path)) include($config_path);
elseif (file_exists(("../".$config_path))) include("../".$config_path);
elseif (file_exists(("../../".$config_path))) include("../../".$config_path);
elseif (file_exists(("../../../".$config_path))) include("../../../".$config_path);

if ($debug) {
    var_dump($_SESSION);
    print('<br />');
    var_dump($_POST);
    print('<br />');
}

function ExecQuery($query) {
    global $mysqli;

    $result = $mysqli->query($query);
    checkQueryResult($result, $query);
}

function ExecQuerys($queryArray) {
    global $mysqli;

    $result = $mysqli->begin_transaction();
    checkQueryResult($result, "begin_transaction");
    if ($result == true) {
        foreach ($queryArray as $q) {
            $result = $mysqli->query($q);
            checkQueryResult($result, $q);
            if (!$result) {
                checkQueryResult($mysqli->rollback(), "rollback_transaction");
                break;
            }
        }
        if ($result) {
            checkQueryResult($mysqli->commit(), "commit_transaction");
        }
    }
}

function GetQueryResult($query) {
    global $mysqli;
    global $_mysqliArray;
    $query = $query;

    $result = $mysqli->query($query);
    checkQueryResult($result, $query);

    $_mysqliArray[] = $result;
    return $result;
}

// debug only. print sql command result.
function checkQueryResult($result, $query) {
    if ($result) {
        printDebug("[success] ");
    } else {
        printDebug("[fail] ");
    }
    printDebug($query);
    printDebug("<br />");
}

// debug=trueのときだけprint
function printDebug($str) {
    global $debug;
    if ($debug) print($str);
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// print br tag
function br() {
    print('<br />');
}

// Riremito
function Mapiromahamadiromat() {
    global $local;
    global $debug;

    // when debug, dont go to home.
    if ($debug) {
        printDebug("<br />Mapiromahamadiromat<br />");
        return;
    }

    if ($local) header("Location: http://localhost/lsd/");
    else header("Location: http://命を洗濯を.xyz/");
    exit;
}

?>