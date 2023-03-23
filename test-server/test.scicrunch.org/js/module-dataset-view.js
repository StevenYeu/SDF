$(function() {
    var app = angular.module("datasetViewApp", ["ui.bootstrap", "datasetsApp"])
        .run(function($rootScope) {
            $rootScope.datasetid = $("#dataset-id").val();
        })
        .config(function($locationProvider) {
            $locationProvider.html5Mode({"enabled": true, "requireBase": false});
        });

    app.controller("datasetViewController", ["$http", "$scope", "$rootScope", "$location", "$uibModal", "datasets", "$log", function($http, $scope, $rootScope, $location, $uibModal, datasets, $log) {
        var that = this;
        that.per_page = 20;
        that.page = 1;
        that.search_query = "";
        that.previous_search = "";
        that.selected_field = null;
        that.searching = false;
        that.all_records = [];
        that.csv_options = {
            ilx: false,
            template_only: false
        };

        this.search = function() {
            that.searching = true;
            var get_query = $location.search();

            var query = "";
            if(get_query["q"]) query = get_query["q"];
            that.search_query = query;
            that.previous_search = query;

            if(get_query["per_page"]) that.per_page = get_query["per_page"];

            var offset = 0;
            if(get_query["page"]) that.page = get_query["page"];
            offset = (that.page - 1) * that.per_page;

            var promise = datasets.search(that.dataset, query, offset, that.per_page);
            promise.then(function(response) {
                that.searching = false;
                updateWidths();
            });
        };

        this.downloadCSVUrl = function() {
            if(!that.dataset) {
                return "javascript:void(0)";
            }
            var url = "/php/dataset-csv.php?datasetid=" + that.dataset.id;
            if(that.csv_options.ilx) {
                url += "&ilx=1";
            }
            if(that.csv_options.template_only) {
                url += "&template-only=1";
            }
            return url;
        };

        this.changeQuery = function(q) {
            $location.search("q", q);
            $location.search("page", 1);
            that.search();
        };

        this.changePerPage = function(per_page) {
            $location.search("per_page", per_page);
            that.search();
        };

        this.changePage = function(page) {
            $location.search("page", page);
            that.search();
        };

        this.selectField = function(field) {
            var fields = that.dataset.template.fields;
            that.selected_field = field;
        };

        this.openGraphScatterPlot = function() {

            new Promise(function(fulfill, reject) {

                if(that.all_records.length == 0) {
                    var fields_data=[];
                    $http.get("/api/1/datasets/search?datasetid=" + that.dataset.id + "&count=10000")
                        .then(function(response) {
                            that.all_records = response.data.data;
                            //process
                            var rec = that.all_records.records;
                            for(var key in that.all_records.records){
                                if(that.all_records.records.hasOwnProperty(key)){
                                    for(var key2 in that.all_records.records[key]){
                                        if(that.all_records.records[key].hasOwnProperty(key2) && key2 != "_id"){
                                            if(!fields_data.hasOwnProperty(key2)){
                                                fields_data[key2] = {
                                                    "numerical": true,
                                                    "unique": []
                                                };
                                            }
                                            if(!fields_data[key2].unique.includes(rec[key][key2])){
                                                fields_data[key2].unique.push(rec[key][key2]);
                                            }

                                            if(!$.isNumeric(rec[key][key2])){
                                                fields_data[key2].numerical = false;
                                            }

                                        }
                                    }
                                }
                            }
                            that.all_records.fields_data =  fields_data;
                            fulfill();
                        }, function() {
                            reject();
                        });
                } else {
                    fulfill();
                }

            }).then(function(){

               var modalInstance = $uibModal.open({
                   animation: true,
                   templateUrl: "graph-scatter-plot.html",
                   controller: "graphScatterPlotModalController",
                   resolve: {
                       dataset: function() {
                           return that.dataset;
                       },
                       all_records: function(){
                           return that.all_records;
                       }
                   }
               });

            });
        };

        function updateWidths() {
            setTimeout(function() {
                $("#dataset-table-scroll-top div").width($("#dataset-table-wrapper table").width());
            }, 1000);
        }

        $("#dataset-table-scroll-top").on("scroll", function() {
            $("#dataset-table-wrapper").scrollLeft($("#dataset-table-scroll-top").scrollLeft());
        });
        $("#dataset-table-wrapper").on("scroll", function() {
            $("#dataset-table-scroll-top").scrollLeft($("#dataset-table-wrapper").scrollLeft());
        });

        $scope.$watch(function() {
            return $location.search();
        }, function(a) {
            if(that.dataset) that.search();
        });

        $http.get("/api/1/datasets/info?datasetid=" + $rootScope.datasetid)
            .then(function(response) {
                that.dataset = response.data.data;
                that.dataset.page = 1;
                that.dataset.data = {};
                that.dataset.template.fields.sort(function(a,b) {
                    if(a.position < b.position) return -1;
                    if(a.position > b.position) return 1;
                    return 0;
                });

                that.search();
            });

    }]);

    app.controller("graphScatterPlotModalController", ["$http", "$scope", "$log", "dataset", "all_records", "allDatasetRecords",function($http, $scope, $log, dataset, all_records, allDatasetRecords) {

        $scope.dataset = dataset;
        $scope.selectedFields = [];
        $scope.allRecords = all_records;
        $scope.plotType = "scatter";

        $scope.selectPlot = function(plot){
            $scope.selectedFields = [];
        };

        $scope.selectField = function(field) {
            $scope.errorText='';
            if($scope.selectedFields.length >= 2) return;
            $scope.selectedFields.push(field);
        };

        $scope.unselectField = function(field) {
            var index = $scope.selectedFields.indexOf(field);
            if(index == 0) {
                $scope.selectedFields = [];
            }
            else if(index !== -1){
                $scope.selectedFields.splice(index, 1);
            }
        };

        $scope.makeGraph = function() {
            if($scope.selectedFields.length < 2) return;
            plotChart();
        };

        function boxplotArray(boxplot_x) {

            var boxplot_y = [];
            var ret = [];

            // remove empty string values
            var ind = boxplot_x.indexOf("");
            if(ind !== -1){
                boxplot_x.splice(ind, 1);
            }

            for(var z=0; z< boxplot_x.length; z++){
                ret.push([]);
            }
            var all_values_sum = 0;
            var all_values_count = 0;
            for(var i = 0; i < boxplot_x.length; i++){
                //values of first selected field
                var name = boxplot_x[i];
                // separates into arrays with unique values of selected field
                boxplot_y[name] = $scope.allRecords.records.filter(function(a){return a[$scope.selectedFields[0]] == name;});
                // gets only the 2nd selected field, not whole obj
                boxplot_y[name] = boxplot_y[name].map(function(a) {return a[$scope.selectedFields[1]];});
                //sort
                boxplot_y[name] = boxplot_y[name].sort(function(a,b){return a-b;});

                /* values for getting the mean */
                for(var j = 0; j < boxplot_y[name].length; j++) {
                    all_values_sum += parseFloat(boxplot_y[name][j]);
                    all_values_count += 1;
                }

                //low, lower med, median, upper med, high
                ret[i].push(boxplot_y[name][0]);
                ret[i].push(boxplot_y[name][Math.floor(boxplot_y[name].length/2/2)]);
                ret[i].push(boxplot_y[name][Math.floor(boxplot_y[name].length/2)]);
                ret[i].push(boxplot_y[name][Math.floor(boxplot_y[name].length/2+boxplot_y[name].length/2/2)]);
                ret[i].push(boxplot_y[name][boxplot_y[name].length-1]);

            }

            // convert float, may need to do this earlier before sorting
            for(var j=0; j < ret.length; j++){
                for(var k=0; k<ret[0].length; k++){
                    ret[j][k]= parseFloat(ret[j][k]);
                }
            }

            /* get the mean */
            var mean = all_values_sum / all_values_count;

            return {series: ret, mean: mean};

        }

        function plotChart() {
            var seriesData = [];
            $scope.errorText = "";
            var boxplot_x = [];


            for(var i = 0; i < $scope.allRecords.records.length; i++) {
                var record = $scope.allRecords.records[i];

                if(record[$scope.selectedFields[0]] === undefined || record[$scope.selectedFields[1]] === undefined) {
                    continue;
                }
                if($scope.plotType == 'scatter'){
                    //format scatter data
                    seriesData.push([parseFloat(record[$scope.selectedFields[0]]), parseFloat(record[$scope.selectedFields[1]])]);
                }
            }

            // boxplot
            if($scope.plotType == 'box'){
                boxplot_x = $scope.allRecords.fields_data[$scope.selectedFields[0]].unique;
                var boxPlotData = boxplotArray(boxplot_x);
                seriesData = boxPlotData.series;
                var boxPlotMean = boxPlotData.mean;
            }

            if(seriesData.length == 0) {
                $scope.errorText = "No data shared between the two fields";
                return;
            }
            var chart = null;
            if($scope.plotType=='scatter'){
                $("#graph-scatter-plot").highcharts({
                    chart: {
                        type: "scatter",
                        zoomType: "xy"
                    },
                    title: {
                        text: 'Scatter Plot'
                    },
                    plotOptions: {
                        scatter: {
                            marker: {
                                radius: 1
                            }
                        }
                    },
                    yAxis:{
                        title:{
                            text: $scope.selectedFields[1]
                        }
                    },
                    xAxis:{
                        title:{
                            text: $scope.selectedFields[0]
                        }
                    },
                    series: [{
                        name: "Data",
                        color: "rgba(200, 20, 200, 08)",
                        data: seriesData
                    }]
                });
            }else if($scope.plotType=='box'){
                $("#graph-scatter-plot").highcharts({
                    chart: {
                        type: 'boxplot'
                    },

                    title: {
                        text: 'Box Plot'
                    },

                    legend: {
                        enabled: false
                    },

                    xAxis: {
                        categories: boxplot_x,
                        title: {
                            text: $scope.selectedFields[0]
                        }
                    },

                    yAxis: {
                        title: {
                            text: $scope.selectedFields[1]
                        },
                        plotLines: [{
                            value: boxPlotMean,
                            color: 'red',
                            width: 1
                        }]
                    },

                    series: [{
                        data: seriesData,
                        tooltip: {
                            headerFormat: '<em>Data for {point.key}</em><br/>'
                        }
                    }]


                });
            }
        }

    }]);

    //service to get/set data
    app.service('allDatasetRecords',function(){
        var all_records = ['asdf'];
        return{
            getRecords: function(){
                return all_records;
            },
            setRecords: function(value){
                all_records = value;
            }
        };
    });

    angular.bootstrap(document.getElementById("dataset-view-app"), ["datasetViewApp"]);
});
