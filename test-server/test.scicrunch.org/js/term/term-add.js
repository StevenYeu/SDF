(function(){

	var app = angular.module("termAddApp", ["errorApp", "ui.bootstrap", "term", 'ngSanitize', 'angular.chips','angularModalService', 'utilitiesApp']);

	app.controller('termAddCtrl',
			["$scope", "$http", "$log", "addIlx", "errorModalCaller", '$filter', 'termList', "curieCatalog", "termCommunity",
			 "termOntologies", "term", "$uibModal", "$rootScope", "$q", "$timeout", "ModalService", "termElasticSearch", "getTermFromIlx",
	        function($scope, $http, $log, addIlx, emc, $filter, termList, curieCatalog, termCommunity,
	        		termOntologies, term, $uibModal, $rootScope, $q, $timeout, ModalService, termElasticSearch, getTermFromIlx) {

		this.cid = parseInt($("#cid").val(), 10);
		this.uid = parseInt($("#uid").val(), 10);
		this.community = $("#community").val();
		if ( typeof($("#referer").val()) !== 'undefined' ){
			this.referer = $("#referer").val();
		}
		var that = this;
		// Sanity check for community while this logic is hardcoded
		console.log('Current Community: '+window.location.pathname.split("/")[1]);

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
		});

		// Only used for adding terms
		// todo find a proper home for this!
    	var populateAvailableCommunities = function() {
    		return $http.get("/api/1/user/info?no-datasets=true").then(function(r){
    			console.log(r.data.data['communities']);
				$scope.availableCommunities = r.data.data['communities'];
    			return r.data.data['communities'];
    		},function(r) {
    			r.data = "Error:\n " + r.data['errormsg'];
    			return r;
    		});
    	};

		$scope.resetForm = function(){
			$scope.no_match = false;
			$scope.matches = [];
			$scope.error = false;
			$scope.feedback = '';
			$scope.term_types = ['term', 'TermSet', 'relationship','annotation','cde', 'pde'];
			$scope.annotation_types = ['text','range'];
			$scope.annotation_type = 'text';

			if ( typeof($("#ttype").val()) !== 'undefined' &&  jQuery.inArray( $("#ttype").val(), $scope.term_types ) ){
				$scope.type = $("#ttype").val();
			} else {
				$scope.type = 'term';
			}

			$scope.label = $("#label").val();
			$scope.definition = '';
			$scope.community = window.location.pathname.split("/")[1]; // Used as a way to pull Community id when adding terms
			if ($scope.community !== 'scicrunch') {
				$scope.hide_community = true;
				$scope.comment = $scope.community.toUpperCase() + ': ';
			} else {
				$scope.hide_community = false;
				populateAvailableCommunities();  // adds to $scope.avaliableCommunities for user to select $scope.community_id
				$scope.comment = '';
			}
			$scope.formData = {};
			$scope.formData.existing_ids = [];
			$scope.formData.synonyms = [];
			$scope.formData.ontologies = [];
			$scope.formData.superclass = undefined;
			$scope.termForm = undefined;
			$scope.lastOntology = undefined;
		};
		$scope.resetForm();

		$scope.addOntology = function(val){
			return term.addOntology($rootScope, $scope, $q, emc, val);
		};

		$scope.removeOntology = function(val){
			return term.removeOntology($scope.val);
		};

		$scope.addEid = function(val){
            return term.addEid($rootScope, $scope, $q, emc, val);
		};

		$scope.removeEid = function(val) {
			return term.removeEid($scope, val);
		};

		$scope.matchTerm = function(){
			$scope.no_match = false;
			termElasticSearch.fetch($scope.label, 100, 0, -1, 'all').then(function(r){
				if (r.data.total == 0){
					$scope.no_match = true;
				}
				$scope.matches = r.data.hits;
			});
		};

		$scope.openOntologyAddModal = function () {
			return term.openOntologyAddModal($rootScope, $uibModal, termOntologies, $scope.availableOntologies, emc);
		};

		$scope.openCurieCatalogAddModal = function () {
		    return term.openCurieCatalogAddModal($rootScope, $uibModal, curieCatalog, $scope.curies, emc);
		};

		$scope.addSynonym = function(val){
			return term.addSynonym($rootScope, $scope, $q, emc, val);
		};

		$scope.removeSynonym = function(val) {
			return term.removeSynonym($scope, val);
		};

		$scope.addSuperclass = function (val){
			//$log.log(val);
		};

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
		};

		$scope.addTerm = function() {

			$scope.error = false;
			$scope.feedback = '';
			console.log($scope.community_id);

			var ok = $scope.validate();
			if (!ok.status){
				emc.call(ok.msg);
				$scope.error = true;
				return false;
			}

			var params = {};
		    params["cid"] = (typeof $scope.community_id === 'undefined') ? that.cid : $scope.community_id;
		    params["uid"] = that.uid;
		    params["label"] = $scope.label;
		    params["type"] = $scope.type;
		    if ($scope.type == 'annotation'){
		    	params['annotation_type'] = $scope.annotation_type;
		    }
		    params["definition"] = $scope.definition;
		    params["comment"] = $scope.comment;

		    params["existing_ids"] = $scope.formData.existing_ids;
			var superclass_template = {
				'superclass_tid':'',
				'ilx':'',
				'label':''
			};
			params["superclasses"] = $scope.formData.superclass != undefined ? $scope.formData.superclass : superclass_template;
		    params["synonyms"] = $scope.formData.synonyms;
		    params["ontologies"] = $scope.formData.ontologies;

		    var superclass_ilx = "";
			if (params['superclasses'].hasOwnProperty('ilx'))
				superclass_ilx = params['superclasses']['ilx'];

    		// fetch superclass to populate
			getTermFromIlx.fetch(superclass_ilx).then(function(resp) {
				params['superclasses']['superclass_tid'] = getTermFromIlx.data.id;
				params['superclasses'] = [params['superclasses']];
				// start term add
				$http.post("/api/1/term/add", params)
				.then(function(r){
					d = r.data.data;
					// Although correct, for the interface this is still an error!
					if (r.status === 200){
						emc.call("Error: Term already exists and was created by you.");
						return;
					}
					if (typeof(that.referer) !== 'undefined') {
						window.location = that.referer.replace(/&ilx=.&/, "&").replace(/&ilx=.+$/, "") + "&ilx=" + d.ilx;
						return;
					}
					var url = "/" + that.community + "/interlex/view/" + d.ilx + "?action=added&what=Term";
					window.location = url;
				}, function(r){
					emc.call("Error:\n " + r.data['errormsg']);
					// $scope.resetForm();
				});
			});

		};

	    $scope.validate = function() {
	    	var ok = true;
	    	var msg = "Error:\n";
			if ($scope.label === '' || $scope.label === undefined) {
				msg += "-- Label is required!\n";
				ok = false;
			}
			if (!$scope.no_match) {
				msg += "-- You need to check the box to confirm there is no match for the term you are trying to add.\n";
				ok = false;
			}
			// Communities such as SPARC need the comment field to help downstream curation.
			if (($scope.community !== 'scicrunch') && !$scope.comment) {
				msg += "-- Comments are requried for this community.\n";
				ok = false;
			}
			angular.forEach($scope.formData.existing_ids, function(value, key) {
				console.log(value);
				if (value.curie_catalog_id < 1) {
					msg += "-- " + value.curie + " (" + value.iri + ") is not in Curie Catalog. Please add it first using the link 'Add new curie catalog entry'\n";
					ok = false;
				}
			});
			if (!($scope.formData.superclass == undefined || $scope.formData.superclass == '') && !$scope.formData.superclass.hasOwnProperty('ilx')) {
				msg += "-- " + $scope.formData.superclass + " is not a valid superclass. Please select a superclass only from the list provided\n";
				ok = false;
			}

			return {"status":ok, "msg":msg};
	    };

	}]);

}());
