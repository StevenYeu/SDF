$(function() {
    var singleItemApp = angular.module("singleItemApp", ["resourceDirectives", "resourceApp", "resolverApp", "ui.bootstrap"])
        .config(function($locationProvider) {
            $locationProvider.html5Mode({enabled: true, requireBase: false});
        })
        .run(function($rootScope, $http) {
            $rootScope.viewid = $("#search-single-item-view").val();
            $rootScope.rid = $("#search-single-item-rrid").val();
            $rootScope.tab = $("#search-single-item-mode").val();
            $rootScope.organization_mentions_rrids = $("#search-single-item-organization-mentions-rrids").val();
            $rootScope.logInCheck();
        });

    singleItemApp.controller("singleItemController", ["$log", "$rootScope", "$http", "$scope", function($log, $rootScope, $http, $scope) {
        var that = this;
        // this.mode = $("#search-single-item-mode").val();
        this.proper_citation = $("#search-single-item-proper-citation").val();
        this.show_citation = false;
        this.show_follow = false;
        this.alerts = {
            copy_rrid: [false,false],
            copy_citation: false
        };

        this.toggleCitation = function() {
            that.show_citation = !that.show_citation;
        };

        this.copyCitation = function() {
            copyToClipboard(that.proper_citation);
            that.alerts.copy_citation = true;
            setTimeout(function() {
                that.alerts.copy_citation = false;
                $scope.$apply();
            }, 2000);
        };

        // copy individual rrid -- Vicky-2019-3-15
        this.copyRRID = function(idx) {
            var tmp = $rootScope.rid.split(",");
            //copyToClipboard($rootScope.rid);
            copyToClipboard(tmp[idx]);
            that.alerts.copy_rrid[idx] = true;
            setTimeout(function() {
                that.alerts.copy_rrid[idx] = false;
                $scope.$apply();
            }, 2000);
        };

        this.toggleFollow = function() {
            that.show_follow = !that.show_follow;
        };

    }]);

    singleItemApp.factory("mentions", ["$log", "$rootScope", "$http", "$q", function($log, $rootScope, $http, $q) {
        var mentions = {};
        mentions.per_page = 100;
        mentions.page = 1;
        mentions.search_filters_display = [];
        mentions.search_filters = [];
        mentions.mentions = {};
        mentions.location_facets = {};
        mentions.counts_by_year = [];
        mentions.mode = "all";
        mentions.set_chart = false;
        mentions.errors = {};
        mentions.all_count = 0;
        mentions.rrid_count = 0;

        mentions.getRRIDMentions = function(options) {
            resetRRIDMentions();
            var offset = (mentions.page - 1) * mentions.per_page;

            var refresh_chart = true;
            if(options) {
                if(options.no_refresh_chart) {
                    refresh_chart = false;
                }
            }

            if($rootScope.tab == "info" || $rootScope.tab == "organizations" || $rootScope.tab == "organization-mentions") {
                /* Total Mentions for this organization and its child organization(s) and grandchild organization(s) */
                var post_data_organization_mentions = {
                    size: mentions.per_page,
                    from: offset,
                    query: {
                        bool: {
                            should: []
                        }
                    },
                    sort: [
                        {
                            "dc.publicationYear": {
                                order: "desc"
                            }
                        },
                        "_score"
                    ]
                };

                var organization_mentions_rrids = $rootScope.organization_mentions_rrids.split(",");

                for(var i = 0; i < organization_mentions_rrids.length; i++) {
                    post_data_organization_mentions.query.bool.should.push(
                      {match_phrase: {"resourceMentions.rrid.keyword": {query: organization_mentions_rrids[i]}}}
                    );
                    post_data_organization_mentions.query.bool.should.push(
                      {match_phrase: {"rridMentions.rrid.keyword": {query: organization_mentions_rrids[i]}}}
                    );
                    post_data_organization_mentions.query.bool.should.push(
                      {match_phrase: {"filteredMentions.rrid.keyword": {query: organization_mentions_rrids[i]}}}
                    );
                }

                if($rootScope.tab == "info" || $rootScope.tab == "organizations") {
                    $http.post("/api/1/elastic/RIN_Mentions_pr/data/_search", post_data_organization_mentions)
                        .then(function(response) {
                            var results = response.data.data;
                            $rootScope.organization_mentions_total_count = response.data.hits.total;
                        });
                }
            }

            if($rootScope.tab == "organization-mentions") {
                var post_data = JSON.parse(JSON.stringify(post_data_organization_mentions));
            } else {
                var post_fields = ["rridMentions.rrid.keyword", "resourceMentions.rrid.keyword", "filteredMentions.rrid.keyword"];
                if(mentions.mode == "rrid") {
                    post_fields = ["rridMentions.rrid.keyword"];
                } else if(mentions.mode == "resource") {
                    post_fields = ["resourceMentions.rrid.keyword", "filteredMentions.rrid.keyword"];
                }

                var post_data = {
                    size: mentions.per_page,
                    from: offset,
                    query: {
                        bool: {
                            must: [
                                // {
                                //     query_string: {
                                //         fields: post_fields,
                                //         query: $rootScope.rid.replace(":", "\\:")
                                //     }
                                // }
                            ]
                        }
                    },
                    aggs: {
                        publicationYear: {
                            terms: {
                                field: "dc.publicationYear",
                                size: 100
                            }
                        },
                        cities: {
                            terms: {
                                field: "dc.creators.locations.city",
                                size: 100
                            }
                        },
                        regions: {
                            terms: {
                                field: "dc.creators.locations.region",
                                size: 100
                            }
                        },
                        place_names: {
                            terms: {
                                field: "dc.creators.locations.name",
                                size: 100
                            }
                        },
                    },
                    sort: [
                        {
                            "dc.publicationYear": {
                                order: "desc"
                            }
                        },
                        "_score"
                    ]
                };

                /* add search filters */
                mentions.search_filters_display = [];
                if(mentions.search_filters.publicationYear) {
                    post_data.query.bool.must.push({
                        multi_match: {
                            fields: ["dc.publicationYear"],
                            query: mentions.search_filters.publicationYear
                        }
                    });
                    mentions.search_filters_display.push({
                        name: "publicationYear",
                        display_name: "Publication Year",
                        value: mentions.search_filters.publicationYear
                    });
                }
                if(mentions.search_filters.cities) {
                    post_data.query.bool.must.push({
                        multi_match: {
                            fields: ["dc.creators.locations.city"],
                            query: mentions.search_filters.cities
                        }
                    });
                    mentions.search_filters_display.push({
                        name: "cities",
                        display_name: "City",
                        value: mentions.search_filters.cities
                    });
                }
                if(mentions.search_filters.regions) {
                    post_data.query.bool.must.push({
                        multi_match: {
                            fields: ["dc.creators.locations.region"],
                            query: mentions.search_filters.regions
                        }
                    });
                    mentions.search_filters_display.push({
                        name: "regions",
                        display_name: "Region",
                        value: mentions.search_filters.regions
                    });
                }
                if(mentions.search_filters.place_names) {
                    post_data.query.bool.must.push({
                        multi_match: {
                            fields: ["dc.creators.locations.name"],
                            query: mentions.search_filters.place_names
                        }
                    });
                    mentions.search_filters_display.push({
                        name: "place_names",
                        display_name: "Name",
                        value: mentions.search_filters.place_names
                    });
                }
                if(mentions.search_filters.location) {
                    post_data.query.bool.must.push({
                        geo_distance: {
                            distance: "10km",
                            "dc.creators.locations.location": {
                                lat: mentions.search_filters.location.coords.latitude,
                                lon: mentions.search_filters.location.coords.longitude
                            }
                        }
                    });
                    mentions.search_filters_display.push({
                        name: "location",
                        display_name: "Near you",
                        value: null
                    });
                }

                // push rrids to query -- Vicky-2019-3-20
                var rrids = $rootScope.rid.split(",");

                // copy post_data to post_data_rrid1 and post_data_rrid1 to prepare another ES queries -- Vicky-2019-7-12
                if(rrids.length == 2) {
                    var post_data_rrid1 = JSON.parse(JSON.stringify(post_data));
                    var post_data_rrid2 = JSON.parse(JSON.stringify(post_data));
                    post_data_rrid1.query.bool.must.push({
                        multi_match: {
                            query: rrids[0],
                            fields: post_fields
                        }
                    });
                    post_data_rrid2.query.bool.must.push({
                        multi_match: {
                            query: rrids[1],
                            fields: post_fields
                        }
                    });

                    // co-mentions summary, generated mentions count for rrid1 and rrid2
                    $http.post("/api/1/elastic/RIN_Mentions_pr/data/_search", post_data_rrid1)
                        .then(function(response) {
                            var results = response.data.data;
                            mentions.rrid1_total_count = response.data.hits.total;
                        });

                    $http.post("/api/1/elastic/RIN_Mentions_pr/data/_search", post_data_rrid2)
                        .then(function(response) {
                            var results = response.data.data;
                            mentions.rrid2_total_count = response.data.hits.total;
                        });
                }

                for(var i = 0; i < rrids.length; i++) {
                    post_data.query.bool.must.push({
                        multi_match: {
                            query: rrids[i],
                            fields: post_fields
                        }
                    });
                }
            }

            $http.post("/api/1/elastic/RIN_Mentions_pr/data/_search", post_data)
                .then(function(response) {
                    var results = response.data.data;
                    mentions.total_count = response.data.hits.total;
                    if (mentions.mode == "all") mentions.all_count = mentions.total_count;
                    else mentions.rrid_count = mentions.total_count;
                    mentions.mentions = response.data.hits.hits;
                    if(mentions.page_links.length == 0) {
                        mentions.page_links = makePageLinks(mentions.total_count, mentions.per_page);
                    }
                    if(response.data.aggregations && response.data.aggregations.publicationYear.buckets) {
                        mentions.counts_by_year = response.data.aggregations.publicationYear.buckets;
                        mentions.location_facets.place_names = response.data.aggregations.place_names.buckets;
                        mentions.location_facets.regions = response.data.aggregations.regions.buckets;
                        mentions.location_facets.cities = response.data.aggregations.cities.buckets;
                        if(refresh_chart) {
                            refreshYearsChart();
                        }
                    }
                    tagLocationFilters();
                });
        };

        function tagLocationFilters() {
            for(var i = 0; i < mentions.mentions.length; i++) {
                mentions.mentions[i].matching_researchers = [];
                for(var j = 0; j < mentions.mentions[i]._source.dc.creators.length; j++) {
                    var creator = mentions.mentions[i]._source.dc.creators[j];
                    if(!creator.locations || creator.locations.length == 0) {
                        continue;
                    }
                    for (var n = 0; n < creator.locations.length; n++) {  //creator has multiple locations -- Vicky-2019-6-12
                        //var matches = [];
                        var match = false;

                        if(mentions.search_filters.location) {
                            var distance = crowDistance(mentions.search_filters.location.coords.latitude, mentions.search_filters.location.coords.longitude, creator.locations[n].location.lat, creator.locations[n].location.lon);
                            if(distance < 15) { // give extra 5 km in case of error
                                //matches.push("nearby");
                                match = true;
                            }
                        }

                        if(mentions.search_filters.place_names && mentions.search_filters.place_names == creator.locations[n].name) {
                            match = true;
                            //matches.push(creator.locations[0].name);
                        }
                        if(mentions.search_filters.cities && mentions.search_filters.cities == creator.locations[n].city) {
                            match = true;
                            //matches.push(creator.locations[0].city);
                        }
                        if(mentions.search_filters.regions && mentions.search_filters.regions == creator.locations[n].region) {
                            match = true;
                            //matches.push(creator.locations[0].region);
                        }
                        //if(matches.length > 0) {
                        if (match) {
                            mentions.mentions[i].matching_researchers.push({
                                creator: creator,
                                //matches: matches
                                matches: creator.locations[n]
                            });
                        }
                    }
                }
            }
        }

        function makePageLinks(count, per_page) {
            var page = 0;
            var page_links = [];
            for(var i = 0; i < count; i += per_page) {
                page += 1;
                var page_link = {
                    page: page,
                    start: i+1
                };
                if(i + per_page >= count) {
                    page_link.end = count;
                } else {
                    page_link.end = i + per_page;
                }
                page_links.push(page_link);
            }
            return page_links;
        }

        mentions.rridMentionsByLocation = function() {
            var current_time = Math.floor((new Date()).getTime() / 1000);
            var old_coords = JSON.parse(localStorage.getItem("geolocation"));
            if(old_coords && (old_coords.timestamp + 86400) > current_time) {  // seconds in day
                mentions.search_filters.location = old_coords;
                mentions.getRRIDMentions();
                mentions.errors.location = "";
            } else if(navigator.geolocation) {
                mentions.errors.location = "Getting location...";
                navigator.geolocation.getCurrentPosition(function(position) {
                    mentions.errors.location = "";
                    var new_coords = {
                        coords: {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        },
                        timestamp: current_time
                    };
                    localStorage.setItem("geolocation", JSON.stringify(new_coords));
                    mentions.search_filters.location = new_coords;
                    mentions.getRRIDMentions();
                }, function() {
                    mentions.errors.location = "Could not get your current location";
                });
            } else {
                mentions.errors.location = "Location not supported";
            }
        };

        mentions.changeMode = function(mode) {
            mentions.mode = mode;
            mentions.page = 1;
            mentions.getRRIDMentions();
        };

        mentions.deleteRRIDMentionFilter = function(filter) {
            delete(mentions.search_filters[filter]);
            mentions.getRRIDMentions();
        };

        // modified changePage -- Vicky-2019-3-20
        mentions.changePage = function(page, page_length) {
            if (page <= 0) mentions.page = 1;
            else if (page > page_length) mentions.page = page_length;
            else mentions.page = page;
            mentions.pageVal = "";
            mentions.getRRIDMentions({no_refresh_chart: true});
        };

        function resetRRIDMentions() {
            mentions.mentions = {};
            mentions.total_count = -1;
            mentions.rrid1_total_count = -1;
            mentions.rrid2_total_count = -1;
            mentions.page_links = [];
        }

        function refreshYearsChart() {
            if(!mentions.set_chart) {
                return;
            }
            if(mentions.counts_by_year.length == 0) {
                if(mentions.year_chart) {
                    mentions.year_chart.destroy();
                }
                return;
            }
            var year_chart_data = [];
            for(var i = 0; i < mentions.counts_by_year.length; i++) {
                if(!mentions.counts_by_year[i].key) {
                    continue;
                }
                year_chart_data.push({
                    year: parseInt(mentions.counts_by_year[i].key),
                    count: mentions.counts_by_year[i].doc_count
                });
            }

            year_chart_data.sort(function(a, b) {
                return a.year - b.year;
            });

            var first_year = year_chart_data[0].year;
            var last_year = year_chart_data[year_chart_data.length - 1].year;

            var years_map = {}
            for(var i = 0; i < year_chart_data.length; i++) {
                years_map[year_chart_data[i].year] = year_chart_data[i].count;
            }

            var counts = [];
            for(var i = first_year; i <= last_year; i++) {
                if(years_map.hasOwnProperty(i)) {
                    counts.push(years_map[i]);
                } else {
                    counts.push(0);
                }
            }

            mentions.year_chart = Highcharts.chart("rrid-report-mentions-graph", {
                title: {
                    text: "Articles by Year"
                },
                xAxis: {
                    allowDecimals: false
                },
                yAxis: {
                    title: {
                        text: "Mentions Count"
                    }
                },
                plotOptions: {
                    series: {
                        pointStart: first_year
                    }
                },
                series: [
                    {
                        name: "Mention Count",
                        data: counts
                    }
                ]
            });
        }

        mentions.autocompleteValues = function(val, records) {
            if(!records) return;
            if(!val) {
                return records.filter(function(rec) {
                    if(rec.key) return true;
                    return false;
                });
            }
            var matches = [];
            for(var i = 0; i < records.length; i++) {
                if(records[i].key.toLowerCase().indexOf(val.toLowerCase()) !== -1) {
                    matches.push(records[i]);
                }
            }
            return matches.sort((a, b) => (a.key < b.key) ? 1 : -1); // sort matche records based on key
            //return matches;
        };

        mentions.getTopComentions = function() {
            var n_comentions = 6; // 5 + 1 for self which will be the most common
            var n_filteredMentions = 1769;
            var post_fields = ["rridMentions.rrid.keyword", "resourceMentions.rrid.keyword"];
            var post_data = {
                size: 0,
                query: {
                    bool: {
                        must: [
                            {
                                query_string: {
                                    fields: post_fields,
                                    query: $rootScope.rid.replace(/:/g, "\\:")
                                }
                            }
                        ]
                    }
                },
                aggs: {
                    rridMentions: {
                        terms: {
                            field: "rridMentions.rrid.keyword",
                            size: n_comentions
                        }
                    },
                    resourceMentions: {
                        terms: {
                            field: "resourceMentions.rrid.keyword",
                            size: n_comentions
                        }
                    },
                    filteredMentions: {
                        terms: {
                            field: "filteredMentions.rrid.keyword",
                            size: n_filteredMentions
                        }
                    }
                }
            };
            return $q(function(resolve, reject) {
                $http.post("/api/1/elastic/RIN_Mentions_pr/data/_search", post_data)
                    .then(function(response) {
                        /* get the rrid and mention counts */
                        //var resource_mentions = response.data.aggregations.resourceMentions.buckets;
                        //var rrid_mentions = response.data.aggregations.rridMentions.buckets;
                        var resource_mentions = [];
                        var rrid_mentions = [];
                        var filtered_mentions = [];
                        var mention_counts = {};

                        for ( var i in response.data.aggregations.filteredMentions.buckets) {
                            filtered_mentions.push(response.data.aggregations.filteredMentions.buckets[i].key);
                        }

                        for (var i in response.data.aggregations.resourceMentions.buckets) {
                            if (filtered_mentions.indexOf(response.data.aggregations.resourceMentions.buckets[i].key) == -1) {
                                resource_mentions.push(response.data.aggregations.resourceMentions.buckets[i]);
                            }
                        }

                        for (var i in response.data.aggregations.rridMentions.buckets) {
                            if (filtered_mentions.indexOf(response.data.aggregations.rridMentions.buckets[i].key) == -1) {
                                rrid_mentions.push(response.data.aggregations.rridMentions.buckets[i]);
                            }
                        }

                        combineMentions(resource_mentions, mention_counts, "resource_count", $rootScope.rid);
                        combineMentions(rrid_mentions, mention_counts, "rrid_count", $rootScope.rid);

                        /* get the names and information of the comentions */
                        var post_data2 = {
                            size: (n_comentions * 2) - 2,
                            query: {bool: {should: []}}
                        };
                        for(var rrid in mention_counts) {
                            var es_rrid = rrid.replace(":", "\\:");
                            post_data2.query.bool.should.push({
                                query_string: {
                                    fields: ["rrid.curie"],
                                    query: es_rrid
                                }
                            });
                        }
                        $http.post("/api/1/elastic/*_pr/rin/_search", post_data2)
                            .then(function(response2) {
                                var all_results = [];
                                for(var i = 0; i < response2.data.hits.hits.length; i++) {
                                    var doc = response2.data.hits.hits[i];
                                    if(mention_counts[doc._source.rrid.curie] === undefined) {
                                        continue;
                                    }
                                    var counts = mention_counts[doc._source.rrid.curie];
                                    counts.name = doc._source.item.name;
                                    counts.rrid = doc._source.rrid.curie;
                                    counts.viewid = indexToViewID(doc._index);
                                    all_results.push(counts);
                                    mention_counts[doc._source.rrid.curie] = undefined; // prevent repeats
                                }
                                resolve(all_results);
                            }, function() {
                                reject();
                            })

                        function combineMentions(results, all, type, rrid) {
                            for(var i = 0; i < results.length; i++) {
                                if(results[i].key == rrid) {
                                    continue;
                                }
                                if(!all.hasOwnProperty(results[i].key)) {
                                    all[results[i].key] = {total: 0};
                                }
                                all[results[i].key].total += results[i].doc_count;
                                all[results[i].key][type] = results[i].doc_count;
                            }
                        }

                        function indexToViewID(index) {
                            if(index.startsWith("scr_005400")) return "nlx_144509-1";
                            if(index.startsWith("scr_013869")) return "SCR_013869-1";
                            if(index.startsWith("scr_006397")) return "nif-0000-07730-1";
                            if(index.startsWith("scr_001421")) return "nlx_154697-1";
                            if(index.startsWith("scr_003115")) return "nlx_154697-1";
                        }
                    }, function() {
                        reject();
                    });
            });
        };

        // return distance between two points in km
        // see: https://stackoverflow.com/a/18883819/3704042
        function crowDistance(lat1, lon1, lat2, lon2) {
            var kmR = 6371;
            var d_lat = toRad(lat2 - lat1);
            var d_lon = toRad(lon2 - lon1);
            var lat1 = toRad(lat1);
            var lat2 = toRad(lat2);

            var a = Math.pow(Math.sin(d_lat / 2), 2) + (Math.pow(Math.sin(d_lon / 2), 2) * Math.cos(lat1) * Math.cos(lat2));
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            var d = kmR * c;
            return d;
        }

        function toRad(v) {
            return v * Math.PI / 180;
        }

        return mentions;
    }]);

    singleItemApp.controller("infoController", ["$log", "$rootScope", "$http", "mentions", function($log, $rootScope, $http, mentions) {
        var that = this;
        this.mentions = mentions;

        this.submitMentionFilter = function() {
            var filters = [];
            if(mentions.search_filters.place_names) {
                filters.push("mentionfilter=organization:" + mentions.search_filters.place_names);
            }
            if(mentions.search_filters.cities) {
                filters.push("mentionfilter=city:" + mentions.search_filters.cities);
            }
            if(mentions.search_filters.regions) {
                filters.push("mentionfilter=region:" + mentions.search_filters.regions);
            }
            if(mentions.search_filters.publicationYear) {
                filters.push("mentionfilter=year:" + mentions.search_filters.publicationYear);
            }
            window.location.href = window.location.origin + window.location.pathname + "/mentions?" + filters.join("&") + "#mentions-list";
        };

        this.submitMentionFilterLocation = function() {
            window.location.href = window.location.origin + window.location.pathname + "/mentions?mentionfilter=location#mentions-list"
        };

        mentions.per_page = 3;
        mentions.getRRIDMentions();
    }]);

    singleItemApp.controller("mentionsController", ["$log", "$rootScope", "$http", "$location", "mentions", function($log, $rootScope, $http, $location, mentions) {
        var that = this;
        this.mentions = mentions;
        mentions.set_chart = true;
        this.comentions = [];

        this.submitMentionFilter = function() {
            mentions.page = 1;
            mentions.getRRIDMentions();
        };

        this.submitMentionFilterLocation = function() {
            mentions.page = 1;
            mentions.rridMentionsByLocation();
        };

        function setMentionSearchFiltersFromQuery() {
            if($location.search()) {
                var query = $location.search();
                var mentionfilter = query.mentionfilter;
                if(mentionfilter == "location") {
                    mentions.rridMentionsByLocation();
                    return;
                }
                if(mentionfilter) {
                    if(typeof mentionfilter == "string") {
                        mentionfilter = [mentionfilter];
                    }
                    for(var i = 0 ; i < mentionfilter.length; i++) {
                        var query_split = mentionfilter[i].split(":");
                        if(query_split.length != 2) {
                            continue;
                        }
                        if(query_split[0] == "organization") {
                            mentions.search_filters.place_names = query_split[1];
                        }
                        if(query_split[0] == "city") {
                            mentions.search_filters.cities = query_split[1];
                        }
                        if(query_split[0] == "region") {
                            mentions.search_filters.regions = query_split[1];
                        }
                        if(query_split[0] == "year") {
                            mentions.search_filters.publicationYear = query_split[1];
                        }
                    }
                }
            }
            mentions.getRRIDMentions();
        }

        mentions.getTopComentions()
            .then(function(comentions) {
                comentions.sort(function(a, b) {
                    if(a.total > b.total) return -1;
                    if(a.total < b.total) return 1;
                    return 0;
                });
                that.comentions = comentions;
            });

        setMentionSearchFiltersFromQuery();
    }]);

    angular.bootstrap(document.getElementById("single-item-app"), ["singleItemApp"]);
});
