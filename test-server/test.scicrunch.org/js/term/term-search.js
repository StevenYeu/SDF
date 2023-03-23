(function(){

	var app = angular.module("termSearchApp", ["ui.bootstrap", "term", "utilitiesApp"]);

	app.controller('termSearchCtrl', ["$scope", "$log", "term", 'termSearch', 'termElasticSearch',
	                                function($scope, $log, term, termSearch, termElasticSearch){

        $scope.totalLimit = 10000;
		var q = $('#q').val();
		var startSearch = $('#startSearch').val();
		$scope.searchTerm = q.length > 0 ? q : "";
		var cid = $('#cid').val();
		$scope.communitySelected = false;
		term.getCommunityTerms(cid).then(function(r){
			if (r.length > 0) {
				$scope.changeCommunitySelection();
			}
		});

		$scope.reset = function(){
			$scope.matches = [];
			$scope.total = 0;
			$scope.pageTotal = 0;
			$scope.perPage = 100;
			$scope.numPages = Math.ceil($scope.total / $scope.perPage);
			$scope.currentPage = 1;
			$scope.from = 0;
			$scope.to = 0;
			$scope.displayLabel = '';
			$scope.notFound = 0;
		}
		$scope.reset();

		$scope.changeCommunitySelection = function(){
			$scope.communitySelected = !$scope.communitySelected;
			//$log.log($scope.communitySelected)
			$scope.startElasticSearch();
		}

		$scope.setPreferredId = function($scope){
			for (i=0; i<$scope.matches.length; i++){
				$scope.matches[i].preferredId = $scope.matches[i]._source.curie;
				for(j=0; j<$scope.matches[i]._source.existing_ids.length; j++){
					if ($scope.matches[i]._source.existing_ids[j].preferred == 1){
						$scope.matches[i].preferredId = $scope.matches[i]._source.existing_ids[j].curie;
						break;
					}
				}
			}
		}

		$scope.startElasticSearch = function(){
			$scope.reset();
			document.body.style.cursor = 'wait';
			var ccid = -1;
			if ($scope.communitySelected){
				ccid = cid;
			}

			if ($scope.searchTerm == null || $scope.searchTerm == undefined || $scope.searchTerm == "*") { $scope.searchTerm = ''}

			termElasticSearch.fetch($scope.searchTerm,$scope.perPage,$scope.from, ccid, 'all').then(function(r){
				//$log.log(r);
				if (r.data.total === 0 || r.data.total === '' || r.data.total === undefined) {
					$scope.notFound = 1;
				}
				$scope.total = r.data.total;
				$scope.pageTotal = Math.min($scope.totalLimit, $scope.total);
				var to = parseInt($scope.from) + parseInt($scope.perPage);
				$scope.to = to < $scope.total ? to : $scope.total;
				$scope.numPages = Math.ceil($scope.total / $scope.perPage);
				$scope.matches = r.data.hits;
				document.body.style.cursor = 'default';
				$scope.displayLabel = $scope.searchTerm;
				$scope.setPreferredId($scope);
			});
		}
		if (startSearch == 1) {
			$scope.startElasticSearch();
		}

		$scope.stripIlx = function(ilx) {
			//console.log(ilx);
			return term.stripIlx(ilx);
		}

		$scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

		$scope.splitClass = function(word){
			if (word != null && word != undefined) {
				var fields = word.split('\.');
				return fields[0];
			} else {
				return word;
			}
		}

		$scope.pageChanged = function() {
			//$log.log('Page changed to: ' + $scope.currentPage);
			document.body.style.cursor = 'wait';
			$scope.from = ($scope.currentPage-1)*$scope.perPage;
			termElasticSearch.fetch($scope.searchTerm,$scope.perPage,$scope.from,-1,"all").then(function(r){
				//$log.log(r.data);
				if (r.data.total === 0 || r.data.total === '' || r.data.total === undefined) {
					$scope.notFound = 1;
				}
				$scope.total = r.data.total;
				$scope.pageTotal = Math.min($scope.totalLimit, $scope.total);
				var to = parseInt($scope.from) + parseInt($scope.perPage);
				$scope.to = to < $scope.total ? to : $scope.total;
				$scope.numPages = Math.ceil($scope.total / $scope.perPage);
				$scope.matches = r.data.hits;
				document.body.style.cursor = 'default';
				$scope.displayLabel = $scope.searchTerm;
				$scope.setPreferredId($scope);
			});
		};

	}]);


}());
