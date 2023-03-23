<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();

    $notifications = $cxn->select("term_notifications", Array("label", "ilx", "send_notification", "update_type", "follow_children"), "i", Array($_POST["term_id"]), "WHERE id=?")[0];

    $output = '<input id="term_id" type="hidden" name="term_id" value="'.$_POST["term_id"].'" />';
    $output .= '<input id="term_name" type="hidden" name="term_name" value="'.$notifications["label"].'" />';
    $output .= "<h3>Update the term: ".$notifications["label"]." (".$notifications["ilx"].")</h3>";
    $output .= '<a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>';
    $output .= "<div class='row'>";
    $output .= "<div class='col-md-4'>Notification Enabled</div>";

    $tmp1 = $tmp0 = '';
    switch ($notifications['send_notification']) {
      case 0:
          $tmp0 = 'checked';
          break;

      case 1:
          $tmp1 = 'checked';
          break;
    }

    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_notification' name='term_notification' value=1 " . $tmp1 . "/>&nbsp;&nbsp;Yes";
    $output .= "</div>";
    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_notification' name='term_notification' value=0 " . $tmp0 . "/>&nbsp;&nbsp;No";
    $output .= "</div>";
    $output .= "</div>";

    $output .= "<div class='row'>";
    $output .= "<div class='col-md-4'>Notification Frequency</div>";

    $tmp1 = $tmp2 = $tmp3 = '';
    switch ($notifications['update_type']) {
      case 'daily':
          $tmp1 = 'checked';
          break;

      case 'weekly':
          $tmp2 = 'checked';
          break;

      case 'monthly':
          $tmp3 = 'checked';
          break;
    }

    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_update' name='term_update' value='daily' " . $tmp1 . "/>&nbsp;&nbsp;Daily";
    $output .= "</div>";
    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_update' name='term_update' value='weekly' " . $tmp2 . "/>&nbsp;&nbsp;Weekly";
    $output .= "</div>";
    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_update' name='term_update' value='monthly' " . $tmp3 . "/>&nbsp;&nbsp;Monthly";
    $output .= "</div>";
    $output .= "</div>";

    $output .= "<div class='row'>";
    $output .= "<div class='col-md-4'>Follow children</div>";

    $tmp1 = $tmp0 = '';
    switch ($notifications['follow_children']) {
      case 0:
          $tmp0 = 'checked';
          break;

      case 1:
          $tmp1 = 'checked';
          break;
    }

    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_follow_children' name='term_follow_children' value=1 " . $tmp1 . "/>&nbsp;&nbsp;Yes";
    $output .= "</div>";
    $output .= "<div class='col-md-2'>";
    $output .= "<input type='radio' id='term_follow_children' name='term_follow_children' value=0 " . $tmp0 . "/>&nbsp;&nbsp;No";
    $output .= "</div>";
    $output .= "</div>";

    echo $output;

    $cxn->close();
?>
