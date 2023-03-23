<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";
    include_once '/assets/plugins/purifier/HTMLPurifier.auto.php';

    $cxn = new Connection();
    $cxn->connect();

    $error = "";
    $comment_sender_name = "";
    $comment_content = "";

    if(empty($_POST["comment_sender_name"])) {
        $error .= '<p class="text-danger">Name is required.</p>';
    } else {
        $comment_sender_name = $_POST["comment_sender_name"];
    }

    if(empty($_POST["comment_content"])) {
        $error .= '<p class="text-danger">Comment is required</p>';
    } else {
        $purifier_config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($purifier_config);
        $comment_content = $purifier->purify($_POST["comment_content"]);
    }

    if($comment_content == "") $error .= '<p class="text-danger">Our system does not accept the comment with HTML code.</p>';

    if($error == "") {
        $cxn->insert("term_comments", "iissisiisii", Array(NULL, $_POST["comment_id"], $comment_content, $comment_sender_name, $_POST["comment_sender_id"], "general", NULL, NULL, $_POST["term_ilx"], $_POST["comment_status"], NULL));
        $error = '<label class="text-success">Comment Added.</label>';
    }

    $data = Array(
        'error' => $error
    );

    echo json_encode($data);

    $cxn->close();
?>
