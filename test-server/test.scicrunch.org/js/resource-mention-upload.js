(function(){
    var mention_upload_app = angular.module("mentionUploadApp", ["ui.bootstrap"]).
        run(function($rootScope, $http) {
            $http.get("/api/1/user/info").
                then(function(response) {
                    $rootScope.logged_in = response.data.data.logged_in;
                }, function (response) {
                    $rootScope.logged_in = false;
                });
        });

    mention_upload_app.factory("upload", ["$http", "$log", function($http, $log) {
        var upload = {};
        upload.mentions = [];
        upload.header = ["Resource ID", "Publication ID", "Snippet", "Verified", "Upload"];
        upload.skipped_rows = [];
        upload.resource_owner = {};
        var concurrency = 5;

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

        function updateMention(index, mentions) {
            var mention = mentions[index];
            mention.message_status = "updating";
            if(mention.data["Upload"].toLowerCase() !== "true") {
                mention.message_status = "skipped";
                mention.message = "Skipped";
                notifyNext(index, mentions);
            } else {
                var resource_id = mention.data["Resource ID"];
                var mention_id = mention.data["Publication ID"];
                var snippet = mention.data["Snippet"];
                $http.post("/api/1/resource/mention/add/" + resource_id, {mentionid: mention_id, snippet: snippet}).
                    then(function(response) {
                        if(response.status === 200) {
                            if(upload.resource_owner[resource_id]) {
                                compareToExistingAndUpdate(index, mentions);
                            } else {
                                mention.message_status = "exists";
                                mention.message = "Already exists";
                                notifyNext(index, mentions);
                            }
                        } else {
                            mention.message_status = "added";
                            mention.message = "Added";
                            notifyNext(index, mentions);
                            if(upload.resource_owner[resource_id]) {
                                markMention(mention);
                            }
                        }
                    }, function(response) {
                        mention.message_status = "error";
                        mention.message = response.data.errormsg;
                        notifyNext(index, mentions);
                    });
            }
        }

        function notifyNext(index, mentions) {
            var next = index + concurrency;
            if(next < mentions.length) updateMention(next, mentions);
        }

        function compareToExistingAndUpdate(index, mentions) {
            var mention = mentions[index];
            var resource_id = mention.data["Resource ID"];
            var mention_id = mention.data["Publication ID"];
            var snippet = mention.data["Snippet"];
            var mark = mention.data["Verified"];
            $http.get("/api/1/resource/mention/view/" + resource_id + "/" + mention_id).
                then(function(response) {
                    var existing = response.data.data;
                    var updated = false;
                    if(snippet && existing.snippet !== snippet) {
                        $http.post("/api/1/resource/mention/updatesnippet/" + resource_id + "/" + mention_id, {snippet: snippet});
                        updated = true;
                    }
                    if(existing.rating !== mark) {
                        if(markMention(mention)) updated = true;
                    }
                    if(updated) {
                        mention.message_status = "updated";
                        mention.message = "Updated";
                        notifyNext(index, mentions);
                    } else {
                        mention.message_status = "nochange";
                        mention.message = "No change";
                        notifyNext(index, mentions);
                    }
                }, function(response) {
                    mention.message_status = "error";
                    mention.message = response.data.errormsg;
                    notifyNext(index, mentions);
                });
        }

        function checkIfOwner(rid) {
            $http.get("/api/1/resource/owner/" + rid + "/check").
                then(function(response) {
                    upload.resource_owner[rid] = response.data.data;
                });
        }

        function markMention(mention) {
            var mark = mention.data["Verified"];
            if(mark === "good" || mark === "bad") {
                $http.post("/api/1/resource/mention/mark/" + mention.data["Resource ID"] + "/" + mention.data["Publication ID"], {mark: mark});
                return true;
            } else {
                return false;
            }
        }

        upload.parseCSV = function(csv_data) {
            upload.mentions = [];
            upload.skipped_rows = [];
            upload.errmsg = "";

            var parsed_data = CSVtoArray(csv_data, ",");
            if(parsed_data.length < 2) return;
            var header = parsed_data[0];
            for(var i = 0; i < upload.header.length; i++) {
                if(header.indexOf(upload.header[i]) === -1) {
                    upload.errmsg = "Missing header: " + upload.header[i];
                    return;
                }
            }
            for(var i = 1; i < parsed_data.length; i++) {
                var mention = {};
                mention.data = {};
                var row = parsed_data[i];
                if(header.length > row.length) {
                    upload.skipped_rows.push(i + 1);
                    continue;
                }
                for(var j = 0; j < header.length; j++) {
                    var value = row[j];
                    if(value === undefined) value = "";
                    mention.data[header[j]] = value;
                }
                upload.resource_owner[mention.data["Resource ID"]] = false;
                upload.mentions.push(mention);
            }
            for(var rid in upload.resource_owner) {
                if(upload.resource_owner.hasOwnProperty(rid)) {
                    checkIfOwner(rid);
                }
            }
        };

        upload.uploadMentions = function(mentions) {
            for(var i = 0; i < mentions.length && i < concurrency; i++) {
                updateMention(i, mentions);
            }
        }

        return upload;
    }]);

    mention_upload_app.controller("uploadController", ["$scope", "$log", "upload", function($scope, $log, upload) {
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

        this.mentionStatusClass = function(message_status) {
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

        this.mentionStatusColor = function(message_status) {
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

    mention_upload_app.filter("startFrom", function() {
        return function(input, start) {
            var start = +start;
            return input.slice(start);
        };
    });

    mention_upload_app.filter("mentionStatus", function() {
        return function(mention) {
            var exists = false;
            var classes = "fa ";
            var color = ""
            switch(mention.message_status) {
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
                var mention_html = '<i class="' + classes + '" style="color:' + color + '"></i> ' + mention.message;
                return mention_html;
            } else {
                return "";
            }
        };
    });
}());
