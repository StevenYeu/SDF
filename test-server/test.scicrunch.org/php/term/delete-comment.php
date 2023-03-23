<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();

    $error = "";
    $comment_content = "";

    $comments = $cxn->select("term_comments", Array("*"), "i", Array($_POST["comment_id"]), "WHERE id=?");
    foreach ($comments as $comment) {
        $comment_sender_id = $comment["comment_sender_id"];
    }


    if ($error == "") {
        if ($_POST["user_role"] == 0 && $_POST["comment_sender_id"] == $comment_sender_id) {
            $cxn->update("term_comments", "ii", Array("status"), Array(-1, $_POST["comment_id"]), "where id=?");
            update_comments_tree($_POST["comment_id"], $cxn, -1);
            $error = '<label class="text-success">Comment Deleted</label>';
        } else if ($_POST["user_role"] > 0) {
            $cxn->update("term_comments", "ii", Array("status"), Array(-2, $_POST["comment_id"]), "where id=?");
            update_comments_tree($_POST["comment_id"], $cxn, -2);
            $error = '<label class="text-success">Comment Deleted</label>';
        } else
            $error = '<label class="text-danger">No permission to delete comments.</label>';
    }

    $data = Array(
        'error' => $error
    );

    echo json_encode($data);

    $cxn->close();

    function update_comments_tree($parent_id, $cxn, $status) {
        $parent_ids = [$parent_id];

        while(count($parent_ids) > 0) {
            foreach ($parent_ids as $parent) {
                $cxn->update("term_comments", "ii", Array("status"), Array($status, $parent), "where parent_comment_id=?");
                $comments = $cxn->select("term_comments", Array("id"), "i", Array($parent), "WHERE parent_comment_id=?");
                if(count($comments) > 0) {
                    foreach ($comments as $comment) {
                        $parent_ids[] = $comment["id"];
                    }
                }
                unset($parent_ids[array_search($parent,$parent_ids)]);  //remove $parent from $parent_ids
            }
        }
    }
?>
