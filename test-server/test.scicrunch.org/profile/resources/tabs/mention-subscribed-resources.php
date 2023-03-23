<?php

if($_SESSION["user"]) $mention_subscriptions = Subscription::loadArrayBy(Array("uid", "type"), Array($_SESSION["user"]->id, "resource-mention"));
else $mention_subscriptions = Array();

function subscribeText($flag){
    if($flag === 1) return "Unsubscribe from";
    else return "Subscribe to";
}

function subscribeAction($flag){
    if($flag === 1) return "unsubscribe";
    else return "subscribe";
}

function checkedText($flag){
    if($flag === 1) return 'checked="checked"';
    else return "";
}

?>

<div class="tab-pane fade in active" id="mention-subscribed-resources">
    <div class="table-search-v2 margin-bottom-20">
        <?php if(count($mention_subscriptions) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>Identifier</th>
                        <th></th>
                        <th class="hidden-sm">Resource Name</th>
                        <th>Subscribed Time</th>
                        <th>SciCrunch Alerts</th>
                        <th>Email Alerts</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach($mention_subscriptions as $ms): ?>
                            <?php
                                $resource = new Resource();
                                $resource->getByRID($ms->fid);
                                $resource->getColumns();
                                $mention_community = new Community();
                                $mention_community->getByID($ms->cid);
                            ?>
                            <tr>
                                <td><?php echo $resource->rid; ?></td>
                                <td><?php if($ms->new_data_scicrunch) echo \helper\htmlElement("notification-inline", Array("text" => "New")) ?></td>
                                <td><a href="/<?php echo $mention_community->portalName ?>/Any/record/nlx_144509-1/<?php echo $resource->uuid ?>/search?notif=<?php echo $ms->id ?>"><?php echo $resource->columns['Resource Name'] ?></a></td>
                                <td><?php echo date("h:ia F j, Y", $ms->time) ?></td>
                                <td><input type="checkbox" disabled="disabled" <?php echo checkedText($ms->scicrunch_notify) ?> /></td>
                                <td><input type="checkbox" disabled="disabled" <?php echo checkedText($ms->email_notify) ?> /></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">Action <i class="fa fa-angle-down"></i></button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li>
                                                <a href="/forms/other-forms/toggle-subscription-notification.php?type=resource-mention&id=<?php echo $ms->fid ?>&action=<?php echo subscribeAction($ms->scicrunch_notify) ?>-scicrunch">
                                                    <i class="fa fa-bell-o"></i> <?php echo subscribeText($ms->scicrunch_notify) ?> notifications
                                                </a>
                                            </li>
                                            <li>
                                                <a href="/forms/other-forms/toggle-subscription-notification.php?type=resource-mention&id=<?php echo $ms->fid ?>&action=<?php echo subscribeAction($ms->email_notify) ?>-email">
                                                    <i class="fa fa-envelope-o"></i> <?php echo subscribeText($ms->email_notify) ?> email updates
                                                </a>
                                            </li>
                                            <li>
                                                <a href="/forms/other-forms/toggle-subscription-notification.php?type=resource-mention&id=<?php echo $resource->rid ?>&action=unsubscribe">
                                                    <i class="fa fa-times"></i> Delete subscription
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>You haven't subscribed to any resources to receive mention updates yet.  Try subscribing to some of you favorite resources to receive updates when new mentions in the literature are found.</p>
        <?php endif ?>
    </div>
</div>
