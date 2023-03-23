var app = angular.module("searchResultsApp", []);

app.controller('searchResultsCtrl', ['$scope', '$window', '$timeout', '$http', '$sce',
	function($scope, $window, $timeout, $http, $sce) {

		// Using `process` field to determine Information Source
		// Records who's process is  `genericLoader` uses `name` in the externalURL field
		var sourceMappings =
			{
				"Cellosaurus-RIN-issues" : '<a href="https://web.expasy.org/cellosaurus/" target="_blank">Cellosaurus</a>',
				"ABR-RIN-issues" : '<a href="https://antibodyregistry.org/" target="_blank">Antibody Registry</a>',
				"AntibodyWatch" : '<a href="https://arxiv.org/pdf/2008.01937.pdf" target="_blank">Antibody Watch</a>',
				"ENCODE" : '<a href="https://www.encodeproject.org/" target="_blank">ENCODE</a>',
				"Human Protein Atlas" : '<a href="https://www.proteinatlas.org/" target="_blank">Human Protein Atlas</a>',
				"ISCC" : '<a href="https://iscconsortium.org" target="_blank">ISCC</a>'
			};

		// What we want to display in the UI based on `display` field value
		var issueStatements =
			{
				"Discontinued" : "Has been documented to be discontinued ",
				"Possibly Discontinued" : "Has been documented to be possibly discontinued ",
				"Partially Contaminated" : "Partial Contamination has been documented ",
				"Misidentified/Contaminated" : "Misidentification/Contamination has been documented ",
				"Misidentified" : "Misidentification has been documented ",
				"Possibly Contaminated" : "Possible Contamination has been documented ",
				"Possibly Misidentified/Contaminated" : "Possible Misidentification/Contamination has been documented ",
				"Contaminated" : "Contamination has been documented ",
				"Possibly Misidentified" : "Possible Misidentification has been documented ",
				"Paper Retracted" : "Paper was retracted ",
				"Partially Misidentified" : "Partial Misidentification has been documented ",
				"Possibly Non-Specific" : "Possibility of Non-Specificity ",
				"Validation Information Available" : "Validation Information Available "
			};

		// Enables loaders while data is loaded in
		$("#nameLoader").show();
		$("#rwDBLoader").show();

		// Populates the left side of the page
		$scope.vendors = [];
		$scope.numVendors = 0;

		// Populates the ride side of the page
		$scope.validationInfo = [];
		$scope.issueInfo = [];
		$scope.entityName = "";

		var protocol =  $window.location.protocol;
		var hostname = $window.location.hostname + ':' + $window.location.port;
		var search = $window.location.search;
		var query = (search.split("=")[1])
		var rrid = query.replace("RRID%3A", "");
		var prefix = (rrid.split("_")[0]).toUpperCase();

		$scope.query = rrid;
		$scope.firstVendor = "";
		$scope.rrid = "";
		$scope.url = "";
		$scope.description = "";

		// Determines how many records are shown
		var num2Show = 2
		$scope.issueLimit = num2Show;
		$scope.validationLimit = num2Show;
		$scope.limitStep = 3;

		/** Populates the left side of the results page **/
		$http.get(protocol + '//' + hostname + '/api/1/resource-watch/UI/ES/' + rrid)
			.then(function (response) { // success
				var data = response.data['data'];

				// If invalid RRID, redirect to No Results Found page. Ex. query = 'test'
				if ((data === "") || (data.length == 0) || (JSON.stringify(data) === '{}')) {
					window.location.href="/ResourceWatch/No_Results_Found?q=" + query;
					return;
				}

				$scope.entityName = (data[0]['_source']['item']['name']).replace(/antibody (\w+(\s)?)*/,"antibody");
				$scope.rrid = data[0]['_source']['item']['identifier'];

				// Constructs the URL based on RRID Prefix
				switch (prefix) {
					case "AB":
						$scope.url = "https://antibodyregistry.org/" + $scope.rrid;
						break;
					case "CVCL":
						$scope.url = "https://web.expasy.org/cellosaurus/" + $scope.rrid;
						break;
				}

				// Consolidate information into one accessible place
				for (var i = 0; i < data.length; i++) {
					var vendor = data[i]['_source'];
					vendor['item']['vendorName'] = (vendor['vendors'] != undefined) ? vendor['vendors'][0]['name'] : "";
					vendor['item']['catalogNumber'] = (vendor['vendors'] != undefined) ?  vendor['vendors'][0]['catalogNumber'] : "";
					vendor['item']['uri'] = (vendor['vendors'] != undefined) ?  vendor['vendors'][0]['uri'] : "";
					$scope.description = vendor['item']['description'];
					if (vendor['item']['vendorName'] != "") {
						$scope.vendors.push(vendor['item']);
					}
				}

				$scope.vendors = filterDuplicates($scope.vendors);
				$scope.vendors = consolidateVendors($scope.vendors);
				$scope.queryRWDB();
			}, function (response) { // error
				alert(response.statusText);
			}).finally(function () {
				$("#nameLoader").hide();
			} );

		/** Populates the right side of the results page **/
		$scope.queryRWDB = function() {
			$http.get(protocol + '//' + hostname + '/api/1/resource-watch/UI/DB/' + $scope.rrid)
				.then(function (response) { // success
					// Sort by scope via sql query --> /resource-watch/data/resource-watch-db.php
					var results = response.data['data'];

					for (var i = 0; i < results.length; i++) {
						var curr = results[i]
						curr["url"] = "";
						curr["displayMessage"] = $sce.trustAsHtml(curr["displayMessage"]);
						curr["source"] = $sce.trustAsHtml(retrieveInfoSource(curr));
						if (curr["externalURL"] != undefined) {
							curr["url"] = JSON.parse(curr["externalURL"])[0]["uri"];
						} else {
							curr["url"] = $scope.url;
						}

						sortRecords(curr); // Sorts issue and val info into separate arrays
					}

				}, function (response) { // error
					alert(response.statusText);
				}).finally(function () {

					$("#rwDBLoader").hide();
					// Has to wait for both sides of the page to load
					$timeout(function () {
						//	Pushes statements with the same vendor as the one selected, to the top
						$scope.firstVendor = retrieveActiveTab();
						if ($scope.firstVendor != "") {
							$scope.issueInfo = ($scope.issueInfo).sort((a) => ((a.vendor === $scope.firstVendor) || (a.vendor === null)) ? -1 : 1); // vendor specific statement second
							$scope.issueInfo = ($scope.issueInfo).sort((a) => ((a.vendor === null)) ? -1 : 1); // global statement first
							$scope.validationInfo = ($scope.validationInfo).sort((a) => ((a.vendor === $scope.firstVendor) || (a.vendor === null)) ? -1 : 1);
						}
					}, 700);

					if ($scope.validationInfo.length === 0 && $scope.issueInfo.length === 0) {
						$("#noResults").css("visibility", "visible");
					}

					// Shows resolved message on load
					$('#resolved').click();
				});
		}

		/*************** Start of Helper Functions ***************/

		$scope.sortByActive = function (vendorName) {
			$scope.issueInfo = ($scope.issueInfo).sort((a) => ((a.vendor === vendorName) || (a.vendor === null)) ? -1 : 1)
			$scope.issueInfo = ($scope.issueInfo).sort((a) => ((a.vendor === null)) ? -1 : 1);
			$scope.validationInfo = ($scope.validationInfo).sort((a) => ((a.vendor === vendorName) || (a.vendor === null)) ? -1 : 1)
		}

		function retrieveActiveTab() {
			// Retrieve the vendor of the first active tab
			if ($('#tabHeader1')[0] != null) {
				return ($('#tabHeader1')[0]).innerText;
			}
			return "";
		}

		function retrieveInfoSource(record) {
			var process = record["process"];
			if (process != "genericLoader") {
				return sourceMappings[process];
			} else {
				if (record["externalURL"] != "") {
					var externalInfo = JSON.parse(record["externalURL"]);
					// TODO Might need to account for multiple obj; just retrieves first one
					return sourceMappings[externalInfo[0]["name"]];
				}
			}
		}

		function sortRecords(curr) {
			// Sorts records into issues or validation info
			if (curr["recordType"] == "validation") {
				$scope.validationInfo.push(curr);
			} else {
				$scope.issueInfo.push(curr);
			}
		}

		function filterDuplicates(vendors) {
			var finalList = [];
			var uniqueVendors = [];

			for (var i = 0; i < vendors.length; i++) {
				uniqueVendors[vendors[i]['vendorName'] + " " + vendors[i]['catalogNumber']] = vendors[i];
			}

			for (var i in uniqueVendors) {
				finalList.push(uniqueVendors[i]);
			}

			// Sort by vendor name via this statement
			finalList = (finalList).sort((a,b) => (a.vendorName > b.vendorName) ? 1 : -1);
			return finalList;
		}

		function consolidateVendors(vendors) {
			var seenVendors = {};
			var catNums = [];
			var finalList = [];
			var uniqueVendors = [];

			// Gather the list of catalog numbers for each vendor
			for (var i = 0; i < vendors.length; i++) {
				if (vendors[i]['vendorName'] in seenVendors) {
					catNums = seenVendors[vendors[i]['vendorName']];
					catNums.push(vendors[i]['catalogNumber']);
					seenVendors[vendors[i]['vendorName']] = catNums;
				} else {
					catNums.push(vendors[i]['catalogNumber']);
					seenVendors[vendors[i]['vendorName']] = catNums;
				}
				catNums = [];
			}

			// Remove duplicate vendors
			for (var i = 0; i < vendors.length; i++) {
				uniqueVendors[vendors[i]['vendorName']] = vendors[i];
			}

			// Compile list of unique vendors
			for (var i in uniqueVendors) {
				finalList.push(uniqueVendors[i]);
				$scope.numVendors += 1;
			}

			// Add the list of catalog numbers back to each vendor
			for (var i = 0; i < finalList.length; i++)  {
				finalList[i]['catNums'] = seenVendors[finalList[i]['vendorName']];
			}

			return finalList
		}


		/***************  End of Helper Functions ***************/


		/***************  Start of Client Functions ***************/

		$scope.incrementLimit = function (type) {
			switch (type) {
				case "issue":
					$scope.issueLimit += $scope.limitStep;
					break;
				case "validation":
					$scope.validationLimit += $scope.limitStep;
					break;
			}
		}

		$scope.decrementLimit = function (type) {
			switch (type) {
				case "issue":
					$scope.issueLimit = num2Show;
					break;
				case "validation":
					$scope.validationLimit = num2Show;
					break;
			}
		}

	/***************  End of Client Functions ***************/

}]);


/*************** Start of Event Functions ***************/

$(document).ready(function () {
	// $('.confirmBttn').hover(function () {
	// 	$(this).css("background-color", "grey")
	// }, function () {
	// 	$(this).css("background-color", "lightgrey")
	// });

	$('#resolved').click(function () {
		// Shows resolved message on load
		$('#resolved').popover("show");
	});

	$('.confirmBttn').click(function () {
		// Retrieves the vendor and catalog number of the select tab
		var index = ($('li.ng-scope.active a').length != 0) ? ($('li.ng-scope.active a')[0].id).split('-')[1] : "";
		var vendor = ($('li.ng-scope.active').length != 0) ? ($('li.ng-scope.active')[0].outerText) : "";
		var vendor4Search = vendor.replaceAll(' ', '_')
		var metadata = $('#tab-' + index);
		var catnum = "";

		// .replaceAll(' ', '_') --> Accounts for vendors with spaces in name
		if (metadata.find("#" + vendor4Search + '-catnum').length > 0) {
			catnum = metadata.find("#" + vendor4Search + '-catnum')[0].outerText;
		} else {
			var catnumElement = document.getElementById(vendor4Search + '-' + index);
			catnum = (catnumElement != undefined) ?  catnumElement.value : "";
		}

		if (vendor == '') { vendor = 'N/A';}
		if (catnum == '') { catnum = 'N/A';}

		document.getElementById("confirmVendor").value = vendor;
		document.getElementById("confirmCatalogNumber").value = catnum;
	});

});

/***************  End of Event Functions ***************/

function closePopover() {
	$('#resolved').popover("hide");
}
