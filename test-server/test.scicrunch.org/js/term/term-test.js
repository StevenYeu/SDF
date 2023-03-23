(function(){

	var app = angular.module("termTestApp", ["ui.bootstrap", 'angular.chips', 'ngSanitize', 'ngTagsInput', "errorApp", "term"]);

	app.controller('termTestCtrl',
			["$scope", "$log", "tags", "errorModalCaller", '$filter', "$uibModal", "$rootScope", "$q", "term", "curieCatalog", "termList", "$window",
	        function($scope, $log, tags, emc, $filter, $uibModal, $rootScope, $q, term, curieCatalog, termList, $window) {

		var self = this;

		  $scope.tags = [
		                 { text: 'Tag1' },
		                 { text: 'Tag2' },
		                 { text: 'Tag3' }
		               ];

		$scope.loadTags = function(query) {
			    return tags.load();
		};

		$scope.curies = [];
		curieCatalog.get().then(function(r){
			$scope.curies = r;
		});

		// DEPRECATED
		// termList.get([]).then(function(r){
		// 	//$log.log(r);
		// 	for (var i= 0; i<r.length; i++){
		// 		$scope.availableSuperclasses.push({"label":r[i].label,"ilx":r[i].ilx,"id":r[i].id});
		// 	}
		// });

		$scope.synonyms = [];
		$scope.existing_ids = [];
		$scope.superclasses = [];
		$scope.superclass = undefined;

		$scope.company = '';
		$scope.companies = ['Apple', 'Cisco', 'Verizon', 'Microsoft'];
		$scope.availableCompanies = ['ACCO Brands',
		                             'Accuquote',
		                             'Accuride Corporation',
		                             'Ace Hardware',
		                             'Google',
		                             'FaceBook',
		                             'Paypal',
		                             'Pramati',
		                             'Bennigan',
		                             'Berkshire Hathaway',
		                             'Berry Plastics',
		                             'Best Buy',
		                             'Carlisle Companies',
		                             'Carlson Companies',
		                             'Carlyle Group',
		                             'Denbury Resources',
		                             'Denny',
		                             'Dentsply',
		                             'Ebonite International',
		                             'EBSCO Industries',
		                             'EchoStar',
		                             'Gateway, Inc.',
		                             'Gatorade',
		                             'Home Shopping Network',
		                             'Honeywell',
		                         ];

		$scope.runTest = function(){
			$log.log('runTest on submit press')
		}

		$scope.addSuperclass = function(val){
			$log.log('adding superclass')
			$log.log(val)
		}

		$scope.removeSuperclass = function(val){
			$log.log(val);
		}

		$scope.addEid = function(val){
            var index = 0;
            var isBad = false;

			$rootScope.lastEid = {"iri": "", "curie": "", "curie_catalog_id": -1};

			var deferred = $q.defer();

			for (index; index < $scope.existing_ids.length; index++) {
				isBad = $scope.existing_ids[index].curie === val || $scope.existing_ids[index].iri === val;
				if (isBad) {
					emc.call("Duplicate existing id: " + val);
					break;
				}
			}
			if ( val.match(/http\:\/\//) ) {
				$rootScope.lastEid.iri = val;
				$rootScope.lastEid.curie_catalog_id = -1;

				var c = term.curieByNamespace($scope.curies, val);
				var existing_id = c.existing_id;
				if (c.found) {
					$rootScope.lastEid.curie_catalog_id = existing_id.id;
					$rootScope.lastEid.curie = existing_id.curie;
				} else {
					$rootScope.lastEid.iri = val;
					isBad = true;
					emc.call(val + 'is not in curie catalog. Please add it first using the link "Add new curie catalog entry"');
				}
			} else if ( val.match(/\:/) ) {
				var copy = val;
				var arr = copy.split(":");
				var c = term.curieByPrefix($scope.curies, arr[0]);
				if (c.found === true) {
					var existing_id = c.existing_id;
					$rootScope.lastEid.curie = val;
					$rootScope.lastEid.curie_catalog_id = existing_id.id;
					$rootScope.lastEid.iri = existing_id.namespace + arr[1];
				} else {
					isBad = true;
					emc.call(arr[0] + " is not a valid prefix. Please remove and add it first using the link 'Add new curie catalog entry'.\n");
				}
			} else {
				isBad = true;
				emc.call(val + " is not a valid curie or iri. Please remove and add it first using the link 'Add new curie catalog entry'.\n");
			}

			isBad ? deferred.reject($rootScope.lastEid) : deferred.resolve($rootScope.lastEid);

			return deferred.promise;
		}

		$scope.removeEid = function(val) {
			for (index = 0; index < $scope.existing_ids.length; index++) {
				if ($scope.existing_ids[index].curie === val || $scope.existing_ids[index].iri === val) {
					$scope.existing_ids.splice(index, 1);
				}
			}
			return true;
		}

		$scope.addSynonym = function(val){
            var index = 0;
            var isDuplicate = false;

    		$rootScope.lastSynonym = {"literal": val, "type": ""};
    		$rootScope.data = {};

			var deferred = $q.defer();

			for (index; index < $scope.synonyms.length; index++) {
				isDuplicate = $scope.synonyms[index].literal === val;
				if (isDuplicate) {
					emc.call("Duplicate synonym: " + val);
					break;
				}
			}

			setTimeout(function() {
				isDuplicate ? deferred.reject($rootScope.lastSynonym) : deferred.resolve($rootScope.lastSynonym);
			}, openSynonymModal($rootScope, $uibModal));

			return deferred.promise;
		}

		$scope.removeSynonym = function(val) {
			for (index = 0; index < $scope.synonyms.length; index++) {
				if ($scope.synonyms[index].literal === val) {
					$scope.synonyms.splice(index, 1);
				}
			}
			return true;
		}

        function openSynonymModal ($rootScope, $uibModal) {
			var modalInstance = $uibModal.open({
		      templateUrl: 'synonym-type-modal',
		      controller: 'synonymModalCtrl',
		      resolve: {
		        lastSynonym: function () {
		          return $rootScope.lastSynonym;
		        }
		      }
		    });

		    modalInstance.result.then(function () {
		    	//$log.log($rootScope.lastSynonym.literal);
		    	//$log.log($rootScope.data.isAbbrev);
		    	if ($rootScope.data.isAbbrev == 'yes') {
		    		$rootScope.lastSynonym.type = "abbrev";
		    	}
		    }, function () {
		      $log.info('Modal dismissed at: ' + new Date());
		    });
		}

        $scope.openTermForm = function(){
        	var host = window.location.hostname;
        	var port = window.location.port;

        	var base = host + ":" + port;
        	if (port == 80) { base = host; }
        	var url = 'http://' + base + '/create/term?referer=1&ttype=cde';
        	$log.log(url);

        	$window.open(url, 'Create Term');

        }

	}]);

	app.service('tags', function($q) {
		  var tags = [
		    { "text": "Tag1" },
		    { "text": "Tag2" },
		    { "text": "Tag3" },
		    { "text": "Tag4" },
		    { "text": "Tag5" },
		    { "text": "Tag6" },
		    { "text": "Tag7" },
		    { "text": "Tag8" },
		    { "text": "Tag9" },
		    { "text": "Tag10" }
		  ];

		  this.load = function() {
		    var deferred = $q.defer();
		    deferred.resolve(tags);
		    return deferred.promise;
		  };
		});

}());
