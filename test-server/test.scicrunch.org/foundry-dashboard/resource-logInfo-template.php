<?php
  $srcID = explode("/",$_SERVER[REQUEST_URI], 5)[4];
  $rrid = explode("-",$srcID,2)[0];
 ?>
<!-- FIXME May need to adjust when pushed to production -->
<div class="profile container content" ng-controller="logsDashBoard">
    <div class="row">
        <div class="col-md-12 ng-cloak" style="padding-bottom: 40px">
            <!--Profile Body-->
            <!-- TODO Retrieve Resource information to populate the title and the description -->
            <h1>
				<span ng-if="resourceName != undefined" ng-cloak>
					{{ resourceName }}
					<a href=<?php echo $hostname . "account/foundry-dashboard/resource/".$rrid?>>
						<i class="fa fa-external-link" style="font-size: 16px"></i>
					</a>
					<a href=<?php echo $hostname . "account/foundry-dashboard/resource/".$rrid?>>
						<span class="label label-info pull-right"  style="margin-top: 10px; margin-bottom: 10px; font-size: 15px"> View Resource Information </span>
					</a>
				</span>
            </h1>
            <img src="https://cdn.ablebits.com/_img-blog/line-graph/multiple-line-graph-excel.png" style="width: 100%;height:400px;">

        </div>

        <div class="col-md-12">
            <!--Profile Body-->
            <div class="profile-body">
                <!--Service Block v3-->

                <div class="table-responsive">
                    <table class="table table-hover" id="logs">
                      <thead>
                        <h2 style="background-color:#d9edf7; border-color:#bce8f1;" ng-cloak> {{ resourceName }} Logs</h2>
                        <tr>
                          <th>Curator</th>
                          <th>Status</th>
                          <th>Run Type</th>
                          <th>Started</th>
                          <th>Ended</th>
                          <th>Time Taken</th>
                        </tr>
                      </thead>
                      <tr ng-repeat="log in logRecords" ng-cloak>
                        <td>{{ log.userName }}</td>
                        <td>{{ log.status }}</td>
                        <td>{{ log.processType }}</td>
                        <td>{{ log.startTime }}</td>
                        <td>{{ log.endTime }}</td>
                        <td>{{ log.timeTaken }}</td>
                      </tr>
                    </table>
                </div>
            </div>
        </div>
        <!--End Profile Body-->
    </div>
    <!--/end row-->
</div>
<!--/container-->
<!--=== End Profile ===-->
<script type="text/javascript" src="/../js/foundry-dashboard.js"></script>
