<?php

$pending_requests = CommunityAccessRequest::loadArrayBy(Array("cid", "status"), Array($community->id, CommunityAccessRequest::STATUS_PENDING));
$review_requests = CommunityAccessRequest::loadArrayBy(Array("cid", "status"), Array($community->id, CommunityAccessRequest::STATUS_UNDER_REVIEW));

?>

<?php
echo Connection::createBreadCrumbs($community->shortName . ' Categories', array('Home', 'Account', 'Communities', $community->shortName), array($profileBase, $profileBase . 'account', $profileBase . 'account/communities', $profileBase . 'account/communities/' . $community->portalName . '?tab=information'), 'Pending User Requests');
?>

<div class="profile container content">
    <div class="row">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>

        <div class="col-md-9">
            <?php echo Connection::createProfileTabs(0, $profileBase . 'account/communities/' . $community->portalName, $profileBase); ?>
            <div class="table-responsive">
                <div class="alert alert-info" role="alert"><strong>Pending</strong></div>
                <table class="table table-hover" id="pendingUserRequestsTable">
                    <thead>
                        <tr>
                            <td>Name</td>
                            <td>Email</td>
                            <td>Organization</td>
                            <td>Message</td>
                            <td>Request date</td>
                            <td>Approve</td>
                            <td>Reject</td>
                            <td>Under Review</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pending_requests as $pr): ?>
                            <?php
                                $pending_user = new User();
                                $pending_user->getByID($pr->uid);
                                if(!$pending_user->id) continue;
                            ?>
                            <tr>
                                <td><?php echo $pending_user->getFullName() ?></td>
                                <td><?php echo $pending_user->email ?></td>
                                <td><?php echo $pending_user->organization ?></td>
                                <td><?php echo $pr->message ?></td>
                                <td><?php echo date("M j, Y", $pr->timestamp) ?>
                                <td><a class="approveLink" href="/forms/community-forms/user-request-response.php?id=<?php echo $pr->id ?>&status=<?php echo CommunityAccessRequest::STATUS_APPROVED ?>"><i style="color:green" class="fa fa-check"></i></a></td>
                                <td><a class="rejectLink" href="/forms/community-forms/user-request-response.php?id=<?php echo $pr->id ?>&status=<?php echo CommunityAccessRequest::STATUS_REJECTED ?>"><i style="color:red" class="fa fa-times"></i></a></td>
                                <td><a class="reviewLink" href="/forms/community-forms/user-request-response.php?id=<?php echo $pr->id ?>&status=<?php echo CommunityAccessRequest::STATUS_UNDER_REVIEW ?>"><i style="color:orange" class="fa fa-exclamation-triangle"></i></a></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php if ($review_requests): ?>
            <div class="table-responsive">
                <div class="alert alert-warning" role="alert"><strong>Under Review</strong></div>
                <table class="table table-hover" id="underReviewUserRequestsTable">
                    <thead>
                        <tr>
                            <td>Name</td>
                            <td>Email</td>
                            <td>Organization</td>
                            <td>Message</td>
                            <td>Request date</td>
                            <td>Approve</td>
                            <td>Reject</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($review_requests as $pr): ?>
                            <?php
                                $pending_user = new User();
                                $pending_user->getByID($pr->uid);
                                if(!$pending_user->id) continue;
                            ?>
                            <tr>
                                <td><?php echo $pending_user->getFullName() ?></td>
                                <td><?php echo $pending_user->email ?></td>
                                <td><?php echo $pending_user->organization ?></td>
                                <td><?php echo $pr->message ?></td>
                                <td><?php echo date("M j, Y", $pr->timestamp) ?>
                                <td><a class="approveLink2" href="/forms/community-forms/user-request-response.php?id=<?php echo $pr->id ?>&status=<?php echo CommunityAccessRequest::STATUS_APPROVED ?>"><i style="color:green" class="fa fa-check"></i></a></td>
                                <td><a class="rejectLink2" href="/forms/community-forms/user-request-response.php?id=<?php echo $pr->id ?>&status=<?php echo CommunityAccessRequest::STATUS_REJECTED ?>"><i style="color:red" class="fa fa-times"></i></a></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="pendingUserRequestsModal" tabindex="-1" role="dialog" aria-labelledby="purModalTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" style="margin-left:90%;" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="purModalTitle"></h4>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">NO</button>
        <button type="button" class="btn btn-primary">YES</button>
      </div>
    </div>
  </div>
</div>

<script>
function actionClicked(event) {
    event.preventDefault();
    var approval = $(this).hasClass('approveLink');
    var rejection = $(this).hasClass('rejectLink');
    var approval2 = $(this).hasClass('approveLink2');
    var rejection2 = $(this).hasClass('rejectLink2');
    var review = $(this).hasClass('reviewLink');
    var url = $(this).attr('href');
    var name = $(this).parents('tr').children(":first").text();
    var modal = $('#pendingUserRequestsModal');
    modal.find('*').off();
    if (approval || approval2) {
        modal.find('.modal-title').text('Approve ' + name + '?');
    } else if (rejection || rejection2) {
        modal.find('.modal-title').text('Reject ' + name + '?');
    } else if (review) {
        modal.find('.modal-title').text('Place ' + name + ' under review?');
    }
    modal.find('.modal-footer').children(':last').click(function() {
        window.location.href = url;
    });
    modal.modal('show');
}

$('#pendingUserRequestsTable .approveLink').click(actionClicked);
$('#pendingUserRequestsTable .rejectLink').click(actionClicked);
$('#pendingUserRequestsTable .reviewLink').click(actionClicked);
$('#underReviewUserRequestsTable .approveLink2').click(actionClicked);
$('#underReviewUserRequestsTable .rejectLink2').click(actionClicked);
</script>
