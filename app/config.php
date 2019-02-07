<?php
require_once("vendor/autoload.php");

// .envファイルを読み込む
if (file_exists(__DIR__ . "/.env")) {
    $dotenv = Dotenv\Dotenv::create(__DIR__);
    $dotenv->load();
}

function GetConfigFromEnvOrDie($name) {
    $valueOrFalse = getenv($name);
    if($valueOrFalse != false) {
        return $valueOrFalse;
    }
    http_response_code(500);
    print(".envなどに環境変数「${name}」を設定してください\n");
    exit();
}

$ldapServer = GetConfigFromEnvOrDie("LDAP_SERVER");
$ldapBaseDN = GetConfigFromEnvOrDie("LDAP_BASE_DN");
$ldapUserRootDN = GetConfigFromEnvOrDie("LDAP_USER_ROOT_DN");
$ldapGroupRootDN = GetConfigFromEnvOrDie("LDAP_GROUP_ROOT_DN");
$ldapBindDN = GetConfigFromEnvOrDie("LDAP_BIND_DN");
$ldapPassword = GetConfigFromEnvOrDie("LDAP_PASSWORD");

//! データベースサーバ
$dbServer = GetConfigFromEnvOrDie("DB_SERVER");

//! データベースユーザ名
$dbUser = GetConfigFromEnvOrDie("DB_USER");

//! データベースパスワード
$dbPass = GetConfigFromEnvOrDie("DB_PASS");

//! データベース名
$dbName = GetConfigFromEnvOrDie("DB_NAME");
?>