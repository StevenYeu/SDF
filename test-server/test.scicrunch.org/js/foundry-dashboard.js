var app = angular.module('dashboard', []);

app.controller('dashboardContent', ['$scope', '$window', '$http', '$timeout', function($scope, $window, $http, $timeout) {

	// Sorts resources into their respective sections
	$scope.errors = Array();
	$scope.running = Array();
	$scope.hold = Array();
	$scope.stuck = Array();
	$scope.scheduled = Array();
	$scope.allResources = Array();
	$scope.modalList = Array();

	// Lists to determine which stats are shown
	$scope.hasIngest = {};
	$scope.hasStuck = {};
	$scope.hasErrors = {};

	// System Status
	$scope.systemStatus = "ALL_CLEAR";

	// Enables loaders while data is loaded in
	$("#errorLoader").show();
	$("#runningLoader").show();
	$("#onHoldLoader").show();
	$("#onStuckLoader").show();
	$("#scheduledLoader").show();
	$("#allResourcesLoader").show();

	var protocol = $window.location.protocol;
	var hostname = $window.location.hostname + ':' + $window.location.port;

	/** Retrieve resources from Foundry DB **/
	$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/MAIN/resources')
		.then(function (response) {
			var resource = {};
			$scope.dataList = response.data['data'];
			for (var i = 0; i < $scope.dataList.length; i++) {
				resource["resourceName"] = $scope.dataList[i]["resourceName"].replace(/ /g, "_");
				resource["mappingType"] = $scope.dataList[i]["mappingType"].replace(/ /g, "_");
				resource["scheduleFreq"] = $scope.dataList[i]["scheduleFreq"];
				resource["lastRanTimeStamp"] = $scope.dataList[i]["lastRanTimeStamp"];
				resource["resourceStatus"] = $scope.dataList[i]["resourceStatus"];
				resource["ingestionStats"] = null;
				resource["actions"] = null;
				sortResources(resource);	// Errors, Running, Scheduled
				$scope.allResources.push(resource);
				$scope.modalList.push($scope.dataList[i]);
				resource = {}; // Reset
			}
		}, function (response) {
			$scope.error = response.statusText;
		}).finally(function () {
			$("#errorLoader").hide();
			$("#runningLoader").hide();
			$("#onHoldLoader").hide();
			$("#onStuckLoader").hide();
			$("#scheduledLoader").hide();
			$("#allResourcesLoader").hide();
			$timeout(retrieveStats, 500);
		});

	/** Retrieve resources from Foundry DB **/


	/** Retrieve resource stats from Foundry DB **/
	function retrieveStats() {
		$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/MAIN/statistics')
			.then(function (response) {
				var resourceStats = response.data['data'];
				for (var i = 0; i < resourceStats.length; i++) {
					resourceStats[i]["resourceID"] = resourceStats[i]["resourceID"];
					resourceStats[i]["resourceName"] = resourceStats[i]["resourceName"].replace(/ /g, "_");
					resourceStats[i]["mappingType"] = resourceStats[i]["mappingType"].replace(/ /g, "_");
				}

				insertActionIcons(resourceStats);
				insertStats(resourceStats);
			}, function (response) {
				$scope.error = response.statusText;
			});
	}
	/** Retrieve resource stats from Foundry DB **/

	/** Retrieve System Status from Foundry DB **/
	$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/MAIN/systemStatus')
		.then(function (response) {
			var resourceStats = response.data['data'];

			if (resourceStats.length > 1) {
				for (var i = 0; i < resourceStats.length; i++){
					if (i == (resourceStats.length - 1)) {
						$scope.systemStatus += resourceStats[i].resourceStatus;
					} else {
						$scope.systemStatus += resourceStats[i].resourceStatus + ' | ';
					}
				}
			} else if (resourceStats.length == 1) {
				$scope.systemStatus = resourceStats[0].resourceStatus;
			}
		}, function (response) {
			$scope.error = response.statusText;
		});
	/** Retrieve System Status from Foundry DB **/

	/****************** Start of Helper Functions ******************/

	function sortResources(resource) {
		switch (resource["resourceStatus"]) {
			case "FINISHED":
				($scope.scheduled).push(resource);
				break;
			case "ALL_CLEAR":
				($scope.scheduled).push(resource);
				break;
			case "CLEAR":
				($scope.scheduled).push(resource);
				break;
			case "RECOVERED":
				($scope.scheduled).push(resource);
				break;
			case "HOLD":
				($scope.hold).push(resource);
				break;
			case "RUNNING":
				($scope.running).push(resource);
				break;
			case "STUCK":
				($scope.stuck).push(resource);
				break;
			case "ERROR":
				$scope.hasErrors[resource["resourceName"] + resource["mappingType"]] = true;
				($scope.errors).push(resource);
				break;
			case "SYSTEM_ERROR":
				$scope.hasErrors[resource["resourceName"] + resource["mappingType"]] = true;
				($scope.errors).push(resource);
				break;
			case "SYSTEM_QUEUE_FAILURE":
				$scope.hasErrors[resource["resourceName"] + resource["mappingType"]] = true;
				($scope.errors).push(resource);
				break;
			case "Ingesting":
				$scope.hasIngest[resource["resourceName"] + resource["mappingType"]] = true;
				break;
			case "":
				($scope.scheduled).push(resource);
				break;
		}
	}

	function getNumRows () {
		var numRows = ($("#Running").find("tr").length - 1);
		return numRows;
	};

	function insertStats (resourceStats) {
		for (var i = 0; i < resourceStats.length; i++) {
			var idTag = "#ingestionStats-" + resourceStats[i]["resourceName"] + "-" + resourceStats[i]["mappingType"] + "-5";
			var statsHTML = '<div class="container col-sm-offset-1"><span class="col-sm-6"><b>total:</b><br/> ' + resourceStats[i]["totalRecCount"] + '</span>';

			// Show stats based on Status: Currently running or in error state
			if ($scope.hasIngest[resourceStats[i]["resourceName"] + resourceStats[i]["mappingType"]]) {
				statsHTML += ' <span class="col-sm-1"><b style="color:green; margin-right:5px">Ingesting:</b><br/> ' + resourceStats[i]["ingestedRecCount"] + '</span>';
			}
			if ($scope.hasErrors[resourceStats[i]["resourceName"] + resourceStats[i]["mappingType"]]) {
				statsHTML += ' <span class="col-sm-1"><b style="color:red; margin-right:5px">Errors:</b><br/> ' + resourceStats[i]["errorCount"] + '</span>';
			}
			statsHTML += '</div>';

			$(idTag).html(statsHTML);
		}
	}

	function insertActionIcons (resources) {
		for (var i = 0; i < resources.length; i++) {
			var resourceID = resources[i]["resourceID"];
			var scrID = resourceID.split("-")[0]; // Ex. SCR_006397-ABR-RIN ---> SCR_006397
			var idTag = "#actions-" + resources[i]["resourceName"] + "-" + resources[i]["mappingType"] + "-6";
			var iconHTML = '<a href="/account/foundry-paneldashboard"><img class="actionButton" src="' + origin + '/images/bar-chart.png" title="view stats info"></img></a>';
			iconHTML += '<a href="/account/foundry-dashboard/logs/' + scrID + '"><img class="actionButton" src="' + origin + '/images/file.png" title="View Logs"></img></a>';
			iconHTML += '<a href="/account/foundry-dashboard/resource/'+ scrID +'"><img class="actionButton" src="' + origin + '/images/information.png" title="View Resource Info"></img></a>';
			iconHTML += '<img class="actionButton" src="' + origin + '/images/round-play-button.png" title="Action Options" data-toggle="modal" data-target="#'+ resourceID + '" data-resourceid="'+ scrID + '"></img>';
			$(idTag).html(iconHTML);
		}

	}

	/****************** End of Helper Functions ******************/

	$scope.applyColor = function (key, value) {
		if (key == 'resourceStatus') {
			if ( value == 'RUNNING') {
				return {'color':'green', 'font-weight': 'bold'};
			}

			if (value == 'ERROR') {
				return {'color':'red', 'font-weight': 'bold'};
			}

			if (value == 'SYSTEM_ERROR') {
				return {'color':'red', 'font-weight': 'bold'};
			}

			if (value == 'SYSTEM_QUEUE_FAILURE') {
				return {'color':'red', 'font-weight': 'bold'};
			}

			if (value == 'HOLD') {
				return {'color':'orange', 'font-weight': 'bold'};
			}

			if (value == 'STUCK') {
				return {'color':'orange', 'font-weight': 'bold'};
			}

			if (value == 'FINISHED') {
				return {'color':'black', 'font-weight': 'bold'};
			}

			if ( value == 'ALL_CLEAR') {
				return {'color':'green', 'font-weight': 'bold'};
			}

			if (value == 'CLEAR') {
				return {'color':'green', 'font-weight': 'bold'};
			}

			if (value == 'RECOVERED') {
				return {'color':'green', 'font-weight': 'bold'};
			}
		}
		return {'color':'black'};
	}

	/** Start of Action functions **/

	$scope.execute = function (resourceName, resourceID, process) {
		$scope.responseMessage = "";

		var id = resourceID + ':' + process + '-' + 'confirm';
		var checked = document.getElementById(id).checked;

		$('#actionLoader').show();

		if (checked) {
			// Start API call
			switch (process) {
				case 'ingest':
					$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/Ingest/' + resourceID)
						.then(function (response) {
							var results = response.data['success'];
							$scope.responseMessage = 'Ingest Initiated for ' + resourceName;
						}, function (response) {
							console.log(response.headers);
						}).finally(function () {
							$('#actionLoader').hide();
							$('#closeConfirmBttn').show();
						});
					break;
				// case 'reprocessAll':
				// 	$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/ReprocessAll/' + resourceID)
				// 		.then(function (response) {
				// 			var results = response.data['success'];
				// 			console.log(results);
				// 			$scope.responseMessage = 'Reprocess All Initiated for ' + resourceName;
				// 		}, function (response) {
				// 			console.log(response.headers);
				// 		}).finally(function () {
				// 			$('#actionLoader').hide();
				// 			$('#closeConfirmBttn').show();
				// 		});
				// 	break;
				case 'reprocessErrors':
					$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/ReprocessErrors/' + resourceID)
						.then(function (response) {
							var results = response.data['success'];
							$scope.responseMessage = 'Reprocess Errors Initiated for ' + resourceName;
						}, function (response) {
							console.log(response.headers);
						}).finally(function () {
							$('#actionLoader').hide();
							$('#closeConfirmBttn').show();
						});
					break;
				case 'hold':
					$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/Hold/' + resourceID)
						.then(function (response) {
							var results = response.data['data'][0];
							$scope.responseMessage = resourceName + ' was placed on Hold';
						}, function (response) {
							console.log(response);
						}).finally(function () {
							$('#actionLoader').hide();
							$('#closeConfirmBttn').show();
						});
					break;
				case 'clear':
					$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/Clear/' + resourceID)
						.then(function (response) {
							var results = response.data['data'][0];
							$scope.responseMessage = resourceName + ' was Cleared';
						}, function (response) {
							console.log(response.headers);
						}).finally(function () {
							$('#actionLoader').hide();
							$('#closeConfirmBttn').show();
						});
					break;
				case 'recover':
					$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/Reprocess')
						.then(function (response) {
							var results = response.data['data'][0];
							$scope.responseMessage = resourceName + ' was Cleared';
						}, function (response) {
							console.log(response.headers);
						}).finally(function () {
							$('#actionLoader').hide();
							$('#closeConfirmBttn').show();
						});
					break;
				// case 'index':
				// 	$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/Controller/Reprocess/' + resourceID)
				// 		.then(function (response) {
				// 			var results = response.data['data'][0];
				// 			$scope.responseMessage = resourceName + ' was Cleared';
				// 		}, function (response) {
				// 			console.log(response.headers);
				// 		}).finally(function () {
				// 			$('#actionLoader').hide();
				// 			$('#closeConfirmBttn').show();
				// 		});
				// 	break;
			}

			document.getElementById('actionComplete').style.display = 'block';

		}
	}

	$scope.closeActionConfirmation = function() {
		document.getElementById('actionComplete').style.display = 'none';
		// TODO Change to a better method
		$window.location.reload();
	}

	/** End of Action functions **/

}]);

/****************** Start of Resource Logs Page ******************/

app.controller('logsDashBoard', ['$scope', '$window','$http', '$timeout', function($scope, $window, $http, $timeout) {

	var hostname = $window.location.hostname + ':' + $window.location.port;
	var rrid = $window.location.pathname.split('/').slice(-1)[0];
	var protocol = $window.location.protocol;

	// Retrieve resource information from Foundry API
	$http.get(protocol + "//"+ hostname + "/api/1/resource/fields/view/" + rrid + "?version=1")
		.then(function (response) {
			var resource = {};
			var results = response.data.data;
			$scope.resourceName = results["fields"][0]["value"];
		}, function (response) {
			$scope.error = response.statusText;
		});

	// Retrieve resource log information from DB
	$http.get(protocol + '//' + hostname + '/api/1/foundryDB/UI/LOG/' + rrid)
	  .then(function (response) {
		var resource = {};
		var results = response.data['data'];
		$scope.logRecords = results.slice(0,10);
	  }, function (response) {
		$scope.error = response.statusText;
	  });

}]);

/****************** End of Resource Logs Page ******************/

/****************** Start of Resource Detail Page ******************/

app.controller('resourceDashBoard', ['$scope', '$window','$http', '$timeout', function($scope, $window, $http, $timeout) {

	var hostname = $window.location.hostname + ':' + $window.location.port;
	var rrid = $window.location.pathname.split('/').slice(-1)[0];
	var protocol = $window.location.protocol;

	// Retrieve resource information from Foundry API
	$http.get(protocol + "//"+ hostname + "/api/1/resource/fields/view/" + rrid + "?version=1")
		.then(function (response) {
			var results = response.data.data;
			$scope.resourceName = results["fields"][0]["value"];
			$scope.description = results["fields"][1]["value"];
			$scope.scicrunchID = results["scicrunch_id"];
		}, function (response) {
			$scope.error = response.statusText;
		});
}]);

/****************** End of Resource Detail Page ******************/
