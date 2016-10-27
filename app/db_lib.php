<?php

require_once("config.php");

function GetKeyListFromDB($dbServer, $dbUser, $dbPass, $dbName, $userName) {
    $dsn = "mysql:dbname=" . $dbName . ";host=" . $dbServer;
    $dbObj = null;
    
    try {
        $dbObj = new PDO($dsn, $dbUser, $dbPass);
    } catch(PDOException $e) {
        return ["succeeded" => false, "message" => "データベースに接続できませんでした"];
    }
    try {
        $dbQuery = $dbObj->prepare("select key_name,key_type,key_content,key_comment from pubkeys where user_index in (select user_index from user_name where user_name = ?)");
        if(!$dbQuery->execute([$userName])) return ["succeeded" => false, "message" => "公開鍵のリストを取得するクエリに失敗しました"];
        $result = $dbQuery->fetchAll(); 
        return ["succeeded" => true, "keys" => array_map(function($row) {
            return ["name" => $row["key_name"], "type" => $row["key_type"], "content" => $row["key_content"], "comment" => $row["key_comment"]];
        }, $result)];
    } catch(PDOException $e) {
        return ["succeeded" => false, "message" => "公開鍵のリストを取得するクエリに失敗しました"];
    }   
}

/*! @brief 公開鍵を登録する
    @param dbServer データベースサーバを表す文字列
    @param dbUser データベースに接続するユーザ名
    @param dbPass データベースに接続するパスワード
    @param dbName データベースの名前
    @param userName 公開鍵を登録するユーザ名
    @param keyData 公開鍵(["name" => "公開鍵の名前", "type" => "公開鍵の種類(ssh-ed25519など)", "content" => "公開鍵を表す文字列(Base64)", "comment" => "公開鍵のコメント"])
    @return ["succeeded" => bool(登録に成功したか), "message" => "エラーメッセージ(登録失敗時のみ)"]

*/
function AddOneKey($dbServer, $dbUser, $dbPass, $dbName, $userName, $keyData) {
    $dsn = "mysql:dbname=" . $dbName . ";host=" . $dbServer;
    $dbObj = null;
    
    if(!isset($dbServer, $dbUser, $dbPass, $dbName, $userName, $keyData)) return ["succeeded" => false, "message" => "引数が不足しています"];
    if(!array_key_exists("name", $keyData) || !array_key_exists("type", $keyData) || !array_key_exists("content", $keyData) || !array_key_exists("comment", $keyData)) return ["succeeded" => false, "message" => "公開鍵を登録するのに必要なデータのいずれかが不足しています"];
    if($keyData["name"] == "") return ["succeeded" => false, "message" => "名前が空です"];
    if(!preg_match("/(ssh-(rsa|dss|ed25519)|ecdsa-sha2-nistp(256|384|521))/",$keyData["type"])) return ["succeeded" => false, "message" => "公開鍵のタイプ「" . $keyData["type"] . "」は無効です"];
    if(!preg_match("@[0-9A-Za-z+/]+(==?)?@", $keyData["content"])) return ["succeeded" => false, "message" => "鍵の内容が有効なBase64文字列ではありません"]; 

    try {
        $dbObj = new PDO($dsn, $dbUser, $dbPass);
    } catch(PDOException $e) {
        return ["succeeded" => false, "message" => "データベースに接続できませんでした"];
    }

    try {
        $dbObj->beginTransaction();
        $dbQuery = $dbObj->prepare("select user_index from user_name where user_name = ?");
        if(!$dbQuery->execute([$userName])) {
            $dbObj->rollBack();
            return ["succeeded" => false, "message" => "ユーザインデックスを取得できませんでした"];
        }
        $userIndex = null;
        switch($dbQuery->rowCount()) {
            case 1:
                $userIndex = $dbQuery->fetch()[0];
                break;
            case 0: // ユーザインデックスに登録
                $dbQuery = $dbObj->prepare("insert into user_index(user_name) values (?)");
                if(!$dbQuery->execute([$userName])) {
                    $dbObj->rollBack();
                    return ["succeeded" => false, "message" => "ユーザインデックスを登録できませんでした"];
                }
                $dbQuery = $dbObj->prepare("select user_index from user_name where user_name = ?");
                if(!$dbQuery->execute([$userName]) || $dbQuery->rowCount() != 1) {
                    $dbObj->rollBack();
                    return ["succeeded" => false, "message" => "登録したユーザインデックスの再取得に失敗しました"];
                }
                $userIndex = $dbQuery->fetch()[0];
            default:
                $dbObj->rollBack();
                return ["succeeded" => false, "message" => $userName . "のユーザ番号が複数登録されています"];
                break;
        } 
        $dbQuery = $dbObj->prepare("insert into pubkeys values (?,?,?,?,?)");
        if(!$dbQuery->execute([$userIndex, $keyData["name"], $keyData["type"], $keyData["content"], $keyData["comment"]])) {
            $dbObj->rollBack();
            return ["succeeded" => false, "message" => "公開鍵をリストに登録するクエリに失敗しました"];
        }
        $dbObj->commit(); 
        return ["succeeded" => true];
    } catch(PDOException $e) {
        $dbObj->rollBack();
        return ["succeeded" => false, "message" => "公開鍵のリストを取得するクエリに失敗しました"];
    }
}

?>