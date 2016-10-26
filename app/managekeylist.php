<?php

require("db_lib.php");

header("Content-Type: application/json; charset=utf-8");
function ErrorAndExit($message = "") {
    $outputJSON = ["succeeded" => false, "message" => $message];
    print(json_encode($outputJSON));
    exit();
}

session_start();
if(!$_SESSION || empty($_SESSION["state"])) {
    ErrorAndExit("認証がされていません");
}

if($_SERVER["REQUEST_METHOD"] != "POST") {
    ErrorAndExit("POSTのみ受け付けています");
}

$operation = (string)filter_input(INPUT_POST, "operation");
$targetUser = (string)filter_input(INPUT_POST, "targetUser");

switch($operation) {
    case "get":
        if($targetUser != $_SESSION["userName"]) ErrorAndExit("現在はログインしているユーザの情報しか取得できません");
        print(json_encode(GetKeyListFromDB($targetUser)));
        break;
    default:
        ErrorAndExit("operationの値が不正か、指定されていません");
        break;
}
?>