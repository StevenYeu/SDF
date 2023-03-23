(function(){
	app = angular.module("termEditApp", ["errorApp", "ui.bootstrap", "term", 'angular.chips','angularModalService', 'utilitiesApp']);

	app.controller("termEditCtrl",
			["$scope", "$http", "$log", "errorModalCaller", "curieCatalog", "termOntologies", "term", 'termList',
			 "termMatch", "$uibModal", "$rootScope", "termHistory", "termVersion", "$q","ModalService", "termElasticSearch", "getTermFromIlx",
	        function($scope, $http, $log, emc, curieCatalog, termOntologies, term, termList,
	        		termMatch, $uibModal, $rootScope, termHistory, termVersion, $q, ModalService, termElasticSearch, getTermFromIlx){

		var that = this;
		this.cid = parseInt($("#cid").val(), 10);
		var tid = $('#tid').val();
		var community = $('#community').val();

	    // popover
	    $('[data-toggle="popover"]').popover({
	        html: true,
	        trigger: 'hover',
	        placement: 'auto'
	    });

	    // dynamically generated popovers
	    $('body').popover({
	        selector: '[data-toggle=popover]',
	        html: true,
	        trigger: 'hover',
	        placement: 'auto',
	    });

		$scope.curies = [];
		curieCatalog.get().then(function(r){
			$scope.curies = r;
		});

		$scope.availableOntologies = [];
		termOntologies.get().then(function(r){
			$scope.availableOntologies = r;
			//$log.log($scope.availableOntologies)
		});

		// $scope.availableSuperclasses = [];
		// termList.get([parseInt(tid, 10)]).then(function(r){
		// 	//$log.log(r);
		// 	for (var i= 0; i<r.length; i++){
		// 		$scope.availableSuperclasses.push({"label":r[i].label,"ilx":r[i].ilx,"superclass_tid":r[i].id});
		// 	}
		// });

		$scope.refresh = function(){

			$scope.error = false;
			$scope.feedback = '';
			$scope.submitted = false;
			$scope.missing_id = false;
			$scope.formData = {};
	        $scope.term_types = ['term', 'TermSet', 'relationship','annotation','cde', 'pde'];
			$scope.annotation_types = ['text','range'];
	        $scope.versions = [];
	        $scope.selectedVersion = 0;
	        $scope.versionInfo = [];

			term.getById(tid).then(function(r){
				//$log.log(r.data);
				var data = r.data;
				if (data.id === null || data.id === undefined){
	                $scope.missing_id = true;
	                $scope.formData = data;
	                return false;
				}

				if (data.version > 1) {
					termHistory.fetch(tid).then(function(r2){
						//$log.log(r2.data);
						$scope.versions = r2.data;
						$scope.selectedVersion = r2.data[0];
						$scope.versionInfo = JSON.parse($scope.selectedVersion.term_info);
					});
				}

				$scope.formData = {};
				$scope.formData.existing_ids = [];
				$scope.formData.synonyms = [];
				$scope.formData.ontologies = [];
				//$scope.formData.superclasses = [];
				$scope.formData.superclass = undefined;

				$scope.formData.label = data.label;
				$scope.formData.ilx = data.ilx;
				$scope.formData.definition = data.definition;
				$scope.formData.comment = data.comment;
				$scope.formData.type = data.type;
				if (data.type == 'annotation'){
					$scope.formData.annotation_type = data.annotation_type;
				}
				//$log.log($scope.formData.annotation_type);
				$scope.formData.status = data.status;
				$scope.formData.display_superclass = data.display_superclass;

				//$log.log(data.existing_ids)

				for(var i=0; i<data.synonyms.length;i++){
					$scope.formData.synonyms.push({"literal": data.synonyms[i].literal, "type": data.synonyms[i].type});
				}

				for(var i=0; i<data.existing_ids.length;i++){
					$scope.formData.existing_ids.push({"iri": data.existing_ids[i].iri, "curie": data.existing_ids[i].curie, "curie_catalog_id": data.existing_ids[i].id, "preferred": data.existing_ids[i].preferred});
				}

				for (var i=0; i<data.superclasses.length ;i++) {
					$scope.formData.superclass = {"superclass_tid":data.superclasses[0].id, "ilx": data.superclasses[0].ilx, "label": data.superclasses[0].label};
					//$scope.formData.superclasses.push({"superclass_tid":data.superclasses[i].id, "ilx": data.superclasses[i].ilx, "label": data.superclasses[i].label});
				}

				for(var i=0; i<data.ontologies.length;i++){
					$scope.formData.ontologies.push({"id":data.ontologies[i].id, "url": data.ontologies[i].url});
				}

				// DEPRECATED
				// $scope.availableSuperclasses = [];
				// termList.get([parseInt(tid, 10)]).then(function(r){
				// 	//$log.log(r);
				// 	for (var i= 0; i<r.length; i++){
				// 		$scope.availableSuperclasses.push({"label":r[i].label,"ilx":r[i].ilx,"superclass_tid":r[i].id});
				// 	}
				// });
			});
		}
		$scope.refresh();

		$scope.addOntology = function(val){
			//$log.log(val);
			return term.addOntology($rootScope, $scope, $q, emc, val);
		}

		$scope.removeOntology = function(val){
			//$log.log(val);
			return term.removeOntology($scope.val);
		}

		$scope.addEid = function(val){
            return term.addEid($rootScope, $scope, $q, emc, val);
		}

		$scope.removeEid = function(val) {
			return term.removeEid($scope, val);
		}

		$scope.addSynonym = function(val){
			return term.addSynonym($rootScope, $scope, $q, emc, val);
		}

		$scope.removeSynonym = function(val) {
			return term.removeSynonym($scope, val);
		}

		$scope.changeVersion = function(){
			//$log.log($scope.selectedVersion);
			$scope.versionInfo = JSON.parse($scope.selectedVersion.term_info);
		}

		$scope.openOntologyAddModal = function () {
			return term.openOntologyAddModal($rootScope, $uibModal, termOntologies, $scope.availableOntologies, emc);
		}

		$scope.openCurieCatalogAddModal = function () {
		    return term.openCurieCatalogAddModal($rootScope, $uibModal, curieCatalog, $scope.curies, emc);
		};

		$scope.addSuperclass = function (val){
			// $log.log(val);
		}

		$scope.updateSuperclassList = function (val){
			return termElasticSearch.fetch(val, 25, 0, -1, 'all').then(function(r){
				if (r.data.total === 0 || r.data.total === '' || r.data.total === undefined) {
					return [];
				}
				$scope.availableSuperclasses = [];
				for (var i= 0; i<r.data.hits.length; i++){
					var ilx = r.data.hits[i]._source.ilx;
					var label = r.data.hits[i]._source.label;
					$scope.availableSuperclasses.push({
						"label": label,
						"ilx": ilx,
						"superclass_tid": ""
					});
				}
				return $scope.availableSuperclasses;
			});
		}

		$scope.stripIlx = function(ilx) {
			//console.log(ilx);
			return term.stripIlx(ilx);
		}

		$scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

		$scope.editTerm = function() {
			//$scope.formData.$submitted = true;
			$scope.error = false;
			$scope.feedback = '';
			$scope.submitted = true;

			var ok = $scope.validate();
			//$log.log('status:'+ok.status);
            if (!ok.status){
                emc.call(ok.msg);
                $scope.error = true;
                $scope.feedback = ok.msg;
                return false;
            }

		    var params = {};
		    params["cid"] = that.cid;
		    params['label'] = $scope.formData.label;
		    params['ilx'] = $scope.formData.ilx;
		    params['status'] = $scope.formData.status;
		    params['display_superclass'] = $scope.formData.display_superclass;
		    params['definition'] = $scope.formData.definition;
		    params['comment'] = $scope.formData.comment;
		    params['type'] = $scope.formData.type;
		    if ($scope.formData.type == 'annotation'){
		    	params['annotation_type'] = $scope.formData.annotation_type;
		    }
		    params["existing_ids"] = $scope.formData.existing_ids;
            params["synonyms"] = $scope.formData.synonyms;
			var superclass_template = {
				'superclass_tid':'',
				'ilx':'',
				'label':''
			};
			params["superclasses"] = $scope.formData.superclass != undefined ? $scope.formData.superclass : superclass_template;
			console.log(params['superclasses']);
			params["ontologies"] = $scope.formData.ontologies;
			var superclass_ilx = params['superclasses']['ilx'];

			getTermFromIlx.fetch(superclass_ilx).then(function(resp) {
				params['superclasses']['superclass_tid'] = getTermFromIlx.data.id;
				params['superclasses'] = [params['superclasses']];
				console.log(params['superclasses']);
				$http.post("/api/1/term/edit/"+tid, JSON.stringify(params)).then(function(r){
	            	d = r.data.data;
	            	//$log.log(d);
					console.log(d);
	            	//var url = '/scicrunch/about/term/ilx/' + d.ilx + "?action=edited&what=Term";
	            	var url = "/" + community + "/interlex/view/" + d.ilx + "?action=edited&what=Term";
	            	window.location = url;

	            }, function(r){
	            	emc.call("Error:\n " + r.data['errormsg']);
	            });
			});
		}


	    $scope.validate = function() {
	    	var ok = true;
	    	var msg = "Error:\n";
			if ($scope.formData.label === '' || $scope.formData.label === undefined) {
				msg += "-- Label is required!\n";
				ok = false;
			}

			angular.forEach($scope.formData.existing_ids, function(value, key) {
//				$log.log(JSON.stringify(value))
				if (value.curie_catalog_id < 1) {
					msg += "-- " + value.curie + " (" + value.iri + ") is not in Curie Catalog. Please add it first using the link 'Add new curie catalog entry'\n";
					ok = false;
				}
			});

			if ( !($scope.formData.superclass == undefined || $scope.formData.superclass == '') && !$scope.formData.superclass.hasOwnProperty('superclass_tid')) {
				msg += "-- " + $scope.formData.superclass + " is not a valid superclass. Please select a superclass only from the list provided\n";
				ok = false;
			}
//			var sups = [];
//			angular.forEach($scope.formData.superclasses, function(value, key) {
//				//$log.log(JSON.stringify(value));
//				if (jQuery.inArray( value.ilx, sups )) {
//					//$log.log(value.ilx + ' already exist');
//					$scope.formData.superclasses.splice(key, 1);
////					msg += "-- Superclass " + value.label + " is entered more than one time. Please remove one.\n";
////					ok = false;
//				}
//				sups.push(value.ilx);
//			});

			return {"status":ok, "msg":msg};
	    }

	}]);

	app.filter('replace', [function () {

	    return function (input, from, to) {

		      if(input === undefined) {
		        return;
		      }

		      var regex = new RegExp(from, 'g');
		      return input.replace(regex, to);

	    };

		}]);

}());
