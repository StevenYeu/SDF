(function(){

	var app = angular.module("termAddAnnotationApp", ["errorApp", "ui.bootstrap", "term", 'ngSanitize','angularModalService']);

	app.controller('termAddAnnotationCtrl',
			["$scope", "$http", "$log", "errorModalCaller", "term", 'termList',"ModalService", "$rootScope", "termElasticSearch", "getTermFromIlx",
	        function($scope, $http, $log, emc, term, termList, ModalService, $rootScope, termElasticSearch, getTermFromIlx) {

		var id = $('#id').val();
		var type = $('#type').val();
		var community = $('#community').val();
		var that = this;
		//$log.log(community);

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
		// 	//$log.log(data)
		// 	for(var i=0; i<data.length; i++) {
		// 		//$log.log(data[i]);
		// 		$scope.annotations.push(data[i]);
		// 	}
		// 	//$log.log($scope.annotations)
		// });

        $scope.reset = function() {
			$scope.error = false;
			$scope.feedback = '';
			$scope.term = undefined;
			$scope.annotation = undefined;
			$scope.value = "";
			$scope.range_start = 0;
			$scope.range_end = 0;
			$scope.range_step = 0;
			$scope.comment = "";
			// console.log('outer');
			if (typeof id !== undefined && !isNaN(parseFloat(id)) && id > 1){
				// console.log('inner');
				term.getById(id).then(function(r){
					// console.log('made it');
					$scope.term = r.data;
				});
			}
        };
        $scope.reset();

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
					// console.log(r.data.hits[i]);
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

        $scope.selectTerm = function($label) {
        	//$log.log("selectTerm "  + $label);
        };

        $scope.selectAnnotationTerm = function(annotation) {
        	//$log.log("selectAnnotationTerm "  + JSON.stringify(annotation));
        };

        $scope.stripIlx = function(ilx) {
            //console.log(ilx);
            return term.stripIlx(ilx);
        };

		$scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

		$scope.addTermAnnotation = function() {
			$scope.error = false;
			$scope.feedback = '';

			console.log($scope.annotation);
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
//			$log.log(params);
//			return;
			getTermFromIlx.fetch($scope.annotation.ilx).then(function(resp) {
				params.annotation_tid = getTermFromIlx.data.id;
				params.annotation_term_version = getTermFromIlx.data.version;
				console.log(params);
				$http.post("/api/1/term/add-annotation", params)
				.then(function (r) {
					//$log.log(r);
					d = r.data.data;
					// duplicate annotations will be of code 200 so we need a precheck
					if (typeof d === "string") {
						emc.call(d);
						return false;
					}

					//var url = '/scicrunch/about/term/ilx/' + $scope.term.ilx + "?action=added&what=Annotation";
					var url = "/" + community + "/interlex/view/" + $scope.term.ilx + "?action=added&what=Annotation";
					window.location = url;

				}, function (r) {
					emc.call("Error:\n " + r.data['errormsg']);

					$scope.reset();
				});
			});

		};

		$scope.validate = function() {
	    	var ok = true;
	    	var msg = "Error:\n";

			if (typeof $scope.term == 'undefined') {
				msg += "You need to select term before submitting.\n";
				ok = false;
			} else if (typeof $scope.term.id == 'undefined') {
				msg += "First term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			}
			// } else if ( !($scope.term.type == 'term' || $scope.term.type == 'cde') ) {
			// 	msg += "Please select a term from the list of terms.\n";
			// 	ok = false;
			// }


			if (typeof $scope.annotation == 'undefined') {
				msg += "You need to select annotation term before submitting.\n";
				ok = false;
			} else if (typeof $scope.annotation.ilx == 'undefined') {
				msg += "Annotation term you selected does not exist in the SciCrunch term registry. Please add it first then select it.\n";
				ok = false;
			} else if ($scope.annotation.type != 'annotation') {
				msg += "Please select a term from the list for annotation term.\n";
				ok = false;
			}


			return {"status":ok, "msg":msg};
		};

	}]);


}());
