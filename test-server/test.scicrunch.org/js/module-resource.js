(function(){
    var resource_app = angular.module("resourceApp", ["errorApp", "ui.bootstrap"]).
        run(function($rootScope, $http, $log){
            $rootScope.logInCheck = function(){
                var rrid = $rootScope.rid.replace("RRID:", "");
                $rootScope.owner_promise = $http.get("/api/1/resource/owner/" + rrid + "/check").
                    then(function(response){
                        $rootScope.is_owner = response.data.data;
                    },function(response){
                        $rootScope.is_owner = false;
                    });
                $http.get("/api/1/resource/pendingowner/" + rrid + "/check").
                    then(function(response){
                        $rootScope.is_pending_owner = response.data.data;
                    },function(response){
                        $rootScope.is_pending_owner = false;
                    });
                $rootScope.logged_in_promise = $http.get("/api/1/user/info?no-datasets=1").
                    then(function(response){
                        $rootScope.logged_in = response.data.data.logged_in;
                    },function(response){
                        $rootScope.logged_in = false;
                    });
            }
        });

    resource_app.factory("fields", ["$rootScope", "$http", "$q", "$log", "versions", "errorModalCaller", function($rootScope, $http, $q, $log, versions, emc){
        var fields = {typeID: 1};
        fields.add_time = 0;
        fields.refresh = function(version){
            if(version) version_string = "?version=" + version;
            else version_string = "";
            var promise = $http.get("/api/1/resource/fields/view/" + $rootScope.rid + version_string).
                then(function(response){
                    fields.fields = response.data.data.fields;
                    fields.version = response.data.data.version;
                    fields.curation_status = response.data.data.curation_status;
                    fields.last_curated_version = response.data.data.last_curated_version;
                    fields.scicrunch_id = response.data.data.scicrunch_id;
                    fields.original_id = response.data.data.original_id;
                    fields.image_src = response.data.data.image_src;
                    fields.uuid = response.data.data.uuid;
                    fields.submitter_email = response.data.data.submitter_email;
                    fields.typeID = response.data.data.typeID;

                    if(fields.image_src) $rootScope.resource_image = fields.image_src;

                    fields.changed = false;
                    for(var i = 0; i < fields.fields.length; i++){
                        if((!fields.fields[i].required) && (fields.fields[i].value === null)) fields.fields[i].visible = false;
                        else fields.fields[i].visible = true;
                        fields.fields[i].add_time = ++fields.add_time;
                        if(fields.fields[i].field == "Resource Name") $rootScope.rname = fields.fields[i];
                    }
                    versions.refresh(fields.version);
                    refreshAdditionalTypes();
                });
            $q.all([promise, $rootScope.owner_promise]).then(function(responses) {

/* Manu - start */

osc_fields = ["Resource Name","Description", "Resource URL", "Keywords", "Defining Citation", "Funding Information", "Open Science Chain ID", "License Information", "Product Type"];

/* Manu - end */

                for(var i = 0; i < fields.fields.length; i++) {
                    if(fields.fields[i].display === "owner-text" && $rootScope.is_owner === false) {
                        fields.fields[i].user_visible = false;
                    } else {
                        fields.fields[i].user_visible = true;
                    }
/* Manu - start */
if (osc_fields.indexOf(fields.fields[i].field) != -1) {
    fields.fields[i].user_visible = true;
} else {
    fields.fields[i].user_visible = false;
}
/* Manu - end */               
                }
            });
            return promise;
        };

        fields.searchResource = function(val){
            return $http.get("/api/1/resource/fields/autocomplete?field=Resource Name&value=" + val).then(function(response){
                return response.data.data;
            });
        };

        fields.curate = function(stat){
            $http.post("/api/1/resource/fields/curate/" + $rootScope.rid, {status: stat, version: fields.version}).
                then(function(response) {
                    fields.refresh(fields.version);
                }, function(response) {
                    if(response.status === 403) {
                        emc.call("Could not curate resource.  If the Canonical ID has been updated since the resource was last curated, then a SciCrunch curator will need to approve the change.");
                    }
                });
        };

        fields.rejectAll = function() {
            $http.post("/api/1/resource/fields/reject-all/" + $rootScope.rid).
                then(function(response) {
                    fields.refresh(fields.version);
                });
        };

        fields.getFieldValue = function(key) {
            fields.value_dict = fields.value_dict || {};
            if(fields.value_dict[key] !== undefined) return fields.value_dict[key];
            for(var i = 0; i < fields.fields.length; i++){
                var field = fields.fields[i];
                if(field.field === key){
                    fields.value_dict[key] = field.value;
                    return field.value;
                }
            }
            return null;
        };

        fields.validate = function(field) {
            if(field.required && field.value == "") {
                field.valid = false;
                field.valid_message = "Field required";
                return false;
            }
            if(field.display == "literature-text" && field.value) {
                var split_val = field.value.split(",");
                for(var i = 0; i < split_val.length; i++) {
                    if(split_val[i].match(/(^[ ]?PMID:[0-9]+[ ]?$|^[ ]?DOI:[^ ]+[ ]?$)/) === null) {
                        field.valid = false;
                        field.valid_message = "Literature must be comma separated list in format: PMID:1234 or DOI:12345"
                        return false;
                    }
                }
            }
            //if(field.type == "resource-types") {
            //    for(var i = 0; i < field.value.length; i++) {
            //        if(fields.additional_types.indexOf(field.value[i]) === -1) {
            //            field.valid = false;
            //            field.valid_message = "Invalid resource type: " + field.value[i];
            //            return false;
            //        }
            //    }
            //}
            field.valid = true;
            field.valid_message = "";
            return true;
        }

        function refreshAdditionalTypes() {
            fields.additional_types = [];
            $http.get("/api/1/resource/fields/additional-types")
                .then(function(response) {
                    var additional_types = response.data.data;
                    for(var key in additional_types) {
                        if(!additional_types.hasOwnProperty(key)) continue;
                        fields.additional_types.push(additional_types[key].label);
                    }
                });
        }

        fields.initial_promise = fields.refresh();
        return fields;
    }]);

    resource_app.factory("mentions", ["$rootScope", "$http", "$q", "$uibModal", "fields", "errorModalCaller", "$log", function($rootScope, $http, $q, $uibModal, fields, emc, $log){
        var mentions = {};
        mentions.resource_identifiers = [];
        mentions.sort = "added_date";
        mentions.per_page = 10;
        mentions.confidence = "high";
        mentions.mark = function(mention, rating){
            mention.rating = rating;
            $http.post("/api/1/resource/mention/mark/" + $rootScope.rid + "/" + mention.mention, {mark: rating});
        };
        mentions.vote = function(mention, rating){
            mention.user_vote = rating;
            $http.post("/api/1/resource/mention/vote/" + $rootScope.rid + "/" + mention.mention, {vote: rating});
        };
        mentions.thumbsUpClass = function(mention){
            switch(mention.user_vote){
                case "good": return "thumb-green fa-thumbs-up";
                case "bad": return "thumb-grey fa-thumbs-o-up";
                default: return "thumb-blue fa-thumbs-o-up";
            }
        };
        mentions.thumbsDownClass = function(mention){
            switch(mention.user_vote){
                case "good": return "thumb-gray fa-thumbs-o-down";
                case "bad": return "thumb-red fa-thumbs-down";
                default: return "thumb-blue fa-thumbs-o-down";
            }
        };
        mentions.markUpClass = function(mention){
            switch(mention.rating){
                case "good": return "btn-success";
                case "bad": return "btn-default";
                default: return "btn-primary";
            }
        };
        mentions.markDownClass = function(mention){
            switch(mention.rating){
                case "good": return "btn-default";
                case "bad": return "btn-danger";
                default: return "btn-primary";
            }
        };

        mentions.page = function(n){
            mentions.loading = true;
            mentions.current_page = n;
            $http.get("/api/1/resource/mention/view/" + $rootScope.rid +
                "?count=" + mentions.per_page +
                "&offset=" + mentions.per_page * (n - 1) +
                "&orderby=" + mentions.sort +
                "&confidence=" + mentions.confidence
            ).then(function(response){
                mentions.mentions = [];
                mentions.bad_mentions = [];
                for(var i = 0; i < response.data.data.length; i++){
                    var datum = response.data.data[i];
                    if(response.data.data[i].id !== undefined) {

                        // set up confidence
                        var confidence_string = "low";
                        if(datum.rating === "good" || (datum.rating !== "bad" && (datum.confidence > 0.5 || datum.vote_good > datum.vote_bad))) confidence_string = "high";
                        datum.confidence_string = confidence_string;

                        // set up mention snippet
                        datum.snippet_html = mentions.highlightSnippet(datum.snippet);

                        mentions.mentions.push(datum);
                    } else {
                        mentions.bad_mentions.push(datum);
                    }
                }
                mentions.loading = false;
            });
        };
        mentions.otherResources = function(mention){
            if(mention.other_resources !== undefined) return;
            $http.get("/api/1/resource/mention/bymention/" + mention.mention + "?not_rating=bad").then(function(response){
                mention.other_resources = response.data.data;
            });
        };
        mentions.subscribe = function(action){
            var cid = $("#cid").val();
            $http.post("/api/1/subscription/" + action + "/resource-mention/" + $rootScope.rid, {cid: cid}).
                then(function(response){
                    mentions.subscriptionRefresh();
                });
        };
        mentions.subscriptionRefresh = function(){
            $http.get("/api/1/subscription/user").
                then(function(response){
                    var subscribed = false;
                    var data = response.data.data;
                    for(var i = 0; i < data.length; i++){
                        if(data[i].identifier === $rootScope.rid) subscribed = true;
                    }
                    mentions.subscription_status = subscribed;
                });
        };
        mentions.addNewMentions = function(mentionids) {
            var results = [];
            var push = function(r) { results.push(r); };
            var mentionids_array = mentionids.split(",");
            for(var i = 0; i < mentionids_array.length; i++) mentionids_array[i] = mentionids_array[i].replace(/^\s+|\s+$/g, "").replace(": ", ":");
            var mentionids_promises = [];
            for(var i = 0; i < mentionids_array.length; i++){
                mentionids_promises.push($http.post("/api/1/resource/mention/add/" + $rootScope.rid, {"mentionid": mentionids_array[i]}).then(push).catch(push));
            }
            $q.all(mentionids_promises).
                then(function(){
                    var data = results;
                    var added_mentionids = [];
                    var existing_mentionids = [];
                    var bad_mentionids = [];
                    for(var i = 0; i < data.length; i++){
                        if(data[i].status === 200) existing_mentionids.push(data[i].data.data.mentionid);
                        else if(data[i].status === 201) added_mentionids.push(data[i].data.data.mentionid);
                        else bad_mentionids.push(data[i].config.data.mentionid);
                    }
                    var modalInstance = $uibModal.open({
                        animation: true,
                        templateUrl: "/templates/mention-exists-modal.html",
                        controller: "mentionExistsModal",
                        resolve: {
                            rid: function(){ return $rootScope.rid; },
                            existing_mentionids: function(){ return existing_mentionids; },
                            added_mentionids: function(){ return added_mentionids; },
                            bad_mentionids: function(){ return bad_mentionids; },
                        }
                    });
                    modalInstance.result.finally(function(response){
                        mentions.page(mentions.current_page);
                    });
                });
        };
        mentions.countResults = function(){
            $http.get("/api/1/resource/mention/count/" + $rootScope.rid + "?confidence=" + mentions.confidence).
                then(function(response){
                    mentions.count = response.data.data;
                    mentions.npages = Math.ceil(mentions.count / mentions.per_page);
                    if(mentions.all_count === undefined) {
                        if(mentions.confidence === "all") mentions.all_count = mentions.count;
                        else mentions.countAllResults();
                    }
                });
        };
        mentions.countAllResults = function() {
            $http.get("/api/1/resource/mention/count/" + $rootScope.rid + "?confidence=all").
                then(function(response) {
                    mentions.all_count = response.data.data;
                });
        };
        mentions.highlightSnippet = function(snippet) {
            var snippet_html = snippet;
            if(typeof snippet_html === "string") {
                for(var j = 0; j < mentions.resource_identifiers.length; j++){
                    re = new RegExp("(" + mentions.resource_identifiers[j] + ")", "gi");
                    snippet_html = snippet_html.replace(re, "<b>$1</b>");
                }
            } else {
                snippet_html = "";
            }
            return snippet_html;
        };
        mentions.idToUrl = function(id) {
            if(id.indexOf("PMID:") === 0) {
                return "http://www.ncbi.nlm.nih.gov/pubmed/" + id.substring(5);
            }
            if(id.indexOf("DOI:") === 0) {
                return "http://dx.doi.org/" + id.substring(4);
            }
            return "";
        };

        mentions.countResults();
        $q.all([fields.initial_promise]).then(function(){
            mentions.resource_identifiers.push(fields.getFieldValue("Resource URL"));
            mentions.resource_identifiers.push(fields.getFieldValue("Resource Name"));
            mentions.resource_identifiers.push(fields.original_id);
            mentions.resource_identifiers.push(fields.scicrunch_id);
            mentions.page(1);
        });
        $rootScope.logged_in_promise.then(function(){
            if($rootScope.logged_in) mentions.subscriptionRefresh();
        });
        return mentions;
    }]);

    resource_app.factory("relationships", ["$rootScope", "$http", "$log", function($rootScope, $http, $log){
        // fields, owners and mentions included to make sure it's loaded first
        var relationships = {};
        var checkDuplicateRels = function(rels){
            var canon = {};
            for(var i = 0; i < rels.length; i++){
                if(rels[i].canon) canon[rels[i].relationship + rels[i].other_rid] = true;
            }
            for(var i = 0; i < rels.length; i++){
                if(!rels[i].canon && canon[rels[i].relationship + rels[i].other_rid] === true) rels[i].duplicate = true;
                else rels[i].duplicate = false;
            }
        }
        var linkFunGen = function(){
            var url = window.location.href;
            var str0 = "/";
            var idx0 = url.indexOf(str0, 10);
            url = url.substring(idx0);
            // changed by Steven - regex term replaced nlx_144509-1 with sdf
            var uuid_re = /(sdf\/)(?:\w+)(\/resolver)/
            return function(type, data){
                if(type == "edit"){
                    return "/browse/resourcesedit/" + data.other_rid;
                }else if(type == "datafed"){
                    // CHanged by Steven from comparing undefined to "" and other_uuid to other_name
                    if(data.other_name === "") return undefined;
                    const repalcedURL =  url.replace(uuid_re, "$1" + data.other_rid + "$2");
                    console.log("DEBUG TYPE: ",repalcedURL)
                    return repalcedURL
                }
            };
        };
        var linkOut = linkFunGen();
        relationships.refresh = function(n){
            if(n === undefined){
                relationships.seen_all = true;
                var n_str = "";
            }else{
                relationships.seen_all = false;
                var n_str = "?count=" + n;
            }
            relationships.loading = true;
            relationships.duplicate_of = undefined;
            $http.get("/api/1/resource/rel/view/" + $rootScope.rid + n_str).
                then(function(response){
                    relationships.loading = false;
                    relationships.relationships = [];
                    if(!relationships.seen_all && response.data.data.length < n) relationships.seen_all = true;
                    for(var i=0; i < response.data.data.length; i++){
                        var rel = response.data.data[i];
                        var new_rel = {};
                        var idx = (rel.id1 == $rootScope.rid || rel.original_id1 == $rootScope.rid) ? 0 : 1;
                        new_rel.canon = (rel.canon_id == idx);
                        new_rel.relationship = (idx == 0) ? rel.reltype.forward : rel.reltype.reverse;
                        new_rel.other_name = (idx == 0) ? rel.name2 : rel.name1;
                        new_rel.other_rid = (idx == 0) ? rel.id2 : rel.id1;
                        new_rel.other_uuid = (idx == 0) ? rel.uuid2 : rel.uuid1;
                        new_rel.link_other = linkOut($rootScope.page_type, new_rel);

                        if(new_rel.relationship == "duplicate of"){
                            relationships.duplicate_of = new_rel;
                            $rootScope.is_duplicate = true;
                            continue;
                        }
                        if(rel.reltype.type1 != "res" || rel.reltype.type2 != "res") {
                            continue;
                        }

                        relationships.relationships.unshift(new_rel); //new record is in the front of the array -- Vicky-2019-8-22
                    }
                    checkDuplicateRels(relationships.relationships);

                });
            if(!relationships.duplicate_of) $rootScope.is_duplicate = false;
        };
        relationships.addDelete = function(left, right, relationship, method, right_name){
            $http({
                method: "POST",
                url: "/api/1/resource/rel/" + method + "/" + $rootScope.rid,
                headers: {"Content-Type": "application/x-www-form-urlencoded"},
                transformRequest: function(obj){
                    var str = [];
                    for(var p in obj) str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                    return str.join("&");
                },
                data: {id1: left, id2: right, type: "res", relationship: relationship}
            }).
                then(function(response){
                    if(method == "add"){
                        var new_rel = {
                            canon: true,
                            relationship: relationship,
                            other_name: right_name,
                            other_rid: right
                        };
                        new_rel.link_other = linkOut($rootScope.page_type, new_rel);
                        if(relationship == "duplicate of"){
                            relationships.duplicate_of = new_rel;
                            $rootScope.is_duplicate = true;
                        }else{
                            relationships.relationships.unshift(new_rel);
                        }
                    }else if(method == "del"){
                        for(var i = 0; i < relationships.relationships.length; i++){
                            rel = relationships.relationships[i];
                            if(!rel.canon) continue;
                            if(rel.relationship === relationship && rel.other_rid === right){
                                relationships.relationships.splice(i, 1);
                                break;
                            }
                        }
                        if(relationship == "duplicate of"){
                            relationships.duplicate_of = undefined;
                            $rootScope.is_duplicate = false;
                        }
                    }
                    checkDuplicateRels(relationships.relationships);
                });
                relationships.getTotalCount();
        };
        relationships.getTypes = function(){
            $http.get("/api/1/resource/rel/types").
                then(function(response){
                    relationships.types = [];
                    for(var i = 0; i < response.data.data.length; i++) {
                        var rel = response.data.data[i];
                        if(rel.id == 1) {
                            continue;
                        }
                        if(rel.type1 != "res" || rel.type2 != "res") {
                            continue;
                        }
                        if(relationships.types.indexOf(rel.forward) === -1) {
                            relationships.types.push(rel.forward);
                        }
                        if(relationships.types.indexOf(rel.reverse) === -1) {
                            relationships.types.push(rel.reverse);
                        }
                    }
                    console.log("relationships.types", relationships.types);
                });
        };
        relationships.getTotalCount = function() {
            $http.get("/api/1/resource/rel/count/" + $rootScope.rid).
                then(function (response) {
                    relationships.totalCount = response.data.data;
                });
        }

        relationships.getTotalCount();
        relationships.getTypes();
        relationships.refresh(10);  // start with only first 10
        return relationships;
    }]);

    resource_app.factory("versions", ["$rootScope", "$http", "$log", function($rootScope, $http, $log){
        var versions = {};
        versions.refresh = function(version_number){
            $http.get("/api/1/resource/versions/all/" + $rootScope.rid).
                then(function(response){
                    versions.allRejected = true;
                    versions.versions = response.data.data;
                    for(var i = 0; i < versions.versions.length; i++){
                        if(versions.versions[i].version == version_number){
                            versions.versions[i].selected = true;
                        }
                        if(versions.versions[i].status != "Rejected") {
                            versions.allRejected = false;
                        }
                    }
                });
        };

        return versions;
    }]);

    resource_app.factory("owners", ["$rootScope", "$http", "$log", function($rootScope, $http, $log){
        var owners = {};
        owners.refresh = function(){
            $http.get("/api/1/resource/owner/" + $rootScope.rid).
                then(function(response){
                    owners.owners = response.data.data;
                });
        }
        owners.searchUsers = function(val){
            return $http.get("/api/1/user/autocomplete?name=" + val).
                then(function(response){
                    return response.data.data;
                });
        };

        owners.addDeleteOwner = function(id, uri_str){
            $http.post("/api/1/resource/owner/" + $rootScope.rid + "/" + uri_str, {uid: id}).
                then(function(response){
                    owners.refresh();
                });
        };

        owners.refresh();
        return owners;
    }]);

    resource_app.controller("resourceMentions", ["$scope", "$rootScope", "$log", "mentions", function($scope, $rootScope, $log, mentions){
        $scope.mentions = mentions;
        $scope.new_mention = "";

        this.changePage = function(){
            mentions.page(mentions.current_page);
        };
        this.sortMentions = function(sort){
            mentions.sort = sort;
            mentions.page(mentions.current_page);
        };
        this.addNewMention = function(){
            mentions.addNewMentions($scope.new_mention, this);
            $scope.new_mention = "";
        };
        this.updateConfidence = function(setting){
            mentions.confidence = setting;
            mentions.countResults();
            mentions.page(1);
        };

        $scope.dynamicPopover = {
            templateUrl: "/templates/mentions_other_resources.html"
        };
        $scope.showLogin = function(){
            globals.showLogin();
        };

        if($rootScope.mentions_sort !== undefined) this.sortMentions($rootScope.mentions_sort);

        $scope.mentions_upload_link = $rootScope.community_portal_name ? "/" + $rootScope.community_portal_name + "/about/resourcementionupload" : "/browse/resourcementionupload";
    }]);

    resource_app.controller("resourceRelationships", ["$rootScope", "$scope", "$http", "$log", "relationships", "fields", function($rootScope, $scope, $http, $log, relationships, fields){
        var that = this;
        this.checked_duplicate = false;
        this.is_duplicate = false;
        var foundTypes = function(rels, current_found_types){
            if(rels === undefined) return [];
            if(current_found_types === undefined) current_found_types = [];
            var found_types = {};
            for(var i = 0; i < rels.length; i++){
                if(rels[i].duplicate || rels[i].link_other == null) continue;
                var rel = rels[i].relationship;
                if(found_types[rel] !== undefined){
                    found_types[rel] += 1;
                }else{
                    found_types[rel] = 1;
                }
            }
            var return_found_types = [];
            var found_types_names = Object.getOwnPropertyNames(found_types);
            for(var i = 0; i < found_types_names.length; i++){
                var cft = current_found_types.filter(function(x){ return x.type == found_types_names[i]; });
                if(cft.length) var checked = cft[0].checked;
                else var checked = true;
                return_found_types.push({
                    type: found_types_names[i],
                    count: found_types[found_types_names[i]],
                    checked: checked
                });
            }
            return return_found_types;
        };

        $scope.relationships = relationships;
        $scope.fields = fields;
        that.filter_search = "";

        $scope.$watch('relationships', function(){
            if(relationships.duplicate_of){
                that.checked_duplicate = true;
                that.is_duplicate = true;
            }
            that.found_types = foundTypes(relationships.relationships, that.found_types);
        }, true);

        $scope.selectResource = function(item, model, label){
            that.selected_id = item.rid;
            that.selected_name = item.name;
        };

        $scope.selectType = function(item, model, label){
            that.selected_type = item;
        };

        $scope.addRelationship = function(){
            relationships.addDelete($rootScope.rid, that.selected_id, that.selected_type, "add", that.selected_name);
            $scope.new_rel = "";
            $scope.new_resource = "";
            that.selected_id = null;
            that.selected_type = null;
        };

        $scope.removeRelationship = function(rid, relationship){
            relationships.addDelete($rootScope.rid, rid, relationship, "del");
        };

        $scope.selectDuplicate = function(item, model, label){
            that.duplicate_id = item.rid
            that.duplicate_name = item.name;
        };

        $scope.addDuplicate = function(){
            relationships.addDelete($rootScope.rid, that.duplicate_id, "duplicate of", "add", that.duplicate_name);
            that.is_duplicate = true;
        };

        $scope.removeDuplicate = function(rid){
            relationships.addDelete($rootScope.rid, rid, "duplicate of", "del");
            that.is_duplicate = false;
        };

        $scope.loadAllRelationships = function(){
            relationships.refresh();
        };

        var searchInFilter = function(filter_string, resource_name){
            if(filter_string == "") return true;
            var afs = filter_string.toLowerCase().split(/\s+/);
            var arn = resource_name.toLowerCase().split(/\s+/);
            for(var i = 0; i < afs.length; i++){
                if(arn.filter(function(x){ return x.indexOf(afs[i]) != -1; }).length == 0) return false;
            }
            return true;
        };
        $scope.relationshipFilter = function(item){
            if(item.duplicate) return false;
            if(item.link_other === undefined) return false;
            for(var i = 0; i < that.found_types.length; i++){
                if(that.found_types[i].type == item.relationship){
                    if(!that.found_types[i].checked) return false;
                    break;
                }
            }
            if(searchInFilter(that.filter_search, item.other_name)) return true;
        }

    }]);

    resource_app.controller("mentionExistsModal", ["$scope", "$uibModalInstance", "$http", "$q", "mentions", "rid", "existing_mentionids", "added_mentionids", "bad_mentionids", "$log", function($scope, $uibModalInstance, $http, $q, mentions, rid, existing_mentionids, added_mentionids, bad_mentionids, $log){
        $scope.existing_mentions = [];
        $scope.added_mentions = [];
        $scope.mentions = mentions
        getMentionData(existing_mentionids, $scope.existing_mentions);
        getMentionData(added_mentionids, $scope.added_mentions);

        $scope.hide_other_resources_mentions = true;
        $scope.existing_mentionids = existing_mentionids;
        $scope.added_mentionids = added_mentionids;
        $scope.bad_mentionids = bad_mentionids;
        $scope.ok = function(){
            $uibModalInstance.close();
        };

        function getMentionData(mentionids, values){
            var promises = [];
            for(var i = 0; i < mentionids.length; i++) promises.push($http.get("/api/1/resource/mention/view/" + rid + "/" + mentionids[i]));
            $q.all(promises).then(function(data){
                for(var i = 0; i < mentionids.length; i++) values.push(data[i].data.data);
            });
        }
    }]);

    resource_app.controller("claimResourceOwnership", ["$rootScope", "$scope", "$uibModal", "$log", function($rootScope, $scope, $uibModal, $log) {
        this.ownerPanel = function() {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "/templates/claim-resource-ownership-modal.html",
                controller: "claimResourceOwnershipModal",
                resolve: {
                    rid: function() { return $rootScope.rid.replace("RRID:", ""); }
                }
            });
        };
    }]);

    resource_app.controller("claimResourceOwnershipModal", ["$rootScope", "$scope", "$uibModalInstance", "$http", "rid", "$log", function($rootScope, $scope, $uibModalInstance, $http, rid, $log) {
        $scope.message = "";
        $scope.submit = function() {
            $http.post("/api/1/resource/pendingowner/" + rid + "/add", {text: $scope.message}).
                then(function(response){
                    $rootScope.is_pending_owner = true;
                });
            $uibModalInstance.close();
        };
        $scope.cancel = function() {
            $uibModalInstance.close();
        };
    }]);

    resource_app.filter("capitalize", function() {
        return function(input) {
            if(input.toLowerCase() === "rrid") return "RRID";
            return (!!input) ? input.charAt(0).toUpperCase() + input.substr(1).toLowerCase() : "";
        };
    });

}());
