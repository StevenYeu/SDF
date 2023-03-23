<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

$key = filter_var($_GET["key"], FILTER_SANITIZE_STRING);
$api_key = APIKey::loadByKey($key);
$user = $_SESSION["user"];

if(!\APIPermissionActions\checkAction("term-curator", $api_key, $user)) {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

$flagged_terms_count = TermFlag::getCount(Array("flag", "active"), Array("elastic-upsert", 1));
http_response_code(200);
echo (string) $flagged_terms_count . " terms found that will be updated\n";
$check = fastcgi_finish_request();

if(!$check) {
    echo "failed\n";
    exit;
}

TermDBO::batchUpsert();

?>
