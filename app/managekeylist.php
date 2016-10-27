<?php

require_once("db_lib.php");

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

$data = json_decode(file_get_contents('php://input'), TRUE);

$operation = $data["operation"];
$targetUser = $data["targetUser"];

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