<!DOCTYPE html>
<html lang="en">
	<body>
		<!--Profile Body-->
		<div class="profile-body" ng-controller="dashboardContent">
			<!--Service Block v3-->
			<div class="table-responsive">
				<h2>All Resources</h2>
				<table class="table table-hover" id="Error" style="margin-bottom: 35px">
					<div ng-if="allResources.length != 0">
						<tr>
							<th style="text-align:center">Resource Name</th>
							<th style="text-align:center">Mapping Type</th>
							<th style="text-align:center">Schedule Frequency</th>
							<th style="text-align:center">Last Ran</th>
							<th style="text-align:center">Status</th>
							<th style="text-align:center">Source Statistics</th>
							<th style="text-align:center">Actions</th>
						</tr>
						<tr ng-repeat="resource in allResources">
							<td id="{{ key }}-{{ resource.resourceName }}-{{resource.mappingType}}-{{ $index }}" class="col-sm-1" ng-repeat="(key,value) in resource"
								ng-style="applyColor(key,value)" ng-cloak>
								{{ value }}
							</td>
						</tr>
					</div>
				</table>
				<div id="allResourcesLoader" class="loader"></div>
			</div>
			<!--End of Table-->
		</div>
		<!--End Profile Body-->
		<!-- <script src="https://code.angularjs.org/1.6.7/angular.js"></script> -->
		<script type="text/javascript" src="/js/foundry-dashboard.js"></script>
	</body>
</html>
