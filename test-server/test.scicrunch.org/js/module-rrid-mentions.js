$(function() {
    var app = angular.module("rridMentionsApp", ["ui.bootstrap", "errorApp"])
        .config(function($locationProvider) {
            $locationProvider.html5Mode({enabled: true, requireBase: false});
        });

    app.controller("rridMentionsController", ["$scope", "$http", "$location", "$log", function($scope, $http, $location, $log) {
        var that = this;
        this.per_page = 20;
        this.query = "";
        this.loading = false;
        this.initialLookupLock = true;
        this.facets = [];
        this.facetDropdownFilter = "";

        this.requestQuery = function() {
            that.page = 1;
            rridMentionRequest();
        };

        this.requestChangePage = function() {
            if(that.initialLookupLock) return;
            rridMentionRequest();
        };

        this.requestFacet = function(facet, value) {
            that.page = 1;
            toggleFacet(facet, value);
            rridMentionRequest();
        };

        this.getGetQueryParams = function() {
            var params = "q=" + that.query;

            var facets = getFacetsArray();
            for(var i = 0; i < facets.length; i++) {
                params += "&facets[]=" + facets[i];
            }

            return params;
        };

        this.toggleFacetDropdown = function(open) {
            if(open) {
                that.facetDropdownFilter = "";
            }
        };

        this.isFacetInUse = function(facet, value) {
            for(var i = 0; i < that.facets.length; i++) {
                if(that.facets[i].facet == facet && that.facets[i].value == value) {
                    return true;
                }
            }
            return false;
        };

        function getFacetsArray() {
            var facets = [];
            for(var i = 0; i < that.facets.length; i++) {
                facets.push(that.facets[i].facet + "|" + that.facets[i].value);
            }
            return facets;
        }

        function rridMentionRequest() {
            var offset = (that.page - 1) * that.per_page;
            that.loading = true;
            loadedPage = that.page;
            var url = "/api/1/rrid-mentions?" + that.getGetQueryParams() + "&offset=" + offset + "&count=" + that.per_page;
            var facets = getFacetsArray();

            $http.get(url)
                .then(function(response) {
                    that.loading = false;
                    processResults(response.data.data["rrid-mentions"]);
                    that.results_count = response.data.data["count"];
                    that.facet_counts = response.data.data["facets"];
                    if(that.initialLookupLock) {    // required because the page keeps switching to page 1
                        if(that.page != loadedPage) {
                            that.page = loadedPage;
                        }
                        that.initialLookupLock = false;
                    }
                }, function(e) {
                    that.loading = false
                });
            $location.search({
                q: that.query,
                page: that.page,
                facets: facets
            });
        };

        function toggleFacet(facet, value) {
            for(var i = 0; i < that.facets.length; i++) {
                if(that.facets[i].facet == facet && that.facets[i].value == value) {
                    that.facets.splice(i, 1);
                    return;
                }
                if(that.facets[i].facet == facet) {
                    that.facets[i].value = value;
                    return;
                }
            }
            that.facets.push({facet: facet, value: value});
        }

        function parseGetQuery() {
            var query = $location.search();
            if(query.q) {
                that.query = query.q;
            }
            if(query.page) {
                that.page = query.page;
            } else {
                that.page = 1;
            }
            if(query.facets) {
                var facets = [];
                if(Array.isArray(query.facets)) {
                    facets = query.facets;
                } else {
                    facets.push(query.facets);
                }
                for(var i = 0; i < facets.length; i++) {
                    var facet = facets[i].split("|");
                    if(facet.length != 2) continue;
                    that.facets.push({facet: facet[0], value: facet[1]});
                }
            }
            rridMentionRequest();
        }

        function processResults(results) {
            that.results  = [];
            for(var i = 0; i < results.length; i++) {
                var record = {
                    "rrid": results[i].rrid,
                    "name": results[i].name,
                    "pmid": results[i].pmid.replace(/^PMID:/i, ""),
                    "snippet": results[i].text_quote_selector,
                    "grants": {},
                    "provider": results[i].provider
                };
                if(results[i].literature_record) {
                    record.paper = results[i].literature_record.title;
                    record.journal = results[i].literature_record.journal_name;
                    record.publication_year = results[i].literature_record.publication_year;

                    /* create grant info */
                    for(var j = 0; j < results[i].literature_record.grant_info.length; j++) {
                        var grant = results[i].literature_record.grant_info[j];
                        if(!record.grants.hasOwnProperty(grant.agency)) {
                            record.grants[grant.agency] = [];
                        }
                        record.grants[grant.agency].push(grant.identifier);
                    }
                } else {
                    record.paper = results[i].pmid;
                }

                that.results.push(record);
            }
        }

        parseGetQuery();
    }]);

    angular.bootstrap(document.getElementById("rrid-mentions-app"), ["rridMentionsApp"]);
});
