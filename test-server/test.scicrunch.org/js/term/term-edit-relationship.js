(function(){
	app = angular.module("termEditRelationshipApp", ["errorApp", "ui.bootstrap", "term",'angularModalService']);

	app.controller("termEditRelationshipCtrl",
			["$scope", "$http", "$log", "errorModalCaller", "term", 'termList',"ModalService", "$rootScope",  "termElasticSearch", "getTermFromIlx",
	        function($scope, $http, $log, emc, term, termList, ModalService, $rootScope, termElasticSearch, getTermFromIlx){

		var that = this;
		var rid = $('#rid').val();
		var uid = $('#uid').val();
		var role = $('#role').val();
		var community = $('#community').val();

		if (typeof rid == undefined || isNaN(parseFloat(rid)) || rid < 1){
			$scope.missing_id = true;
		}

		$scope.reset = function(){
			$scope.error = false;
			$scope.feedback = '';
			$scope.canWithdraw = 'no';
			$scope.term1 = $scope.term1 && $scope.terms.length > 0 ? $scope.term1 : undefined;
			$scope.term2 = $scope.term2 && $scope.terms.length > 0 ? $scope.term2 : undefined;
			$scope.relationship = $scope.relationship && $scope.relationships.length > 0 ? $scope.relationship : undefined;
			$scope.missing_id = false;
			$scope.withdrawn = 0;
			$scope.curator_status = 0;
			$scope.upvote = 0;
			$scope.downvote = 0;
			$scope.comment = "";
			$scope.withdraw = false;
			$scope.remove_withdrawal = false;
		}
		$scope.reset();

		$scope.terms = [];
		$scope.relationships = [];
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

		$scope.refresh = function(){

			term.getRelationshipById(rid).then(function(r){
				//$log.log(r.data);

				if (r.data.id < 1) {
					$scope.missing_id = true;
					return false;
				}

				$scope.withdrawn = r.data.withdrawn;
				$scope.curator_status = r.data.curator_status == ""? 0 : r.data.curator_status;
				$scope.upvote = r.data.upvote;
				$scope.downvote = r.data.downvote;
				$scope.comment = r.data.comment;

				if (r.data.orig_uid == uid || role > 1){
					$scope.canWithdraw = 'yes';
				}
				term.getById(r.data.term1_id).then(function(r2){
					//$log.log(r2.data);
					$scope.term1 = r2.data;
				});
				term.getById(r.data.term2_id).then(function(r2){
					//$log.log(r2.data);
					$scope.term2 = r2.data;
				});
				term.getById(r.data.relationship_tid).then(function(r2){
					//$log.log(r2.data);
					$scope.relationship = r2.data;
				});

			});

		};
		$scope.refresh();

		$scope.stripIlx = function(ilx) {
			//console.log(ilx);
			return term.stripIlx(ilx);
		};

		$scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

        $scope.selectTerm = function($label) {
        	//$log.log("selectTerm "  + $label);
        };

		$scope.editTermRelationship = function() {
			$scope.error = false;
			$scope.feedback = '';

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
			params.comment = $scope.comment;
			params.curator_status = $scope.curator_status;
			if ($scope.withdraw == true) {
				params.withdrawn = 1;
			} else if ($scope.remove_withdrawal == true) {
				params.withdrawn = 0;
			}

			getTermFromIlx.fetch($scope.relationship.ilx).then(function(resp) {
				params.relationship_tid = getTermFromIlx.data.id;
				params.relationship_term_version = getTermFromIlx.data.version;
				getTermFromIlx.fetch($scope.term2.ilx).then(function (resp) {
					params.term2_id = getTermFromIlx.data.id;
					params.term2_version = getTermFromIlx.data.version;
					getTermFromIlx.fetch($scope.term1.ilx).then(function (resp) {
						params.term1_id = getTermFromIlx.data.id;
						params.term1_version = getTermFromIlx.data.version;
						$http.post("/api/1/term/edit-relationship/"+rid, params)
						.then(function(r){
							//$log.log(r);
							d = r.data.data;
							if ("errormsg" in d) {
								emc.call(d.errormsg);
								return false;
							}

							//var url = '/scicrunch/about/term/ilx/' + $scope.term1.ilx + "?action=edited&what=Relationship";
							var url = "/" + community + "/interlex/view/" + $scope.term1.ilx + "?action=edited&what=Relationship";
							window.location = url;

						}, function(r){
							emc.call("Error:\n " + r.data['errormsg']);

							$scope.reset();
							$scope.refresh();
						});
					});
				});
			});

		};


	    $scope.validate = function() {
	    	var ok = true;
	    	var msg = "Error:\n";

			if (typeof $scope.term1 == 'undefined') {
				msg += "You need to select first term before submitting.\n";
				ok = false;
			}
			else if (typeof $scope.term1.ilx == 'undefined') {
				msg += "First term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			}
			// Only output
			// else if ( !($scope.term1.type == 'term' || $scope.term1.type == 'cde') ) {
			// 	msg += "Please select a term from the list for first term.\n";
			// 	ok = false;
			// }

			if (typeof $scope.term2 == 'undefined') {
				msg += "You need to select second term before submitting.\n";
				ok = false;
			}
			else if (typeof $scope.term2.ilx == 'undefined') {
				msg += "Second term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			}
			// else if ( !($scope.term2.type == 'term' || $scope.term2.type == 'cde') ) {
			// 	msg += "Please select a term from the list for second term.\n";
			// 	ok = false;
			// }

			if (typeof $scope.relationship == 'undefined') {
				msg += "You need to select relationship term before submitting.\n";
				ok = false;
			} else if (typeof $scope.relationship.ilx == 'undefined') {
				msg += "Relationship term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			} else if ($scope.relationship.type != 'relationship') {
				msg += "Please select a term from the list for relationship term.\n";
				ok = false;
			}


			return {"status":ok, "msg":msg};
	    }

	}]);

}());
