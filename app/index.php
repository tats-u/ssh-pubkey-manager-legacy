<?php

function redirectToLoginPage() {
    header("Location: login.php",TRUE,307);
    print("Redirecting to the login page...\n");
    exit();
}

session_start();
if(!$_SESSION || empty($_SESSION["state"])) {
    redirectToLoginPage();
}

?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>管理画面</title>
    </head>
    <body>
        <h1>管理画面</h1>
        <p><a href="login.php">ログイン</a></p>
        <p><a href="logout.php">ログアウト</a></p>
    </body>
</html>