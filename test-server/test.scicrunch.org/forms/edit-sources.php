<?php

include '../classes/classes.php';

\helper\scicrunch_session_start();

// verify permission
if(empty($_SESSION['user']) || $_SESSION['user']->role < 1){
    http_response_code(403);
    exit();
}

function getSourceByID($post)
{
    if (empty($_POST['id'])) {
        http_response_code(403);
        exit();
    }
    $id = filter_var($post['id'],FILTER_SANITIZE_NUMBER_INT);
    $sources = new Sources();
    $source = $sources->getByID($id);
    if (empty($source)) {
        http_response_code(404);
        exit();
    }
    return $source;
}

function setSourceDescriptionEncoded($source, $post)
{
    if (empty($post['value'])) {
        http_response_code(403);
        exit();
    }
    $value = strtolower($post['value']);
    if ($value == 'true' || $value == '1') {
        $source->setDescriptionEncoded(true); 
    } else {
        $source->setDescriptionEncoded(false); 
    }
    $new_source = $source->getByID($source->id);
    echo json_encode($new_source);
}

// check if we have a valid action
if (empty($_POST['action']) ) {
    http_response_code(403);
    exit();
}
$action = strtolower(filter_var($_POST['action'],FILTER_SANITIZE_STRING));

switch ($action) {
case 'set-description-encoded':
    $source = getSourceByID($_POST);
    setSourceDescriptionEncoded($source, $_POST);
    break;
default:
    http_response_code(403);
    break;
}

exit();

?>
