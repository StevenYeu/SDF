(function(){
	app = angular.module("mappingCurateApp", ["ui.bootstrap", "term", 'angularModalService', "errorApp"]);

	app.controller("mappingCurateCtrl",
			["$scope", "$log", "term", "$rootScope", "$filter", "ModalService", "$http", "errorModalCaller",
	        function($scope, $log, term, $rootScope, $filter, ModalService, $http, emc){

		var that = this;
		var tmid = $('#tmid').val() !== '' ? $('#tmid').val() : 0;

		$scope.term = null;
    $scope.records = [];
    $scope.selectedRecord = [];
    $scope.statusOptions = ['submitted','matched','pending','rejected','approved'];
		$scope.relationOptions = ['exact','part of','sub class of','overlaps with','other'];
    $scope.selectedStatus = '';
    $scope.message = "";
		$scope.keywords = "";

		$http.get("/api/1/term/mappings-order/" + tmid)
			.then(function(r) {
					// console.log(r.data);
					$scope.previous_id = r.data.data.previous_id;
					$scope.next_id = r.data.data.next_id;
			},function(r){
				emc.call("Error:\n " + r.data['errormsg']);
			});

		$scope.changeRecord = function(){
				$scope.message = '';
				//$log.log($scope.selectedRecord);
				$scope.selectedStatus = $scope.selectedRecord.curation_status;
				term.getMappingLogs($scope.selectedRecord.id).then(function(r){
						$scope.selectedRecord.curation_logs = r;
				//$log.log(r);
				});
				$scope.message = "Changed record to id="+$scope.selectedRecord.id+": "+
				$scope.selectedRecord.source+", "+$scope.selectedRecord.view_name+", "+
				$scope.selectedRecord.column_name+", "+$scope.selectedRecord.value;
		}

    $scope.initializeMappings = function(data, selectedTmid) {
				$scope.notes = '';
				$scope.records = data;

				for (i=0; i < $scope.records.length; i++) {
						if ($scope.records[i].id == selectedTmid) {
								$scope.selectedRecord = $scope.records[i];
								$scope.is_whole = $scope.selectedRecord.is_whole;
								$scope.is_ambiguous = $scope.selectedRecord.is_ambiguous;
								$scope.relation = $scope.selectedRecord.relation;
								$scope.source = $scope.selectedRecord.source;
								$scope.column_name = $scope.selectedRecord.column_name;
								$scope.view_name = $scope.selectedRecord.view_name;

								if ($scope.selectedRecord.tid != null && $scope.selectedRecord.tid > 0) {
										$scope.tid = $scope.selectedRecord.tid;
										$scope.concept = $scope.selectedRecord.concept;
										$scope.concept_id = $scope.selectedRecord.concept_id;
										$scope.ilx = $scope.term.ilx;
										$scope.description = $scope.term.definition;
										$scope.version = $scope.term.version;
										$scope.type = $scope.term.type;
										for (j=0; j<$scope.term.existing_ids.length; j++){
												if ($scope.term.existing_ids[j].preferred == 1){
														$scope.preferredId = $scope.term.existing_ids[j].curie;
														break;
												}
										}
										var synonyms = [];
										for (n=0; n<$scope.term.synonyms.length; n++){
												synonyms.push($scope.term.synonyms[n].literal);
										}
										$scope.synonyms = synonyms.join(", ")
								} else {
										$scope.concept = "";
										$scope.concept_id = "";
										$scope.ilx = "";
										$scope.description = "";
										$scope.version = "";
										$scope.type = "";
										$scope.preferredId = "";
										$scope.synonyms = "";
								}

								$http.get("/api/1/term/mapping-search/" + $scope.selectedRecord.value.replaceAll("/", " ") + "?matchedValue=" + $scope.selectedRecord.matched_value.replaceAll("/", " "))
							    .then(function(r1) {
								    	// console.log(r.data);
								      $scope.suggested_matches = r1.data.data;
							    },function(r1){
										emc.call("Error:\n " + r1.data['errormsg']);
									});

								if ($scope.keywords == "") {
										if ($scope.selectedRecord.matched_value != "") $scope.keywords = $scope.selectedRecord.matched_value;
										if ($scope.keywords == "") $scope.keywords = $scope.selectedRecord.value;
								}
								$scope.keywords = $scope.keywords.replaceAll("/", " ");
								$http.get("/api/1/term/elastic-search/" + $scope.keywords)
							    .then(function(r2) {
								    	// console.log(r.data);
								      $scope.interlex_matches = r2.data.data;
							    },function(r2){
										emc.call("Error:\n " + r2.data['errormsg']);
									});
								break;
						}
				}
				//$log.log($scope.selectedRecord);
				if (typeof $scope.selectedRecord == 'undefined' || $scope.selectedRecord.length == 0){
						$scope.selectedRecord = $scope.records[0];
						$scope.is_whole = $scope.selectedRecord.is_whole;
						$scope.is_ambiguous = $scope.selectedRecord.is_ambiguous;
						$scope.relation = $scope.selectedRecord.relation;
						term.getMappingLogs($scope.selectedRecord.id).then(function(r){
							$scope.selectedRecord.curation_logs = r;
							$log.log(r);
						});
				}

				$scope.selectedStatus = $scope.selectedRecord.curation_status;
    }

    $("body").css("cursor", "progress");
		term.getWithMappings(tmid).then(function(r){
				$scope.term = r;
				$scope.initializeMappings(r.mappings, tmid);
				$("body").css("cursor", "default");
		});

		$scope.changeMappedTerm = function (match_result) {
				$scope.concept = match_result.name;
				$scope.concept_id = match_result.concept_id;
				$scope.description = match_result.description;
				$scope.version = match_result.version;
				$scope.type = match_result.type;
				$scope.preferredId = match_result.preferredID;
				$scope.synonyms = match_result.synonyms;
				$scope.ilx = match_result.ilx;
				$scope.tid = match_result.tid;
		}

		$scope.searchInterlexMatches = function () {
				if($scope.keywords == "") $scope.keywords = "*";
				$http.get("/api/1/term/elastic-search/" + $scope.keywords)
					.then(function(r2) {
							// console.log(r.data);
							$scope.interlex_matches = r2.data.data;
					},function(r2){
						emc.call("Error:\n " + r2.data['errormsg']);
					});
		}

		$scope.changeStatus = function () {
				$scope.message = '';
				//$log.log("change status: " + $scope.selectedRecord.id);
				var params = {};
				params['action'] = 'status_change';
				params['tid'] = $scope.tid;
				params['notes'] = $scope.notes;
				params['curation_status'] = $scope.selectedStatus;
				params['is_whole'] = $scope.is_whole;
				params['is_ambiguous'] = $scope.is_ambiguous;
				params['relation'] = $scope.relation;
				params['concept'] = $scope.concept;
				params['concept_id'] = $scope.concept_id;
				$("body").css("cursor", "progress");
	            $http.post("/api/1/term/mapping/curate/"+$scope.selectedRecord.id, JSON.stringify(params)).then(function(r){
            	//$log.log(r);
            	$scope.term = r.data.data;
							$scope.initializeMappings($scope.term.mappings, $scope.selectedRecord.id);
							$("body").css("cursor", "default");
							$scope.message = "Changed status of record id="+$scope.selectedRecord.id+": "+
							$scope.selectedRecord.source+", "+$scope.selectedRecord.view_name+", "+
							$scope.selectedRecord.column_name+", "+$scope.selectedRecord.value+" to '"+$scope.selectedRecord.curation_status+"'";
            }, function(r){
            	emc.call("Error:\n " + r.data['errormsg']);
            	$("body").css("cursor", "default");
            });
		}

		$scope.deleteRecord = function () {
			$rootScope.entry = $scope.selectedRecord;
			$rootScope.entry.delete_reason = "";
            ModalService.showModal({
                templateUrl: 'mapping-delete-modal',
                controller: "mappingDeleteModalCtrl"
            }).then(function(modal) {
                modal.element.modal();
                modal.close.then(function(choice) {
                    //$log.log("You said " + choice);
                    if (choice == 'cancel') {
                    	$log.log('cancel delete');
                    }
                    if (choice == 'delete') {
                    	//$log.log('delete it!');
            			var params = {};
            			params['action'] = 'delete';
            			params['tid'] = $scope.tid;
            			params['notes'] = $rootScope.entry.delete_reason;
            			//params['curation_status'] = $scope.selectedStatus;
            			$("body").css("cursor", "progress");
                        $http.post("/api/1/term/mapping/curate/"+$scope.selectedRecord.id, JSON.stringify(params)).then(function(r){
                        	var msg = "Deleted record id="+$scope.selectedRecord.id+": "+
                        			$scope.selectedRecord.source+", "+$scope.selectedRecord.view_name+", "+
                        			$scope.selectedRecord.column_name+", "+$scope.selectedRecord.value;
                        	d = r.data.data;
                        	$scope.selectedRecord = d.mappings[0];
													$scope.is_whole = $scope.selectedRecord.is_whole;
													$scope.is_ambiguous = $scope.selectedRecord.is_ambiguous;
                        	$scope.initializeMappings(d.mappings, $scope.selectedRecord.id);
                        	$("body").css("cursor", "default");
                        	$scope.message = msg;
                        }, function(r){
                        	emc.call("Error:\n " + r.data['errormsg']);
                        	$("body").css("cursor", "default");
                        });
												if($scope.next_id != 0) window.location.href = window.location.href.split("tmid=")[0] + "tmid=" + $scope.next_id;
												else window.location.href = window.location.href;
                    }
                });
            });
		}

	}]);


	app.controller('mappingDeleteModalCtrl', function($scope, close) {
		 $scope.close = function(result) {
		    close(result, 500); // close, but give 500ms for bootstrap to animate
		 };
	});
}());
