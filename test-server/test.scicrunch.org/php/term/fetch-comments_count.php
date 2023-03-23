<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();
    $comments_count = $cxn->select("term_comments", Array("count('id')"), "s", Array($_POST["term_ilx"]), "WHERE ilx=? and status>0");
    foreach ($comments_count as $count) {
      echo $count["count('id')"];
    }
    $cxn->close();
?>
