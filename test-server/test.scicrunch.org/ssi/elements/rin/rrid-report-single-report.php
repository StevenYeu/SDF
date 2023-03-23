<?php

$community = $data["community"];
$user = $data["user"];
$report_id = $data["report-id"];

$report = RRIDReport::loadBy(Array("id", "uid"), Array($report_id, $user->id));

if(is_null($report)) {
    \helper\errorPage("rrid-report");
    return;
} else {
    $report_name = $report->name;
    $report_items = RRIDReportItem::loadArrayBy(Array("rrid_report_id"), Array($report->id));
    $snapshots = RRIDReportFreeze::loadArrayBy(Array("rrid_report_id"), Array($report->id));
    $snapshot_json = Array();
    foreach($snapshots as $s) {
        $snapshot_json[] = Array("id" => $s->id, "timestamp" => $s->timestamp, "timestamp-pretty" => \helper\dateFormat("normal", $s->timestamp));
    }
}

$base_uri = $community->fullURL() . "/rin/rrid-report/" . $report->id;

?>

<?php ob_start(); ?>
<div class="profile container content" id="rrid-single-report" ng-controller="rridSingleReportController as ric" ng-cloak>
    <input type="hidden" value="<?php echo $report->id ?>" id="rrid-report-id" />
    <input type="hidden" value="<?php echo $community->fullURL() ?>" id="community-fullURL" />
    <!--<img src="/images/BetaTest64x54.png" style="display:inline" />
    <p style="display:inline">
        This is a new feature that we are testing and we will be adding more functions very soon.  If you have any comments or suggestions, let us know with the Report an issue button at the bottom right.
    </p>-->
    <div class="profile-body">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="js-rrid-info">
                        <h2><?php echo $report_name ?></h2>
                        <p><?php echo $report->description ?></p>
                        <a class="js-update" href="javascript:void(0)">Update description</a>
                    </div>
                    <div class="js-rrid-info-update" style="display:none">
                        <form method="POST" action="/forms/rrid-report-forms/update-name-description.php">
                            <div class="form-group">
                                <p>Name</p>
                                <input type="text" name="name" value="<?php echo $report_name ?>" />
                                <p>Description</p>
                                <textarea name="description"><?php echo $report->description ?></textarea>
                            </div>
                            <input type="hidden" name="report-id" value="<?php echo $report->id ?>" />
                            <button class="btn btn-primary">Update</button>
                            <div class="btn btn-danger js-cancel">Cancel</div>
                        </form>
                    </div>
                    <div ng-show="ric.report_items.length > 0" style="margin-top:10px">
                        <a class="btn btn-primary" href="<?php echo $base_uri ?>/preview"><i class="fa fa-file-text"></i> Preview the report (Step 4)</a>
                        <!-- <a class="btn btn-primary" href="javascript:void(0)" ng-click="ric.createSnapshotButton()"><i class="fa fa-camera"></i> Snapshot report</a> -->
                    </div>
                    <div style="margin-top:10px" ng-show="ric.snapshots.length > 0">
                        <div>List of PDF reports</div>
                        <div ng-repeat="snapshot in ric.snapshots | limitTo: ric.snapshot_limit">
                            <a ng-href="<?php echo $base_uri ?>/snapshot?id={{ snapshot.id }}">{{ snapshot["timestamp-pretty"] }}</a>
                        </div>
                        <div ng-show="ric.snapshots.length > ric.snapshot_limit">
                            <a href="javascript:void(0)" ng-click="ric.showAllSnapshots()">[Show all]</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <h4 id="report_item">Report items (Step 3)</h4>
                    <table class="table" style="height: 100%" ng-show="ric.report_items.length > 0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>RRID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="item in ric.report_items">
                                <td>{{ ric.allowedTypesMap(item.type)["pretty-type-name"] }}</td>
                                <td>
                                    <a ng-href="<?php echo $base_uri ?>/{{ item.uuid }}">
                                        <span class="label label-success" ng-show="item.updated_flag == 1" title="This resource has updated information">NEW DATA</span>
                                        <span ng-show="item.warning"><i class="text-danger fa fa-exclamation-triangle"></i></span>
                                        {{ item.data[ric.allowedTypesMap(item.type)["rrid-name-col"]] }}
                                    </a>
                                    <!--<div class="text-danger" ng-show="item.needs_data">
                                        ** User input needed
                                    </div>-->
                                <td>{{ item.rrid }}</td>
                                </td>
                                <td><button class="btn btn-danger" ng-click="ric.deleteRRIDItem(item)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div ng-hide="ric.report_items.length > 0">
                        <p>
                            Use the search below to add a resource to this report.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div><br>
    <hr/>
    <div class="margin-top-20" id="add-rrid-report-item">
        <h3>Step 2. Search for your resources and add them to the report</h3>
        <h5>
            Search tips: for antibodies and cell lines, it is easier to find the specific antibody or cell line when using <strong>SUGGESTED SEARCH CRITERIA</strong> such as "catalog number" and/or "vendor name."
        </h5>
        <br>
        <form class="rrid-search-input margin-bottom-20" ng-submit="ric.searchItems()">
            <div class="row">
              <div class="col-md-12">
                  <p class="search-section-header" style="display: inline-block">select a resource type</p>
              </div>
              <div class="col-md-2">
                <input type="radio" ng-model="ric.searchType" ng-click="ric.resetSearch()" value=0 /><strong>&nbsp;&nbsp;Antibody</strong>
              </div>
              <div class="col-md-2">
                <input type="radio" ng-model="ric.searchType" ng-click="ric.resetSearch()" value=1 /><strong>&nbsp;&nbsp;Cell Line</strong>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-md-12">
                  <div>
                        <p class="search-section-header" style="display: inline-block">Suggested Search Criteria</p>
                        <p style="display: inline-block">Enter extra filters to help narrow your search</p>
                  </div>
                  <div class="form-inline margin-bottom-20">
                      <label>Vendor:</label>
                      <input type="text" ng-model="ric.searchVendor" class="form-control search-block-filter" />
                      <span ng-show="ric.searchType == 0">
                        <label>Catalog Number:</label>
                        <input type="text" ng-model="ric.searchCatalogNumber" class="form-control search-block-filter" />
                      </span>
                  </div>
              </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <p class="search-section-header" style="display: inline-block">Search</p>
                    <p style="display: inline-block">Type in a keyword to search, such as resource name, target antigen, RRID...etc.</p>
                </div>
                <div class="col-md-6">
                    <span class="input-group">
                        <input class="form-control" type="text" ng-model="ric.searchText"/>
                        <span class="input-group-btn"><button class="btn btn-success">Search</button></span>
                    </span>
                </div>
                <div class="col-md-3">
                    <span ng-click="ric.resetSearch()" class="btn btn-primary">Reset search</span>
                </div>
            </div>
        </form>

        <?php ## added information bars (green or red) -- Vicky-2019-2/11/2019 ?>
        <span ng-show="ric.newItemMap()['flag'] == 0"><a href="#report_item" class="btn btn-success" style="white-space: normal;width:95%">{{ric.newItemMap()['name']}} is successfully added! You can check resource information under Report Items section now.</a></span>
        <span ng-show="ric.newItemMap()['flag'] == 1"><a href="#report_item" class="btn btn-danger" style="white-space: normal;width:95%">{{ric.newItemMap()['name']}} is successfully added! We found there may be critical issues with this resource. Please check resource information under Report Items section now. <br><i style="color:yellow" class="fa fa-warning" data-toggle="popover" data-trigger="hover"></i> Warning: {{ric.newItemMap()['warning']}}</a></span>
        <br><br>
        <uib-tabset active="active">
            <uib-tab>
              <uib-tab-heading>
                    {{ ric.allowed_types[ric.searchType]['pretty-type-name'] }} ({{ ric.allowed_types[ric.searchType]["results-count"] }})
              </uib-tab-heading>
            </uib-tab>
        </uib-tabset>
        <?php ## changed sroll style -- Vicky-2019-1-11?>
        <div style="overflow-y: scroll; height: 500px; width: 100%">
            <table class="table table-bordered">
              <ul uib-pagination
                  ng-hide="ric.allowed_types[ric.searchType]['results-count'] == 0"
                  total-items="ric.allowed_types[ric.searchType]['results-count']"
                  items-per-page="ric.per_page"
                  ng-model="ric.allowed_types[ric.searchType]['page']"
                  ng-change="ric.changePage(ric.allowed_types[ric.searchType])"
                  max-size="7"
                  boundary-links="true">
                </ul>
              <thead>
                  <tr>
                      <th style="width: 100px; position: sticky; top: 0; z-index: 10 ; background: white">Add</th>
                      <?php ## removed Uid column -- Vicky-2019-2-21 ?>
                      <th style="min-width: 150px; position: sticky; top: 0; z-index: 10; background: white" ng-repeat="col in ric.allowed_types[ric.searchType]['rrid-data-cols']" ng-show="col != 'Uid'">
                          {{ col }}
                          <div ng-show="['Clone ID', 'Antibody ID', 'Catalog Number', 'ID', 'Comments'].indexOf(col) === -1">
                              Sort
                              <a ng-click="ric.searchSort(ric.allowed_types[ric.searchType], col, 'asc')" href="javascript:void(0)"><i class="fa fa-sort-asc"></i></a>
                              <a ng-click="ric.searchSort(ric.allowed_types[ric.searchType], col, 'desc')" href="javascript:void(0)"><i class="fa fa-sort-desc"></i></a>
                          </div>
                          <form ng-show="col != 'Uid'" ng-submit="ric.searchFilter(ric.allowed_types[ric.searchType], col)">
                              <div class="input-group input-group-sm">
                                  <input class="form-control" type="text" placeholder="Filter" ng-model="ric.allowed_types[ric.searchType]['filters-input'][col]" />
                                  <span class="input-group-btn"><button class="btn btn-info">Go</button></span>
                              </div>
                          </form>
                      </th>
                  </tr>
              </thead>
              <tbody>
                  <tr ng-repeat="result in ric.allowed_types[ric.searchType]['results']">
                      <td style="width: 100px">
                          <span>
                              <a ng-click="ric.openAddItemModal(result['Uid'], result['v_uuid'], result[ric.allowed_types[ric.searchType]['rrid-view-col']], result[ric.allowed_types[ric.searchType]['rrid-name-col']], ric.allowed_types[ric.searchType]['name'], ric.allowed_types[ric.searchType]['subtypes'])" href="javascript:void(0)" ng-hide="ric.reportHasUUID(result['v_uuid'])" class="btn btn-success">Add</a>
                              <a ng-click="ric.deleteRRIDItem(ric.getItemFromUUID(result['v_uuid']))" href="javascript:void(0)" ng-show="ric.reportHasUUID(result['v_uuid'])" class="btn btn-danger">Remove</a>
                          </span>
                      </td>
                      <?php ## removed Uid column and added warning, resource report icons -- Vicky-2019-3-8 ?>
                      <td style="min-width: 150px" ng-repeat="col in ric.allowed_types[ric.searchType]['rrid-data-cols']" ng-show="col != 'Uid'">
                          <span ng-show="result['Warning'] == 1 && ['Name', 'Antibody Name'].indexOf(col) !== -1"><i class="text-danger fa fa-exclamation-triangle"></i></span>
                          <span ng-show="col == 'Name'">
                            <a target="_blank" ng-href="http://web.expasy.org/cellosaurus/{{result['ID']}}">
                              <span ng-bind-html="result[col]"></span>
                            </a><br>
                            <a target="_blank" ng-href="/data/record/SCR_013869-1/{{result['ID']}}/resolver?i={{result['Uid']}}" data-toggle="tooltip" title="Resource report">
                              <span class="fa-stack fa-md">
                                <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
                                <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                              </span>
                            </a>
                          </span>
                          <span ng-show="col == 'Antibody Name'">
                            <a target="_blank" ng-href="http://antibodyregistry.org/{{result['Antibody ID']}}">
                              <span ng-bind-html="result[col]"></span>
                            </a><br>
                            <a target="_blank" ng-href="<?php echo $community->fullURL() ?>/data/record/nif-0000-07730-1/{{result['Antibody ID']}}/resolver?i={{result['Uid']}}" data-toggle="tooltip" title="Resource report">
                              <span class="fa-stack fa-md">
                                <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
                                <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                              </span>
                            </a>
                          </span>
                          <span ng-show ="['Name', 'Antibody Name'].indexOf(col) === -1" ng-bind-html="result[col]"></span><br>
                      </td>
                  </tr>
              </tbody>
            </table>
            <br/>
            <ul uib-pagination
                ng-hide="ric.allowed_types[ric.searchType]['results-count'] == 0"
                total-items="ric.allowed_types[ric.searchType]['results-count']"
                items-per-page="ric.per_page"
                ng-model="ric.allowed_types[ric.searchType]['page']"
                ng-change="ric.changePage(ric.allowed_types[ric.searchType])"
                max-size="7"
                boundary-links="true">
              </ul>
        </div>
    </div>

    <input type="hidden" id="json-rrid-report-snapshots" value='<?php echo json_encode($snapshot_json) ?>' />

    <!-- MODAL: DELETE REPORT ITEM -->
    <script type="text/ng-template" id="deleteRRIDItemConfirm.html">
        <div class="modal-header">
            <h2>Are you sure you want to remove this item?</h2>
            <!-- <h2>Are you sure you want to remove this item?  All user data associated with it will be permanently deleted.</h2> -->
        </div>
        <div class="modal-body">
            <button class="btn btn-danger" ng-click="delete()">Remove</button>
            <button class="btn btn-default" ng-click="cancel()">Cancel</button>
        </div>
    </script>
    <!-- /MODAL: DELETE REPORT ITEM -->

    <!-- MODAL: CREATE SNAPSHOT -->
    <script type="text/ng-template" id="create-snapshot.html">
        <div class="modal-header"><h2>Create a PDF for this report</h2></div>
        <div class="modal-body">
            <h4>
                Would you like to create a permanent snapshot of this report?
                This will create a read-only copy of all the data in its current state, which cannot be deleted.
                <!--Users are limited to creating five snapshots per day across all authentication reports.-->
            </h4>
            <h4>
                You can <a href="<?php echo $base_uri ?>/preview" target="_blank">preview</a> the report before taking a snapshot.
            </h4>
            <hr/>
            <a href="/forms/rrid-report-forms/snapshot.php?cid=<?php echo $community->id ?>&id=<?php echo $report->id ?>"><button class="btn btn-success">Create</button></a>
            <button ng-click="cancel()" class="btn btn-default close-btn">Cancel</button>
        </div>
    </script>
    <!-- /MODAL: CREATE SNAPSHOT -->

    <!-- MODAL: ITEM ADDED -->
    <!--<script type="text/ng-template" id="item-added.html">
        <div class="modal-header"><h2>{{ item_info.name }}</h2></div>
        <div class="modal-body">
            <h4>
                Additional information is needed to help generate your authentication report.
                <a class="btn btn-success" ng-href="<?php echo $base_uri ?>/{{ item_info.uuid }}">Go to</a> the resource or close this message to continue adding resources.
            </h4>
            <h4>
                If you added this resource by mistake you can <a href="javascript:void(0)" class="btn btn-danger" ng-click="delete()">remove</a> it.
            </h4>
            <hr/>
            <a class="btn btn-default" href="javascript:void(0)" ng-click="close()">Close</a>
        </div>
    </script>-->
    <!-- /MODAL: ITEM ADDED -->

</div>
<?php $report_html = ob_get_clean(); ?>

<?php

$report_data = Array(
    "title" => "Authentication Report",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports", "url" => $community->fullURL() . "/rin/rrid-report"),
        Array("text" => "Report Dashboard", "url" => $community->fullURL() . "/rin/rrid-report/overview"),
        Array("text" => $report_name, "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);

?>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/module-error.js"></script>
<script>
    $(".rrid-item-delete").click(function(e) {
        var itemid = $(this).data("itemid");
        $(".rrid-item-delete-confirm").data("itemid", itemid);
    });

    $(".rrid-item-delete-confirm").click(function(e) {
        var itemid = $(this).data("itemid");
        window.location = "/forms/rrid-report-forms/delete-report-item.php?itemid=" + itemid;
    });

    $(".js-rrid-info .js-update").on("click", function() {
        $(".js-rrid-info-update").show();
        $(".js-rrid-info").hide();
    });
    $(".js-rrid-info-update .js-cancel").on("click", function() {
        $(".js-rrid-info-update").hide();
        $(".js-rrid-info").show();
    });

    window.rrid_allowed_types = <?php echo json_encode(RRIDReportItem::$allowed_types) ?>;
</script>

<script src="/js/module-rrid-single-report.js"></script>
<?php
echo \helper\htmlElement("collection-modals", Array(
    "user" => $_SESSION["user"],
    "community" => NULL,
    "uuids" => NULL,
    "views" => NULL,
));
?>
