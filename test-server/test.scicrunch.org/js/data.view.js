$(function() {
    var views_app = angular.module("viewsApp", ["ui.bootstrap", "utilitiesApp", "ngSanitize"]).
        config(function($locationProvider) {
            $locationProvider.html5Mode({"enabled": true, "requireBase": false});
        });

    views_app.controller("viewsController", ["$scope", "$location", "$log", function($scope, $location, $log) {
        // init values
        var that = this;
        this.category_filters_flag = true;
        this.portalName = window.globals.portalName;
        this.community_filter_name = this.portalName + " Community Resources";
        this.views = parseViews(window.globals.views);
        this.viewids = window.globals.viewids || [];
        this.community_viewids = window.globals.community_viewids || [];
        this.allViewids = this.viewids.slice(); // copy views array by value
        this.categories = {};
        this.filter_text = "";
        this.subscription_set = ($("#subscription_set").val() == "true") ? true : false;
        this.updateURLChangedFlag = false;  // flag to say the url was changed by the user, not internally, and should trigger an update
        var resultDisplayModeCookie = readCookie("dataViewResultDisplayMode");
        this.resultDisplayMode = resultDisplayModeCookie ? resultDisplayModeCookie : "columns";
        this.isCategoriesSelected = false;  // if any categories at all are selected
        this.counts = {
            total_count: 0,
            current_count: 0
        };

        // refresh, called after every filter by text or category
        this.refresh = function() {
            that.viewids = that.allViewids.slice(); // copy views array by value
            that.filterViewsByCategory();
            that.filterViewsByText();
            that.sortAlpha();
            that.rechunk();
            that.updateURLHash();
            that.refreshCategories();
            refreshCounts();
        };

        // splits the views into three columns
        this.rechunk = function() {
            that.chunkedViewIds = chunk(this.viewids, 3);
        };

        // sorts views in alphabetical order
        this.sortAlpha = function() {
            that.viewids = that.viewids.sort(function(a,b) {
                var viewa = that.views[a];
                var viewb = that.views[b];
                if(viewa["name"].toUpperCase() < viewb["name"].toUpperCase()) return -1;
                if(viewa["name"].toUpperCase() > viewb["name"].toUpperCase()) return 1;
                return 0;
            });
            that.rechunk();
        };

        // sort views by number of results
        this.sortCount = function() {
            that.viewids = that.viewids.sort(function(a,b) {
                if(that.views[a]["count"] < that.views[b]["count"]) return 1;
                if(that.views[a]["count"] > that.views[b]["count"]) return -1;
                return 0;
            });
            that.rechunk();
        };

        this.updateURLHash = function() {
            that.updateURLChangedFlag = false;
            var deselectedFound = false;
            var selected = [];
            for(var category in that.categories) {
                if(!that.categories.hasOwnProperty(category)) continue;
                for(var child in that.categories[category]["children"]) {
                    if(that.categories[category]["children"].hasOwnProperty(child)) {
                        if(that.categories[category]["children"][child]["selected"]) {
                            selected.push("category-filter=" + category + ":" + child);
                        } else {
                            deselectedFound = true;
                        }
                    }
                }
            }
            var selectedString = "";
            if(deselectedFound && selected.length > 0) {
                selectedString = selected.sort().join("&");
            }
            if(selectedString == "") selectedString = "all";
            if(window.location.href.includes("#")) {
                var category_filters_url = window.location.href.split("#")[1].replace(/%20/g, " ").split("&").sort().join("&");
                if(category_filters_url != selectedString) {
                    that.category_filters_flag = false;
                    var category_filters = getCategoryFiltersFromURL(category_filters_url);
                    var tmp = [];
                    // category_filters[0] is CommunityFilter; category_filters[1] is CategoryFilters; category_filters[2] is SubCategoryFilters
                    if(category_filters[0] != "") tmp.push("Community Resources - " + category_filters[0]);
                    if(category_filters[1] != "") tmp.push("Category - " + category_filters[1]);
                    if(category_filters[2] != "") tmp.push("Subcategory - " + category_filters[2]);
                    that.category_filters = tmp.join(", ");

                    var selected_category_filters = getCategoryFiltersFromURL(selectedString);
                    that.error = "";
                    if(category_filters[0] == selected_category_filters[0] && category_filters[1] == selected_category_filters[1] && category_filters[2] != selected_category_filters[2]) that.error = "There are results found in other subcategories that you might be interested. Check it out!";
                    else if(category_filters[0] == selected_category_filters[0] && category_filters[1] != selected_category_filters[1]) that.error = "There are results found in other categories that you might be interested. Check it out!";
                    else if(category_filters[0] != selected_category_filters[0]) that.error = "There are results found in all resources that you might be interested. Check it out!";
                } else that.category_filters_flag = true;
            }
            $location.hash(selectedString);
        };

        function getCategoryFiltersFromURL(category_filter_url) {
          var CommunityFilter = "";
          var CategoryFilters = [];
          var SubCategoryFilters = [];
          var category_filters = [];
          if(category_filter_url.includes("&")) category_filters = category_filter_url.split("&");
          else category_filters[0] = category_filter_url;
          for(var idx in category_filters) {
              var category_filter = category_filters[idx].replace("category-filter=", "").split(":");
              if(category_filter[1] == "dknetbeta Community Resources" || category_filter[1] == "dkNET Community Resources") {
                  CommunityFilter = category_filter[1];
              } else if (category_filter[0] == "Output Type") {
                  CategoryFilters.push(category_filter[1]);
              } else if (category_filter[0] == "Category") {
                  SubCategoryFilters.push(category_filter[1]);
              }
          }
          CategoryFilters = CategoryFilters.join(", ");
          SubCategoryFilters = SubCategoryFilters.sort().join(", ");

          return [CommunityFilter, CategoryFilters, SubCategoryFilters];
        }

        // refresh the categories, get new category counts and dont include categories with zero results
        this.refreshCategories = function() {
            // reset category counts to zero
            for(var category in that.categories) {
                if(!that.categories.hasOwnProperty(category)) continue;
                that.categories[category]["show"] = false;
                for(var child in that.categories[category]["children"]) {
                    if(!that.categories[category]["children"].hasOwnProperty(child)) continue;
                    that.categories[category]["children"][child]["count"] = 0;
                    that.categories[category]["children"][child]["show"] = false;
                }
                if(!that.categories.hasOwnProperty("Output Type")) {
                    that.categories["Output Type"] = {children: {}};
                }
                if(!that.categories["Output Type"]["children"].hasOwnProperty("Other")) {
                    that.categories["Output Type"]["children"]["Other"] = {count: 0, show: false, selected: false};
                }
            }
            // get the category counts for each active view
            for(var i = 0; i < that.viewids.length; i++) {
                var view = that.views[that.viewids[i]];
                var hasOutputType = view.categories.map(function(a) { return a.parent; }).indexOf("Output Type") !== -1;
                if(!hasOutputType) {
                    view.categories.push({child: "Other", parent: "Output Type"});
                }
                for(var j = 0; j < view.categories.length; j++) {
                    var category = view.categories[j];
                    var parent_name = category["parent"];
                    var child_name = category["child"];
                    if(!that.categories.hasOwnProperty(parent_name)) that.categories[parent_name] = {href_name: parent_name.replace(/[ /]/g, "_"), children: {}};
                    that.categories[parent_name]["show"] = true;
                    if(!that.categories[parent_name]["children"].hasOwnProperty(child_name)) {
                        that.categories[parent_name]["children"][child_name] = {count: view["count"], selected: false, show: true};
                    } else {
                        that.categories[parent_name]["children"][child_name]["count"] += view["count"];
                        that.categories[parent_name]["children"][child_name]["show"] = true;
                    }
                }
            }
        };

        // set a category to selected and then refresh to apply all the filters
        this.chooseCategory = function(child) {
            child.selected = !child.selected;
            that.refresh();
        };

        // filter categories, only show views that include all the selected categories, dont filter if no categories are selected
        this.filterViewsByCategory = function() {
            that.isCategoriesSelected = true;
            var selected_categories = [];
            var selected_viewids = [];
            // get the selected categories
            for(var category in that.categories) {
                if(!that.categories.hasOwnProperty(category)) continue;
                for(var child in that.categories[category]["children"]) {
                    if(that.categories[category]["children"].hasOwnProperty(child) && that.categories[category]["children"][child]["selected"]) {
                        var cat_string = "parent-" + category + "-child-" + child;
                        selected_categories.push(cat_string);
                    }
                }
            }
            // if no categories selected, don't filter out anything
            if(selected_categories.length == 0) {
                that.isCategoriesSelected = false;
                return;
            }
            // get the selected views with a matching category
            for(var i in that.viewids) {
                var viewid = that.viewids[i];
                var view = that.views[viewid];
                view_categories = [];
                for(var j in view.categories) {
                    var cat = view.categories[j];
                    var cat_string = "parent-" + cat["parent"] + "-child-" + cat["child"];
                    view_categories.push(cat_string);
                }
                var skip = false;
                for(var j in selected_categories) {
                    if(view_categories.indexOf(selected_categories[j]) == -1) {
                        var skip = true;
                        break;
                    }
                }
                if(!skip) selected_viewids.push(viewid);
            }
            that.viewids = selected_viewids;
        };

        // filter views that contain the text
        this.filterViewsByText = function() {
            if(!that.filter_text) return;

            var selected_viewids = [];
            var text_array = that.filter_text.split(" ");
            for(i in text_array) text_array[i] = text_array[i].replace(/^\s+|\s+$/g, "").toLowerCase();
            for(i in that.viewids) {
                var viewid = that.viewids[i];
                var view = that.views[viewid];
                var name = view.name.toLowerCase();
                var skip = false;
                for(j in text_array) {
                    var text_string = text_array[j];
                    if(name.indexOf(text_string) == -1) {
                        skip = true;
                        break;
                    }
                }
                if(!skip) selected_viewids.push(viewid);
            }
            that.viewids = selected_viewids;
        };

        $scope.categoryOrderBy = function(category) {
            return category.child;
        };

        // divide an array (arr) into ~equal columns (size)
        function chunk(arr, size) {
            var newArr = [];
            var chunkSize = Math.ceil(arr.length / size);
            for(var i = 0; i < arr.length; i += chunkSize) {
                newArr.push(arr.slice(i, chunkSize+i));
            }
            return newArr;
        }

        // get a flat array of all the categories and all counts (unselected)
        function flattenCategories() {
            that.flatCategories = [];
            for(var category in that.categories) {
                if(!that.categories.hasOwnProperty(category)) continue;
                for(var child in that.categories[category].children) {
                    if(!that.categories[category].children.hasOwnProperty(child)) continue;
                    that.flatCategories.push({"name": child, "count": that.categories[category].children[child].count, "parent": category});
                }
            }
            that.flatCategories.sort(function(a,b) {
                if(a.count == b.count) return 0;
                return a.count > b.count ? -1 : 1;
            });
        }

        // class to return the color of the categories
        this.categoryBoxColorClass = function(name) {
            return "category-color-0";
            var n = 0;
            for(var i = 0; i < name.length && i < 3; i++) {
                n += name[i].charCodeAt(0);
            }
            var color_n = n % 4;
            var color = "category-color-" + color_n.toString();
            return color;
        };

        // select category box button function
        this.selectBoxCategory = function(parentCat, childCat, shouldRefresh) {
            if(shouldRefresh === undefined) shouldRefresh = true;
            // resetAllCategories(); don't reset anymore
            if(
                parentCat !== undefined &&
                childCat !== undefined &&
                that.categories.hasOwnProperty(parentCat) &&
                that.categories[parentCat].children.hasOwnProperty(childCat) &&
                that.categories[parentCat].children[childCat].count > 0
            ) {
                that.categories[parentCat].children[childCat].selected = !that.categories[parentCat].children[childCat].selected;
            } else if(parentCat === undefined && childCat === undefined) {
                resetAllCategories();
            }
            if(shouldRefresh) {
              that.refresh();
              that.category_filters_flag = true;
            }
        };

        this.clearCategories = function() {
            resetAllCategories();
            that.refresh();
            that.category_filters_flag = true;
        };

        this.changeResultDisplayMode = function(mode) {
            that.resultDisplayMode = mode;
            createCookie("dataViewResultDisplayMode", mode);
        };

        // set all categories selected to false
        function resetAllCategories() {
            for(var parentCatKey in that.categories) {
                if(!that.categories.hasOwnProperty(parentCatKey)) continue;
                var parentCat = that.categories[parentCatKey];
                for(var childCatKey in parentCat.children) {
                    if(!parentCat.children.hasOwnProperty(childCatKey)) continue;
                    var childCat = parentCat.children[childCatKey];
                    childCat.selected = false;
                }
            }
        }

        function openCategoryDropdown(parentCat) {
            $("#collapse-" + parentCat.replace(/[ /]/g, "_")).collapse("show");
        }

        function closeAllCategoryDropdowns() {
            for(var catName in that.categories) {
                $("#collapse-" + catName.replace(/[ /]/g, "_")).collapse("hide");
            }
        }

        // check if the get param category-filter is set
        function checkGetParamCategoryFilter() {
            if(!$location.hash()) {
                that.clearCategories();
                return;
            }
            var hashParams = $location.hash().split("&");
            for(var i = 0; i < hashParams.length; i++) {
                var param = hashParams[i].split("=");
                if(param.length != 2 || param[0] != "category-filter") continue;
                var cfa = param[1].split(":");
                if(cfa.length !== 2) return;
                var catParent = cfa[0];
                var catChild = cfa[1];
                that.selectBoxCategory(catParent, catChild, false);
            }
            that.refresh();
        }

        /* order by function for output type */
        $scope.outputTypeOrderBy = function(category) {
            if(category == that.community_filter_name) return "AAAA";
            if(category == "Physical Resource or Software Tool") return "AAAB";
            return category;
        };

        this.outputTypeTooltip = function(category) {
            switch(category) {
                case "Physical Resource or Software Tool":
                    return "Repositories that store research resources such as Antibody, cell line, adenovirus, plasmids, tissues, animals or software tools.";
                case "Data or Model":
                    return "Repositories that store datasets such as scientific data, clinical trial registries, or images.";
                case "Funding":
                    return "Databases that stores funding information.";
                case "Information":
                    return "Web Resources or databases that store information such as protocols, gene or protein structure information.";
                case "Literature":
                    return "PubMed abstracts";
                case "Other":
                    return "Other types of data";
                case that.community_filter_name:
                    return that.portalName + " specific data resources";
                default:
                    return "";
            }
        };

        function addCommunityCategories() {
            for(var i = 0; i < that.community_viewids.length; i++) {
                var viewid = that.community_viewids[i];
                if(that.views.hasOwnProperty(viewid)) {
                    that.views[viewid].categories.push({
                        parent: "Output Type",
                        child: that.community_filter_name
                    });
                }
            }
        }

        /* get the counts of results after a filter */
        function refreshCounts() {
            if(that.counts.total_count == 0) {
                for(var i = 0; i < that.allViewids.length; i++) {
                    that.counts.total_count += that.views[that.allViewids[i]].count;
                }
            }
            that.counts.current_count = 0;
            for(var i = 0; i < that.viewids.length; i++) {
                that.counts.current_count += that.views[that.viewids[i]].count;
            }
        }

        function parseViews(views) {
            for(var viewid in views) {
                if(views.hasOwnProperty(viewid)) {
                    views[viewid].count = parseInt(views[viewid].count);
                }
            }
            return views;
        }

        $scope.$watch(function() {
            return $location.hash();
        }, function(a) {
            if(that.updateURLChangedFlag) {
                checkGetParamCategoryFilter();
                that.refresh();
            }
            that.updateURLChangedFlag = true;
        });

        $scope.keys = function(obj) {
            return obj ? Object.keys(obj) : [];
        };

        addCommunityCategories();
        this.refreshCategories();
        flattenCategories();
        checkGetParamCategoryFilter();
        this.refresh();
        this.sortAlpha();

        /* remove links from descriptions */
        for(var key in this.views) {
            if(!this.views.hasOwnProperty(key)) continue;
            var desc = this.views[key].description;
            this.views[key].description = $("<div>" + desc + "</div>").find("a").replaceWith(function() { return this.innerHTML; }).end().html();
        }
    }]);

    views_app.filter("nbsp", function() {
        return function(text) {
            return String(text).replace(/&nbsp;/g, "");
        };
    });

    views_app.filter("viewURLPath", function() {
        return function(viewid, portal_name) {
            if(viewid == "literature") {
                return "/" + portal_name + "/literature/search";
            }
            return "/" + portal_name + "/data/source/" + viewid + "/search";
        };
    });

    angular.bootstrap(document.getElementById("viewsApp"), ["viewsApp"]);

});
