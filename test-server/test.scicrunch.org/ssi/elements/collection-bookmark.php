<?php

    $user = $data["user"];
    $uuid = $data["uuid"];
    $community = $data["community"];
    $view = $data["view"];

    $is_rrid_report_view = RRIDReportItem::isRRIDReportView($view);
    if($is_rrid_report_view) {
        $rrid_reports = RRIDReport::getUserReports($user);
        $rrid_data = $data["rrid-data"];
    }

    $in_use = Collection::checkInCollection($uuid, $user);

?>

<style>
    .in-collection {
        color: green;
    }
</style>

<span class="coll-li">
    <?php if (!is_null($user)): ?>
        <?php
            $coll = new Item();
            $items = $coll->checkRecord($user->id, $uuid);
        ?>
        <?php if($uuid): ?>
            <?php if($in_use): ?>
                <?php if(in_array($view, ["SCR_013869-1", "nif-0000-07730-1"])): ?>
                  <i title="In a Collection or Authentication Report" class="icon-sm fa fa-check-square-o collection-icon in-collection <?php echo $uuid ?>-image" uuid="<?php echo $uuid ?>" style="cursor:pointer"></i>
                <?php else: ?>
                  <i title="In a Collection" class="icon-sm fa fa-check-square-o collection-icon in-collection <?php echo $uuid ?>-image" uuid="<?php echo $uuid ?>" style="cursor:pointer"></i>
                <?php endif ?>
            <?php else: ?>
                <?php if(in_array($view, ["SCR_013869-1", "nif-0000-07730-1"])): ?>
                  <i title="Add to a Collection or Authentication Report" class="icon-sm fa fa-square-o collection-icon <?php echo $uuid ?>-image" uuid="<?php echo $uuid ?>" style="cursor:pointer"></i>
                <?php else: ?>
                  <i title="Add to a Collection" class="icon-sm fa fa-square-o collection-icon <?php echo $uuid ?>-image" uuid="<?php echo $uuid ?>" style="cursor:pointer"></i>
                <?php endif ?>
            <?php endif ?>
        <?php else: ?>
            <i title="This record cannot be added to a collection at this time" class="icon-sm fa fa-square-o" style="color:#DDD"></i>
        <?php endif ?>
        <div class="collection-box no-propagation shadow-effect-1">
            <div class="updating update-<?php echo $uuid ?>">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
            <?php if($is_rrid_report_view && !empty($rrid_reports)): ?>
                <h4>Authentication reports</h4>
                <div class="table-search-v2">
                    <div class="table-responsive">
                        <table class="table table-hover rrid-report-tables" uuid="<?php echo $uuid ?>" style="margin:0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Records</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($rrid_reports as $rr): ?>
                                    <tr>
                                        <td><a target="_blank" href="<?php echo $community->fullURL()."/rin/rrid-report/".$rr->id ?>"><?php echo $rr->name ?></a></td>
                                        <td><?php echo $rr->uniqueUUIDCount() ?></td>
                                            <td><a
                                                href="javascript:void(0)"
                                                class="update-rrid-report-item"
                                                data-rrid-report-id="<?php echo $rr->id ?>"
                                                data-view="<?php echo $view ?>"
                                                data-uuid="<?php echo $uuid ?>"
                                                data-rrid="<?php echo $rrid_data["rrid"] ?>"
                                                data-type="<?php echo $rrid_data["type"] ?>"
                                                data-name="<?php echo $rrid_data["name"] ?>"
                                                data-uid="<?php echo $rrid_data["uid"] // add uid#?>"
                                                data-added="<?php echo $rr->hasItemUUID($uuid) ? 1 : 0 ?>"
                                                data-subtypes="<?php echo $rrid_data["subtypes"] ?>"
                                            >
                                                <i style="<?php if($rr->hasItemUUID($uuid)): ?>display:none;<?php endif ?>color:#00bb00" class="fa fa-plus-circle report-add"></i>
                                                <!-- <i style="color:#00bb00" class="fa fa-plus-circle"></i> -->
                                                <i style="<?php if(!$rr->hasItemUUID($uuid)): ?>display:none;<?php endif ?>color:#bb0000" class="fa fa-times-circle report-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr/>
            <?php endif ?>
            <h4>Collections</h4>
            <div class="table-search-v2">
                <div class="table-responsive">
                    <table class="table table-hover collection-tables" uuid="<?php echo $uuid ?>" style="margin:0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Records</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user->collections as $id => $collection): ?>
                                <tr>
                                    <td><a target="_blank" href="/<?php echo $community->portalName ?>/account/collections/<?php echo $collection->id ?>"><?php echo $collection->name ?></a></td>
                                    <td class="<?php echo $id ?>-count"><?php echo number_format($collection->count) ?></td>
                                    <?php if ($items[$id]): ?>
                                        <td>
                                            <a href="javascript:void(0)" class="remove-item" collection="<?php echo $collection->id ?>" community="<?php echo $community->id ?>" view="<?php echo $view ?>" uuid="<?php echo $uuid ?>">
                                                <i style="font-size: 16px;color:#bb0000" class="fa fa-times-circle"></i>
                                            </a>
                                        </td>
                                    <?php else: ?>
                                        <td>
                                            <a href="javascript:void(0)" class="add-item" collection="<?php echo $collection->id ?>" community="<?php echo $community->id ?>" view="<?php echo $view ?>" uuid="<?php echo $uuid ?>">
                                                <i style="font-size: 16px;color:#00bb00" class="fa fa-plus-circle"></i>
                                            </a>
                                        </td>
                                    <?php endif ?>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                    <a class="ajax-new-collection btn-u" href="javascript:void(0)" style="width:100%;color:#fff;text-align: center" community="<?php echo $community->portalName ?>" cid="<?php echo $community->id ?>" view="<?php echo $view ?>" uuid="<?php echo $uuid ?>">
                        Create New Collection
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php if(in_array($view, ["SCR_013869-1", "nif-0000-07730-1"])): ?>
          <i title="Log In to Use Collections or Authentication Reports" class="icon-sm fa fa-square-o btn-login" style="cursor:pointer"></i>
        <?php else: ?>
          <i title="Log In to Use Collections" class="icon-sm fa fa-square-o btn-login" style="cursor:pointer"></i>
        <?php endif ?>
    <?php endif ?>
</span>
