(function() {

    var app = angular.module("rridItemApp", ["errorApp", "ui.bootstrap"]);

    app.controller("rridItemController", ["$scope", "$http", "$log", function($scope, $http, $log) {
        var that = this;

        $scope.active = false;
        var callingElement = null;
        var updateFun = null;
        $scope.nosubtypes = true;

        $scope.changeUUID = function(uuid, report_id, rrid, name, type, uid=NULL, subtypes, ce, updateFunArg) {
            $scope.report_id = report_id;
            $scope.uuid = uuid;
            $scope.rrid = rrid;
            $scope.name = name;
            $scope.type = type;
            $scope.uid = uid;   // added uid -- Vicky-2019-2019-3-6
            $scope.subtypes = subtypes.split(",");
            callingElement = ce;
            updateFun = updateFunArg;
            refreshElements();
        };

        $scope.addItem = function(subtype) {
          if(!subtype) subtype = "";
          var data = {
              id: $scope.report_id,
              type: $scope.type,
              rrid: $scope.rrid,
              uuid: $scope.uuid,
              subtype: subtype,
              uid: $scope.uid    // added Uid -- Vicky-2019-3-6
          };
            // $http.post("/api/1/rrid-report/add-item", {
            //     id: $scope.report_id,
            //     type: $scope.type,
            //     subtype: subtype,
            //     uuid: $scope.uuid,
            //     rrid: $scope.rrid
            // })
          $http.post("/api/1/rrid-report/add-item", data)
                .then(function(response) {
                    // location.reload();
                    refreshElements();
                    callUpdateFun();
                    setBookmarkStyle(true);
                    location.reload();
                });
          // location.reload();
          // setTimeout(location.reload.bind(location), 500);
        };

        $scope.deleteItem = function(subtype) {
            if(!subtype) subtype = "";
            $http.post("/api/1/rrid-report/delete-item", {
                id: $scope.report_id,
                uuid: $scope.uuid,
                type: $scope.type,
                subtype: subtype,
                full_delete: true   // added full_delete -- Vicky-2019-3-6
            })
                .then(function(response) {
                    // location.reload();
                    refreshElements();
                    callUpdateFun();
                    setBookmarkStyle(response.data.data.inColl);
                    location.reload();
                });
            // setTimeout(location.reload.bind(location), 1000);
        };

        function refreshElements() {
            $http.get("/api/1/rrid-report/items/byuuid?uuid=" + $scope.uuid + "&id=" + $scope.report_id)
                .then(function(response) {
                    $scope.active = true;
                    $scope.items = response.data.data;
                    if($scope.subtypes.length > 1 || $scope.subtypes[0]) {
                        $scope.nosubtypes = false;
                        $scope.fmt_subtypes = genSubtypes($scope.subtypes);
                    } else {
                        $scope.nosubtypes = true;
                    }
                    if(callingElement) {
                        if($scope.items) {
                            callingElement.children(".report-remove").show();
                            callingElement.children(".report-add").hide();
                        } else {
                            callingElement.children(".report-remove").hide();
                            callingElement.children(".report-add").show();
                        }
                    }
                }, function(response) {
                    $scope.active = false;
                });
        }

        function genSubtypes(subtypes) {
            var used_subtypes = {};
            if($scope.items) {
                for(var i = 0; i < $scope.items.subtypes.length; i++) {
                    used_subtypes[$scope.items.subtypes[i].subtype] = true;
                }
            }

            var fmt_subtypes = [];
            for(var i = 0; i < subtypes.length; i++) {
                var used = false;
                if(used_subtypes[subtypes[i]]) used = true;
                fmt_subtypes.push({ name: subtypes[i], used: used });
            }

            return fmt_subtypes;
        }

        function setBookmarkStyle(inColl) {
            if(!callingElement) return;
            var parent = callingElement.closest(".coll-li").children(".collection-icon");
            if(inColl) {
                $(parent).attr("title", "In a Collection");
                $(parent).removeClass('fa-square-o');
                $(parent).addClass('fa-check-square-o');
                $(parent).addClass('in-collection');
            } else {
                $(parent).attr('title', 'Not in a Collection');
                $(parent).removeClass('in-collection');
                $(parent).removeClass('fa-check-square-o');
                $(parent).addClass('fa-square-o');
            }
        }

        function callUpdateFun() {
            if(typeof updateFun === "function") {
                updateFun();
            }
        }
    }]);

    angular.bootstrap(document.getElementById("rrid-report-item-update-app"), ["rridItemApp"]);
}());

$(function() {
    $(".update-rrid-report-item").click(function(e) {
        var uuid = $(this).data("uuid");
        var report_id = $(this).data("rrid-report-id");
        var rrid = $(this).data("rrid");
        var name = $(this).data("name");
        var type = $(this).data("type");
        var subtypes = $(this).data("subtypes");
        var uid = $(this).data("uid");    // added uid -- Vicky-2019-3-6
        var callingElement = $(this);
        var appElement = document.getElementById("rrid-report-item-update-app");
        var appScope = angular.element(appElement).scope();
        appScope.$apply(function() {
            appScope.changeUUID(uuid, report_id, rrid, name, type, uid, subtypes, callingElement);  // added uid -- Vicky-2019-3-6
        });
        $(".background").show();
        $(".rrid-report-item-update").show();
    });
});
