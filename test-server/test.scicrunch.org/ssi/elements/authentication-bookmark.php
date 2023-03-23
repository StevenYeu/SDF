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
            <?php if(in_array($view, ["SCR_013869-1", "nif-0000-07730-1"])): ?>
                <?php if($in_use): ?>
                  <i class="fa fa-times-circle collection-icon <?php echo $uuid ?>-image" uuid="<?php echo $uuid ?>" style="cursor:pointer; color:#bb0000"><span style="font-family: Arial">&nbsp;Add/Remove from an authentication report</span></i>
                <?php else: ?>
                  <i class="fa fa-plus-circle collection-icon <?php echo $uuid ?>-image" uuid="<?php echo $uuid ?>" style="cursor:pointer; color:#00bb00"><span style="font-family: Arial">&nbsp;Add to an authentication report</span></i>
                <?php endif ?>
            <?php endif ?>
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
                                        <td><a target="_blank" href="<?php echo $community->fullURL().'/rin/rrid-report/'.$rr->id ?>"><?php echo $rr->name ?></a></td>
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
            <?php endif ?>
            <a target="_blank" href="<?php echo $community->fullURL().'/rin/rrid-report/overview' ?>" style="color:#00bb00"><font size=2px>Create a new authentication report</font></a>
        </div>
      <?php else: ?>
          <?php if(in_array($view, ["SCR_013869-1", "nif-0000-07730-1"])): ?>
            <i title="Log In to Use Authentication Reports" class="fa fa-plus-circle btn-login" style="cursor:pointer; color:#00bb00"><span style="font-family: Arial">&nbsp;Add&nbsp;to&nbsp;an&nbsp;authentication&nbsp;report</span></i>
          <?php endif ?>
      <?php endif ?>
  </span>
