(function(){

	var app = angular.module("termAddRelationshipApp", ["errorApp", "ui.bootstrap", "term", 'ngSanitize','angularModalService']);

	app.controller('termAddRelationshipCtrl',
			["$scope", "$rootScope", "$http", "$log", "errorModalCaller", "term", 'termList',"ModalService",  "termElasticSearch", "getTermFromIlx",
	        function($scope, $rootScope, $http, $log, emc, term, termList, ModalService, termElasticSearch, getTermFromIlx) {

		var id = $('#id').val();
		var type = $('#type').val();
		var community = $('#community').val();
		var that = this;
		$log.log(community);

		$scope.terms = [];
		$scope.relationships = [];

		// DEPRECATED: BREAKS
		// termList.getByType('term').then(function(data){
		// 	for(var i=0; i<data.length; i++) {
		// 		//$log.log(data[i]);
		// 		$scope.terms.push(data[i]);
		// 	}
		// });
		// termList.getByType('cde').then(function(data){
		// 	for(var i=0; i<data.length; i++) {
		// 		//$log.log(data[i]);
		// 		$scope.terms.push(data[i]);
		// 	}
		// });
		// termList.getByType('relationship').then(function(data){
		// 	for(var i=0; i<data.length; i++) {
		// 		//$log.log(data[i]);
		// 		$scope.relationships.push(data[i]);
		// 	}
		// });

		$scope.updateRelationshipList = function (val){
			return termElasticSearch.fetch(val, 25, 0, -1, 'relationship').then(function(r){
				if (r.data.total === 0 || r.data.total === '' || r.data.total === undefined) {
					return [];
				}
				$scope.availableRelationships = [];
				for (var i= 0; i<r.data.hits.length; i++){
					var ilx = r.data.hits[i]._source.ilx;
					var label = r.data.hits[i]._source.label;
					var id = r.data.hits[i]._source.id;
					var type = r.data.hits[i]._source.type;
					var version = r.data.hits[i]._source.version;
					// for testing
					// ilx = ilx.replace('ilx_', 'tmp_');
					$scope.availableRelationships.push({
						"label": label,
						"ilx": ilx,
						"id": id,
						"type": type,
						"version": version
					});
				}
				return $scope.availableRelationships;
			});
		};

		// TODO: need to update termElasticSearch to handle all term types minus the annotation & relationships
		$scope.updateTermList = function (val){
			return termElasticSearch.fetch(val, 25, 0, -1, 'all').then(function(r){
				if (r.data.total === 0 || r.data.total === '' || r.data.total === undefined) {
					return [];
				}
				$scope.availableTermList = [];
				for (var i= 0; i<r.data.hits.length; i++){
					var ilx = r.data.hits[i]._source.ilx;
					var label = r.data.hits[i]._source.label;
					var id = r.data.hits[i]._source.id;
					var version = r.data.hits[i]._source.version;
					var type = r.data.hits[i]._source.type;
					if (type === 'annotation' || type === 'relationship'){
						continue;
					}
					// for testing
					// ilx = ilx.replace('ilx_', 'tmp_');
					$scope.availableTermList.push({
						"label": label,
						"ilx": ilx,
						"id": id,
						"type": "term",
						"version": version
					});
				}
				return $scope.availableTermList;
			});
		};

        $scope.reset = function() {
			$scope.error = false;
			$scope.feedback = '';
			$scope.term1 = undefined;
			$scope.term2 = undefined;
			$scope.relationship = undefined;

			if (typeof id !== undefined && !isNaN(parseFloat(id)) && id > 1){
				term.getById(id).then(function(r){
					// if(r.data.type === 'term' || r.data.type === 'cde' || r.data.type === 'TermSet')
					// 	$scope.term1 = r.data
					if(r.data.type === 'relationship')	{
						$scope.relationship = r.data;
					} else {
						// console.log('made it!');
						$scope.term1 = r.data;
					}
				});
			}
        };
        $scope.reset();

        $scope.selectTerm = function($label) {
        	//$log.log("selectTerm "  + $label);
        };

        $scope.stripIlx = function(ilx) {
            //console.log(ilx);
            return term.stripIlx(ilx);
        };

        $scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

		$scope.addTermRelationship = function() {
			$scope.error = false;
			$scope.feedback = '';
			console.log($scope.term1);
			console.log($scope.term2);
			console.log($scope.relationship);
			var ok = $scope.validate();
			if (!ok.status){
				emc.call(ok.msg);
				$scope.error = true;
				$scope.feedback = ok.msg;
				return false;
			}

			var params = {};
			params.term1_id = $scope.term1.id;
			params.term2_id = $scope.term2.id;
			params.relationship_tid = $scope.relationship.id;
			params.term1_version = $scope.term1.version;
			params.term2_version = $scope.term2.version;
			params.relationship_term_version = $scope.relationship.version;

			getTermFromIlx.fetch($scope.relationship.ilx).then(function(resp) {
				params.relationship_tid = getTermFromIlx.data.id;
				params.relationship_term_version = getTermFromIlx.data.version;
				getTermFromIlx.fetch($scope.term2.ilx).then(function (resp) {
					params.term2_id = getTermFromIlx.data.id;
					params.term2_version = getTermFromIlx.data.version;
					getTermFromIlx.fetch($scope.term1.ilx).then(function (resp) {
						params.term1_id = getTermFromIlx.data.id;
						params.term1_version = getTermFromIlx.data.version;
						$http.post("/api/1/term/add-relationship", params)
							.then(function (r) {
								d = r.data.data;
								// duplicate relationships will be of code 200 so we need a precheck
								if (typeof d === "string") {
									emc.call(d);
									return false;
								}

								//var url = '/scicrunch/about/term/ilx/' + $scope.term1.ilx + "?action=added&what=Relationship";
								var url = "/" + community + "/interlex/view/" + $scope.term1.ilx + "?action=added&what=Relationship";
								window.location = url;

							}, function (r) {
								emc.call("Error:\n " + r.data['errormsg']);

								$scope.reset();
							});
					});
				});
			});

		};

		$scope.validate = function() {
	    	var ok = true;
	    	var msg = "Error:\n";

			if (typeof $scope.term1 == undefined) {
				msg += "You need to select first term before submitting.\n";
				ok = false;
			} else if (typeof $scope.term1.ilx == undefined) {
				msg += "First term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			}
			// } else if ( !($scope.term1.type == 'term' || $scope.term1.type == 'cde') ) {
			// 	msg += "Please select a term from the list for first term.\n";
			// 	ok = false;
			// }

			if (typeof $scope.term2 == undefined) {
				msg += "You need to select second term before submitting.\n";
				ok = false;
			} else if (typeof $scope.term2.ilx == undefined) {
				msg += "Second term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			}
			// else if ( !($scope.term2.type == 'term' || $scope.term2.type == 'cde') ) {
			// 	msg += "Please select a term from the list for second term.\n";
			// 	ok = false;
			// }

			if (typeof $scope.relationship == undefined) {
				msg += "You need to select relationship term before submitting.\n";
				ok = false;
			} else if (typeof $scope.relationship.ilx == undefined) {
				msg += "Relationship term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			} else if ($scope.relationship.type != 'relationship') {
				msg += "Please select a term from the list for relationship term.\n";
				ok = false;
			}


			return {"status":ok, "msg":msg};
		};

	}]);


}());
