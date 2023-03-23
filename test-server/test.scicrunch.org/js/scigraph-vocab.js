(function(){

    var vocab_app = angular.module("vocabApp", ["errorApp"])
        .config(function($locationProvider){
            $locationProvider.html5Mode({enabled: true, requireBase: false});
        })
        .run(function($rootScope, $location){
            var path = $location.path().split("/");
            if(path.length > 3){
                $rootScope.init_search = path[3];
            }
        });

    vocab_app.controller("searchCtrl", ["$scope", "$rootScope", "$http", "$location", "$log", function($scope, $rootScope, $http, $location, $log){
        var that = this;
        var arrayMembers = ["labels", "synonyms", "acronyms", "abbreviations", "definitions"];
        var base_url = "/forms/scigraph-vocab-search.php?";
        var limit = 50;
        function array2String(arr){
            return arr.join(", ");
        }
        function stringifyArrays(data){
            for(var i = 0; i < data.length; i++){
                var row = data[i];
                for(var key in row){
                    if(row.hasOwnProperty(key)){
                        if(arrayMembers.indexOf(key) !== -1){
                            row[key] = array2String(row[key]);
                        }
                    }
                }
                data[i] = row;
            }
            return data;
        }

        this.q = "";
        this.q_prev = "";
        this.results = {"Term": [], "Search": []};
        this.table_titles = ["Curie", "Labels", "Synonyms", "Acronyms", "Abbreviations", "Definitions", "Iri"];
        this.table_fields = ["curie", "labels", "synonyms", "acronyms", "abbreviations", "definitions", "iri"];
        this.search = function(){
            var q = that.q;
            if(!q) return;
            that.q_prev = that.q;
            that.q = "";
            $http.get(base_url + "q=" + q + "&type=term")
                .then(function(response){
                    that.results.Term = stringifyArrays(response.data);
                }, function(response){
                    that.results.Term = [];
                });
            $http.get(base_url + "q=" + q + "&type=search&limit=" + limit)
                .then(function(response){
                    that.results.Search = stringifyArrays(response.data);
                }, function(response){
                    that.results.Search = [];
                });
            var path = $location.path().split("/");
            if(path.length <= 3) path.push(q);
            else path[3] = q;
            $location.path(path.join("/"));
        };

        if($rootScope.init_search !== undefined){
            this.q = $rootScope.init_search;
            this.search();
        }
    }]);

}());
