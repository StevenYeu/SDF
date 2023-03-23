<!DOCTYPE html>
<html lang="en">
	<body>
		<!--Profile Body-->
		<div class="profile-body" ng-controller="dashboardContent">
			<!--Service Block v3-->

			<div class="ng-cloak" style="font-size:20px; margin-bottom:35px">
				<b>[Current System Health]:</b>
				<span id="systemStatus" ng-style="applyColor('resourceStatus', systemStatus)">
					{{ systemStatus }}
				</span>
			</div>

			<div class="table-responsive">

				<h2>Error</h2>
				<table id="Error" class="table table-hover resource-status-table">
					<div>
						<tr ng-if="errors.length != 0">
							<th style="text-align:center">Resource Name</th>
							<th style="text-align:center">Mapping Type</th>
							<th style="text-align:center">Schedule Frequency</th>
							<th style="text-align:center">Last Ran</th>
							<th style="text-align:center">Status</th>
							<th style="text-align:center">Source Statistics</th>
							<th style="text-align:center">Actions</th>
						</tr>
						<tr ng-repeat="resource in errors">
							<td id="{{ key }}-{{ resource.resourceName }}-{{resource.mappingType}}-{{ $index }}" class="col-sm-1" ng-repeat="(key,value) in resource"
								ng-style="applyColor(key,value)" ng-cloak>
								{{ value }}
							</td>
						</tr>
					</div>
					<p class="no-records-message" ng-if="errors.length == 0">No Errors Found</p>
				</table>
				<div id="errorLoader" class="loader"></div>

				<h2>Running</h2>
				<table id="Running" class="table table-hover resource-status-table">
					<div>
						<tr ng-if="running.length != 0">
							<th style="text-align:center">Resource Name</th>
							<th style="text-align:center">Mapping Type</th>
							<th style="text-align:center">Schedule Frequency</th>
							<th style="text-align:center">Last Ran</th>
							<th style="text-align:center">Status</th>
							<th style="text-align:center">Source Statistics</th>
							<th style="text-align:center">Actions</th>
						</tr>
						<tr ng-repeat="resource in running">
							<td id="{{ key }}-{{ resource.resourceName }}-{{resource.mappingType}}-{{ $index }}" class="col-sm-1" ng-repeat="(key,value) in resource"
								ng-style="applyColor(key,value)" ng-cloak>
								{{ value }}
							</td>
						</tr>
					</div>
					<p class="no-records-message" ng-if="running.length == 0">Currently No Running Resources</p>

				</table>
				<div id="runningLoader" class="loader"></div>

				<h2>On Hold</h2>
				<table id="Hold" class="table table-hover resource-status-table">
					<div>
						<tr ng-if="hold.length != 0">
							<th style="text-align:center">Resource Name</th>
							<th style="text-align:center">Mapping Type</th>
							<th style="text-align:center">Schedule Frequency</th>
							<th style="text-align:center">Last Ran</th>
							<th style="text-align:center">Status</th>
							<th style="text-align:center">Source Statistics</th>
							<th style="text-align:center">Actions</th>
						</tr>
						<tr ng-repeat="resource in hold">
							<td id="{{ key }}-{{ resource.resourceName }}-{{resource.mappingType}}-{{ $index }}" class="col-sm-1" ng-repeat="(key,value) in resource"
								ng-style="applyColor(key,value)" ng-cloak>
								{{ value }}
							</td>
						</tr>
					</div>
					<p class="no-records-message" ng-if="hold.length == 0">Currently No Resources on Hold</p>

				</table>
				<div id="onHoldLoader" class="loader"></div>

				<h2>Stuck</h2>
				<table id="Stuck" class="table table-hover resource-status-table">
					<div>
						<tr ng-if="stuck.length != 0">
							<th style="text-align:center">Resource Name</th>
							<th style="text-align:center">Mapping Type</th>
							<th style="text-align:center">Schedule Frequency</th>
							<th style="text-align:center">Last Ran</th>
							<th style="text-align:center">Status</th>
							<th style="text-align:center">Source Statistics</th>
							<th style="text-align:center">Actions</th>
						</tr>
						<tr ng-repeat="resource in stuck">
							<td id="{{ key }}-{{ resource.resourceName }}-{{resource.mappingType}}-{{ $index }}" class="col-sm-1" ng-repeat="(key,value) in resource"
								ng-style="applyColor(key,value)" ng-cloak>
								{{ value }}
							</td>
						</tr>
					</div>
					<p class="no-records-message" ng-if="stuck.length == 0">Currently No Resources Are Stuck</p>
				</table>
				<div id="onStuckLoader" class="loader"></div>

				<h2>Scheduled</h2>
				<table id="Scheduled" class="table table-hover resource-status-table">
					<div>
						<tr ng-if="scheduled.length != 0">
							<th style="text-align:center">Resource Name</th>
							<th style="text-align:center">Mapping Type</th>
							<th style="text-align:center">Schedule Frequency</th>
							<th style="text-align:center">Last Ran</th>
							<th style="text-align:center">Status</th>
							<th style="text-align:center">Source Statistics</th>
							<th style="text-align:center">Actions</th>
						</tr>
						<tr ng-repeat="resource in scheduled">
							<td id="{{ key }}-{{ resource.resourceName }}-{{resource.mappingType}}-{{ $index }}" class="col-sm-1" ng-repeat="(key,value) in resource"
								ng-style="applyColor(key,value)" ng-cloak>
								{{ value }}
							</td>
						</tr>
					</div>
					<p class="no-records-message" ng-if="scheduled.length == 0">No Scheduled Ingests</p>
				</table>
				<div id="scheduledLoader" class="loader"></div>
			</div>
			<!--End of Table-->

			<!--Action Panel Modal-->
			<div ng-repeat="resource in modalList">
				<div class="modal" id="{{ resource.resourceID }}" role="dialog">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">{{ resource.resourceName }}
									<span style="float:right; margin-right:25px">
										<b>Status:</b>
										<span ng-style="applyColor('resourceStatus', resource.resourceStatus)">
											{{ resource.resourceStatus }}
										</span>
									</span>
								</h5>
							</div>
							<div class="modal-body" style="margin-bottom:30px">

								<!-- Start of Header -->
								<div class="row">
									<div class="col-lg-5 alignActionRow">
										<label><u>Action</u></label>
									</div>
									<div class="col-lg-3 col-lg-offset-4">
										<label><u>Execute Process</u></label>
									</div>
								</div>
								<!-- End of Header -->

								<div ng-if="resource.resourceStatus != 'HOLD' && resource.resourceStatus != 'SYSTEM_QUEUE_FAILURE' && resource.resourceStatus != 'SYSTEM_ERROR'">
									<div ng-if="resource.resourceStatus != 'ERROR'">
										<div class="row actionRow">
											<div class="col-lg-5 alignActionRow">
												<label>Start Resource Ingest:</label>
											</div>
											<div class="col-lg-3 col-lg-offset-4">
												<label>Confirm</label>
												<input id="{{ resource.resourceID }}:ingest-confirm" class="confirmBox" type="checkbox">
												<img class="actionIcon" src="/images/upload.png" title="Start Ingest" ng-click="execute(resource.resourceName, resource.resourceID, 'ingest')">
											</div>
										</div>

										<!-- <div class="row actionRow">
											<div class="col-lg-5 alignActionRow">
												<label>Reprocess All Records:</label>
											</div>
											<div class="col-lg-3 col-lg-offset-4">
												<label>Confirm</label>
												<input id="{{ resource.resourceID }}:reprocessAll-confirm" class="confirmBox" type="checkbox">
												<img class="actionIcon" src="/images/redo.png" title="Reprocess All Records" ng-click="execute(resource.resourceName, resource.resourceID, 'reprocessAll')">
											</div>
										</div> -->
									</div>

									<div ng-if="resource.resourceStatus == 'ERROR'">
										<div class="row actionRow">
											<div class="col-lg-5 alignActionRow">
												<label>Reprocess Errors:</label>
											</div>

											<div class="col-lg-3 col-lg-offset-4">
												<label>Confirm</label>
												<input id="{{ resource.resourceID }}:reprocessErrors-confirm" class="confirmBox" type="checkbox">
												<img class="actionIcon" src="/images/bug-fixing.png" title="Reprocess Errors" ng-click="execute(resource.resourceName, resource.resourceID, 'reprocessErrors')">
											</div>
										</div>
									</div>

									<hr/>
								</div>


								<div ng-if="resource.resourceStatus != 'HOLD' && resource.resourceStatus != 'SYSTEM_QUEUE_FAILURE' && resource.resourceStatus != 'SYSTEM_ERROR'">
									<div class="row actionRow">
										<div class="col-lg-5 alignActionRow">
											<label>Place Resource On Hold:</label>
										</div>
										<div class="col-lg-3 col-lg-offset-4">
											<label>Confirm</label>
											<input id="{{ resource.resourceID }}:hold-confirm"  class="confirmBox" type="checkbox">
											<img class="actionIcon" src="/images/pause.png" title="Place On Hold" ng-click="execute(resource.resourceName, resource.resourceID, 'hold')">
										</div>
									</div>
									<hr/>
								</div>

								<div ng-if="resource.resourceStatus == 'HOLD'">
									<div class="row actionRow">
										<div class="col-lg-5 alignActionRow">
											<label>Clear Resource From Hold:</label>
										</div>
										<div class="col-lg-3 col-lg-offset-4">
											<label>Confirm</label>
											<input id="{{ resource.resourceID }}:clear-confirm" class="confirmBox" type="checkbox">
											<img class="actionIcon" src="/images/play-button.png" title="Clear Hold" ng-click="execute(resource.resourceName, resource.resourceID, 'clear')">
										</div>
									</div>
									<hr/>
								</div>

								<div ng-if="resource.resourceStatus == 'SYSTEM_ERROR' || resource.resourceStatus == 'SYSTEM_QUEUE_FAILURE'">
									<div class="row actionRow">
										<div class="col-lg-5 alignActionRow">
											<label>Recover Resource:</label>
										</div>
										<div class="col-lg-3 col-lg-offset-4">
											<label>Confirm</label>
											<input id="{{ resource.resourceID }}:recover-confirm" class="confirmBox" type="checkbox">
											<img class="actionIcon" src="/images/bathroom-first-aid-kit-box.png" title="Recover Resource" ng-click="execute(resource.resourceName, resource.resourceID, 'recover')">
										</div>
									</div>
									<hr/>
								</div>

								<!-- <div ng-if="resource.resourceStatus == 'FINISHED' || resource.resourceStatus == 'STUCK'
								            ||resource.resourceStatus == 'ALL_CLEAR' || resource.resourceStatus == 'CLEAR'
														|| resource.resourceStatus == 'RECOVERED'">
									<div class="row actionRow">
										<div class="col-lg-5 alignActionRow">
											<label>Index To Production:</label>
										</div>
										<div class="col-lg-3 col-lg-offset-4">
											<label>Confirm</label>
											<input id="{{ resource.resourceID }}:index-confirm" class="confirmBox" type="checkbox">
											<img class="actionIcon" src="/images/cloud-computing.png" title="Index Resource" ng-click="execute(resource.resourceName, resource.resourceID, 'index')">
										</div>
									</div>
								</div> -->
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal" style="height:35px">Close</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!--Action Panel Modal-->

			<div>
				<div class="modal" id="actionComplete" role="dialog">
					<div class="modal-dialog modal-dialog-centered modal-sm" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">{{ resource.resourceName }}
									<span style="float:right; margin-right:25px">
									</span>
								</h5>
							</div>
							<div class="modal-body" style="margin-bottom:30px">
								<div id="actionLoader" class="loader"></div>
								<div style="text-align:center">
									<b>{{ responseMessage }}</b>
								</div>
							</div>
							<div class="modal-footer">
								<button id="closeConfirmBttn" type="button" class="btn btn-secondary" ng-click="closeActionConfirmation()" style="height:35px; display:none">Close</button>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--Action Panel Modal-->

		</div>
		<!--End Profile Body-->
		<script type="text/javascript" src="/js/foundry-dashboard.js"></script>
	</body>

</html>
