<?php

require_once("config.php");

function GetKeyListFromDB($dbServer, $dbUser, $dbPass, $dbName, $userName) {
    $dsn = "mysql:dbname=" . $dbName . ";host=" . $dbServer;
    $dbObj = null;
    
    try {
        $dbObj = new PDO($dsn, $dbUser, $dbPass);
    } catch(PDOException $e) {
        return ["succeeded" => false, "message" => "データベースに接続できませんでした\n" . $e.getMessage()];
    }
    try {
        $dbQuery = $dbObj->prepare("select key_name,key_type,key_content,key_comment from pubkeys where user_index in (select user_index from user_name where user_name = ?)");
        if(!$dbQuery->execute([$userName])) return ["succeeded" => false, "message" => "公開鍵のリストを取得するクエリに失敗しました"];
        $result = $dbQuery->fetchAll(); 
        ob_start();
        $dbQuery->debugDumpParams();
        var_dump($result);
        $info = ob_get_clean();
        file_put_contents("php://stderr", "--- DEBUG MESSAGE BEGIN ---\n" . $info . "\n--- DEBUG MESSAGE END ---\n");
        return ["succeeded" => true, "data" => array_map(function($row) {
            return ["name" => $row["key_name"], "type" => $row["key_type"], "content" => $row["key_content"], "comment" => $row["key_comment"]];
        }, $result)];
    } catch(PDOException $e) {
        return ["succeeded" => false, "message" => "公開鍵のリストを取得するクエリに失敗しました"];
    }   
}

?>