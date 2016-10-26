<?php

require("config.php");

function redirectToIndexPage() {
  header("Location: index.php",TRUE,307);
  print("Redirecting to the index page...\n");
  exit();
}

session_start();
if($_SESSION && isset($_SESSION["state"])) {
  redirectToIndexPage();
}

$isAuthFailed = false;

if($_ENV["REQUEST_METHOD"] = "POST") {
  $userName = (string)filter_input(INPUT_POST, "username");
  $password = (string)filter_input(INPUT_POST, "password");
  if(!empty($userName) && !empty($password)) {
    ldap_set_option($con, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($con, LDAP_OPT_TIMELIMIT, 1);
    $ldapObj = ldap_connect("ldap://" . $ldapServer);
    if($ldapObj && ldap_bind($ldapObj, "uid=" . $userName . $ldapUserRootDN, $password)) {
      ldap_unbind($ldapObj);
      $_SESSION["state"] = "logined";
      redirectToIndexPage();
    }
  }
  $isAuthFailed = true;
}

?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow,noarchive">
    <title>ログイン - 戸田研究室公開鍵管理システム</title>
    <!-- Bootstrap JS 3.3.7 -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- Bootstrap CSS 3.3.7 -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- jQuery JS 3.1.0 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <!-- AngularJS JS 1.5.7 -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.5.7/angular.min.js"></script>
  </head>
  <body>
    <h1 class="text-center">戸田研究室公開鍵管理システム　ログイン</h1>
    <div class="container">
      <?php
      if($isAuthFailed) print('<div class="alert alert-danger"><strong>ユーザ名またはパスワードが間違っています！</strong> いずれも正しいかどうか確認してください。</div>'); 
      ?>
      <form action="login.php" method="POST">
        <div class="form-group">
          <label for="username">ユーザ名</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="ユーザ名">
        </div>
        <div class="form-group">
          <label for="password">パスワード</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="パスワード">
        </div>
        <div class="checkbox">
          <label>
            <input type="checkbox">
            ログインしたままにする
          </label>
        </div>
        <button type="reset" class="btn btn-default"><span class="glyphicon glyphicon-remove"></span> リセット</button>
        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon log-in"></span> ログイン</button>
      </form>
    </div>
  </body>
</html>
