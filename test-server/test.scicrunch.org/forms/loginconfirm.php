<?php
    include_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";
    \helper\scicrunch_session_start();

    $previous_page = $_SERVER["HTTP_REFERER"];
    if(!isset($_SESSION['user'])) header("location:" . $previous_page);

    $user = $_SESSION['user'];
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
    $good_pass = $user->checkPassword($password);
    if($good_pass) $_SESSION["loginconfirm"] = true;
    elseif(isset($_SESSION["loginconfirm"])) unset($_SESSION["loginconfirm"]);

    header("location:" . $previous_page);
?>
