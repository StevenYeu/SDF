(function(){
	var app = angular.module("termViewApp", ["errorApp", "ui.bootstrap", "treeControl", "term", "utilitiesApp"])
        .config(function($locationProvider) {
            $locationProvider.html5Mode({"enabled": true, "requireBase": false});
        });

	app.controller('termViewCtrl', ["$q", "$timeout", "$http", "$scope", "$log", "term", "termParents", "termChildren", "$rootScope", "termHistory", "termVote", "termCommunity", "$uibModal", "errorModalCaller", "termMapping",
	                                function($q, $timeout, $http, $scope, $log, term, termParents, termChildren, $rootScope, termHistory, termVote, termCommunity, $uibModal, emc, termMapping){

		var ilx = $('#ilx').val();
		var action = $('#action').val();
		var what = $('#what').val();
		var uid_suggested = $('#uid_suggested').val();
		var uid_curated = $('#uid_curated').val();
		var cid = $('#cid').val();
		var cname = $('#cname').val();
		var community = $('#community').val();

		$scope.searchTerm = $('#searchTerm').val();
		$scope.error = false;
		$scope.message = "";
		$scope.childCount = "";
		$scope.term = {};
		$scope.term.allParents = [];
		$scope.term.allRelationships = [];
		$scope.term.allAnnotations = [];
		$scope.term.community = {};
		$scope.versions = [];
		$scope.showComments = true;
		$scope.statusOptions = ['all','submitted','matched','pending','rejected','approved'];
		$scope.curation_status = "all";
		//$log.log('searchTerm: ' + $scope.searchTerm);

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


		$rootScope.voteEntry = {"subject": "", "object": "", "predicate": "", "vote": "", "table": "", "table_id": ""};
		$rootScope.communityEntry = {"id":0, "uid_curated":0,"status":'',"notes":'',"date_suggested":''};

		$scope.getMappings = function (tid, size, from, curation_status) {
			$scope.term.mappings.from = Number(from);
			$scope.term.mappings.size = Number(size);
			$scope.term.mappings.curation_status = curation_status;
			$scope.term.mappings = [];
			$scope.term.mappings.showPrev = false;
			$scope.term.mappings.showNext = false;

			$("body").css("cursor", "progress");
			termMapping.fetch(tid, size, from, curation_status).then(function(r3){
				$log.log(r3);
				$("body").css("cursor", "default");
				$scope.term.mappings = r3.data.mappings;
				$scope.term.mappings.count = count = Number(r3.data.count);
				$scope.term.mappings.from = Number(r3.data.from.replace(/^0+/, ''));
				$scope.term.mappings.size = Number(r3.data.size.replace(/^0+/, ''));

				var prev = Number(from) - Number(size);
				var next = Number(from) + Number(size);
				$scope.term.mappings.prevFrom = prev;
				$scope.term.mappings.nextFrom = next;
//				$log.log(prev);
//				$log.log(next);

				if (prev >= 0) {
					$scope.term.mappings.showPrev = true;
				}
				if (next < count) {
					$scope.term.mappings.showNext = true;
				}
				//$log.log($scope.term.mappings.prevFrom);
				//$log.log($scope.term.mappings.nextFrom);
			});
		}

		$("body").css("cursor", "progress");
		term.getByIlx(ilx).then(function(r){
			$("body").css("cursor", "default");
			//$log.log(r.data);
			data = r.data;
			$scope.term_id = data.id;
			$scope.term_label = data.label;
			if (data.ilx === null || data.ilx === undefined){
                $scope.error = true;
                $scope.errorMsg = "InterLex ID does not exist!";
				return false;
			}
			if (data.status < 0){
                $scope.error = true;
                $scope.errorMsg = data.curie + " (label: " + data.label + ") has been deleted.";
                return false;
			}

			if (data.version > 1) {
				termHistory.fetch(data.id).then(function(r2){
					//$log.log(r2.data);
					$scope.versions = r2.data;
					$scope.selectedVersion = r2.data[0];
					$scope.orig_user = r2.data[0]['orig_user'];
					$scope.last_modify_user = r2.data[0]['modify_user'];
					$scope.versionInfo = JSON.parse($scope.selectedVersion.term_info);
				});
			}

			$scope.error = false;
			$scope.message = "";
			$scope.term = data;
			if (action.length && what.length) {
				$scope.message = what + ' was ' + action + ' successfully.';
			}

			$scope.term.ontologies = data.ontologies;

			termParents.fetch(data.id).then(function(r2){
				$scope.term.allParents = r2.data.parent0;
				$scope.toggleParentList('short');
			});

			term.getAnnotationsByTid(data.id).then(function(r2){
				$scope.term.allAnnotations = r2;
				$scope.toggleAnnotations('short');
			});

			term.getRelationshipsByTid(data.id).then(function(r2){
				$scope.term.allRelationships = r2;
				$scope.filtered_relationships = r2;
				$scope.toggleRelationships('short');
			});

			$scope.term.existing_ids = data.existing_ids;
			$scope.term.preferredId = $scope.term.curie;
			for (i=0; i<$scope.term.existing_ids.length; i++){
				//$log.log($scope.term.existing_ids);
				if ($scope.term.existing_ids[i].preferred == 1){
					$scope.term.preferredId = $scope.term.existing_ids[i].curie;
					break;
				}
			}

			$scope.getMappings($scope.term.id, 5, 0, "all");

			$scope.chooseCurationStatus = function(curation_status) {
					$scope.curation_status = curation_status;
					$scope.getMappings($scope.term.id, 5, 0, curation_status);
			}

			termCommunity.fetch(data.id, cid).then(function(r){
				$scope.term.community = r.data;
				//$log.log($scope.term.community);
			});
		});

		$scope.toggleParentList = function(display){
			//$log.log($scope.term.allParents);
			$scope.term.parentListDisplay = display;
			$scope.term.parents = [];
			if (display == 'long') {
				$scope.term.parents = $scope.term.allParents;
			}
			if (display == 'short'){
				if ( undefined != $scope.term.allParents && $scope.term.allParents.length > 0) {
					for (var i=0; i<$scope.term.allParents.length; i++) {
						if ($scope.term.allParents[i].parent_display == 0) { break; }

						$scope.term.parents.push($scope.term.allParents[i]);
					}
				}
			}
			//$log.log($scope.term.parents);
		}

		$scope.toggleRelationships = function(display){
			//$log.log($scope.term.allRelationships);
			$scope.term.relationshipDisplay = display;
			$scope.term.relationships = [];
			if (display == 'long') {
				$scope.term.relationships = $scope.term.allRelationships;
			}
			if (display == 'short'){
				if ( undefined != $scope.term.allRelationships && $scope.term.allRelationships.length > 0) {
					for (var i=0; i<$scope.term.allRelationships.length; i++) {
						// console.log($scope.term.allRelationships[i].withdrawn);
						if ($scope.term.allRelationships[i].withdrawn != '1') {
							// console.log('HERE!');
							$scope.term.relationships.push($scope.term.allRelationships[i]);
						}
					}
				}
			}
			//$log.log($scope.term.relationships);
		}

		$scope.toggleAnnotations = function(display){
			$scope.term.annotationDisplay = display;
			$scope.term.annotations = [];
			if (display == 'long') {
				$scope.term.annotations = $scope.term.allAnnotations;
			}
			if (display == 'short'){
				//$log.log($scope.term.allAnnotations);
				if ( undefined != $scope.term.allAnnotations && $scope.term.allAnnotations.length > 0) {
					for (var i=0; i<$scope.term.allAnnotations.length; i++) {
						// console.log($scope.term.allAnnotations[i].withdrawn);
						if ($scope.term.allAnnotations[i].withdrawn != '1') {
							// console.log('HERE!');
							$scope.term.annotations.push($scope.term.allAnnotations[i]);
						}
					}
				}
			}
			//$log.log($scope.term.annotations);
		}

		  $scope.loadingTime = 0;
		  $scope.treeModel = [];
		  $scope.treeOptions = {
		    dirSelectable: false,    // Click a folder name to expand (not select)
		    isLeaf: function isLeafFn(node) {
		      //return !node.hasOwnProperty('has_children');
		      return !node.has_children;
		    }
		  };

		  $scope.fetchChildNodes = function fetchChildNodes(node, expanded) {
		    function doFetch(node) {
		      //if (node.hasOwnProperty('has_children')) {
		      if (node.has_children == true) {
		        //console.log('GET ' + node.ilx);
		        $http.get("/api/1/term/children/" + node.ilx)
		          .success(function(data) {
		            //console.log('GET ' + node.ilx + ' ... ok! ' + angular.toJson(data));
		            node.children = data.data;
		          });
		      } else {
		        // Leaf node
		      }
		    }

		    if (node._sent_request) {
		      return;
		    }
		    node._sent_request = true;
		    // Add a dummy node.
		    node.children = [{label: 'Loading ...'}];
		    $timeout(function() { doFetch(node) }, $scope.loadingTime);
		  };

			$scope.loadingTime = 0;
		  $scope.collectionTreeModel = [];
		  $scope.collectionTreeOptions = {
		    dirSelectable: false,    // Click a folder name to expand (not select)
		    isLeaf: function isLeafFn(node) {
		      return !node.has_children;
		    }
		  };

			$scope.fetchCollectionNodes = function fetchCollectionNodes(node, expanded) {
		    function doFetch(node) {
		      if (node.type == "TermSet") {
		        $http.get("/api/1/term/collection/" + node.ilx)
		          .success(function(data) {
		            node.children = data.data;
		          });
		      }
		    }

		    if (node._sent_request) {
		      return;
		    }
		    node._sent_request = true;
		    // Add a dummy node.
		    node.children = [{label: 'Loading ...'}];
		    $timeout(function() { doFetch(node) }, $scope.loadingTime);
		  };

		  //$scope.getFirstGeneration = function(){
			  $("body").css("cursor", "progress");
			  $http.get("/api/1/term/children/" + ilx)
			    .success(function(data) {
			    	//$log.log(data.data)
			      $scope.treeModel = data.data;
			      $scope.childCount = data.data.length;
			      $("body").css("cursor", "default");
			    });
			  $("body").css("cursor", "default");
		  //}

				$("body").css("cursor", "progress");
				$http.get("/api/1/term/collection/" + ilx)
					.success(function(data) {
						$scope.collectionTreeModel = data.data;
						$("body").css("cursor", "default");
					});
				$("body").css("cursor", "default");

		$scope.stripIlx = function(ilx) {
			//console.log(ilx);
			return term.stripIlx(ilx);
		}

		$scope.curiefyIlx = function(ilx) {
			return term.curiefyIlx(ilx);
		};

		$scope.stripNewline = function(text) {
			//console.log(text);
			return term.stripNewline(text);
		}

		$scope.suggestTerm = function(){
			termCommunity.add($scope.term.id, cid, uid_suggested).then(function(r){
				//$log.log($scope.term.community);
				$scope.term.community = r.data;
			});
		}

		$scope.changeVersion = function(){
			//$log.log($scope.selectedVersion);
			$scope.versionInfo = JSON.parse($scope.selectedVersion.term_info);
		}

		$scope.changeCommentsStatus = function(){
				console.dir($scope.showComments);
				$scope.showComments = !$scope.showComments;
				console.dir($scope.showComments);
		}

		$scope.getFilteredRelationships = function(data, input, col_num){
			//$log.log($scope.selectedVersion);
			var filtered_data = [];
			var relationship_items = ["term1_label", "relationship_term_label", "term2_label"];
			if (input != "") {
					for (var i = 0; i < data.length; i++) {
							var lowercase_data = "";
							if ((relationship_items[col_num] == "term1_label" && data[i]["term1_id"] == $scope.term_id) ||
									(relationship_items[col_num] == "relationship_term_label" && data[i]["relationship_tid"] == $scope.term_id) ||
									(relationship_items[col_num] == "term2_label" && data[i]["term2_id"] == $scope.term_id)) {
									lowercase_data = $scope.term_label.toLowerCase();
							} else {
									lowercase_data = data[i][relationship_items[col_num]].toLowerCase();
							}
							var lowercase_input = input.toLowerCase();
							if (lowercase_data.includes(lowercase_input)) filtered_data.push(data[i]);
					}
			} else {
					filtered_data = data;
			}
			$scope.filtered_relationships = filtered_data;
		}

		$scope.sortFilteredRelationships = function(data, status, col_num){
				var relationship_items = ["term1_label", "relationship_term_label", "term2_label"];
				$scope.filtered_relationships = data.sort(dynamicSort(relationship_items[col_num], status));
		}

		function dynamicSort(property, status) {
		    var sortOrder = 1;
		    if(status == "down") {
		        sortOrder = -1;
		    }
		    return function (a,b) {
		        /* next line works with strings and numbers,
		         * and you may want to customize it to your needs
		         */
						if(typeof a[property] === 'undefined') a[property] = $scope.term_label;
						if(typeof b[property] === 'undefined') b[property] = $scope.term_label;
						var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
		        return result * sortOrder;
		    }
		}
		$scope.approveCommunity = function(){
			$rootScope.communityEntry = $scope.term.community;
			$rootScope.communityEntry.uid_curated = uid_curated;
			$rootScope.communityEntry.community_name = cname;
			$rootScope.communityEntry.term = $scope.term.label;
			$rootScope.communityEntry.tid = $scope.term.id;
			$rootScope.communityEntry.cid = cid;
			$rootScope.communityEntry.date_suggested = $scope.term.community.date_suggested;
			$rootScope.communityEntry.user_suggested = $scope.term.community.user_suggested;

			return termCommunity.edit($rootScope, $uibModal, $q, $scope.term.community);
		}

		$scope.vote = function(vote, table, obj){
			//$log.log(obj);
			if (table == 'term_relationships') {
				$rootScope.voteEntry.subject = obj.hasOwnProperty("term1_label") ? obj.term1_label : $scope.term.label;
				$rootScope.voteEntry.object = obj.hasOwnProperty("term2_label") ? obj.term2_label : $scope.term.label;
				$rootScope.voteEntry.predicate = obj.hasOwnProperty("relationship_term_label") ? obj.relationship_term_label : $scope.term.label;
			}
			if (table == 'term_annotations') {
				$rootScope.voteEntry.subject = obj.hasOwnProperty("term_label") ? obj.term_label : $scope.term.label;
				$rootScope.voteEntry.predicate = obj.hasOwnProperty("annotation_term_label") ? obj.annotation_term_label : $scope.term.label;
				$rootScope.voteEntry.object = obj.value;
			}
			$rootScope.voteEntry.vote = vote;
			$rootScope.voteEntry.table = table;
			$rootScope.voteEntry.table_id = obj.id;
			return term.openVoteModal($rootScope, $uibModal, termVote, obj, emc);
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
