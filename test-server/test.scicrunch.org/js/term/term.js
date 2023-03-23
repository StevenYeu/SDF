(function(){
    var app = angular.module("term", []);

	app.directive('enterDirective', function ($log) {
	    return {
	        link: function (scope, element, attrs) {
	            $(element).keypress(function (e) {
	                if (e.keyCode == 13) {
	                    //$log.log("Enter pressed " + element.val())
	                    return false;
	                }
	            });
	        }
	    }
	});

    app.directive("ddTextCollapse", ["$compile", function(a) {
        return {
            restrict: "A",
            scope: !0,
            link: function(b, c, d) {
                b.collapsed=!1, b.toggle = function() {
                    b.collapsed=!b.collapsed
                }, d.$observe("ddTextCollapseText", function(e) {
                    var f = b.$eval(d.ddTextCollapseMaxLength);
                    if (e.length > f) {
                        var g = String(e).substring(0, f), h = String(e).substring(f, e.length), i = a("<span>" + g + "</span>")(b), j = a('<span ng-if="collapsed">' + h + "</span>")(b), k = a('<span ng-if="!collapsed">... </span>')(b), l = a('<br ng-if="collapsed">')(b), m = a('<span class="collapse-text-toggle text-danger" ng-click="toggle()">{{collapsed ? "[less]" : "[more]"}}</span>')(b);
                        c.empty(), c.append(i), c.append(j), c.append(k), c.append(l), c.append(m)
                    } else
                        c.empty(), c.append(e)
                })
            }
        }
    }]);

//    app.directive('toggle', function(){
//    	  return {
//    	    restrict: 'A',
//    	    link: function(scope, element, attrs){
//    	      if (attrs.toggle=="tooltip"){
//    	        $(element).tooltip();
//    	      }
//    	      if (attrs.toggle=="popover"){
//    	        $(element).popover();
//    	      }
//    	    }
//    	  };
//    });

	app.controller('gotoModalCtrl', function($scope, close) {
		 $scope.close = function(result) {
		    close(result, 500); // close, but give 500ms for bootstrap to animate
		 };
	});

	app.controller('synonymModalCtrl', function ($rootScope, $uibModalInstance, lastSynonym) {
		$rootScope.lastSynonym = lastSynonym;
		$rootScope.ok = function () {
			//console.log('synonymModalCtrl ok');
		    $uibModalInstance.close();
		};

		$rootScope.cancel = function () {
			//console.log('synonymModalCtrl cancel');
		    $uibModalInstance.dismiss('cancel');
		};
	});

	app.controller('eidModalCtrl', function ($rootScope, $uibModalInstance, lastEid) {
		$rootScope.lastEid = lastEid;
		$rootScope.ok = function () {
			//console.log('eidModalCtrl ok');
		    $uibModalInstance.close();
		};

		$rootScope.cancel = function () {
			//console.log('synonymModalCtrl cancel');
		    $uibModalInstance.dismiss('cancel');
		};
	});

	app.controller('addCurieCatalogModalCtrl', function ($rootScope, $uibModalInstance, newCurieCatalogEntry) {
		$rootScope.newCurieCatalogEntry = newCurieCatalogEntry;
		$rootScope.ok = function () {
			//console.log('eidModalCtrl ok');
		    $uibModalInstance.close();
		};

		$rootScope.cancel = function () {
		    $uibModalInstance.dismiss('cancel');
		};
	});

	app.controller('addOntologyModalCtrl', function ($rootScope, $uibModalInstance, newOntologyEntry) {
		$rootScope.newOntologyEntry = newOntologyEntry;
		$rootScope.ok = function () {
			//console.log('eidModalCtrl ok');
		    $uibModalInstance.close();
		};

		$rootScope.cancel = function () {
		    $uibModalInstance.dismiss('cancel');
		};
	});

	app.controller('termVoteModalCtrl', function ($rootScope, $uibModalInstance, voteEntry) {
		$rootScope.voteEntry = voteEntry;
		$rootScope.ok = function () {
			//console.log('termVoteModalCtrl ok');
		    $uibModalInstance.close();
		};

		$rootScope.cancel = function () {
			//console.log('termVoteModalCtrl ok');
		    $uibModalInstance.dismiss('cancel');
		};
	});

	app.controller('termCommunityApproveCtrl', function ($rootScope, $uibModalInstance, communityEntry) {
		$rootScope.communityEntry = communityEntry;
		$rootScope.ok = function () {
			//console.log('termVoteModalCtrl ok');
		    $uibModalInstance.close();
		};

		$rootScope.cancel = function () {
			//console.log('termVoteModalCtrl ok');
		    $uibModalInstance.dismiss('cancel');
		};
	});

    app.factory("term", function($http, $log, $filter, $uibModal, $rootScope){
		var term = {};

		term.stripIlx = function(ilx) {
			//console.log(ilx);
			if (ilx != null && ilx != undefined) {
				return ilx.replace("ilx_", "");
			} else {
				return ilx;
			}
		}

		term.curiefyIlx = function(ilx) {
			if (ilx != null && ilx != undefined) {
				var [prefix, suffix] = ilx.split('_');
				return prefix.toUpperCase() + ':' + suffix;
			} else {
				return ilx;
			}
		};

		term.stripNewline = function(text) {
			//console.log(text);
			if (text != null && text != undefined) {
				return text.replace(/\\n/g, " ");
			} else {
				return text;
			}
		}

		term.curieByNamespace = function(curieCatalog, iri) {
			var existing_id = {};
			var found = false;
			for (var i=0; i<curieCatalog.length; i++){
				var str = iri.trim();
				var substr = curieCatalog[i].namespace.trim();
				if (str.indexOf(substr) >= 0){
					found = true;
					existing_id["curie_catalog_id"] = curieCatalog[i].id;
					var id = str.substring(substr.length);
					existing_id["curie"] = curieCatalog[i].prefix + ":" + id;
					existing_id["iri"] = str;

					break;
				}
			}
			if (!found) {
				existing_id["curie_catalog_id"] = -1;
				existing_id["iri"] = iri;
				existing_id["curie"] = undefined;
			}

			return {"existing_id":existing_id, "found": found};
		}

		// returns curie_catalog table entry
		term.curieByPrefix = function(curieCatalog, prefix) {

			var curie = {};
			var found = false;
			for (var i=0; i<curieCatalog.length; i++){
				//$log.log(curies[i]);
				if (curieCatalog[i].prefix.trim() == prefix.toUpperCase().trim()) {
					found = true;
					curie = curieCatalog[i];
					//$log.log(curie);
					break;
				}
			}

			//$log.log(curie);
			return {"existing_id":curie,"found":found};
		}

		term.containsObject = function(obj, list) {
		    var i;
		    for (i = 0; i < list.length; i++) {
		        if (angular.equals(list[i], obj) && JSON.stringify(list[i]) == JSON.stringify(obj)) {
		            return true;
		        }
		    }

		    return false;
		};

		term.containsSynonym = function(obj, list) {
		    var i;
		    for (i = 0; i < list.length; i++) {
		    	if (list[i].type == obj.type && list[i].literal == obj.literal) {
		            return true;
		        }
		    }

		    return false;
		};

		term.containsEid = function(obj, list) {
		    var i;
		    for (i = 0; i < list.length; i++) {
		        if (list[i].curie == obj.curie && list[i].iri == obj.iri) {
		            return true;
		        }
		    }

		    return false;
		};

		term.containsSuperclass = function(obj, list) {
		    var i;
		    for (i = 0; i < list.length; i++) {
		        if (list[i].superclass_tid == obj.superclass_tid) {
		            return true;
		        }
		    }

		    return false;
		};

        term.addSuperclass = function($rootScope, $scope, $q, emc, val){
            var index = 0;
            var isDuplicate = false;

            $scope.formData.superclass = val;

			var deferred = $q.defer();

			for (index; index < $scope.formData.superclass.length; index++) {
				isDuplicate = $scope.formData.superclass[index].ilx === val.ilx;
				if (isDuplicate) {
					emc.call("Duplicate superclass: " + val.label);
					break;
				}
			}

			setTimeout(function() {
				isDuplicate ? deferred.reject($scope.lastSuperclass) : deferred.resolve($scope.lastSuperclass);
			}, 1000);

			return deferred.promise;
		}

		term.addOntology = function($rootScope, $scope, $q, emc, val){
            var index = 0;
            var isDuplicate = false;

            $scope.lastOntology = val;

			var deferred = $q.defer();

			for (index; index < $scope.formData.ontologies.length; index++) {
				isDuplicate = $scope.formData.ontologies[index].url === val.url;
				if (isDuplicate) {
					emc.call("Duplicate onlogy: " + val.url);
					break;
				}
			}

			setTimeout(function() {
				isDuplicate ? deferred.reject($scope.lastOntology) : deferred.resolve($scope.lastOntology);
			}, 1000);

			return deferred.promise;
		}

		term.removeOntology = function($scope, val) {
			for (index = 0; index < $scope.formData.ontologies.length; index++) {
				if ($scope.formData.ontologies[index].url === val.url) {
					$scope.formData.ontologies.splice(index, 1);
				}
			}
			return true;
		}

		term.addSynonym = function($rootScope, $scope, $q, emc, val){
            var index = 0;
            var isDuplicate = false;

    		$rootScope.lastSynonym = {"literal": val, "type": ""};
    		$rootScope.data = {};

			var deferred = $q.defer();

			for (index; index < $scope.formData.synonyms.length; index++) {
				isDuplicate = $scope.formData.synonyms[index].literal === val;
				if (isDuplicate) {
					emc.call("Duplicate synonym: " + val);
					break;
				}
			}

			setTimeout(function() {
				isDuplicate ? deferred.reject($rootScope.lastSynonym) : deferred.resolve($rootScope.lastSynonym);
			}, term.openSynonymModal($rootScope, $uibModal));

			return deferred.promise;
		}

		term.removeSynonym = function($scope, val) {
			for (index = 0; index < $scope.formData.synonyms.length; index++) {
				if ($scope.formData.synonyms[index].literal === val) {
					$scope.formData.synonyms.splice(index, 1);
				}
			}
			return true;
		}

		term.addEid = function($rootScope, $scope, $q, emc, val){
            var index = 0;
            var isBad = false;

			$rootScope.lastEid = {"iri": "", "curie": "", "curie_catalog_id": -1, "preferred": 0};
			$rootScope.data = {};

			var deferred = $q.defer();

			for (index = 0; index < $scope.formData.existing_ids.length; index++) {
				isBad = $scope.formData.existing_ids[index].curie === val || $scope.formData.existing_ids[index].iri === val;
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

			if ($rootScope.lastEid.curie_catalog_id == -1){
				isBad = true;
				emc.call(val + " is not in curie catalog. Please remove and add it first using the link 'Add new curie catalog entry'.\n");
			}

			//isBad ? deferred.reject($rootScope.lastEid) : deferred.resolve($rootScope.lastEid);

			setTimeout(function() {
				isBad ? deferred.reject($rootScope.lastEid) : deferred.resolve($rootScope.lastEid);
			}, term.openEidModal($rootScope, $uibModal));

			return deferred.promise;
		}

		term.removeEid = function($scope, val) {
			for (index = 0; index < $scope.formData.existing_ids.length; index++) {
				if ($scope.formData.existing_ids[index].curie === val || $scope.formData.existing_ids[index].iri === val) {
					$scope.formData.existing_ids.splice(index, 1);
				}
			}
			return true;
		}

		term.getById = function(id){
			return $http.get("/api/1/term/view/" + id).
			then(function(r){
				term.data = r.data.data;
				return term;
			},function(r) {
				term.data = "Error:\n " + r.data['errormsg'];
				return term;
			});
		}

		term.getAnnotationById = function(id){
			return $http.get("/api/1/term/get-annotation/" + id).
			then(function(r){
				term.data = r.data.data;
				return term;
			},function(r) {
				term.data = "Error:\n " + r.data['errormsg'];
				return term;
			});
		}

		term.getAnnotationsByTid = function(id){
			//$log.log('tid: ' + id);
			return $http.get("/api/1/term/get-annotations/" + id).
			then(function(r){
				//$log.log(r);
				term.annotations = r.data.data;
				return term.annotations;
			},function(r) {
				term.annotations = "Error:\n " + r.data['errormsg'];
				return term.annotations;
			});
		}

		term.getRelationshipById = function(id){
			return $http.get("/api/1/term/get-relationship/" + id).
			then(function(r){
				term.data = r.data.data;
				return term;
			},function(r) {
				term.data = "Error:\n " + r.data['errormsg'];
				return term;
			});
		}

		term.getRelationshipsByTid = function(tid){
			return $http.get("/api/1/term/get-relationships/" + tid).
			then(function(r){
				term.relationships = r.data.data;
				return term.relationships;
			},function(r) {
				term.relationships = "Error:\n " + r.data['errormsg'];
				return term.relationships;
			});
		}

		term.getByIlx = function(ilx){
			return $http.get("/api/1/term/ilx/" + ilx).
			then(function(r){
				term.data = r.data.data;
				return term;
			},function(r) {
				term.data = "Error:\n " + r.data['errormsg'];
				return term;
			});
		}

		term.getWithMappings = function(tmid){
			return $http.get("/api/1/term/with-mappings/" + tmid).
			then(function(r){
				term.data = r.data.data;
				return term.data;
			},function(r) {
				term.data = "Error:\n " + r.data['errormsg'];
				return term.data;
			});
		}

		term.getMappingLogs = function(tmid){
			return $http.get("/api/1/term/mapping/logs/" + tmid).
			then(function(r){
				term.data = r.data.data;
				return term.data;
			},function(r) {
				term.data = "Error:\n " + r.data['errormsg'];
				return term.data;
			});
		}

		term.getByName = function(name){
			return $http.get("/api/1/term/lookup/" + name).
				then(function(r){
					term.data = r.data.data;
					return term;
				},function(r) {
					term.data = "Error:\n " + r.data['errormsg'];
					return term;
				});
		}

		term.getCommunityTerms = function(cid){
			return $http.get("/api/1/term/get-community-terms/" + cid).
				then(function(r){
					term.data = r.data.data;
					return term.data;
				},function(r) {
					term.data = "Error:\n " + r.data['errormsg'];
					return term.data;
				});
		}

	    term.openCurieCatalogAddModal = function ($rootScope, $uibModal, curieCatalog, curies, emc) {

	    	$rootScope.newCurieCatalogEntry = {"prefix": "", "namespace": "", "name": "",
	    				"description": "", "homepage": "", "logo": "", "type": "ontology", "source_uri": "true"};

	    	var modalInstance = $uibModal.open({
		      templateUrl: 'curie-catalog-add-modal',
		      controller: 'addCurieCatalogModalCtrl',
		      resolve: {
		    	  newCurieCatalogEntry: function () {
		          return $rootScope.newCurieCatalogEntry;
		        }
		      }
		    });

			modalInstance.result.then(function () {
				//$log.log($rootScope.newCurieCatalogEntry.prefix)
				if ($rootScope.newCurieCatalogEntry.prefix == "" || $rootScope.newCurieCatalogEntry.namespace == "") {
					emc.call("You need to fill prefix and namespace fields before submitting");
					return false;
				}
				curieCatalog.add($rootScope.newCurieCatalogEntry, curies).then(function(r){
					//$log.log(r.data);
					if (typeof r.data != 'undefined') {
						emc.call("Added successfully: " + JSON.stringify(r.data));
					} else {
						emc.call("Problem adding to curie catalog: " + JSON.stringify(r));
					}
				});
		    }, function () {
		      $log.info('Modal dismissed at: ' + new Date());
		    });
	    }

	    term.openOntologyAddModal = function ($rootScope, $uibModal, termOntologies, ontologies, emc) {
	    	$rootScope.newOntologyEntry = {"url": ""};

	    	var modalInstance = $uibModal.open({
			      templateUrl: 'term-ontology-add-modal',
			      controller: 'addOntologyModalCtrl',
			      resolve: {
			    	  newOntologyEntry: function () {
			          return $rootScope.newOntologyEntry;
			        }
			      }
			});

			modalInstance.result.then(function () {
				//$log.log($rootScope.newOntologyEntry.url)
				if ($rootScope.newOntologyEntry.url == "") {
					emc.call("You need to fill URL field before submitting");
					return false;
				}
				termOntologies.add($rootScope.newOntologyEntry, ontologies).then(function(r){
					//$log.log(r.data);
					if (typeof r.data != 'undefined') {
						emc.call("Added successfully: " + JSON.stringify(r.data));
					} else {
						emc.call("Problem adding ontology: " + JSON.stringify(r));
					}
				});
		    }, function () {
		      $log.info('Modal dismissed at: ' + new Date());
		    });
	    }

	    term.openVoteModal = function ($rootScope, $uibModal, termVote, obj, emc) {
	    	//voteEntry.term1 = propObj;

	    	var modalInstance = $uibModal.open({
		      templateUrl: 'term-vote-modal',
		      controller: 'termVoteModalCtrl',
		      resolve: {
		    	  voteEntry: function () {
		          return $rootScope.voteEntry;
		        }
		      }
		    });

			modalInstance.result.then(function () {
				//$log.log($rootScope.voteEntry.id)
				termVote.add($rootScope.voteEntry.table, $rootScope.voteEntry.table_id, $rootScope.voteEntry.vote).then(function(res){
					//$log.log(res);
					//$log.log(obj);

					if ( res.data.data.errormsg != "" ) {
						emc.call(res.data.data.errormsg);
						return;
					}
					if ( res.data.data.upvote != ""){
						obj.upvote = res.data.data.upvote;
					}
					if ( res.data.data.downvote != ""){
						obj.downvote = res.data.data.downvote;
					}
					if ( res.data.data.action != "" ) {
						emc.call(res.data.data.action);
					}

				});
		    }, function () {
		      $log.info('Modal dismissed at: ' + new Date());
		    });
	    }

	    term.openSynonymModal = function($rootScope, $uibModal) {
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
		};

	    term.openEidModal = function($rootScope, $uibModal) {
			var modalInstance = $uibModal.open({
			      templateUrl: 'eid-type-modal',
			      controller: 'eidModalCtrl',
			      resolve: {
			        lastEid: function () {
			          return $rootScope.lastEid;
			        }
			      }
			    });

			    modalInstance.result.then(function () {
			    	//$log.log($rootScope.lastEid.curie);
			    	//$log.log($rootScope.data.preferred);
			    	if ($rootScope.data.preferred == 'yes') {
			    		$rootScope.lastEid.preferred = 1;
			    	}
			    }, function () {
			      $log.info('Modal dismissed at: ' + new Date());
			    });
		};

		return term;
    });

    app.factory("termMatch", function($http, $log){
		var term = {};
		term.getMatches = function(label){
			term.data = [];
			return $http.get("/api/1/term/match/" + label).
				then(function(r){
					term.data = r.data.data;
					return term;
				},function(r) {
					term.data = "Error:\n " + r.data['errormsg'];
					return term;
				});
		};

		return term;
    });

    app.factory("termList", function($http, $log){
		var termList = new Array();

        // Used for annotations and relationships.
        // Will focus on /term/list endpoint first, but this also
        // has to go.
		termList.getByType = function(type){
			return $http.get("/api/1/term/list/"+type).
				then(function(r){
					//$log.log(r.data.data);
					return r.data.data;
				}, function(e) {
					return [{"Error":e.data['errormsg']}];
				});
			return termList;
		};

        // PRODUCES 500 ERROR | Need to discuss if purging this is best.
		// termList.get = function(exclude){
		// 	//$log.log(exclude);
		// 	return $http.get("/api/1/term/list").
		// 		then(function(r){
		// 			var d = r.data.data;
		// 			//$log.log(d);
		// 			for (var i=0; i<d.length; i++){
		// 				if (exclude.indexOf(d[i].id) === -1) {
		// 					termList.push(d[i]);
		// 					//$log.log(d[i]);
		// 				}
		// 			}
		// 			//$log.log(termList);
		// 			return termList;
		// 		},function(r) {
		// 			return [{"Error": r.data}];
		// 		});
		// }

        termList.getTermsForSuperclass = function(exclude, label){
			return $http.get("/api/1/term/search/" + label).
				then(function(r){
                    var termList = new Array(); // = [] creates memory conficts
					var d = r.data.data;
					for (var i=0; i<d.length; i++){
						if (exclude.indexOf(d[i].id) === -1) {
							termList.push(d[i]);
						}
					}
					return termList;
				},function(r) {
					return [{"Error": r.data}];
				});
		}

        // Need to DEPRECATE
		termList.getAnnotationTermList = function(){
			return $http.get("/api/1/term/annotation/list").
				then(function(r){
					//$log.log(r.data.data);
					return r.data.data;
				}, function(e) {
					return [{"Error":e.data['errormsg']}];
				});
			return termList;
		};

		return termList;
    });

    app.factory("curieCatalog", function($http, $log){
		var cc = {};
		cc.get = function(){
			return $http.get("/api/1/curies/catalog").
				then(function(r){
					//$log.log(r.data.data);
					cc = r.data.data;
					return cc;
				},function(r) {
					cc = {"Error":r.data['errormsg']};
					return cc;
				});
		}

		cc.add = function(ccEntry, curies){
			//$log.log(JSON.stringify(ccEntry));
			 return $http.post("/api/1/curie/catalog/add", JSON.stringify(ccEntry)).
				then(function(r){
					cc.data = r.data.data;
					curies.push(r.data.data);
					//$log.log(curies);
					return cc;
				},function(r) {
					ccEntry.data = "Error:\n " + r.data['errormsg'];
					return cc;
				});
		}

		return cc;
    });

    app.factory("termOntologies", function($http, $log){
		var to = {};
		to.get = function(){
			return $http.get("/api/1/term/ontologies").
				then(function(r){
					//$log.log(r.data.data);
					to = r.data.data;
					return to;
				},function(r) {
					to = {"Error":r.data['errormsg']};
					return to;
				});
		}

		to.add = function(toEntry, ontologies){
			//$log.log(JSON.stringify(ccEntry));
			 return $http.post("/api/1/term/ontology/add", JSON.stringify({"url":toEntry.url})).
				then(function(r){
					to.data = r.data.data;
					ontologies.push(r.data.data);
					//$log.log(ontologies);
					return to;
				},function(r) {
					to.data = "Error:\n " + r.data['errormsg'];
					return to;
				});
		}

		return to;
    });

    app.factory("addIlx", function($http, $log){
		var ilx = {};
		ilx.add = function(term){
			 return $http.post("/api/1/ilx/add", JSON.stringify({"term":term})).
				then(function(r){
					ilx.data = r.data.data;
					return ilx;
				},function(r) {
					ilx.data = "Error:\n " + r.data['errormsg'];
					return ilx;
				});
		}
		return ilx;
    });

    app.factory("termSearch", function($http, $log){
		var matches = {};
		matches.fetch = function(searchTerm){
			 return $http.get("/api/1/term/search/" + searchTerm).
				then(function(r){
					//$log.log(r.data.data);
					matches.data = r.data.data;
					return matches;
				},function(r) {
					matches.data = "Error:\n " + r.data['errormsg'];
					return matches;
				});
		}
		return matches;
    });

    app.factory("getTermFromIlx", function($http, $log){
		var matches = {};
		matches.fetch = function(ilx){
			 return $http.get("/api/1/term/ilx/" + ilx).
				then(function(r){
					matches.data = r.data.data;
				},function(r) {
                    matches.data = {};
				});
		}
		return matches;
    });

    app.factory("termElasticSearch", function($http, $log){
		var matches = {};
		matches.fetch = function(searchTerm, size, from, cid, type){
			//console.log(cid)
			 return $http.get("/api/1/term/elastic/search?term=" + searchTerm + "&size=" + size + "&from=" + from + '&cid=' + cid + '&type=' + type).
				then(function(r){
					//$log.log(r);
					matches.data = r.data.data.hits;
					return matches;
				},function(r) {
					//$log.log('error');$log.log(r);
					matches.data = "Error:\n " + r.data['errormsg'];
                    // match.data = [];
					return matches;
				});
		}
		return matches;
    });

    app.factory("customTermElasticSearch", function($http, $log){
		var matches = {};
		matches.fetch = function(query, size, from, cid, type){
			//console.log(cid)
			 return $http.get("/api/1/term/elastic/search?query=" + query + "&size=" + size + "&from=" + from + '&cid=' + cid + '&type=' + type).
				then(function(r){
					//$log.log(r);
					matches.data = r.data.data.hits;
					return matches;
				},function(r) {
					//$log.log('error');$log.log(r);
					matches.data = "Error:\n " + r.data['errormsg'];
                    // match.data = [];
					return matches;
				});
		}
		return matches;
    });

    app.factory("termFromIlx", function($http, $log){
        var matches = {};
        matches.fetch = function(searchTerm, size, from, cid, type){
            //console.log(cid)
             return $http.get("/api/1/term/elastic/search?term=" + searchTerm + "&size=" + size + "&from=" + from + '&cid=' + cid + '&type=' + type).
                then(function(r){
                    //$log.log(r);
                    matches.data = r.data.data.hits;
                    return matches;
                },function(r) {
                    //$log.log('error');$log.log(r);
                    // matches.data = "Error:\n " + r.data['errormsg'];
                    match.data = [];
                    return matches;
                });
        }
        return matches;
    });

    app.factory("termParents", function($http, $log){
    	var parents = {};
    	parents.fetch = function(id){;
    		return $http.get("/api/1/term/parents/" + id).
    		then(function(r){
    			// console.log(r.data);
    			parents.data = r.data.data;
    			return parents;
    		},function(r) {
    			parents.data = "Error:\n " + r.data['errormsg'];
    			return parents;
    		});
    	}
    	return parents;
    });

    app.factory("termChildren", function($http, $log){
    	var children = {};
    	children.fetch = function(id){;
    		return $http.get("/api/1/term/children/" + id).
    		then(function(r){
    			//console.log(r.data);
    			children.data = r.data.data;
    			return children;
    		},function(r) {
    			children.data = "Error:\n " + r.data['errormsg'];
    			return children;
    		});
    	}
    	return children;
    });

    app.factory("termMapping", function($http, $log){
    	var mapping = {};
    	mapping.fetch = function(id, size, from, curation_status){;
    		return $http.get("/api/1/term/mappings/" + id + '?size=' + size + '&from=' + from + '&curation_status=' + curation_status).
    		then(function(r){
    			//console.log(r.data);
    			mapping.data = r.data.data;
    			return mapping;
    		},function(r) {
    			//mapping.data = "Error:\n " + r.data['errormsg'];
    			return 'error';
    		});
    	}
    	return mapping;
    });

    app.factory("termHistory", function($http, $log){
    	var history = {};
    	history.fetch = function(id){;
    		return $http.get("/api/1/term/history/" + id).
    		then(function(r){
    			//console.log(r.data);
    			history.data = r.data.data;
    			return history;
    		},function(r) {
    			history.data = "Error:\n " + r.data['errormsg'];
    			return history;
    		});
    	}
    	return history;
    });

    app.factory("termVersion", function($http, $log){
    	var version = {};
    	version.fetch = function(id, version){
    		return $http.get("/api/1/term/version/?tid=" + id + "&version=" + version).
    		then(function(r){
    			//console.log(r.data);
    			version.data = r.data.data;
    			return version;
    		},function(r) {
    			version.data = "Error:\n " + r.data['errormsg'];
    			return version;
    		});
    	}
    	return version;
    });

    app.factory("termVote", function($http, $log){
    	var vote = {};
    	vote.add = function(table, table_id, vote){
    		return $http.post("/api/1/term/vote", JSON.stringify({"table":table,"table_id":table_id,"vote":vote})).
    		then(function(r){
    			//console.log(r);
    			vote.data = r;
    			return r;
    		},function(r) {
    			vote.data = "Error:\n " + r.data['errormsg'];
    			return vote;
    		});
    	}

    	return vote;
    });

    app.factory("termTypeCounts", function($http, $log){
    	var types = {};
    	types.fetch = function(){
    		return $http.get("/api/1/term/type-counts").
    		then(function(r){
    			//console.log(r.data);
    			types.data = r.data.data;
    			return types;
    		},function(r) {
    			types.data = "Error:\n " + r.data['errormsg'];
    			return types;
    		});
    	}

    	return types;
    });

    app.factory("termCurieCounts", function($http, $log){
    	var obj = {};
    	obj.fetch = function(type){
    		return $http.get("/api/1/term/curie-counts?type="+type).then(function(r){
    			//console.log(r.data);
    			obj.data = r.data.data;
    			return obj;
    		},function(r) {
    			obj.data = "Error:\n " + r.data['errormsg'];
    			return obj;
    		});
    	}

    	return obj;
    });

    app.factory("termAffiliates", function($http, $log){
    	var obj = {};
    	obj.fetch = function(){
    		return $http.get("/api/1/term/affiliates").
    		then(function(r){
    			//console.log(r.data);
    			obj.data = r.data.data;
    			return obj;
    		},function(r) {
    			obj.data = "Error:\n " + r.data['errormsg'];
    			return obj;
    		});
    	}

    	return obj;
    });

    app.factory("termCommunity", function($http, $log){
    	var obj = {};

    	obj.add = function(tid, cid, uid){
    		return $http.post("/api/1/term/add-community/" + tid, JSON.stringify({"uid_suggested":uid,"cid":cid})).
    		then(function(r){
    			//console.log(r);
    			community = r.data.data;
    			obj.data = r.data.data;
    			return obj;
    		},function(r) {
    			obj.data = "Error:\n " + r.data['errormsg'];
    			return obj;
    		});
    	}

    	obj.fetch = function(tid, cid){
    		return $http.get("/api/1/term/get-community?tid=" + tid + '&cid=' + cid).
    		then(function(r){
    			community = r.data.data;
    		    obj.data = r.data.data;
    			//console.log(community);
    			return obj;
    		},function(r) {
    			obj.data = "Error:\n " + r.data['errormsg'];
    			return obj;
    		});
    	}

		obj.edit = function($rootScope, $uibModal, $q, community){

			var deferred = $q.defer();

			var success = false;
			setTimeout(function() {
				success ? deferred.resolve(community) : deferred.reject(community);
			}, obj.openCurateModal($rootScope, $uibModal, community, success));

			return deferred.promise;
		}

	    obj.openCurateModal = function($rootScope, $uibModal, community, success) {
	    	var modalInstance = $uibModal.open({
			      templateUrl: 'term-community-curate-modal',
			      controller: 'termCommunityApproveCtrl',
			      resolve: {
			    	  communityEntry: function () {
			          return $rootScope.communityEntry;
			        }
			      }
			    });

			    modalInstance.result.then(function () {
					//var success = false;
					$http.post("/api/1/term/curate-community/" + $rootScope.communityEntry.id,
		    				JSON.stringify({"uid_curated":$rootScope.communityEntry.uid_curated,
		    					"tid": $rootScope.communityEntry.tid,
		    					"notes":$rootScope.communityEntry.notes,
		    					"status":$rootScope.communityEntry.status,
		    					"cid":$rootScope.communityEntry.cid})).
		    		then(function(r){
		    			community = obj.data = r.data.data;
		    			success = true;
		    		},function(r) {
		    			community = obj.data = "Error:\n " + r.data['errormsg'];
		    			success = false;
		    		});

			    }, function () {
			      $log.info('Modal dismissed at: ' + new Date());
			    });
		};

    	return obj;
    });

}());
