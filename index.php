<?php
$error = $_POST["error"];
session_abort();

require("./setting.php");

unset($_SESSION["GId"]);
?>

<html>
<head>
    <title>Let's Speakeasy Dugtrio | サタスペオンセ支援サイト</title>
</head>
<body <?php if($error == "error") { $mes = "'そのIDは使われているです。'"; print('onload="confirm('.$mes.')"'); }; ?>>
<img src="logo.png"><br />
アジアンパンクＲＰＧ「サタスペ」のスピークイージーを手軽に実現するサイト<br />
<a href="http://whyimoeat.blogspot.jp/2016/11/blog-post_16.html" target="_blank">操作全般の説明はこちらから（外部サイト）</a><br />
<br />
<br />
<br />
<form action="./db/" method="post">
    <input type="hidden" name="mode" value="GuildAdd" />
    <input type="hidden" name="from" value="guild.php" />
    <table border="0" ><tr><td>卓名称：</td><td><input type="text" name="GName" id="GName" /></td></tr>
    <tr><td>ログインID：</td><td><input type="text" name="LoginId" /></td><td><input type="submit" value="卓を作成する" onclick="return inputCheck('GName');" /></td></tr></table>
</form>
<br />
<br />
<br />
<br />
卓の名称を入力して「卓を作成する」ボタンを押すと、個別の卓ページに遷移します。<br />
遷移後のURLは再現できないので、表示されているURLをどっかに控えといてください。<br />
次回以降のアクセスはそのURLから行ってください。<br />
<br />
作った人がその憂き目にあったのでログインID的なものを追加しました。<br />
下のやつに入力するとログインできます。<br />
<br />
<form action="./db/" method="post">
    <input type="hidden" name="mode" value="Login" />
    <input type="hidden" name="from" value="guild.php" />
    <input type="text" name="LoginId" id="LoginId" /> <input type="submit" value="ログイン" onclick="return inputCheck('LoginId');" />
</form>
<script type="text/javascript">
    <!--
    function inputCheck($name) {
        if (document.getElementById($name).value == null || document.getElementById($name).value == "") {
            confirm("にゅうりょくして");
            return false;
        }
    }
    //-->
</script>
</body>
</html>
