(function() {
    var app = angular.module("scDataApp", ["ui.bootstrap"]);

    app.controller("dataController", ["$http", "$log", function($http, $log) {
        var that = this;
        this.types = [];
        this.selectedType = "";
        this.data = [];
        this.dataCount = 0;

        this.page = 1;
        this.per_page = 20;
        this.sortField = "id";
        this.sortDir = "asc";
        this.query = "";

        this.getTypes = function() {
            $http.get("/api/1/scicrunch-data/types")
                .then(function(response) {
                    that.types = response.data.data;
                });
        };

        this.updateType = function() {
            that.page = 1;
            that.per_page = 20;
            that.sortField = "id";
            that.sortDir = "asc";
            that.query = "";
            that.search();
        };

        this.search = function() {
            var offset = (that.page - 1) * that.per_page;
            var url = "/api/1/scicrunch-data/type" +
                "?type=" + that.selectedType +
                "&count=" + that.per_page +
                "&offset=" + offset + 
                "&sort-field=" + that.sortField +
                "&sort=" + that.sortDir;
            if(that.query) url += "&q=" + that.query;
            $http.get(url)
                .then(function(response) {
                    that.data = response.data.data.results;
                    that.dataCount = response.data.data.count;
                });
        };

        this.sort = function(field, dir) {
            that.sortField = field;
            that.sortDir = dir;
            that.search();
        };

        this.getTypes();
    }]);

    angular.bootstrap(document.getElementById("scicrunch-data-app"), ["scDataApp"]);
}());
