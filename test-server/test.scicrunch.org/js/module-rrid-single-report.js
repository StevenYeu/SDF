(function() {

    var app = angular.module("rridSingleReportApp", ["errorApp", "ui.bootstrap", "ngSanitize"]);

    app.controller("rridSingleReportController", ["$http", "$uibModal", "$scope", "$log",function($http, $uibModal, $scope, $log) {
        var that = this;
        this.allowed_types = [];
        this.per_page = 10;
        var rrid_allowed_types = window.rrid_allowed_types;
        var report_id = $("#rrid-report-id").val();
        var community_fullURL = $("#community-fullURL").val();
        var uuids_map = {};
        var new_item_map = {};
        this.snapshots = JSON.parse($("#json-rrid-report-snapshots").val());
        this.snapshots.sort(function(a, b) { return b.timestamp - a.timestamp; });
        this.snapshot_limit = 5;
        this.searchType = 0;

        this.searchItems = function() {
            new_item_map = {};
            var searchText = getSearchText();
            var searchType = getSearchType();
            var searchVendor = getSearchVendor();
            var searchCatalogNumber = getSearchCatalogNumber();

            prepareFilters(that.allowed_types[searchType], "Vendor", searchVendor);
            prepareFilters(that.allowed_types[searchType], "Catalog Number", searchCatalogNumber);
            servicesSearch(that.allowed_types[searchType], getSearchText());

            // for(var i = 0; i < that.allowed_types.length; i++) {
            //     that.allowed_types[i].resetSearch();
            //     servicesSearch(that.allowed_types[i], searchText);
            // }
        };

        this.showAllSnapshots = function() {
            that.snapshot_limit = 10000;
        };

        this.changePage = function(type) {
            var searchText = getSearchText();
            servicesSearch(type, searchText);
        };

        this.openAddItemModal = function(uid, uuid, rrid, name, type, subtypes) {
            var data = {
                id: report_id,
                type: type,
                rrid: rrid,
                uuid: uuid,
                uid: uid    // added Uid -- Vicky-2019-2-21
            };
            // added information bars (green or red) -- Vicky-2019-2/11/2019
            new_item_map = {};
            new_item_map["name"] = name;
            switch(type) {
              case "cellline":
                new_item_map["type"] = "cell line";
                break;
              default:
                new_item_map["type"] = type;
                break;
            };

            $http.post("/api/1/rrid-report/add-item", data)
                .then(function(response) {
                    that.refreshItems();
                    var new_item = response.data.data;
                    // added information bars (green or red) -- Vicky-2019-2/11/2019
                    var new_item_comments = JSON.parse(new_item.data)["Comments"].toLowerCase();

                    new_item_map["flag"] = 0;
                    var tmp = [];
                    if(new_item_comments.includes("discontinued")) {
                      new_item_map["flag"] = 1;
                      tmp.push("Discontinued " + new_item_map["type"]);
                    }
                    if (new_item_comments.includes("problematic")) {
                      new_item_map["flag"] = 1;
                      tmp.push("Problematic " + new_item_map["type"]);
                    }
                    new_item_map["warning"] = tmp.join(", ");

                    var modalInstance = $uibModal.open({
                        animation: true,
                        templateUrl: "item-added.html",
                        controller: "itemAddedModalController",
                        resolve: {
                            "item_info": function() {
                                return {
                                    name: name,
                                    id: new_item.id,
                                    type: type,
                                    subtypes: subtypes,
                                    rrid: rrid,
                                    report_id: report_id,
                                    uuid: response.data.data.uuid,
                                };
                            },
                            "refresher": function() {
                                return function() {
                                    that.refreshItems();
                                };
                            }
                        }
                    });
                });
        };

        // added information bars (blue or red) -- Vicky-2019-2/11/2019
        this.newItemMap = function() {
          return new_item_map;
        };

        this.refreshItems = function() {
            $http.get("/api/1/rrid-report/items?id=" + report_id)
                .then(function(response) {
                    uuids_map = {};
                    that.report_items = response.data.data;
                    for(var i = 0; i < that.report_items.length; i++) {
                        that.report_items[i].warning = false;
                        that.report_items[i].data = JSON.parse(that.report_items[i].data);
                        if (that.report_items[i].data.Comments.toLowerCase().includes('discontinued') || that.report_items[i].data.Comments.toLowerCase().includes('problematic')) that.report_items[i].warning = true;
                        uuids_map[that.report_items[i].uuid] = that.report_items[i];
                    }
                });
        };

        this.allowedTypesMap = function(type) {
            for(var i = 0; i < that.allowed_types.length; i++) {
                if(that.allowed_types[i].name === type) {
                    return that.allowed_types[i];
                }
            }
            return null;
        };

        this.reportHasUUID = function(uuid) {
            return uuids_map.hasOwnProperty(uuid);
        };

        this.getItemFromUUID = function(uuid) {
            return uuids_map[uuid];
        };

        this.deleteRRIDItem = function(item) {
            new_item_map = {};
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "deleteRRIDItemConfirm.html",
                controller: "deleteItemModal"
            });
            modalInstance.result.then(function() {
                $http.post("/api/1/rrid-report/delete-item", {id: report_id, uuid: item.uuid, type: item.type, full_delete: true})
                    .then(function(response) {
                        that.refreshItems();
                    });
            });
        };

        this.createSnapshotButton = function() {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "create-snapshot.html",
                controller: "createSnapshotModalController",
            });
        };

        this.searchFilter = function(allowed_type, field) {
            var value = allowed_type["filters-input"][field]
            if(value) {
                allowed_type["filters"][field] = value;
            } else {
                delete(allowed_type["filters"][field]);
            }
            servicesSearch(allowed_type, getSearchText());
        };

        this.searchSort = function(allowed_type, field, direction) {
            allowed_type["sort"] = {field: field, direction: direction};
            servicesSearch(allowed_type, getSearchText());
        };

        this.resetSearch = function() {
            new_item_map = {};
            that.searchText = "";
            that.searchVendor = "";
            that.searchCatalogNumber = "";
            for(var i = 0; i < that.allowed_types.length; i++) {
                that.allowed_types[i].resetSearch();
            }
            that.searchItems();
        };

        function getSearchText() {
            var searchText = "*";
            if(that.searchText) {
                //searchText = that.searchText;
                // split keywords -- Vicky-2019-2-6
                var str = that.searchText.trim().replace("Cat#", "");
                var str_len = str.length;
                var keywords_l = [];
                var tmp_s = "";
                var key_flag = quote_flag = bracket_flag = false;
                var bracket_count = 0;

                for (var i = 0; i < str_len; i++) {
                  var char = str.charAt(i);
                  switch(char) {
                    case '"':
                      if (quote_flag) {
                        quote_flag = false;
                        keywords_l.push(tmp_s);
                        tmp_s = "";
                      } else quote_flag = true;
                      break;
                    case "(":
                      bracket_count ++;
                      if (!key_flag && !quote_flag) {
                        keywords_l.push(char);
                        bracket_flag = true;
                      } else {
                        tmp_s += char;
                        if (i == str_len-1 && key_flag) keywords_l.push(tmp_s);
                      }
                      break;
                    case ")":
                      if (quote_flag || !bracket_flag || bracket_count > 1) {
                        tmp_s += char;
                        if (i == str_len-1 && key_flag) keywords_l.push(tmp_s);
                      } else if (key_flag || bracket_flag) {
                        if (tmp_s != "") {
                          keywords_l.push(tmp_s);
                          tmp_s = "";
                        }
                        keywords_l.push(char);
                        key_flag = false;
                        bracket_flag = false;
                      }
                      bracket_count--;
                      break;
                    case " ":
                      if (!quote_flag && key_flag) {
                        key_flag = false;
                        if (tmp_s != "") {
                          keywords_l.push(tmp_s);
                          tmp_s = "";
                        }
                      } else if (quote_flag) tmp_s += char;
                      break;
                    case "+":
                      if (quote_flag || key_flag) {
                        tmp_s += char;
                        if (i == str_len-1 && key_flag) keywords_l.push(tmp_s);
                      }
                      break;
                    case "-":
                      if (!quote_flag && !key_flag) {
                        keywords_l.push("NOT");
                      } else {
                        tmp_s += char;
                        if (i == str_len-1 && key_flag) keywords_l.push(tmp_s);
                      }
                      break;
                    default:
                      tmp_s += char;
                      if (!key_flag) key_flag = true;
                      if (i == str_len-1 && key_flag) keywords_l.push(tmp_s);
                      break;
                  }
                }

                var keys = ["(", ")", "AND", "OR", "NOT"];
                for (i = 0; i < keywords_l.length; i++){
                  if (!keys.includes(keywords_l[i])) keywords_l[i] = '"' + keywords_l[i] + '"';
                }
                keywords_s = keywords_l.join(" ");
                searchText = keywords_s;
            }
            return searchText;
        }

        function getSearchType() {
            var searchType = 0;
            if(that.searchType) {
                searchType = that.searchType;
            }
            return searchType;
        }

        function getSearchVendor() {
            var searchType = "";
            if(that.searchVendor) {
                searchType = that.searchVendor;
            }
            return searchType;
        }

        function getSearchCatalogNumber() {
            var searchType = "";
            if(that.searchCatalogNumber) {
                searchType = that.searchCatalogNumber;
            }
            return searchType;
        }


        function prepareFilters(allowed_type, field, value) {
            allowed_type.loading = true;
            var val = value;
            if(val) {
                allowed_type["filters"][field] = val;
            } else {
                delete(allowed_type["filters"][field]);
            }
        }

        function servicesSearch(allowed_type, query) {
            allowed_type.loading = true;
            var url = "/api/1/elasticservices/" + allowed_type.viewid + "/search?q=" + query + "&per_page=" + that.per_page + "&page=" + allowed_type.page;
            for(var filter in allowed_type.filters) {
                url += "&filter[]=" + filter + ":" + allowed_type.filters[filter];
            }
            if(allowed_type.sort) {
                url += "&sort=" + allowed_type.sort.direction;
                url += "&column=" + allowed_type.sort.field;
            }
            $http.get(url)
                .then(function(response) {
                    allowed_type.loading = false;
                    var count = response.data.data.count;
                    var results = response.data.data.results;
                    // modified "Comments", "References", "Hierarchy", "Originate from Same Individual" -- Vicky-2019-2-15
                    for (var i = 0; i < results.length; i++) {
                        //comments
                        results[i]["Warning"] = 0;
                        results[i]["Comments"] = results[i]["Comments"].replace('<font color="#ff6347"></> ', '').replace('<font color="#000000"></> ', '');
                        var new_item_comments = results[i]["Comments"].toLowerCase();
                        if (new_item_comments.includes("discontinued") || new_item_comments.includes("problematic")) {
                            results[i]["Comments"] = "<font color='red'>" + results[i]["Comments"] + "</font>";
                            results[i]["Warning"] = 1;
                        }

                        //Reference
                        var values = results[i]["References"].split(",");
                        var val = "";
                        for (var j = 0; j < values.length; j++){
                            var tmp = values[j].trim().split(":");
                            if(tmp.length > 1) {
                                switch (tmp[0].trim()) {
                                    case "DOI":
                                        values[j] = "<a target='_blank' href='https://dx.doi.org/" + tmp[1] + "'>" + tmp[0].trim() + ":" + tmp[1] + "</a>";
                                        break;
                                    case "PMID":
                                        values[j] = "<a target='_blank' href='" + community_fullURL + "/" + tmp[1] + "?rpKey=on'>" + tmp[0].trim() + ":" + tmp[1] + "</a>";
                                        break;
                                    case "CelloPub":
                                        values[j] = "<a target='_blank' href='https://web.expasy.org/cellosaurus/cellopub" + tmp[1].replace("-", "") + "'>" + tmp[0].trim() + ":" + tmp[1] + "</a>";
                                        break;
                                }
                            } else if(tmp[0].search("WBPaper") == 0) {
                                values[j] = "<a target='_blank' href='https://wormbase.org/resources/paper/" + tmp[0] + "'>" + tmp[0] + "</a>";
                            }
                        }
                        results[i]["References"] = values.join("<br>");

                        //Hierarchy & Originate from Same Individual
                        if(results[i]["Hierarchy"] == "CVCL:") results[i]["Hierarchy"] = "";
                        if(results[i]["Originate from Same Individual"] == "CVCL:") results[i]["Originate from Same Individual"] = "";
                    }

                    allowed_type.results = results;
                    allowed_type['results-count'] = count;

                    /* delete all input filters */
                    for(var filter_input in allowed_type["filters-input"]) {
                        delete(allowed_type["filters-input"][filter_input]);
                    }

                    /* insert filters into input filters */
                    for(var filter in allowed_type["filters"]) {
                        allowed_type["filters-input"][filter] = allowed_type["filters"][filter];
                    }
                }, function(error) {
                    allowed_type.loading = false;
                });
        }

        for(var name in rrid_allowed_types) {
            if(!rrid_allowed_types.hasOwnProperty(name)) continue;
            var subtypes_list_array = [];
            for(var subtype_name in rrid_allowed_types[name].subtypes) {
                if(!rrid_allowed_types[name].subtypes.hasOwnProperty(subtype_name)) continue;
                subtypes_list_array.push(subtype_name);
            }
            var subtypes_list = subtypes_list_array.join(",");
            this.allowed_types.push({
                "name": name,
                "pretty-type-name": rrid_allowed_types[name]["pretty-type-name"],
                "viewid": rrid_allowed_types[name]["viewid"],
                "rrid-data-cols": rrid_allowed_types[name]["rrid-data-cols"],
                "page": 1,
                "results": [],
                "results-count": 0,
                "table-open": false,
                "rrid-name-col": rrid_allowed_types[name]['rrid-name-col'],
                "rrid-view-col": rrid_allowed_types[name]['rrid-view-col'],
                "subtypes": subtypes_list_array,
                "subtypes-list": subtypes_list,
                "loading": false,
                "filters": {},
                "filters-input": {},
                "sort": null,
                "resetSearch": function() {
                    this.filters = {};
                    this.page = 1;
                    this.sort = null;
                    this["filters-input"] = {};
                }
            });
        }

        this.searchItems(); // start a default search
        this.refreshItems();
    }]);

    app.controller("deleteItemModal", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
        $scope.delete = function() {
            $uibModalInstance.close();
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        }
    }]);

    app.controller("createSnapshotModalController", ["$uibModalInstance", "$scope", function($uibModalInstance, $scope) {
        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);

    app.controller("itemAddedModalController", ["$uibModalInstance", "$http", "$scope", "item_info", "refresher", function($uibModalInstance, $http, $scope, item_info, refresher) {
        $scope.item_info = item_info;

        $scope.delete = function() {
            var data = {
                id: item_info.report_id,
                uuid: item_info.uuid,
                type: item_info.type,
                full_delete: true
            };
            $http.post("/api/1/rrid-report/delete-item", data)
                .then(function() {
                    refresher();
                    $uibModalInstance.close();
                });
        };

        $scope.close = function() {
            $uibModalInstance.close();
        };
    }]);

    angular.bootstrap(document.getElementById("rrid-single-report"), ["rridSingleReportApp"]);
}());
