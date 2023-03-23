<?php
    /* test check-term-notifications.php */
    if($_SESSION["test_notification_script"]) {
        include $_SERVER['DOCUMENT_ROOT'] . "/cron/check-term-notifications.php";
    }
    /* test end */

    $cxn = new Connection();
    $cxn->connect();
    $notifications = Array();
    $results = $cxn->select("term_notifications", Array("*"), "i", Array($_SESSION["user"]->id), "where uid=? and status=1");
    foreach ($results as $result) {
        $notifications[$result['id']] = $result;
    }
    $cxn->close();
?>

<div class="table-search-v2 margin-bottom-20">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>ILX#</th>
                <th>Last Update Time</th>
                <th>Last Notification Time</th>
                <th>Follow Children</th>
                <th>Update Frequency</th>
                <th>Email Alerts</th>
                <th>Actions</th>
                <!-- <th>Next Notification Time</th> -->
            </tr>
            </thead>
            <tbody>
            <?php foreach ($notifications as $id => $notification): ?>
                <tr>
                    <td><a style="color:blue" target="_blank" href="<?php echo $profileBase . 'interlex/view/' . $notification['ilx'] ?>"><?php echo $notification['label'] ?></a></td>
                    <td class="truncate-desc-short"><?php echo $notification['definition'] ?></td>
                    <td><?php echo $notification['ilx'] ?></td>
                    <!-- <td><?php echo date('m/d/Y', $notification['last_updated_time']) ?></td>
                    <td><?php echo date('m/d/Y', $notification['last_notification_time']) ?></td> -->
                    <td><?php echo date('h:ia F j, Y', $notification['last_updated_time']) ?></td>
                    <td><?php echo date('h:ia F j, Y', $notification['last_notification_time']) ?></td>
                    <td><?php if($notification['follow_children']) echo "Yes"; else echo "No";?></td>
                    <td><?php echo $notification['update_type'] ?></td>
                    <td>
                        <?php if($notification['send_notification']): ?>
                            <a href="/php/term/toggle-notification.php?notif_id=<?php echo $id ?>&action=0" style="color: green"><i class="fa fa-check-square-o"></i></a> <font style="color: green">On</font>
                        <?php else: ?>
                            <a href="/php/term/toggle-notification.php?notif_id=<?php echo $id ?>&action=1" style="color: red"><i class="fa fa-square-o"></i></a> <font style="color: red">Off</font>
                        <?php endif ?>
                    </td>
                    <td>
                        <div class="btn-group" style="margin-top:-4px">
                            <button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">
                                Action
                                <i class="fa fa-angle-down"></i>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href onclick="fetchNotification('<?php echo $id ?>');"><i class="fa fa-cog"></i>&nbsp;&nbsp;Update</a></li>
                                <li><a href data-target="#delete_notification_Modal" data-toggle="modal" data-code1="<?php echo $notification['ilx'] ?>" data-code2="<?php echo $notification['label'] ?>"><i class="fa fa-bell-slash"></i>&nbsp;Unsubscribe</a></li>
                            </ul>
                        </div>
                    </td>
                    <!-- <td><?php echo date('m/d/Y', $notification['next_notification_time']) ?></td> -->
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<div id="update_notification_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form method="POST" id="notification_update_form">
                    <div class="form-group" id="notification_detail"></div>
                    <div class="form-froup">
                        <input id="submit" type="submit" name="submit" value="Update" class="btn btn-info">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="delete_notification_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <form method="POST" id="notification_delete_form">
                    <h3>Do you want to unsubscribe this notification?</h3>
                    <a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>
                    <div class="form-group">
                      <input id="user_id" type="hidden" name="user_id" value="<?php echo $_SESSION['user']->id ?>" />
                      <input id="term_ilx" type="hidden" name="term_ilx" value="" />
                      <input id="term_name" type="hidden" name="term_name" value="" />
                    </div>
                    <div class="form-froup">
                        <input id="submit" type="submit" name="submit" value="Unsubscribe" class="btn btn-info">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function fetchNotification(term_id) {
        event.preventDefault();
        $.ajax({
            url:"/php/term/fetch-notification.php",
            method:"POST",
            data:{"term_id":term_id},
            success:function(data){
                $('#notification_detail').html(data);
                $('#update_notification_Modal').modal('show');
            }
        });
    };

    $('#notification_update_form').on('submit', function(event){
        event.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            url:"/php/term/update-notification.php",
            method:"POST",
            data:form_data,
            dataType:"JSON",
            success:function(data){
                $('#update_notification_Modal').modal('hide');
                window.location.reload(false);
            }
        })
    });

    $(function () {
        $('#delete_notification_Modal').on('show.bs.modal', function (event) {
            var link = $(event.relatedTarget); // Link that triggered the modal
            var code1 = link.data('code1'); // Extract info from data-* attributes
            var code2 = link.data('code2');
            var modal = $(this);
            modal.find('#term_ilx').val(code1);
            modal.find('#term_name').val(code2);
        });
    });

    $('#notification_delete_form').on('submit', function(event){
        event.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            url:"/php/term/delete-notification.php",
            method:"POST",
            data:form_data,
            dataType:"JSON",
            success:function(data){
                $('#delete_notification_Modal').modal('hide');
                window.location.reload(false);
            }
        })
    });
</script>

<script type="text/javascript">
    jQuery(document).ready(function () {
        $('.truncate-desc-short').truncate({max_length: 130});
    });
</script>
