<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();
    $comments = $cxn->select("term_comments", Array("*"), "s", Array($_POST["term_ilx"]), "WHERE parent_comment_id=0 AND ilx=? ORDER BY id DESC");
    $output = "";
    foreach ($comments as $comment) {
        if(($_POST["user_role"] > 0 && $comment["status"] == -2) || ($_POST["user_role"] == 0 && $comment["status"] < 0)) pass;
        else {
            $output .= '
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-7">By <b>' . $comment["comment_sender_name"] . '</b> on <i>' . $comment["reg_time"] . '</i>
                        </div>';
            if($_POST["user_role"] != "") {
                $output .= '
                            <div class="col-md-5" align="right">
                                <a href="javascript:void(0)" data-target="#reply_comment_Modal" data-toggle="modal" data-code="' . $comment["id"] . '"><i class="fa fa-mail-reply"></i> Reply</a>
                                &nbsp;&nbsp;&nbsp;&nbsp
                                <a href="javascript:void(0)" data-target="#delete_comment_Modal" data-toggle="modal" data-code="' . $comment["id"] . '" style="color:red"><i class="fa fa-times"></i> Delete</a>
                            </div>';
            }
            $output .= '
                        </div>
                    </div>
                    <div class="panel-body">' . $comment["comment"] . '</div>
                </div>
            ';
            $output .= get_reply_comment($cxn, $comment["id"]);
        }
    }

    echo $output;

    function get_reply_comment($cxn, $parent_id = 0, $marginleft = 0){
        $comments = $cxn->select("term_comments", Array("*"), "is", Array($parent_id, $_POST["term_ilx"]), "WHERE parent_comment_id=? AND ilx=?");
        $count = count($comments);
        if($parent_id == 0) {
            $marginleft = 0;
        } else {
            $marginleft = $marginleft + 48;
        }
        if($count > 0) {
            foreach ($comments as $comment) {
                // status == 1(new comment, version 1), status == -1(comment deleted by normal user), status == -2(comment deleted by admin)
                if(($_POST["user_role"] > 0 && $comment["status"] == -2) || ($_POST["user_role"] == 0 && $comment["status"] < 0)) pass;
                else {
                    $output .= '
                        <div class="panel panel-default" style="margin-left:' . $marginleft . 'px">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-7">By <b>' . $comment["comment_sender_name"] . '</b> on <i>' . $comment["reg_time"] . '</i>
                                    </div>';
                        if($_POST["user_role"] != "") {
                            $output .= '
                                        <div class="col-md-5" align="right">
                                            <a href data-target="#reply_comment_Modal" data-toggle="modal" data-code="' . $comment["id"] . '"><i class="fa fa-mail-reply"></i> Reply</a>
                                            &nbsp;&nbsp;&nbsp;&nbsp
                                            <a href data-target="#delete_comment_Modal" data-toggle="modal" data-code="' . $comment["id"] . '" style="color:red"><i class="fa fa-times"></i> Delete</a>
                                        </div>';
                        }
                        $output .= '
                                </div>
                            </div>
                            <div class="panel-body">' . $comment["comment"] . '</div>
                        </div>
                    ';
                    $output .= get_reply_comment($cxn, $comment["id"], $marginleft);
                }
            }
        }
        return $output;
    }

    $cxn->close();
?>
