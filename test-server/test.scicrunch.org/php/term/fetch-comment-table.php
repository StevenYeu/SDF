<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();

    if($_POST["comments_type"] == 0) $comments = $cxn->select("term_comments", Array("*"), "", Array(), "WHERE status='-2' ORDER BY id DESC");
    else $comments = $cxn->select("term_comments", Array("*"), "", Array(), "WHERE status>'-2' ORDER BY id DESC");
    $total_count = count($comments);

    $output = '
        <div class="panel panel-grey margin-bottom-50">
            <div class="panel-heading">
                <h3 style="font-size: 16px;color: inherit;margin-top: 0;margin-bottom: 0;">
                    <i class="fa fa-globe"></i> Total ' . $total_count .' Results
                </h3>
            </div>

            <div class="panel-body">
                <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed" id="result-table">
                  <thead>
                      <tr">
                          <th style="width:3%;background-color:white"></th>
                          <th style="width:15%;background-color:white">Term</th>
                          <th style="width:39%;background-color:white">Comments</th>
                          <th style="width:10%;background-color:white">Type</th>
                          <th style="width:17%;background-color:white">Sender</th>
                          <th style="width:17%;background-color:white">Time</th>
                      </tr>
                  </thead>
                  <tbody>
        ';
    foreach ($comments as $comment) {
        $output .= '<tr>';

        if($_POST["comments_type"] == 0) $output .= '<td></td>';
        else $output .= '<td><a href="javascript:void(0)" data-target="#delete_comment_Modal" data-toggle="modal" data-code="' . $comment["id"] . '" style="color:red"><i class="fa fa-trash-o"></i></a></td>';

        $output .= '
                <td><a target="_blank" href="/' . $_POST["portal_name"] . '/interlex/view/' . $comment["ilx"] . '">' . $comment["ilx"] . ' <i class="fa fa-external-link"></i></a></td>
                <td style="word-wrap:break-word;">' . $comment["comment"] . '</td>
                <td>' . $comment["type"] . '</td>
                <td>' . $comment["comment_sender_name"] . '</td>
                <td>' . $comment["reg_time"] . '</td>
            </tr>
        ';
    }
    $output .= '</tbody></table></div></div>';

    echo $output;

    $cxn->close();
?>
