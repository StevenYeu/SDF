(function(){
	app = angular.module("termEditAnnotationApp", ["errorApp", "ui.bootstrap", "term",'angularModalService']);

	app.controller("termEditAnnotationCtrl",
			["$scope", "$http", "$log", "errorModalCaller", "term", 'termList',"ModalService", "$rootScope", "termElasticSearch", "getTermFromIlx",
	        function($scope, $http, $log, emc, term, termList, ModalService, $rootScope, termElasticSearch, getTermFromIlx){

		var that = this;
		var aid = $('#aid').val();
		var uid = $('#uid').val();
		var role = $('#role').val();
		var community = $('#community').val();

		if (typeof aid == undefined || isNaN(parseFloat(aid)) || aid < 1){
			$scope.missing_id = true;
		}

		$scope.reset = function(){
			$scope.error = false;
			$scope.feedback = '';
			$scope.canWithdraw = 'no';
			$scope.value = "";
			$scope.comment = "";
			$scope.value = "";
			$scope.range_start = 0;
			$scope.range_end = 0;
			$scope.range_step = 0;
			$scope.withdrawn = 0;
			$scope.remove_withdrawal = false;
			$scope.missing_id = false;
			$scope.term = $scope.term && $scope.terms.length > 0 ? $scope.term : undefined;
			$scope.annotation = $scope.annotation && $scope.annotations.length > 0 ? $scope.annotation : undefined;
		}
		$scope.reset();

		$scope.terms = [];
		$scope.annotations = [];
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
		// termList.getAnnotationTermList().then(function(data){
		// 	for(var i=0; i<data.length; i++) {
		// 		//$log.log(data[i]);
		// 		$scope.annotations.push(data[i]);
		// 	}
		// 	//$log.log($scope.annotations)
		// });

		$scope.refresh = function(){
			term.getAnnotationById(aid).then(function(r){
//				$log.log('here')
//				$log.log(r.data);

				if (r.data.id < 1) {
					$scope.missing_id = true;
					return false;
				}

				$scope.withdrawn = r.data.withdrawn;
				$scope.curator_status = r.data.curator_status == ""? 0 : r.data.curator_status;
				$scope.upvote = r.data.upvote;
				$scope.downvote = r.data.downvote;
				$scope.value = r.data.value;
				$scope.comment = r.data.comment;
				if (r.data.orig_uid == uid || role > 1){
					$scope.canWithdraw = 'yes';
				}
				term.getById(r.data.tid).then(function(r2){
					//$log.log(r2.data);
					$scope.term = r2.data;
				});
				term.getById(r.data.annotation_tid).then(function(r2){
					//$log.log(r2.data);
					$scope.annotation = r2.data;
					if ($scope.annotation.annotation_type == 'range') {
						var parts = $scope.value.split(",");
						$scope.range_start = parseInt(parts[0].trim().replace("[", ""));
						$scope.range_end = parseInt(parts[1].trim());
						$scope.range_step = parseInt(parts[2].trim().replace("]", ""));
					}

				});
				//$log.log($scope.canWithdraw);
			});

		};
		$scope.refresh();

		$scope.updateAnnotationList = function (val){
			return termElasticSearch.fetch(val, 25, 0, -1, 'annotation').then(function(r){
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
					console.log(r.data.hits[i]);
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

		$scope.stripIlx = function(ilx) {
			//console.log(ilx);
			return term.stripIlx(ilx);
		}

		$scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

        $scope.selectTerm = function($label) {
        	//$log.log("selectTerm "  + $label);
        }

		$scope.editTermAnnotation = function() {
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
			params.tid = $scope.term.id;
			params.annotation_tid = $scope.annotation.id;
			params.term_version = $scope.term.version;
			params.annotation_term_version = $scope.annotation.version;
			if ($scope.annotation.annotation_type == 'range'){
				params.value = "[" + $scope.range_start + ", " + $scope.range_end + ", " + $scope.range_step + "]";
			}
			else {
				params.value = $scope.value;
			}
			params.comment = $scope.comment;
			params.curator_status = $scope.curator_status;
			if ($scope.withdraw == true) {
				params.withdrawn = 1;
			} else if ($scope.remove_withdrawal == true) {
				params.withdrawn = 0;
			}

			getTermFromIlx.fetch($scope.annotation.ilx).then(function(resp) {
				params.annotation_tid = getTermFromIlx.data.id;
				params.annotation_term_version = getTermFromIlx.data.version;
				console.log(params);
				$http.post("/api/1/term/edit-annotation/"+aid, params)
				.then(function(r){
					//$log.log(r);
					d = r.data.data;
					if ("errormsg" in d) {
						emc.call(d.errormsg);
						return false;
					}

					//var url = '/scicrunch/about/term/ilx/' + $scope.term.ilx + "?action=edited&what=Annotation";
					var url = "/" + community + "/interlex/view/" + $scope.term.ilx + "?action=edited&what=Annotation";
					window.location = url;

				}, function(r){
					emc.call("Error:\n " + r.data['errormsg']);

					$scope.reset();
					$scope.refresh();
				});
			});

		};


	    $scope.validate = function() {
	    	var ok = true;
	    	var msg = "Error:\n";

			if (typeof $scope.term == undefined) {
				msg += "You need to select term before submitting.\n";
				ok = false;
			} else if (typeof $scope.term.id == undefined) {
				msg += "First term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			}
			// } else if ( !($scope.term.type == 'term' || $scope.term.type == 'cde') ) {
			// 	msg += "Please select a term from the list of terms.\n";
			// 	ok = false;
			// }

			if (typeof $scope.annotation == undefined) {
				msg += "You need to select annotation term before submitting.\n";
				ok = false;
			} else if (typeof $scope.annotation.id == undefined ) {
				if (typeof $scope.annotation.ilx == undefined ) {
					msg += "Annotation term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
					ok = false;
				}
			} else if ($scope.annotation.type != 'annotation') {
				msg += "Please select a term from the list for annotation term.\n";
				ok = false;
			}

			return {"status":ok, "msg":msg};
	    };

	}]);


}());
