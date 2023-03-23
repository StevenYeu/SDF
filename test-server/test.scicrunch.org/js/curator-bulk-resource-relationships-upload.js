(function(){
    var bulk_resource_relationships_upload_app = angular.module("bulkResourceRelationshipsUploadApp", ["ui.bootstrap"]).
        run(function($rootScope, $http) {
            $http.get("/api/1/user/info").
                then(function(response) {
                    $rootScope.logged_in = response.data.data.logged_in;
                }, function (response) {
                    $rootScope.logged_in = false;
                });
        });

    bulk_resource_relationships_upload_app.factory("upload", ["$http", "$log", function($http, $log) {
        var upload = {};
        upload.resources = [];
        upload.skipped_rows = [];
        upload.resource_owner = {};
        var concurrency = 28;

        function CSVtoArray(csv_data, delim) {
            var delimiter = delim || ",";
            var objPattern = new RegExp(
                (
                // Delimiters
                "(\\" + delimiter + "|\\r?\\n|\\r|^)" +
                // Quoted fields
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
                // Standard fields
                "([^\"\\" + delimiter + "\\r\\n]*))"
                ), "gi"
            );
            var arrData = [[]];
            var arrMatches = null;
            while(arrMatches = objPattern.exec(csv_data)) {
                var strMatchedDelimiter = arrMatches[1];
                if(strMatchedDelimiter.length && strMatchedDelimiter !== delimiter) {
                    arrData.push([]);
                }
                var strMatchedValue;
                if(arrMatches[2]) {
                    strMatchedValue = arrMatches[2].replace(new RegExp("\"\"", "g"), "\"");
                } else {
                    strMatchedValue = arrMatches[3];
                }
                arrData[arrData.length - 1].push(strMatchedValue);
            }
            return arrData;
        };

        function updateResource(index, resources) {
            var resource = resources[index];
            resource.message_status = "updating";
            /* if the resource is to be added */
            if(resource.data["Action"].toLowerCase() == "add"){
                $http({
                    method: "POST",
                    url: "/api/1/resource/rel/add/" + resource.data.id1,
                    headers: {"Content-Type": "application/x-www-form-urlencoded"},
                    transformRequest: function(obj){
                        var str = [];
                        for(var p in obj) str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },
                    data: {id1: resource.data.id1, id2: resource.data.id2, type: "res", relationship: resource.data.reltype}
                }).
                    then(function(response) {
                            resource.message_status = "added";
                            resource.message = "Added";
                            notifyNext(index, resources);
                    }, function(response) {
                        resource.message_status = "error";
                        resource.message = response.data.errormsg;
                        notifyNext(index, resources);
                    });
            /* if there are no changes to resource */
            } else {
                resource.message_status = "skipped";
                resource.message = "Skipped";
                notifyNext(index, resources);
            }
        }

        function notifyNext(index, resources) {
            var next = index + concurrency;
            if(next < resources.length) updateResource(next, resources);
        }

        upload.parseCSV = function(csv_data) {
            upload.resources = [];
            upload.skipped_rows = [];
            upload.errmsg = "";

            var parsed_data = CSVtoArray(csv_data, ",");
            if(parsed_data.length < 2) return;
            var header = parsed_data[0];
            upload.header = header;
            for(var i = 1; i < parsed_data.length; i++) {
                var resource = {};
                resource.data = {};
                var row = parsed_data[i];
                if(header.length > row.length) {
                    upload.skipped_rows.push(i + 1);
                    continue;
                }
                for(var j = 0; j < header.length; j++) {
                    var value = row[j];
                    if(value === undefined) value = "";
                    resource.data[header[j]] = value;
                }
                upload.resource_owner[resource.data["Resource ID"]] = false;
                upload.resources.push(resource);
            }
        };

        upload.uploadResources = function(resources) {
            for(var i = 0; i < resources.length && i < concurrency; i++) {
                updateResource(i, resources);
            }
        }

        return upload;
    }]);

    bulk_resource_relationships_upload_app.controller("uploadController", ["$scope","$http", "$log", "upload", function($scope, $http, $log, upload) {
        this.current_page = 1;
        this.per_page = 20;
        $scope.upload = upload;
        $("#csv-file").change(function(evt){
            var f = evt.target.files[0];
            if(f) {
                var r = new FileReader();
                r.onload = function(e) {
                    var contents = e.target.result;
                    upload.parseCSV(contents);
                    $scope.$apply();
                }
                r.readAsText(f);
            }
        });

        this.resourceStatusClass = function(message_status) {
            switch(message_status) {
            case "updating":
                return "fa-spinner fa-spin";
            case "skipped":
            case "nochange":
                return "fa-minus";
            case "exists":
                return "fa-clone";
            case "added":
                return "fa-upload";
            case "error":
                return "fa-times";
            case "updated":
                return "fa-floppy-o"
            default:
                return "";
            }
        };

        this.resourceStatusColor = function(message_status) {
            switch(message_status) {
            case "updating":
                return "black";
            case "skipped":
            case "nochange":
            case "exists":
                return "orange";
            case "error":
                return "red";
            case "updated":
            case "added":
                return "green";
            default:
                return "";
            }
        };

    }]);

    bulk_resource_relationships_upload_app.filter("startFrom", function() {
        return function(input, start) {
            var start = +start;
            return input.slice(start);
        };
    });

    bulk_resource_relationships_upload_app.filter("resourceStatus", function() {
        return function(resource) {
            var exists = false;
            var classes = "fa ";
            var color = ""
            switch(resource.message_status) {
            case "updating":
                classes += "fa-spinner fa-spin";
                color = "black";
                break;
            case "skipped":
            case "nochange":
                classes += "fa-minus";
                color = "yellow";
                break;
            case "exists":
                classes += "fa-clone";
                color = "yellow";
                break;
            case "added":
                classes += "fa-upload";
                color = "green";
                break;
            case "error":
                classes += "fa-times";
                color = "red";
                break;
            case "updated":
                classes += "fa-floppy-o"
                color = "green";
                break;
            }
            if(exists) {
                var resource_html = '<i class="' + classes + '" style="color:' + color + '"></i> ' + resource.message;
                return resource_html;
            } else {
                return "";
            }
        };
    });

    bulk_resource_relationships_upload_app.filter("newResourceID", function () {
        return function(resource) {
            var id_html = resource.new_id;
            return id_html;
        }
    });
}());
