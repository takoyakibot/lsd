<?php
/**
 * Created by PhpStorm.
 * Date: 2016/09/22
 * Time: 17:59
 */
$_mysqliArray;

function GetMysqli()
{
    global $local;
    global $debug;
    global $_mysqliArray;

    $_mysqliArray = array();

    if ($local) {
        $host = "localhost";
        $username = "root";
        $password = "root";
        $dbname = "dbname_local";
    } else {
        $host = "hostname";
        $username = "username";
        $password = "password";
        if ($debug) $dbname = "dbname_debug";
        else $dbname = "dbname";
    }

    $mysqli = new mysqli($host, $username, $password, $dbname);
    if ($mysqli->connect_error) {
        error_log($mysqli->connect_error);
        exit;
    }
    $mysqli->set_charset("utf8");

    return $mysqli;
}

function CloseMysqli($mysqli)
{
    $mysqli->close();
}

// 今までに実行したmysqliArrayを根こそぎ閉じていく関数
function CloseMysqliArray($mysqli)
{
    global $_mysqliArray;
    $_mysqliArray[] = $mysqli;
    foreach ($_mysqliArray as $m) {
        $m->close();
    }
}

?>