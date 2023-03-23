$(function() {
    var datasets_app = angular.module("datasetsApp", ["ui.bootstrap", "resourceDirectives", "errorApp"]);

    datasets_app.factory("datasets", ["$http", "$q", "$uibModal", "$log", "$window", function($http, $q, $uibModal, $log, $window) {
        var datasets = {};
        datasets.templates = [];
        var per_page = 20;
        var lab = null;
        var keyvalues = null;

        datasets.textILX = $("#ilx-cde-text").val();
        datasets.defaultILX = $("#ilx-cde-default").val();
        datasets.valueRestrictionILX = $("#annotation-value-restriction").val();
        datasets.valueRestrictionID = $("#annotation-value-restriction-id").val();
        datasets.valueRangeILX = $("#annotation-value-range").val();
        datasets.valueRangeID = $("#annotation-value-range-id").val();
        datasets.annotationSourceILX = $("#annotation-source").val();
        datasets.annotationSourceValue = $("#annotation-source-value").val();
        datasets.annotationSourceID = $("#annotation-source-id").val();
        datasets.annotationDomainILX = $("#annotation-domain").val();
        datasets.annotationDomainID = $("#annotation-domain-id").val();
        datasets.annotationSubdomainILX = $("#annotation-subdomain").val();
        datasets.annotationSubdomainID = $("#annotation-subdomain-id").val();
        datasets.annotationAssessmentdomainILX = $("#annotation-assessmentdomain").val();
        datasets.annotationAssessmentdomainID = $("#annotation-assessmentdomain-id").val();
        datasets.annotationDefaultValueILX = $("#annotation-default-value").val();
        datasets.annotationDefaultValueID = $("#annotation-default-value-id").val();
        datasets.annotationMultipleValuesILX = $("#annotation-multiple-values").val();
        datasets.annotationMultipleValuesID = $("#annotation-multiple-values-id").val();
        datasets.portalName = $("#portal-name").val();
        datasets.cid = $("#cid").val();
        datasets.labid = $("#labid").val();
        datasets.term_restrictions_map = {};
        datasets.subject_field_name = $("#subject_field_name").val();

        datasets.refreshTemplatesCallbacks = [];

        datasets.addDatasetTemplate = function(name, labid, requiredTemplateName, noRefresh) {
            var postData = {
                name: name,
                labid: labid,
                "required-fields-name": requiredTemplateName
            };
            var promise = $http.post("/api/1/datasets/template/add", postData);
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.getDataset = function(dataset_id) {
            var promise = $q(function(resolve, reject) {
                $http.get("/api/1/datasets/info?datasetid=" + dataset_id)
                    .then(function(response) {
                        var dataset = response.data.data;
                        datasets.flattenTemplate(dataset.template);
                        resolve(dataset);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        };

        datasets.getAssociatedFiles = function(dataset_id, type) {
            var promise = $q(function(resolve, reject) {
                $http.get("/api/1/datasets/associated-files?dataset_id=" + dataset_id)
                    .then(function(response) {
                        var files = response.data;
                        this.dictionary = files.dictionary;
                        this.methodology = files.methodology;
                        resolve(files);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        };


        datasets.getLabDatasets = function(labid) {
            var promise = $q(function(resolve, reject) {
                $http.get("/api/1/lab/datasets?labid=" + labid)
                    .then(function(response) {
                        var lab_datasets = response.data.data;
                        for(var i = 0; i < lab_datasets.length; i++) {
                            datasets.flattenTemplate(lab_datasets[i].template);
                            resolve(lab_datasets);
                        }
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        };

        datasets.deleteDatasetTemplate = function(template, labid, noRefresh) {
            var promise = $http.post("/api/1/datasets/template/delete", {template_id: template.id});
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.copyTemplate = function(template, labid, name, noRefresh) {
            var promise = $http.post("/api/1/datasets/template/copy", {template_id: template.id, name: name});
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.refreshTemplates = function(labid) {
            var promise = $http.get("/api/1/datasets/template/bylab?labid=" + labid);
            promise.then(function(response) {
                datasets.templates = response.data.data;
                for(var i = 0; i < datasets.templates.length; i++) {
                    var template = datasets.templates[i];
                    datasets.flattenTemplate(template);
                }
                for(var i = 0; i < datasets.refreshTemplatesCallbacks.length; i++) {
                    if(typeof datasets.refreshTemplatesCallbacks[i] === "function") {
                        datasets.refreshTemplatesCallbacks[i]();
                    }
                }
            });
            return promise;
        };

        datasets.getTemplates = function(labid) {
            var promise = $q(function(resolve, reject) {
                $http.get("/api/1/datasets/template/bylab?labid=" + labid)
                    .then(function(response) {
                        var templates = response.data.data;
                        for(var i = 0; i < templates.length; i++) {
                            datasets.flattenTemplate(templates[i]);
                        }
                        resolve(templates);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        };

        datasets.getTemplate = function(template_id) {
            return $q(function(resolve, reject) {
                $http.get("/api/1/datasets/template?template_id=" + template_id)
                    .then(function(response) {
                        var template = response.data.data;
                        datasets.flattenTemplate(template);
                        resolve(template);
                    }, function(e) {
                        reject();
                    });
            });
        };

        datasets.addField = function(template, labid, name, ilxid, required, queryable, noRefresh) {
            var promise = $http.post("/api/1/datasets/fields/add", {template_id: template.id, name: name, ilxid: ilxid, required: required, queryable: queryable});
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.toggleSubjectField = function(labid, template, field, noRefresh) {
            var url = "";
            if(datasets.isSubjectField(field)) {
                url = "/api/1/datasets/field/annotation/remove";
            } else {
                url = "/api/1/datasets/field/annotation/add";
            }
            var data = {
                name: field.name,
                template_id: template.id,
                annotation_name: "subject",
                annotation_value: ""
            };
            var promise = $http.post(url, data);
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.isSubjectField = function(field) {
            if(!field.annotations) return false;
            for(var i = 0; i < field.annotations.length; i++) {
                if(field.annotations[i].name == "subject") return true;
            }
            return false;
        };

        datasets.submitTemplate = function(labid, template, action, noRefresh) {
            var url = "";
            var data = {
                template_id: template.id
            };
            if(action == "submit") {
                url = "/api/1/datasets/template/submit";
            } else {
                url = "/api/1/datasets/template/unsubmit";
            }
            var promise = $http.post(url, data);
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.updateTemplateName = function(labid, template, new_name, noRefresh) {
            var url = "/api/1/datasets/template/name";
            var data = {
                template_id: template.id,
                name: new_name
            };
            var promise = $http.post(url, data);
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.updateFieldName = function(labid, template, field, new_name, noRefresh) {
            var url = "/api/1/datasets/fields/name";
            var data = {
                template_id: template.id,
                name: field.name,
                new_name: new_name
            };
            var promise = $http.post(url, data);
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.updateFieldILX = function(labid, template, field, new_ilx, noRefresh) {
            var url = "/api/1/datasets/fields/ilx";
            var data = {
                template_id: template.id,
                name: field.name,
                ilx: new_ilx
            };
            var promise = $http.post(url, data);
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.addFieldsMultiple = function(template, labid, name, suffixes, ilxid, required, queryable, noRefresh) {
            var promise = $http.post("/api/1/datasets/fields/add/multiple",
                    {template_id: template.id, name: name, ilxid: ilxid, required: required, queryable: queryable, suffixes: suffixes}
                );
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.deleteField = function(template, labid, field, noRefresh) {
            var promise = $http.post("/api/1/datasets/fields/delete", {template_id: template.id, name: field.name});
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.deleteTemplate = function(template, labid, noRefresh) {
            var promise = $http.post("/api/1/datasets/template/delete", {template_id: template.id});
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.moveField = function(template, labid, field, direction, noRefresh) {
            var promise = $http.post("/api/1/datasets/fields/move", {template_id: template.id, name: field.name, direction: direction});
            promise.then(function(response) {
                if(!noRefresh) {
                    datasets.refreshTemplates(labid);
                }
            });
            return promise;
        };

        datasets.addDataset = function(template, name, longName, description, publications, metadata) {
            return $http.post("/api/1/datasets/add", {name: name, long_name: longName, description: description, publications: publications, metadata: metadata, template_id: template.id});
        };

        datasets.search = function(dataset, term, offset, count) {
            dataset.lastTerm = term;
            var promise = $http.get("/api/1/datasets/search?q=" + term + "&datasetid=" + dataset.id + "&offset=" + offset + "&count=" + count);
            promise.then(function(response) {
                dataset.data = response.data.data;
            });
            return promise;
        };

        datasets.deleteRecord = function(dataset, id) {
            var promise = $http.post("/api/1/datasets/records/delete", {datasetid: dataset.id, recordid: id});
            promise.then(function(response) {
                for(var i = 0; i < dataset.data.records.length; i++) {
                    if(dataset.data.records[i]["_id"] == id) {
                        dataset.data.records.splice(i, 1);
                    }
                }
            });
            return promise;
        };

        datasets.deleteDataset = function(dataset) {
            return $http.post("/api/1/datasets/delete", {datasetid: dataset.id});
        };

        datasets.deleteAllRecords = function(dataset) {
            return $http.post("/api/1/datasets/records/delete/all", {datasetid: dataset.id});
        };

        datasets.submitDataset = function(dataset, portalName) {
            return $http.post("/api/1/datasets/submit", {datasetid: dataset.id, portalname: portalName});
        };

        datasets.withdrawDataset = function(dataset, portalName) {
            return $http.post("/api/1/datasets/withdraw", {datasetid: dataset.id, portalname: portalName});
        };

        datasets.addSingleRecord = function(dataset, record) {
            return $http.post("/api/1/datasets/records/add", {datasetid: dataset.id, fields: record});
        };

        datasets.addUploadableRecords = function(dataset, uploadable, callback) {
            var at_once = 100;
            var chain = $q.when();
            var i = 0;
            if(dataset.field_set.length > 1000) {
                at_once = 10;
            }
            while(i < uploadable.data.length) {
                var end = i + at_once;
                var records = [];
                for(var j = i; j < end && j < uploadable.data.length; j++) {
                    records.push(uploadable.data[j]);
                }
                chain = addToChain(chain, dataset, records);
                i = end;
            }
            chain = chain.then(endChain, endChain);

            function endChain() {
                if(typeof callback === "function") {
                    callback("complete");
                }
                uploadable.complete = true;
            }

            function addToChain(chain, dataset, records) {
                var fields = [];
                for(var i = 0; i < records.length; i++) {
                    fields.push(records[i].values);
                    records[i].status = "Uploading";
                }
                var then_function = function() {
                    var promise = $http.post("/api/1/datasets/records/add/multiple", {datasetid: dataset.id, records: fields});
                    promise.then(function(response) {
                        if(typeof callback === "function") {
                            callback("added");
                        }
                        for(var i = 0; i < records.length; i++) {
                            records[i].status = "Uploaded";
                            records[i].upload_success = true;
                        }
                        for(var i = 0; i < response.data.data.length; i++) {
                            if(!response.data.data[i].success) {
                                records[i].status = response.data.data[i].error_message;
                                records[i].upload_success = false;
                            }
                        }
                    }, function() {
                        for(var i = 0; i < records.length; i++) {
                            records[i].status = "Error uploading";
                            records[i].upload_success = false;
                        }
                    });
                    return promise;
                };
                chain = chain.then(then_function, then_function);
                return chain;
            }
        };

        datasets.valueRestrictions = function(field) {
            var vr = {
                allowedValues: null,
                allowedRange: null
            };
            if(!field || !field.termid) {
                if(!datasets.term_restrictions_map[null]) {
                    datasets.term_restrictions_map[null] = vr;
                }
                return datasets.term_restrictions_map[null];
            }
            if(!datasets.term_restrictions_map[field.termid.id]) {
                var allowedValues = [];
                var allowedRange = null;
                for(var i = 0; i < field.termid.annotations.length; i++) {
                    var annotation = field.termid.annotations[i];
                    if(annotation.annotation_term_ilx == datasets.valueRestrictionILX) {
                        allowedValues.push(annotation.value);
                        continue;
                    }
                    if(annotation.annotation_term_ilx == datasets.valueRangeILX) {
                        var range = annotation.value.replace(" ", "").replace("[", "").replace("]", "");
                        var allowedRangeSplit = range.split(",");
                        if(allowedRangeSplit.length == 3) {
                            allowedRange = allowedRangeSplit;
                            continue;
                        }
                    }
                }

                if(allowedValues.length > 0) {
                    vr.allowedValues = allowedValues;
                } else if(allowedRange) {
                    vr.allowedRange = allowedRange;
                }
                datasets.term_restrictions_map[field.termid.id] = vr;
            }

            return datasets.term_restrictions_map[field.termid.id];
        };

        datasets.editDataset = function(dataset, name, long_name, description, publications, metadata, lab_status) {
            return $http.post("/api/1/datasets/edit", {
                datasetid: dataset.id,
                name: name,
                long_name: long_name,
                description: description,
                publications: publications,
                metadata: metadata,
                lab_status: lab_status
            });
        };

        datasets.editDatasetStatus = function(dataset, status) {
            var promise = $q(function(resolve, reject) {
                var data = {
                    datasetid: dataset.id,
                    status: status
                };
                $http.post("/api/1/datasets/change-lab-status", data)
                    .then(function(response) {
                        resolve(response.data.data);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        };

        // This is used by curation tool
        datasets.editCurationStatus = function(dataset_id, status) {
            //alert(" I want to edit Curation Status AND save Request DOI fields!");
            var promise = $q(function(resolve, reject) {
                var data = {
                    datasetid: dataset_id,
                    status: status
                };
                $http.post("/api/1/datasets/change-curation-status", data)
                    .then(function(response) {
                        resolve(response.data.data);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        }

        // This is used by curation tool
        datasets.editEditorStatus = function(dataset_id, status) {
            //alert(" I want to edit Curation Status AND save Request DOI fields!");
            var promise = $q(function(resolve, reject) {
                var data = {
                    datasetid: dataset_id,
                    status: status
                };
                $http.post("/api/1/datasets/change-editor-status", data)
                    .then(function(response) {
                        resolve(response.data.data);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        }

        datasets.flattenTemplate = function(template) {
            template.flatFields = [];
            if(template.fields) {
                template.fields.sort(function(a,b) {
                    if(a.position < b.position) return -1;
                    if(a.position > b.position) return 1;
                    return 0;
                });
                for(var j = 0; j < template.fields.length; j++) {
                    field = template.fields[j];
                    var flatField = $.extend(true, {}, field);
                    template.flatFields.push(flatField);
                }
            }
        };

        datasets.parseCDE = function(rawCDE) {
            var cde = {
                label: rawCDE.label,
                ilx: rawCDE.ilx,
                definition: rawCDE.definition,
                annotations: []
            };
            for(var i = 0; i < rawCDE.annotations.length; i++) {
                cde.annotations.push({
                    value: rawCDE.annotations[i].value,
                    annotationsTermLabel: rawCDE.annotations[i].annotation_term_label,
                    annotationTermILX: rawCDE.annotations[i].annotation_term_ilx
                });
            }
            return cde;
        };

        datasets.createUploadable = function(dataset, file_contents) {
            var uploadable = {
                errors: {
                    file: "",
                    missing_header: [],
                    extra_header: [],
                    incomplete_records: [],
                    invalid_records: [],
                    missing_subject: []
                },
                success: true,
                complete: false,
                hasError: function() {
                    return (
                        this.errors.file ||
                        this.errors.missing_header.length > 0 ||
                        this.errors.incomplete_records.length > 0 ||
                        this.errors.invalid_records.length > 0 ||
                        this.errors.missing_subject.length > 0 ||
                        this.errors.extra_header.length > 0
                    );
                },
            };

            if(file_contents.length < 2 || file_contents[0].length < 1) {
                uploadable.success = false;
                uploadable.errors.file = "No records";
                return uploadable;
            }
            var header = file_contents[0];
            var data = file_contents.slice(1);

            var template_fields = dataset.template.fields;
            var column_mapping = [];
            for(var i = 0; i < template_fields.length; i++) {
                var found = false;
                for(var j = 0; j < header.length; j++) {
                    if(template_fields[i].name === header[j]) {
                        column_mapping.push(j);
                        found = true;
                        break;
                    }
                }
                if(!found) {
                    uploadable.success = false;
                    uploadable.errors.missing_header.push(template_fields[i].name);
                }
            }
            if(header.length > template_fields.length) {
                for(var i = 0; i < header.length; i++) {
                    if(column_mapping.indexOf(i) === -1) {
                        uploadable.errors.extra_header.push(header[i]);
                    }
                }
            }

            var final_data = [];
            if(uploadable.success) {
                for(var i = 0; i < data.length; i++) {
                    if(data[i].length == 1 && data[i][0] == "") { // skip if record is empty line
                        continue;
                    }
                    var datum = {};
                    var success = true;
                    for(var j = 0; j < column_mapping.length; j++) {
                        if(column_mapping[j] >= data[i].length) {
                            uploadable.errors.incomplete_records.push({line: i + 2, data: data[i]});
                            success = false;
                            break;
                        }
                        if(datasets.isSubjectField(template_fields[j]) && !data[i][column_mapping[j]].replace(/^\s+|\s+$/g, "")) {
                            uploadable.errors.missing_subject.push({line: i + 2, data: data[i]});
                            success = false;
                            break;
                        }
                        if(!datasets.validateRecordField(template_fields[j], data[i][column_mapping[j]])) {
                            uploadable.errors.invalid_records.push({line: i + 2, data: data[i]});
                            success = false;
                            break;
                        }
                        datum[template_fields[j].name] = data[i][column_mapping[j]];
                    }
                    if(success) {
                        final_data.push({
                            status: "none",
                            values: datum,
                            upload_success: true
                        });
                    }
                }
            }
            uploadable.data = final_data;
            if(final_data.length < 1 && uploadable.success) {
                uploadable.success = false;
                uploadable.errors.file = "No records";
            }
            return uploadable;
        };

        datasets.validateRecordField = function(template_field, data_record_field) {
            var value_restriction_exists = false;
            var value_restriction_valid = false;
            var value_range_exists = false;
            for(var i = 0; i < template_field.termid.annotations.length; i++) {
                var annotation = template_field.termid.annotations[i];
                if(annotation.annotation_term_ilx == datasets.valueRestrictionILX) {
                    if(value_restriction_valid) {
                        continue;
                    }
                    value_restriction_exists = true;
                    if(annotation.value == data_record_field) {
                        value_restriction_valid = true;
                    }
                }
                if(annotation.annotation_term_ilx == datasets.valueRangeILX) {
                    if(value_range_exists) {    // only check one value range
                        continue;
                    }
                    value_range_match = annotation.value.match(/\[(\d+\.*\d*),\s*(\d+.*\d*),\s*(\d+.*\d*)\]/)
                    if(value_range_match.length != 4) {
                        continue;
                    }
                    var start = parseFloat(value_range_match[1]);
                    var stop = parseFloat(value_range_match[2]);
                    var step = parseFloat(value_range_match[3]);
                    if(isNaN(start) || isNaN(stop) || isNaN(step)) {
                        continue;
                    }

                    value_range_exists = true;
                    var value_float = parseFloat(data_record_field);
                    if(isNaN(value_float)) {
                        return false;
                    }

                    if(value_float < start || value_float > stop) {
                        return false;
                    }
                    var lower_diff = value_float - start;
                    var diff_divide = lower_diff / step;
                    if(Math.fmod(diff_divide, 1.0) > 0.001) {
                        return false;
                    }
                }
            }

            if(value_restriction_exists && !value_restriction_valid) {
                return false;
            }
            return true;
        };

        datasets.getLab = function() {
            var promise = $q(function(resolve, reject) {
                if(!lab) {
                    $http.get("/api/1/lab?labid=" + datasets.labid)
                        .then(function(response) {
                            lab = response.data.data;
                            resolve(lab);
                        }, function(response) {
                            reject(response.errormsg);
                        });
                } else {
                    resolve(lab);
                }
            });
            return promise;
        };

        datasets.getCDEFromModal = function() {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "/templates/labs/get-cde-modal.html",
                controller: "chooseCDEModalController",
                resolve: {
                    portalName: function() {
                        return datasets.portalName;
                    },
                }
            });

            return $q(function(resolve, reject) {
                modalInstance.result.then(function(data) {
                    resolve(data);
                }, function() {
                    reject();
                });
            });
        };

        datasets.validTemplateFieldName = function(name) {
            if(name.trim() == '') return false;
            if(!name) return false;
            if(name[0] == "_") return false;
            return true;
        };

        datasets.workflow_paths = {
            add_data: {
                steps: [
                    {step: "Add data"},
                    {step: "Choose method"}
                ],
                parent: "",
                parent_final_index: 0,
                clickFun: function(index) {
                    if(index == 0) {
                        $window.location.href = datasets.urls.addData();
                        return true;
                    }
                    return false;
                }
            },
            create_dataset_from_existing_data: {
                steps: [
                    {step: "Upload CSV dataset"},
                    {step: "View dataset"},
                ],
                parent: "create_dataset",
                parent_final_index: 0
            },
            create_new_dataset_from_template: {
                steps: [
                    {step: "Create new dataset from existing template"},
                    {step: "Add data to new dataset"},
                    {step: "View dataset"},
                ],
                parent: "create_dataset",
                parent_final_index: 0,
                clickFun: function(index) {
                    if(index == 0) {
                        $window.location.href = datasets.urls.createDatasetFromExisting();
                    }
                }
            },
            create_template: {
                steps: [
                    {step: "Choose template method"},
                    {step: "Create template"},
                    {step: "View template"},
                ],
                parent: "create_dataset",
                parent_final_index: 0,
                clickFun: function(index) {
                    if(index == 0) {
                        $window.location.href = datasets.urls.createTemplate();
                        return true;
                    }
                    return false;
                }
            },
            create_template_from_existing_data: {
                steps: [
                    {step: "Create template from existing data"},
                    {step: "View template"},
                ],
                parent: "create_template",
                parent_final_index: 0,
            },
            manually_create_template: {
                steps: [
                    {step: "Manually create template"},
                    {step: "View template"},
                ],
                parent: "create_template",
                parent_final_index: 0,
            },
            add_to_existing_dataset: {
                steps: [
                    {step: "Add to existing dataset"},
                    {step: "Dataset review"},
                ],
                parent: "add_data",
                parent_final_index: 0,
            },
            create_dataset: {
                steps: [
                    {step: "Choose dataset method"},
                    {step: "Upload data"},
                    {step: "View dataset"}
                ],
                parent: "add_data",
                parent_final_index: 0,
                clickFun: function(index) {
                    if(index == 0) {
                        $window.location.href = datasets.urls.createDataset();
                        return true;
                    }
                    return false;
                }
            }
        };

        datasets.urls = {
            addData: function() {
                return "/" + datasets.portalName + "/lab/add-data?labid=" + datasets.labid;
            },
            createDataset: function() {
                return "/" + datasets.portalName + "/lab/create-dataset?labid=" + datasets.labid;
            },
            createDatasetFromExisting: function() {
                return "/" + datasets.portalName + "/lab/create-dataset?labid=" + datasets.labid + "&from-existing";
            },
            createTemplate: function() {
                return "/" + datasets.portalName + "/lab/create-template?labid=" + datasets.labid;
            }
        };

        datasets.isILXFormat = function(s) {
            var regex = /^ilx:\d+$/i
            return regex.test(s);
        };

        datasets.ilxUserToDBFormat = function(ilx) {
            return ilx.replace(/^ilx:/i, "ilx_");
        };


        datasets.getDOIKeyvalues = function(datasetid) {
            var promise = $q(function(resolve, reject) {
                 $http.get("/api/1/datasets/doi/keyvalues?dataset_id=" + datasetid)
                      .then(function(response) {
                            keyvalues = response.data.data;
                            resolve(keyvalues);
                        }, function (response) {
                            reject(response.errormsg);
                        });
            });
            return promise;
        };

        datasets.getDOIAuthors = function(datasetid) {
            var promise = $q(function(resolve, reject) {
                 $http.get("/api/1/datasets/doi/authors?dataset_id=" + datasetid)
                      .then(function(response) {
                            keyvalues = response.data.data;
                            resolve(keyvalues);
                        }, function (response) {
                            reject(response.errormsg);
                        });
            });
            return promise;
        };

        datasets.saveRequestDOIFields = function(dataset_id, result) {
            var promise = $q(function(resolve, reject) {
                if (result.form.pending_deadlines === '')
                    result.form.pending_deadlines = null;
                if (result.form.journals_involved === '')
                    result.form.journals_involved = null;
                if (result.form.relevant_details === '')
                    result.form.relevant_details = null;

                var postData = {
                    dataset_id: dataset_id,
                    data: {
                        "pending_deadlines": result.form.pending_deadlines,
                        "journals_involved": result.form.journals_involved,
                        "relevant_details": result.form.relevant_details
                    },
                    type: "request_form",
                };

                $http.post("/api/1/datasets/doi/keyvalues/multipleAdd", postData)
                    .then(function(response) {
                        resolve(response.data.data);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        }

        datasets.requestDOI = function(dataset_id) {
            var promise = $q(function(resolve, reject) {
                var data = {
                    "datasetid": dataset_id
                };

                $http.post("/api/1/datasets/request-doi", data)
                    .then(function(response) {
                        resolve(response.data.data);
                    }, function(e) {
                        reject(e.errormsg);
                    });
            });
            return promise;
        }

        return datasets;
    }]);

    datasets_app.controller("deleteConfirmController", ["$scope", "$uibModalInstance", "host", function($scope, $uibModalInstance, host) {
        $scope.host = host;

        $scope.delete = function() {
            $uibModalInstance.close();
        };
        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);

    datasets_app.controller("deleteConfirmControllerGeneric", ["$scope", "$uibModalInstance", "text", "button_text", function($scope, $uibModalInstance, text, button_text) {
        $scope.text = text;
        $scope.button_text = button_text;

        $scope.delete = function() {
            $uibModalInstance.close();
        };
        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);

/*

    datasets_app.controller("chooseCDEModalController", ["$uibModalInstance", "$log", "$scope", "$http", "portalName", function($uibModalInstance, $log, $scope, $http, portalName) {
*/
    datasets_app.component("autogenTemplateComponent", {
        templateUrl: "/templates/labs/autogen-template-component.html",
        bindings: {
            type: "@",
            "dataset": "=",
            labid: "=",
            portalName: "@"
        },
        controller: function($http, $log, $scope, datasets, $uibModal, $q, $window, errorModalCaller) {
            var that = this;

            this.mode = "naming";
            this.errors = {};
            this.notices = {};

            this.notices_file = {
                // notice is like an alert/warning. can still proceed
                hasNotice: function() {
                    if (
                        this.fields_to_add.length > 0 ||
                        this.fields_to_delete.length > 0
                    ) return true;
                    return false;
                },
                reset: function() {
                    this.fields_to_add = [];
                    this.fields_to_delete = [];
                }
            };

            this.errors_file = {

                hasError: function() {
                    if(
                        this.could_not_read_file ||
                        this.empty_file ||
                        this.blank_headers_count > 0 ||
                        this.invalid_header.length > 0 ||
                        this.repeated_header.length > 0 ||
                        this.no_data_provided ||
                        this.fieldmismatch.length > 0 ||
                        this.field_too_wide > 0
                    ) return true;
                    return false;
                },
                reset: function() {
                    this.could_not_read_file = false;
                    this.empty_file = false;
                    this.blank_headers_count = 0;
                    this.invalid_header = [];
                    this.repeated_header = [];
                    this.no_data_provided = false;
                    this.fieldmismatch = [];
                    this.field_too_wide = 0;
                }   
            };

            this.errors_file.reset();
            this.notices_file.reset();

            this.file_contents = null;
            this.fields = {
                dataset_name: "",
                dataset_description: "",
                template_name: "",
                dataset_publications: ""
            };
            this.subject_index = null;
            this.submit_blocked = false;
            this.uploadable = null;
            this.submitting_template = {
                in_progress: false,
                percent: 0
            };
            this.goodFile = false;
            this.filename = "";
            this.user_updated_new_template_name = false;
            this.dataset_name_pristine = true;
            this.preview_page = 1;
            this.preview_per_page = 20;
            this.show_subject_pick_help = false;
            this.highlight_subject_row = false;

            this.downloadCSVUrl = '/php/dataset-csv.php?datasetid=' + $("#dataset_id").val();

            this.changeMode = function(mode) {
                that.mode = mode;
                if ((that.mode == 'namingUpload') || (that.mode == 'namingAppend')) {
                    document.getElementById('step0').classList.add("is-complete");
                    document.getElementById('step1').classList.remove("is-complete");
                    document.getElementById('step1').classList.add("is-active");
                    document.getElementById('step2').classList.remove("is-active");
                    document.getElementById('step2').classList.remove("is-complete");
                    document.getElementById('step3').classList.remove("is-complete");
//                    document.getElementById('step4').classList.remove("is-complete");
                    that.resetFile();
                    pageWorkflowClear("select-file");
                } else if (that.mode == 'naming') {
                    that.resetFile();
                    pageWorkflowClear("select-file");
                    document.getElementById('step0').classList.remove("is-complete");
                    document.getElementById('step0').classList.add("is-active");
                    document.getElementById('step1').classList.remove("is-complete");
                    document.getElementById('step1').classList.remove("is-active");
                    document.getElementById('step2').classList.remove("is-complete");
                    document.getElementById('step3').classList.remove("is-complete");
//                    document.getElementById('step4').classList.remove("is-complete");
                }
            };

            this.submitTemplateButtonValue = function() {
                return "Preview (Next)";
            };

            this.defaultILX = function() {
                return datasets.defaultILX;
            };

            this.showSubjectPickHelp = function() {
                that.show_subject_pick_help = true;
                that.highlight_subject_row = true;
            };

            this.previewStartIndex = function() {
                return (that.preview_page - 1) * that.preview_per_page;
            };

            this.hasUnmappedCDEs = function() {
                if(!that.file_contents || !that.file_contents.header) {
                    return false;
                }
                for(var i = 0; i < that.file_contents.header.length; i++) {
                    if(that.file_contents.header[i].term.ilx == that.defaultILX()) {
                        return true;
                    }
                }
                return false;
            };

            this.resetFile = function() {
                that.errors_file.reset();
                that.file_contents = null;
                that.filename = null;
                that.goodFile = false;
            };

            this.userUpdatedNewTemplateName = function() {
                that.user_updated_new_template_name = true;
            };

            this.csvHeaderError = function(header) {
                var problems = [];
                if(!header) {
                    problems.push("Empty header");
                    return problems;
                }
                if(that.errors_file.invalid_header.indexOf(header) !== -1) {
                    problems.push("Invalid header");
                }
                if(that.errors_file.repeated_header.indexOf(header) !== -1) {
                    problems.push("Repeated header");
                }
                return problems;
            };

            this.newDatasetHasErrors = function() {
                if(!that.new_dataset || !that.new_dataset.added_records) {
                    return false;
                }
                for(var i = 0; i < that.new_dataset.added_records.length; i++) {
                    if(!that.new_dataset.added_records[i].success) {
                        return true;
                    }
                }
                return false;
            };

            // papa parse errors ....
            function DoNotFunction(error, message) {
                $("#csv_error").append("<span style='color:red'>Error(s) detected around row(s) " + (error.join()) + ". Note: there may be more ...</span><br />\n");
                $("#csv_error").append("<span style='color:red'><strong>Errors to look for:</strong></span><br />\n");
                $("#csv_error").append("<span style='color:red'>" + message + "</span>");
            }

            function NoticeFunction(error, message) {
                $("#update_notification").append("boo hoo");
            }

            $scope.set_color = function(notice) {
                if (notice.trim() == that.dataset.subject_field_name.trim())
                    return {color: "red"};
            }

            $scope.subject_id = function(notice) {
                if (notice.trim() == that.dataset.subject_field_name.trim())
                    return notice.trim() + " (Subject ID)";
                else
                    return notice.trim();
            }

            this.datasetFileSelect = function(evt) {
                that.resetFile();
                pageWorkflowClear("select-file");

                if (that.type != 'dataset') {
                    that.whichNaming = document.getElementById('whichNaming').value;
                }

                var f = evt.target.files[0];
                that.filename = f.name;
                that.resetBrowse();
                if(!f) {
                    that.errors_file.could_not_read_file = true;
                    return;
                }

                // PapaParse is a Javascript CSV parser. using it to stop Excel file being allowed
                Papa.parse(f, {
                    delimiter: "",  // auto-detect
                    newline: "",    // auto-detect
                    quoteChar: '"',
                    escapeChar: '"',
                    skipEmptyLines: true,
                    header: true,

                /*    step: function(results, parser) {
                        if (results.errors.length) {
                            parser.abort();
                            DoNotFunction(results.errors);
                        }
                    },
                */    

                    error: function(err, file, inputElem, reason)
                    {
                        // executed if an error occurs while loading the file,
                        // or if before callback aborted for some reason
                    },

                    complete: function(results) {
                        data = results;

                        // check to see if data can be POSTed. size will increase a lot due to json_encoding
                        num_fields = Object.keys(results.data[0]).length;
                        num_rows = results.data.length;

                        // The 4 = \" \" and the 2 = \n ... (num_rows -1) is commas between rows ... 66 characters for template stuff
                        ne = (4 * num_fields) * num_rows  + (num_rows - 1) + (num_rows * 2) + (66 * num_fields);
                        if ((f.size + ne) > (75 * 1024 * 1000)) {
                            alert("Warning: file may be too large (fields * rows). You can proceed, but the upload may fail. We recommend uploading a smaller file and then adding the remaining data.")
                        }

                        if (that.type == 'dataset-update') {
                            var old_fields = [];
                            var new_fields = []; // array for trimmed fields
                            var add_fields = []; // if new header field not in old_fields
                            var delete_fields = [];

                            for (var i=0; i<that.dataset.template.fields.length; i++) {
                                old_fields.push(that.dataset.template.fields[i].name);
                            }
                        }

                        let wideSet = new Set();
                        that.hasSubject = false;

                        // checking that.dataset.subject_field_name in the new file (exact match, no trims)
                        var WideTest = Object.keys(results.data[0]).forEach(key => {
                            var trimmed_field = key.trim();

                            // check to see if field is too big
                            if (key.length > 64) {
                                wideSet.add(key);
                            }

                            if (that.type == 'dataset-update') {
                                if (!that.hasSubject) { // skip if subject already found in loop
                                    if (key == that.dataset.subject_field_name) {
                                       that.hasSubject = true;
                                    }
                                }

                                new_fields.push(key);
                            }
                        });

                        if (that.type == 'dataset-update') {
                            $scope.was_subject = that.dataset.subject_field_name;

//                            console.log("Know the old subject was: " + that.dataset.subject_field_name + " at: " + that.subject_index);


                            var fields_to_delete = old_fields.filter(x => !new_fields.includes(x));
                            var fields_to_add = new_fields.filter(x => !old_fields.includes(x));
                            if ($scope.notice_add) {
                                $scope.notice_add.length = 0;    
                            }
                            if ($scope.notice_delete) {
                                $scope.notice_delete.length = 0;
                            }

                            if (fields_to_add.length) {
                                //that.errors_file.fields_to_add.push(fields_to_add);
                                //DoNotFunction(that.errors_file.fields_to_add, "There are new fields!");
                                $scope.notice_add = fields_to_add;
                            }

                            if (fields_to_delete.length) {
                                $scope.notice_delete = fields_to_delete;
                                $scope.subject_deleted = that.dataset.subject_field_name;
                                //that.errors_file.fields_to_delete.push(fields_to_delete);
                            }
                            $scope.$apply();
                        }

                        if (wideSet.size) {
                            that.errors_file.fieldmismatch.push(3);
                            $scope.foooo = "There are " + wideSet.size + " field(s) that are wider than the allowed 64 characters";
                            $scope.$apply();
                        }

                        let mySet = new Set();
                        for(var i = 0; i < Math.min(5, results.errors.length); i++) {
                            that.errors_file.fieldmismatch.push(results.errors[i].row + 1);
                            mySet.add(results.errors[i].message);
                            if(that.errors_file.hasError()) {
                                $scope.$apply();
                            }
                        }

                        // Excel files usually have [Content_Types].xml in header, so use this to catch ...
                        var Testresult = Object.keys(results.data[0]);
                        if (Testresult[0].indexOf("[Content_Types].xml") != -1) {
                            DoNotFunction(that.errors_file.fieldmismatch, "Only CSV files are allowed.");
                            return;
                        }

                        if (results.errors.length) {
                            DoNotFunction(that.errors_file.fieldmismatch, [...mySet].join("<br />\n"));
                        }

                    }
                });

                var r = new FileReader();
                r.onload = function(e) {
                    /* get the contents from the file */
                    var raw_csv_array = CSVtoArray(e.target.result, ",");
                    if(raw_csv_array.length < 1 || raw_csv_array[0].length < 1) {
                        that.errors_file.empty_file = true;
                    }
                    var raw_header = raw_csv_array[0];

                    /* validate header */
                    var seen_headers = {};
                    var header = [];
                    that.errors_file_blank_headers_count = 0;
                    for(var i = 0; i < raw_header.length; i++) {
                        var lower_header = raw_header[i].toLowerCase();

                        if (raw_header[i].length > 64) {
                            //console.log(raw_header[i] + " is too long: " + raw_header[i].length);
                            that.errors_file.field_too_wide += 1;
                        }


                        if(raw_header[i].replace(/^\s+|\s+$/g, "") == "") {
                            that.errors_file.blank_headers_count += 1;
                        }
                        if(seen_headers[lower_header]) {
                            that.errors_file.repeated_header.push(lower_header);
                            var column_label_1 = excelColumnFromNumber(i);
                            var column_label_2 = excelColumnFromNumber(seen_headers[lower_header]);
                            alert("Duplicate header found:\nColumn " + column_label_1 + " - '" + raw_header[i] + "'\nColumn " + column_label_2 + " - '" + raw_header[seen_headers[lower_header]] + "'");
                        }
                        if(!datasets.validTemplateFieldName(raw_header[i])) {
                            that.errors_file.invalid_header.push(raw_header[i]);
                        }
                        seen_headers[lower_header] = i;
                        header.push({
                            name: raw_header[i],
                            position: i,
                            id: i,
                            term: {
                                ilx: datasets.defaultILX,
                                label: "default"
                            }
                        });
                    }

                    //if(that.errors_file.hasError()) {
                    //    $scope.$apply();
                    //    return;
                    //}

                    /* check if second row is an ilx row */
                    var has_ilx_row = true;
                    var ilx_row = [];
                    if(raw_csv_array.length > 1) {
                        var second_row = raw_csv_array[1];
                        if(second_row.length < header.length) {
                            has_ilx_row = false;
                        } else {
                            for(var i = 0; i < second_row.length; i++) {
                                if(!datasets.isILXFormat(second_row[i])) {
                                    has_ilx_row = false;
                                    break;
                                }
                                ilx_row.push(second_row[i]);
                            }
                        }
                    }
                    if(has_ilx_row) {
                        for(var i = 0; i < header.length; i++) {
                            var ilx = datasets.ilxUserToDBFormat(ilx_row[i]);
                            header[i].term.ilx = ilx;
                            if(ilx != datasets.defaultILX) {
                                header[i].term.label = "mapped";
                            }
                        }
                    }

                    /* parse contents into an object */
                    var contents = {
                        header: header,
                        data: []
                    };
                    if ((that.type == 'dataset') || (that.type == 'dataset-update')){
                        var content_start_row = 1;
                        if(has_ilx_row) {
                            content_start_row = 2;
                        }
                        if(raw_csv_array.length > content_start_row) {
                            contents.data = raw_csv_array.slice(content_start_row);
                        }

                        if(contents.data.length == 0) {
                            that.errors_file.no_data_provided = true;
                        }
                    }

                    if (that.type == 'dataset-update') {
                        var sub_index = raw_header.indexOf(that.dataset.subject_field_name);
                        if (sub_index != -1)
                            that.subject_index = raw_header.indexOf(that.dataset.subject_field_name);
                        else
                            that.subject_index = null;
                    }

                    if ((that.type == 'dataset-update') && !that.hasSubject) {
                        that.errors_file.missing_subject = true;
                        $scope.missing_subject = true;
                    }

                    that.file_contents = contents;
                    that.goodFile=true;

                    $scope.$evalAsync();
                };
                r.readAsText(f);

            };

            this.moveHeader = function(header, direction) {
                var new_pos;
                var old_pos = header.position;
                if(direction == "up") {
                    if(header.position == 0) return;
                    new_pos = header.position - 1;
                } else {    // down
                    if(header.position == that.file_contents.header.length - 1) return;
                    new_pos = header.position + 1;
                }

                for(var i = 0; i < that.file_contents.header.length; i++) {
                    if(that.file_contents.header[i].position == new_pos) {
                        that.file_contents.header[i].position = old_pos;
                        break;
                    }
                }
                header.position = new_pos;
            };

            this.named = function() {
                if(
                    (that.type == "template" || that.fields.dataset_name) &&
                    (that.type == 'template' || that.fields.dataset_description) &&
                    (that.type == 'dataset' || that.fields.template_name) &&
                    that.file_contents &&
                    (that.type == 'template' || that.new_dataset_name_unique)
                ) return true;
                return false;
            };

            this.submitable = function() {
                if(
                    !that.submit_blocked &&
                    (that.type == 'template' || that.fields.dataset_name) &&
                    (that.type == 'template' || that.fields.dataset_description) &&
                    (that.type == 'dataset' || that.fields.template_name) &&
                    that.file_contents &&
                    that.subjectChosen() &&
                    (that.type == 'template' || that.new_dataset_name_unique)
                ) return true;

                if (!that.submit_blocked && that.type == 'dataset-update' && (that.hasSubject || that.subjectChosen())) {
                    //that.hasUnmappedCDEs() = false;
                    return true;
                }

    
                return false;
            };

            this.subjectChosen = function() {
                if(
                    that.subject_index !== null &&
                    that.subject_index >= 0 &&
                    that.subject_index < that.file_contents.header.length
                ) return true;
                return false;
            };

            this.submitFullDatasetButton = function() {
                if (that.type == 'dataset-update') {
                    if (that.whichNaming == 'namingUpload') {
                        var modalInstance = $uibModal.open({
                            animation: true,
                            templateUrl: "/templates/labs/upload-warning-modal.html",
                            controller: "uploadWarningModalController"
                        });
                    } else {
                        //console.log("Should show append modal");
                        var modalInstance = $uibModal.open({
                            animation: true,
                            templateUrl: "/templates/labs/append-warning-modal.html",
                            controller: "uploadWarningModalController"
                        });
                    }
                    modalInstance.result.then(function() {
                        submitFullDataset();
                    });
                } else {
                    if(that.hasUnmappedCDEs()) {
                        var modalInstance = $uibModal.open({
                            animation: true,
                            templateUrl: "/templates/labs/upload-missing-cdes-modal.html",
                            controller: "uploadMissingCDEsModalController"
                        });
                        modalInstance.result.then(function() {
                            submitFullDataset();
                        });
                    } else {
                        submitFullDataset();
                    }
                }
            };

            function excelColumnFromNumber(column_number) {
                var char1 = Math.floor(column_number/26);
                var char2 = column_number%26;

                if (char1 < 1) {
                    char1 = '';
                } else {
                    char1 = String.fromCharCode(65 + char1 - 1);
                }

                return char1 + String.fromCharCode(65 + char2);
            }

            function submitFullDataset() {
                if(!that.submitable()) {
                    return;
                }

                // if dataset-update, skip to post-upload since don't need CDE screen
                if (that.type == 'dataset-update')
                    that.changeMode("post-upload");
                else
                    that.changeMode("uploading");

                var fields = [];
                var header = that.file_contents.header.slice(0);
                header.sort(function(a, b) {
                    if(a.position < b.position) return -1;
                    if(a.position > b.position) return 1;
                    return 0;
                });

                if (that.type == 'dataset-update') {
                    new_fields = []; // doesn't necessarily mean it's a new, unseen field. it just means it's field in upload file
                    old_fields = []; // means field from database

                    for(var i = 0; i < that.dataset.template.fields.length; i++) {
                        old_fields.push(that.dataset.template.fields[i].name);
                    }

                }
                var subject_name = "";


                for(var i = 0; i < header.length; i++) {
                    var h = header[i];
        
                    if (that.type == 'dataset-update')
                        new_fields.push(h.name);

                    if(h.id == that.subject_index) {
                        subject_name = h.name;
//                        console.log(h.name + " in the loop");
                    }

                    // new: 'dataset-update' part, which should only skip over an existing field
//                    if(!datasets.validTemplateFieldName(h.name) || h.name === "notes" || (that.type == 'dataset-update' && old_fields.includes(h.name))) {
                    if(!datasets.validTemplateFieldName(h.name) || h.name === "notes") {
                        continue;
                    }
/*
                    if (that.type == 'dataset-update') {
                        fields.push({name: h.name, ilxid: h.term.ilx, required: false, queryable: true, position: i});
                    } else 
                        fields.push({name: h.name, ilx: h.term.ilx, required: false, queryable: true});
*/
                    fields.push({name: h.name, ilx: h.term.ilx, required: false, queryable: true});
                    //console.log(h.name);                        
                }
if (that.type == 'dddddataset-update') {
console.log(old_fields);
console.log("THose were the OLD db fields\n");

console.log(new_fields);
console.log("THese are the NEW file fields\n");

//console.log("this is the fields length: " + fields.length);

console.log(fields);
console.log("Those were actual new fields to be added");

// **** if trimmed versions are the same, then same field
// **** if untrimmed are same, then leave alone
// **** if untrimmed are different, then
// **** drop old version since file has new version

//console.log("\nDrop theSe\n");
//console.log(difference);
}

                if (that.type == 'dataset-update') {
                    var difference = old_fields.filter(x => !new_fields.includes(x));

//                    console.log(that.file_contents.data);
//                    console.log("shoudl have seen data");
                }

                var records = [];
                for(var i = 0; i < that.file_contents.data.length; i++) {
                    var record = [];
                    for(var j = 0; j < that.file_contents.header.length; j++) {
                        record.push(that.file_contents.data[i][j]);
                    }
                    records.push(JSON.stringify(record));
                }

                var post_data = {
                    // I don't know why labid isn't being bound. @ worked for the create, so why not for the update. Switch to "="?
//                    labid: that.labid
                    labid: $("#labid").val()
                };

                var submit_data = {
                    template: {
                        name: that.fields.template_name,
                        fields: fields,
                        subject: subject_name,
                    }
                };

                if(that.type == "dataset") {
                    submit_data.dataset = {
                        name: that.fields.dataset_name,
                        long_name: that.fields.dataset_name,
                        description: that.fields.dataset_description
                    };
                    submit_data.records = records;
                } else if(that.type == "template") {
                    submit_data.template_only = true;
                } else if(that.type == "dataset-update") {
                    if (that.whichNaming == 'namingUpload') {
                        submit_data.dataset = {
                            id: $("#dataset_id").val(),
                            //dataset_fields_template_id: 350,
                            drop: difference,
                            old_subject: that.dataset.subject_field_name,
                            fields: fields
                        };
                    } else {
                        submit_data.dataset = {
                            id: $("#dataset_id").val(),
                            old_subject: that.dataset.subject_field_name,
                            fields: fields
                        };
                    }
                    submit_data.records = records;
                }

                post_data.data = submit_data;
                
                // this fixes a strange "$ctrl.portalName" issue where the value was 0 ...
                that.portalName = datasets.portalName;

                if (that.type == 'dataset') {
                    /* Comment this out for safety! */
                    $http.post("/api/1/datasets/full-upload", post_data)
                        .then(function(response) {
                            that.new_dataset = {};
                            that.new_template = {};
                            that.new_dataset.id = response.data.data.datasetid;
                            that.new_dataset.added_records = response.data.data["added-records"];
                            that.new_template.id = submit_data.dataset.dataset_fields_template_id;

                            document.getElementById('prepare-your-data').style.display = 'none';
                            document.getElementById('upload-help-buttons').style.display = 'none';
                            that.changeMode("post-upload");

                        }, function() {
                            that.changeMode("post-upload-error");
                            document.getElementById('step4').classList.remove("is-active");
                            document.getElementById('step4').classList.add("is-complete");
                        });
                   
                } else if (that.type == 'dataset-update') {
                    if (that.whichNaming == 'namingUpload') {
                        $http.post("/api/1/datasets/full-update", post_data)
                            .then(function(response) {
                                that.new_dataset = {};
                                that.new_dataset.id = response.data.data.datasetid;
                                that.new_dataset.added_records = response.data.data["added-records"];

                                that.changeMode("post-upload");
                                document.getElementById('step2').classList.remove("is-active");
                                document.getElementById('step2').classList.add("is-complete");
                                document.getElementById('step3').classList.add("is-complete");
                            }, function() {
                                that.changeMode("post-upload-error");
                                document.getElementById('step4').classList.remove("is-active");
                                document.getElementById('step4').classList.add("is-complete");
                            });
                    } else {
                        $http.post("/api/1/datasets/full-append", post_data)
                            .then(function(response) {
                                that.new_dataset = {};
                                that.new_dataset.id = response.data.data.datasetid;
                                that.new_dataset.added_records = response.data.data["added-records"];

                                that.changeMode("post-upload");
                                document.getElementById('step2').classList.remove("is-active");
                                document.getElementById('step2').classList.add("is-complete");
                                document.getElementById('step3').classList.add("is-complete");
                            }, function() {
                                that.changeMode("post-upload-error");
                                document.getElementById('step4').classList.remove("is-active");
                                document.getElementById('step4').classList.add("is-complete");
                            });

                    }
                }
            }

            this.resetBrowse = function(){
                document.getElementById("browse-file-select").value="";
            };


            this.selectIndex = function(index) {
                pageWorkflowClear("select-subject");
                that.subject_index = index;
                that.highlight_subject_row = false;
            };

            this.changeCDE = function(header) {
                var modalInstance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/choose-ilx-modal.html",
                    controller: "chooseILXModalController"
                });
                modalInstance.result.then(function(data) {
                    header.term.ilx = data.ilx;
                    header.term.label = data.label;
                });
            };

            this.newDatasetNameChange = function() {
                if(that.dataset_name_pristine) {
                    that.dataset_name_pristine = false;
                    pageWorkflowClear("dataset-name");
                }
                checkUniqueNewDatasetName(that.fields.dataset_name)
                    .then(function(unique) {
                        if(unique) {
                            that.errors.new_dataset_name = "";
                            if(!that.user_updated_new_template_name) {
                                if(that.fields.dataset_name) {
                                    that.fields.template_name = that.fields.dataset_name + " template";
                                } else {
                                    that.fields.template_name = "";
                                }
                            }
                        } else {
                            that.errors.new_dataset_name = "Name is already taken";
                        }
                    });
            };

            function checkUniqueNewDatasetName(name) {
                that.new_dataset_name_unique = false;
                return $q(function(resolve, reject) {
                    if(!name) {
                        resolve(true);
                        that.new_dataset_name_unique = true;
                        return;
                    }
                    $http.get("/api/1/datasets/check-name?labid=" + datasets.labid + "&name=" + name)
                        .then(function(response) {
                            that.new_dataset_name_unique = response.data.data;
                            resolve(response.data.data);
                        }, function() {
                            resolve(false);
                        });
                });
            }
        }
    });

    datasets_app.controller("chooseILXModalController", ["datasets", "$scope", "$uibModalInstance", "$log", "$http", function(datasets, $scope, $uibModalInstance, $log, $http) {
        $scope.new_term = null;

        $scope.filterFieldTypes = function(filter) {
            if(!filter) {
                filter = "";
            }
            var url = "/api/1/term/search_by_annotation?type=cde&count=100&term=" + filter;
            $http.get(url)
                .then(function(response) {
                    $scope.terms = {};
                    var raw_terms = response.data.data;
                    for(var i = 0; i < raw_terms.length; i++) {
                        $scope.terms[raw_terms[i].ilx] = datasets.parseCDE(raw_terms[i]);
                    }
                });
        };

        $scope.submit = function() {
            if($scope.new_term) {
                $uibModalInstance.close($scope.new_term);
            }
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };

        $scope.filterFieldTypes("");
    }]);

    datasets_app.controller("chooseCDEModalController", ["$uibModalInstance", "$log", "$scope", "$http", "portalName", function($uibModalInstance, $log, $scope, $http, portalName) {
        $scope.new_field_type_filter = "";
        $scope.new_field_type_ilx = null;
        $scope.terms = {};
        $scope.portalName = portalName;

        $scope.filterFieldTypes = function() {
            $http.get("/api/1/term/search_by_annotation?type=cde&count=30&term=" + $scope.new_field_type_filter)
                .then(function(response) {
                    $scope.terms = {};
                    var terms = response.data.data;
                    for(var i = 0; i < terms.length; i++) {
                        $scope.terms[terms[i].ilx] = terms[i];
                    }
                });
        };

        $scope.submit = function() {
            var term = $scope.terms[$scope.new_field_type_ilx];
            if(term) {
                $uibModalInstance.close($scope.terms[$scope.new_field_type_ilx]);
            } else {
                $uibModalInstance.dismiss();
            }
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss();
        };

        $scope.filterFieldTypes();
    }]);

    datasets_app.component("uploadDataComponent", {
        templateUrl: "/templates/labs/upload-data-component.html",
        bindings: {
            "dataset": "=",
            "uploadable": "=",
            "portalName": "=",
            "labid": "="
        },
        controller: function($log, datasets, $scope, $window, errorModalCaller) {
            var that = this;

            this.table_mode = "normal";
            this.submitting_data = {
                in_progress: false,
                percent: 0
            };
            this.page_length = 20;
            this.current_page = 1;
            this.upload_error_rows = [];

            var submitting_data_percent_step = 0;

            this.uploadReady = function() {
                if(that.uploadable && that.uploadable.success && !that.uploadable.complete && that.dataset && !that.submitting_data.in_progress) {
                    return true;
                }
                return false;
            };

            this.upload = function() {
                if(!that.uploadReady()) {
                    return;
                }
                that.submitting_data.in_progress = true;
                submitting_data_percent_step = 10000 / that.uploadable.data.length;
                datasets.addUploadableRecords(that.dataset, that.uploadable, in_progress_callback);
            };

            this.changeTableMode = function(mode) {
                if(mode == "error") {
                    pageWorkflowClear("dataupload-errors");
                }
                that.table_mode = mode;
            };

            this.pageIndex = function() {
                return (that.current_page - 1) * that.page_length;
            };

            this.isSubjectField = function(field) {
                return datasets.isSubjectField(field);
            };

            this.valueRestrictions = function(field) {
                return datasets.valueRestrictions(field);
            };

            var in_progress_callback = function(type) {
                switch(type) {
                    case "added":
                        that.submitting_data.percent += submitting_data_percent_step;
                        break;
                    case "complete":
                        that.upload_error_rows = [];
                        for(var i = 0; i < that.uploadable.data.length; i++) {
                            if(!that.uploadable.data[i].upload_success) {
                                that.upload_error_rows.push(i);
                            }
                        }
                        if(that.upload_error_rows.length > 0) {
                            errorModalCaller.call(that.upload_error_rows.length + " rows failed to upload.  Please check the failed records in the upload box.");
                            that.changeTableMode("upload-error");
                            that.current_page = 1;
                        } else {
                            that.submitting_data.in_progress = false;
                            var l = $window.location.href.split("/");
                            var new_l = l.slice(0, -1).join("/") + "/dataset?labid=" + datasets.labid + "&datasetid=" + that.dataset.id;
                            $window.location.href = new_l;
                        }
                        break;
                }
            }
        }
    });

    datasets_app.component("datasetWorkflow", {
        templateUrl: "/templates/labs/lab-workflow-component.html",
        bindings: {
            "workflow": "=",
            "lab": "=",
        },
        controller: function(datasets, $scope, $log){
            var that = this;
            that.$onInit = function() {
                processOverflowWorkFlow();
            };
            var workflow_paths = datasets.workflow_paths;
            this.overview_workflow_array = [];

            function processOverflowWorkFlow() {
                // initialize current
                var currOverviewStep = that.workflow.overview.path;
                var currOverviewIndex = that.workflow.overview.pathIndex;
                var currStep = workflow_paths[currOverviewStep].steps[currOverviewIndex].step;
                var parent = workflow_paths[currOverviewStep].parent;
                var type = "future";

                for(var i = workflow_paths[currOverviewStep].steps.length - 1; i > currOverviewIndex; i--){
                    currStep = workflow_paths[currOverviewStep].steps[i].step;
                    that.overview_workflow_array.push({
                        "workflow": currOverviewStep,
                        "step": currStep,
                        "step_index": i,
                        "type": type,
                    });
                }

                type = "current";
                // add current step to aray, update current to parent
                do {
                    while(currOverviewIndex >= 0){
                        currStep = workflow_paths[currOverviewStep].steps[currOverviewIndex].step;
                        that.overview_workflow_array.push({
                            "workflow": currOverviewStep,
                            "step": currStep,
                            "step_index": currOverviewIndex,
                            "type": type,
                        });
                        type="past";
                        currOverviewIndex--;

                    }
                    parent = workflow_paths[currOverviewStep].parent;
                    currOverviewIndex = workflow_paths[currOverviewStep].parent_final_index;
                    currOverviewStep = parent;
                } while(parent);
                that.overview_workflow_array = that.overview_workflow_array.reverse();
            }

            this.clickOverviewStep = function(stepObj){
                if(typeof datasets.workflow_paths[stepObj.workflow].clickFun === "function") {
                    if(datasets.workflow_paths[stepObj.workflow].clickFun(stepObj.step_index)) {
                        return;
                    }
                }
                if(typeof that.workflow.clickFun === "function") {
                    that.workflow.clickFun(stepObj);
                }
            };
        }

    });

    datasets_app.filter("labStatusPrettyFilter", function() {
        return function(status) {
            switch(status) {
                case "pending": return "Pending PI Approval";
                case "rejected": return "Rejected";
                case "approved-doi": return "Public DOI";
                case "request-doi": return "Request DOI";
                case "approved-community": return "Community Space";
                case "approved-internal": return "Lab Space";
                case "not-submitted": return "Personal Space";
            }
            return "";
        };
    });

    datasets_app.filter("datasetStatusFilter", function() {
        return function(status) {
            switch(status) {
                case "pending": return "Pending lab manager approval";
                case "rejected": return "Rejected";
                case "approved-doi": return "Public with DOI";
                case "request-doi": return "Request DOI (Publish)";
                case "approved-community": return "Release to ODC Community";
                case "approved-internal": return "Share to Lab Members";
                case "not-submitted": return "Personal (uploader and PI only)";
            }
            return "";
        };
    });

    datasets_app.filter("datasetStatusColorFilter", function() {
        return function(status) {
            switch(status) {
                case "pending": return "red";
                case "rejected": return "red";
                case "approved-doi": return "green";
                case "request-doi": return "#F0E68C";
                case "approved-community": return "blue";
                case "approved-internal": return "orange";
                case "not-submitted": return "#8A2BE2";
            }
            return "";
        };
    });

    datasets_app.component("progressBar", {
        templateUrl: "/templates/labs/progress-bar.html",
        bindings: {
            "percent": "="
        },
        controller: function() {
            var that = this;
        }
    });

    datasets_app.filter("startFrom", function() {
        return function(input, start) {
            if(input && typeof input.slice === "function") {
                start = +start;
                return input.slice(start);
            }
            return [];
        };
    });

    datasets_app.component("valueRestrictionsRange", {
        templateUrl: "/templates/labs/value-restrictions-range.html",
        bindings: {
            "range": "="
        }
    });

    datasets_app.component("valueRestrictionsValues", {
        templateUrl: "/templates/labs/value-restrictions-values.html",
        bindings: {
            "values": "="
        }
    });

    datasets_app.component("associatedFiles", {
        templateUrl: "/templates/labs/associated-files-component.html"
    });

    datasets_app.controller("uploadWarningModalController", ["$uibModalInstance", "$scope", function($uibModalInstance, $scope) {
        $scope.continue = function() {
            $uibModalInstance.close();
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);

    datasets_app.controller("uploadMissingCDEsModalController", ["$uibModalInstance", "$scope", function($uibModalInstance, $scope) {
        $scope.continue = function() {
            $uibModalInstance.close();
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);


    /* create template module */
    if($("#create-template-app").length) {
        var app = angular.module("createTemplateApp", ["datasetsApp"]);

        app.controller("createTemplateController", ["$log", "datasets", "$window", function($log, datasets, $window) {
            var that = this;
            this.mode = "choose-type";
            this.templates = null;
            this.selected_template = null;
            this.loading_single_template = false;
            this.new_template = {
                name: ""
            };
            this.workflows = {
                choose_type: {
                    title: "Create a new template",
                    workflow: [],
                    overview: {
                        path: "create_template",
                        pathIndex: 0
                    }
                },
                autogen: {
                    title: "Create template from existing data",
                    workflow: [],
                    overview: {
                        path: "create_template_from_existing_data",
                        pathIndex: 0
                    }
                },
                manual: {
                    title: "Create a new template",
                    workflow: [],
                    overview: {
                        path: "manually_create_template",
                        pathIndex: 0
                    }
                }
            };

            this.changeMode = function(mode) {
                that.mode = mode;
            };

            this.selectTemplate = function(lite_template) {
                if(that.selected_template && lite_template.id == that.selected_template.id) {
                    that.selected_template = null;
                } else {
                    that.loading_single_template = true;
                    datasets.getTemplate(lite_template.id)
                        .then(function(template) {
                            that.loading_single_template = false;
                            that.selected_template = template;
                        });
                }
            };

            this.submitable = function() {
                if(!that.new_template.name) {
                    return false;
                }
                return true;
            };

            this.createTemplate = function() {
                if(!that.submitable()) {
                    return;
                }

                if(that.selected_template) {
                    datasets.copyTemplate(that.selected_template, datasets.labid, that.new_template.name, true)
                        .then(function(response) {
                            var template = response.data.data;
                            goToNewTemplate(template);
                        });
                } else {
                    datasets.addDatasetTemplate(that.new_template.name, datasets.labid, null, true)
                        .then(function(response) {
                            var template = response.data.data;
                            goToNewTemplate(template);
                        });
                }

            };

            function goToNewTemplate(template) {
                var l = $window.location.href.split("/");
                new_l = l.slice(0, -1).join("/") + "/template?labid=" + datasets.labid + "&templateid=" + template.id;
                $window.location.href = new_l;
            }

            function refreshTemplates() {
                datasets.getTemplates(datasets.labid)
                    .then(function(response) {
                        that.templates = response;
                    });
            }

            datasets.getLab()
                .then(function(lab) {
                    that.lab = lab;
                });

            refreshTemplates();
        }]);

        angular.bootstrap(document.getElementById("create-template-app"), ["createTemplateApp"]);
    }
    /* /create template module */

    /* admin module */
    if($("#lab-management-app").length) {
        var app = angular.module("labManagementApp", ["datasetsApp", 'datatables']);

        app.controller("labManagementController", ["datasets", "$scope", "$http", "$log", "$uibModal", 'DTOptionsBuilder', 'DTColumnDefBuilder', function(datasets, $scope, $http, $log, $uibModal, DTOptionsBuilder, DTColumnBuilder,DTColumnDefBuilder) {

            // settings for the DataTable
            $scope.dtInstance = {};   
            $scope.dtOptions = DTOptionsBuilder.newOptions()
                              .withOption('paging', false)
                              .withOption('searching', true)
                              .withOption('info', false)
                              .withOption('order', [])

            $scope.searchTable = function () {
                $scope.dtInstance.DataTable.search($scope.filterbox);
                $scope.dtInstance.DataTable.search($scope.filterbox).draw();
            };

            var that = this;
            this.user = { id: $("#data-uid").val() };
            this.users = null;
            this.lab = null;
            this.lab_edit = null;
            this.edit_lab_info_mode = false;
            this.lab_datasets = null;
            this.lab_templates = null;

            this.allowed_levels = [
                {level: 0, name: "Remove user"},
                {level: 1, name: "Member"},
                {level: 2, name: "Manager"},
                {level: 3, name: "PI"}
            ];

            this.updateUserLevel = function(user, level) {
                var data = {
                    uid: user.uid,
                    review: level,
                    labid: datasets.labid
                };
                $http.post("/api/1/lab/review-user", data)
                    .then(function(response) {
                        getUsers();
                    });
            };

            this.updateUserLevelConfirm = function(user, level, title, action) {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "user-status-confirm-modal.html",
                    controller: "userStatusConfirmModalController",
                    resolve: {
                        user: function() {
                            return user;
                        },
                        text: function() {
                            return {
                                title: title,
                                action: action
                            };
                        },
                    }
                });
                modal_instance.result.then(function() {
                    that.updateUserLevel(user, level);
                });
            };

            this.changeMode = function(mode) {
                that.mode = mode;
            };

            this.toggleEditLabInfo = function() {
                that.edit_lab_info_mode = !that.edit_lab_info_mode;
            };

            this.updateLabInfo = function() {
                var data = {
                    name: that.lab_edit.name,
                    public_description: that.lab_edit.public_description,
                    private_description: that.lab_edit.private_description,
                    broadcast_message: that.lab_edit.broadcast_message,
                    labid: datasets.labid
                };
                var promise = $http.post("/api/1/lab/update", data);
                promise.then(function(response) {
                    updateLabFromSource(response.data.data);
                    that.edit_lab_info_mode = false;
                    alert("The lab info has been updated.");
                });
                return promise;
            };

            this.changeDatasetStatus = function(dataset) {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "change-dataset-status-modal.html",
                    controller: "changeDatasetStatusModalController",
                    windowClass: 'app-modal-window',
                    resolve: {
                        dataset: function() {
                            return dataset;
                        }
                    }
                });

                modal_instance.result.then(function(result) {
                    if(result.update) {
                        datasets.editDatasetStatus(dataset, result.status)
                            .then(function(new_dataset) {
                                dataset.lab_status = new_dataset.lab_status;
                                dataset.lab_status_pretty = new_dataset.lab_status_pretty;
                                dataset.lab_status_color = new_dataset.lab_status_color;
                            });
                    }

                    // Request DOI Form gets processed here
                    if(result.update_curation) {
                        datasets.saveRequestDOIFields(dataset.id, result);
                        datasets.requestDOI(dataset.id);
                        dataset.curation_status = "DOI Requestedd!";
                    }
                });
            };

            /***** don't think this part gets used ... ****/
            this.requestDOIfields = function(dataset) {
                alert("Quick!");
                /*
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "request-doi-fields-modal.html",
                    controller: "changeDatasetStatusModalController",
                    windowClass: 'app-modal-window',
                    resolve: {
                        dataset: function() {
                            return dataset;
                        }
                    }
                });
                */
                modal_instance.result.then(function(result) {
                    if(result.update) {
                        datasets.editDatasetStatus(dataset, result.status)
                            .then(function(new_dataset) {
                                dataset.lab_status = new_dataset.lab_status;
                                dataset.lab_status_pretty = new_dataset.lab_status_pretty;
                                dataset.lab_status_color = new_dataset.lab_status_color;
                            });
                    }
                    if(result.update_curation) {
                        alert("maybe here");
                        console.log("DOI Requested!");
                        datasets.requestDOI(dataset);
                        dataset.curation_status = "DOI Requestedd!";
                    }
                });
            };
            // this is for the button/link on the admin page
            this.requestDOI = function (dataset) {
                alert("try this");
             //   datasets.requestDOI(dataset);
                console.log("request complete");
            };

            // so far, so good. click was seen
            this.publishDOI = function (dataset) {
                if (confirm("Please confirm the publishing of the DOI")) {
                    datasets.editDatasetStatus(dataset, "approved-doi")
                        .then(function(new_dataset) {
                            dataset.lab_status = new_dataset.lab_status;
                            dataset.lab_status_pretty = new_dataset.lab_status_pretty;
                            dataset.lab_status_color = new_dataset.lab_status_color;
                            dataset.curation_status = new_dataset.curation_status;
                        });
                    alert("Request received. The new DOI should appear on the Public Dataset page shortly.");
                } else {
                    alert("Request canceled.");
                }
            }

            function getLabDatasets() {
                datasets.getLabDatasets(datasets.labid)
                    .then(function(datasets) {
                        that.lab_datasets = datasets;
                    });
            }

            function getLabTemplates() {
                datasets.getTemplates(datasets.labid)
                    .then(function(templates) {
                        that.lab_templates = templates;
                    });
            }

            function getLabInfo() {
                var promise = $http.get("/api/1/lab?labid=" + datasets.labid);
                promise.then(function(response) {
                    updateLabFromSource(response.data.data);
                });
                return promise;
            }

            function getUsers() {
                var promise = $http.get("/api/1/lab/users?labid=" + datasets.labid);
                promise.then(function(response) {
                    that.users = response.data.data;
                    for(var i = 0; i < that.users.length; i++) {
                        if(that.users[i].uid == that.user.id) {
                            that.user.lab_level = that.users[i].level;
                            break;
                        }
                    }
                    that.user.can_demote_self = false;
                    if(that.users[i].level == 3) {
                        for(var i = 0; i < that.users.length; i++) {
                            if(that.users[i].level >= that.user.lab_level && that.users[i].uid != that.user.id) {
                                that.user.can_demote_self = true;
                                break;
                            }
                        }
                    }
                });
                return promise;
            }

            function updateLabFromSource(data) {
                that.lab = data;
                that.lab_edit = {
                    name: that.lab.name,
                    public_description: that.lab.public_description,
                    private_description: that.lab.private_description,
                    broadcast_message: that.lab.broadcast_message
                };
            }

            getLabInfo();
            getUsers().then(function() {
                getLabTemplates();
                getLabDatasets();



            });

        }]);


        app.controller("changeDatasetStatusModalController", ["dataset", "$scope", "$uibModalInstance", "$uibModal", function(dataset, $scope, $uibModalInstance, $uibModal) {
            $scope.mode = "status";
            $scope.status = dataset.lab_status;
            $scope.current_status = dataset.lab_status;
            $scope.curation_status = dataset.curation_status;
            $scope.dataset_name = dataset.name;

            $scope.statuses = [
                //"pending",
                "rejected",
                //status"approved-doi", disable public status
                "request-doi",
                "approved-community",
                "approved-internal",
                //"not-submitted"
            ];

            // Only show "rejected" option if status is pending
            if ($scope.current_status != 'pending') {
                //$scope.statuses = $filter('filter')($scope.statuses, {name: '!rejected'})
                $scope.statuses = $scope.statuses.filter(function(item) {
                    return item !== 'rejected';
                });
            }

            $scope.done = function(newstatus) {
                if (newstatus == 'approved-doi') {
                    if (confirm("Please confirm the DOI release")) {
                        var data = {
                            update: newstatus != dataset.lab_status,
                            status: newstatus
                        };
                    } else {
                        alert("Update canceled");
                    }
                } else if (newstatus == 'approved-community') {
                    if (confirm("Please confirm the status change to: 'Release to Community Space'")) {
                        var data = {
                            update: newstatus != dataset.lab_status,
                            status: newstatus
                        };
                    } else {
                        alert("Update canceled");
                    }
                } else {
                    var data = {
                        update: newstatus != dataset.lab_status,
                        status: newstatus
                    };
                }
                $uibModalInstance.close(data);
            };


            $scope.changeMode = function(mode) {
                $scope.mode = mode;
            }

            //*** this doesn't seem to be working either .... ***//
/*
            $scope.requestDOIfields = function() {
                console.log("This didn't show the alert, but maybe because modal");
                alert("bringing up form");
                $uibModalInstance.close();
                alert("wha† closed?");
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "request-doi-fields-modal.html",
                    controller: "changeDatasetStatusModalController",
                    resolve: {
                        dataset: function() {
                            return dataset;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                    alert("try , try again");
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            }

            $scope.submitRequestDOIForm = function() {
                console.log($scope.form);
                console.log("that was the form in submitRequestDOIForm");
                console.log($scope.pending_deadlines + " is the deadline");
            }
*/

            $scope.done_requestDOI = function(result) {
                var data = {
                    update_curation: true,
                    curation_status: 'DOI Requested',
                    form: result,
                }

                $scope.curation_status = 'DOI Requested';
                $uibModalInstance.close(data);
            }

            $scope.cancel = function() {
                $uibModalInstance.dismiss();
            };
        }]);

        app.controller("userStatusConfirmModalController", ["$uibModalInstance", "user", "text", "$scope", function($uibModalInstance, user, text, $scope) {
            $scope.user = user;
            $scope.title = text.title;
            $scope.action = text.action;

            $scope.ok = function() {
                $uibModalInstance.close();
            };

            $scope.cancel = function() {
                $uibModalInstance.dismiss();
            };
        }]);


        app.controller("xyzModalController", ["$uibModalInstance", "user", "text", "$scope", function($uibModalInstance, user, text, $scope) {
            $scope.done = function() {
                alert("xyz done");
                $uibModalInstance.close();
            };

            $scope.cancel = function() {
                alert("xyz cancel");
                $uibModalInstance.dismiss();
            };
        }]);
        app.filter("levelFilter", function() {
            return function(level) {
                switch(level) {
                    case 3: return "PI";
                    case 2: return "Manager";
                    case 1: return "Member";
                    case 0: return "Pending request";
                }
                return "";
            };
        });

        angular.bootstrap(document.getElementById("lab-management-app"), ["labManagementApp"]);
    }
    /* /admin module */

    /* create dataset */
    if($("#create-dataset-app").length) {
        var app = angular.module("createDatasetApp", ["datasetsApp", "errorApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("createDatasetController", ["$log", "$q", "$window", "$location", "datasets", "errorModalCaller", function($log, $q, $window, $location, datasets, emc) {
            var that = this;
            this.templates = null;
            this.selected_template = null;
            this.new_dataset_fields = {
                name: "",
                description: "",
                publications: ""
            };
            this.request_templateid = $location.search().templateid;
            this.start_from_template_pick = !!$location.search()["from-existing"];
            this.full_templates = {};

            this.workflows = {
                autogen: {
                    title: "Create a new dataset",
                    workflow: [],
                    overview: {
                        path: "create_dataset_from_existing_data",
                        pathIndex: 0
                    }
                }
                ,
                choose_action: {
                    title: "Create a new dataset",
                    workflow: [],
                    overview: {
                        path: "create_dataset",
                        pathIndex: 0
                    },
                },
            };

            this.changeMode = function(mode) {
                that.mode = mode;
                if(that.mode == "from-template-pick") {
                    that.changeFromTemplateMode("template-pick");
                }
            };

            // switch labs for Create Dataset
            this.switchLab = function (id) {
                var l = $window.location.href.split("/");
                new_l = l.slice(0, -1).join("/") + "/create-dataset?labid=" + id;
                $window.location.href = new_l;
            }

            this.changeFromTemplateMode = function(mode) {
                that.from_template_mode = mode;
            };

            this.selectTemplate = function(template) {
                that.selected_template = template;
                if(that.full_templates.hasOwnProperty(template.id)) {
                    that.selected_template = that.full_templates[template.id];
                    that.changeFromTemplateMode("info");
                } else {
                    datasets.getTemplate(template.id)
                        .then(function(full_template) {
                            that.full_templates[full_template.id] = full_template;
                            that.selected_template = full_template;
                            that.changeFromTemplateMode("info");
                        });
                }
            };

            this.submitable = function() {
                if(
                    !that.new_dataset_fields.name ||
                    !that.new_dataset_fields.description ||
                    !that.selected_template
                ) {
                    return false;
                }
                return true;
            };

            this.submitDataset = function() {
                if(!that.submitable()) {
                    return;
                }
                datasets.addDataset(
                    that.selected_template,
                    that.new_dataset_fields.name,
                    that.new_dataset_fields.name,
                    that.new_dataset_fields.description,
                    that.new_dataset_fields.publications,
                    null
                )
                    .then(function(response) {
                        var dataset = response.data.data;
                        var l = $window.location.href.split("/");
                        new_l = l.slice(0, -1).join("/") + "/add-to-dataset?labid=" + datasets.labid + "&datasetid=" + dataset.id + "&new";
                        $window.location.href = new_l;
                    }, function(response) {
                        emc.call(response.data.errormsg);
                    });
            }

            function refreshTemplates() {
                var promise = $q(function(resolve, reject) {
                    datasets.getTemplates(datasets.labid)
                        .then(function(templates) {
                            that.templates = templates;
                            resolve(templates);
                        }, function(e) {
                            reject(e);
                        });
                });
                return promise;
            }

            this.changeMode("choose-action");
            if(this.request_templateid || this.start_from_template_pick) {
                this.changeMode("from-template-pick");
            }

            datasets.getLab()
                .then(function(lab) {
                    that.lab = lab;
                });

            refreshTemplates()
                .then(function() {
                    if(that.request_templateid) {
                        for(var i = 0; i < that.templates.length; i++) {
                            if(that.templates[i].id == that.request_templateid) {
                                that.selectTemplate(that.templates[i]);
                                break;
                            }
                        }
                    }
                });

        }]);

        angular.bootstrap(document.getElementById("create-dataset-app"), ["createDatasetApp"]);
    }
    /* /create dataset */

    /* admin module */
    if($("#curation-tools-app").length) {
        var app = angular.module("curationToolsApp", ["datasetsApp"]);
        app.controller("curationToolsController", ["datasets", "$http", "$log", "$scope", "$q", "$window",  "$uibModal", function(datasets, $http, $log, $scope, $q, $window, $uibModal) {
            $scope.changeEditorStatus = function(dataset_id, status, dataset_name) {
                $scope.dataset_id = dataset_id;
                $scope.dataset_name = decodeURI(dataset_name);
                $scope.current_status = status;
                var modal_instance = $uibModal.open({
                    backdrop: 'static',
                    keyboard: false,
                    templateUrl: "change-editor-status-modal.html",
                    controller: "changeEditorConfirmModalController",
                    scope: $scope,
                    windowClass: 'app-modal-window',
                 
                });
                
            };
            
            $scope.changeStatus = function(dataset_id, status) {
                if (status == 'curation-approved') {
                    if (confirm("Please confirm the approval of the dataset")) {
                        datasets.editCurationStatus(dataset_id, status);
                        $window.location.reload();
                    }
                } else if(status == 'request-doi-locked' || status == 'request-doi-unlocked') {
                    datasets.editCurationStatus(dataset_id, status);
                    $window.location.reload();
                } else
                    alert("Approval request canceled");
            }

            $scope.changeStatusConfirmed = function(dataset_id, status) {
                datasets.editCurationStatus(dataset_id, status)
                //alert(dataset_id);
            }

            $scope.expandCurationRow = function(dataset_id) {
                var promise = $q(function(resolve, reject) {
                $http.get("/api/1/datasets/doi/keyvalues/request_form?dataset_id=" + dataset_id)
                      .then(function(response) {
                            $("#showID_" + dataset_id).show();
                            keyvalues = response.data.data;
                            if (!$scope.clicked && keyvalues[0]) {
                                var details = "<br />Request DOI form data: ";
                                details += "<br /><p style='font-size:.8em'>&nbsp;&nbsp;<strong>" + keyvalues[0].subtype + "</strong>: " + keyvalues[0].text;
                                details += "<br />&nbsp;&nbsp;<strong>" + keyvalues[1].subtype + "</strong>: " + keyvalues[1].text;
                                details += "<br />&nbsp;&nbsp;<strong>" + keyvalues[2].subtype + "</strong>: " + keyvalues[2].text + "</p>";
                                $("#showID_" + dataset_id).append(details);
                                $scope.clicked=true;
                                resolve(keyvalues);
                            }
                        }, function (response) {
                            reject(response.errormsg);
                        });
            });
            return promise;

            }
            /*
 datasets.editDatasetStatus(dataset, result.status)
                            .then(function(new_dataset) {
                                dataset.lab_status = new_dataset.lab_status;
                                dataset.lab_status_pretty = new_dataset.lab_status_pretty;
                                dataset.lab_status_color = new_dataset.lab_status_color;
                            });
                    }
                    if(result.update_curation) {
                        console.log("DOI Requested!");
                        datasets.requestDOI(dataset);
                        dataset.curation_status = "DOI Requestedd!";
                    }            
            */
        }]);


        app.controller("changeEditorConfirmModalController", ["datasets", "$scope", "$uibModalInstance", "$window", function(datasets, $scope, $uibModalInstance, $window) {
            $scope.done = function(status) {
                if (confirm("Please confirm the editorial approval to status: " + status)) {
                    datasets.editEditorStatus($scope.dataset_id, status);
                    window.location.reload();
                } else {
                    alert("Status change canceled.");
                }
                $uibModalInstance.close();
            };

            $scope.cancel = function() {
                $uibModalInstance.dismiss();
            };
        }]);

        angular.bootstrap(document.getElementById("curation-tools-app"), ["curationToolsApp"]);
    }
    /* /admin module */



    /* create dataset 
        dataset creation process was streamlined to hide the concept of templates and
        also to reduce the options to make things easier ... good code, so don't want to lose it ...    
    */
    if($("#create-dataset-app-BEFORE-MVP1-SAVE-CODE").length) {
        var app = angular.module("createDatasetApp", ["datasetsApp", "errorApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("createDatasetController", ["$log", "$q", "$window", "$location", "datasets", "errorModalCaller", function($log, $q, $window, $location, datasets, emc) {
            var that = this;
            this.templates = null;
            this.selected_template = null;
            this.new_dataset_fields = {
                name: "",
                description: "",
                publications: ""
            };
            this.request_templateid = $location.search().templateid;
            this.start_from_template_pick = !!$location.search()["from-existing"];
            this.full_templates = {};

            this.workflows = {
                autogen: {
                    title: "Create a new dataset",
                    workflow: [],
                    overview: {
                        path: "create_dataset_from_existing_data",
                        pathIndex: 0
                    }
                },
                choose_action: {
                    title: "Create a new dataset",
                    workflow: [],
                    overview: {
                        path: "create_dataset",
                        pathIndex: 0
                    },
                },
                from_template_pick: {
                    title: "Create a new dataset",
                    workflow: [],
                    overview: {
                        path: "create_new_dataset_from_template",
                        pathIndex: 0
                    }
                }
            };

            this.changeMode = function(mode) {
                that.mode = mode;
                if(that.mode == "from-template-pick") {
                    that.changeFromTemplateMode("template-pick");
                }
            };

            this.changeFromTemplateMode = function(mode) {
                that.from_template_mode = mode;
            };

            this.selectTemplate = function(template) {
                that.selected_template = template;
                if(that.full_templates.hasOwnProperty(template.id)) {
                    that.selected_template = that.full_templates[template.id];
                    that.changeFromTemplateMode("info");
                } else {
                    datasets.getTemplate(template.id)
                        .then(function(full_template) {
                            that.full_templates[full_template.id] = full_template;
                            that.selected_template = full_template;
                            that.changeFromTemplateMode("info");
                        });
                }
            };

            this.submitable = function() {
                if(
                    !that.new_dataset_fields.name ||
                    !that.new_dataset_fields.description ||
                    !that.selected_template
                ) {
                    return false;
                }
                return true;
            };

            this.submitDataset = function() {
                if(!that.submitable()) {
                    return;
                }
                datasets.addDataset(
                    that.selected_template,
                    that.new_dataset_fields.name,
                    that.new_dataset_fields.name,
                    that.new_dataset_fields.description,
                    that.new_dataset_fields.publications,
                    null
                )
                    .then(function(response) {
                        var dataset = response.data.data;
                        var l = $window.location.href.split("/");
                        new_l = l.slice(0, -1).join("/") + "/add-to-dataset?labid=" + datasets.labid + "&datasetid=" + dataset.id + "&new";
                        $window.location.href = new_l;
                    }, function(response) {
                        emc.call(response.data.errormsg);
                    });
            }

            function refreshTemplates() {
                var promise = $q(function(resolve, reject) {
                    datasets.getTemplates(datasets.labid)
                        .then(function(templates) {
                            that.templates = templates;
                            resolve(templates);
                        }, function(e) {
                            reject(e);
                        });
                });
                return promise;
            }

            this.changeMode("choose-action");
            if(this.request_templateid || this.start_from_template_pick) {
                this.changeMode("from-template-pick");
            }

            datasets.getLab()
                .then(function(lab) {
                    that.lab = lab;
                });

            refreshTemplates()
                .then(function() {
                    if(that.request_templateid) {
                        for(var i = 0; i < that.templates.length; i++) {
                            if(that.templates[i].id == that.request_templateid) {
                                that.selectTemplate(that.templates[i]);
                                break;
                            }
                        }
                    }
                });

        }]);

        angular.bootstrap(document.getElementById("create-dataset-app"), ["createDatasetApp"]);
    }
    /* /create dataset */


    /* add data */
    if($("#add-data-app")) {
        var app = angular.module("addDataApp", ["datasetsApp"]);

        app.controller("addDataController", ["$log", "datasets", function($log, datasets) {
            var that = this;

            this.workflow = {
                title: "Add data",
                workflow: [],
                overview: {
                    path: "add_data",
                    pathIndex: 0
                }
            };

            datasets.getLab()
                .then(function(lab) {
                    that.lab = lab;
                });
        }]);

        angular.bootstrap(document.getElementById("add-data-app"), ["addDataApp"]);
    }
    /* /add data */

    /* add to dataset */
    if($("#add-to-dataset-app")) {
        var app = angular.module("addToDatasetApp", ["datasetsApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("addToDatasetController", ["$log", "$q", "$location", "$window", "$scope", "datasets", function($log, $q, $location, $window, $scope, datasets) {
            var that = this;
            this.lab_datasets = null;
            this.selected_dataset = null;
            this.file_contents;
            this.errors = {};
            this.uploadable = null;
            this.query_datasetid = $location.search().datasetid;
            this.new_dataset = !!$location.search().new;
            this.show_other_datasets = !this.query_datasetid;
            this.labid = datasets.labid;
            this.portalName = datasets.portalName;
            this.initial_loading_datasets = true;
            this.loading_single_data = false;


            this.goodFile=false;
            this.filename="";
            this.mode = "pick-csv";

            this.changeMode = function(mode) {
                that.mode = mode;
            };


            this.selectDataset = function(dataset_lite) {
                that.loading_single_dataset = true;
                datasets.getDataset(dataset_lite.id).then(function(dataset) {
                    that.loading_single_dataset = false;
                    that.selected_dataset = dataset;
                    //that.workflow.workflow[1].complete=true;
                    parseNewData();
                });
            };

            this.showOtherDatasets = function() {
                that.show_other_datasets = true;
            };

            this.dataFileSelect = function(evt) {
                that.goodFile=false;
                var f = evt.target.files[0];
                that.filename=f.name;

                // CSV file extension check
                if (f.name.split('.').pop().toLowerCase() != 'csv') {
                    that.errors.file = "Selected file is not a CSV file.";
                    return;
                }

                // empty file check
                if (f.size == 0) {
                    that.errors.file = "The CSV file was empty.";
                    return;
                }

                that.resetBrowse();
                that.errors.file = "";
                that.errors.file_lines = [];
                that.file_contents = null;
                if(!f) {
                    that.errors.file = "Could not read file";
                    return;
                }
                var r = new FileReader();
                r.onload = function(e) {
                    var contents_array = CSVtoArray(e.target.result, ",");
                    if(contents_array.length < 2 || contents_array[0].length < 1) {
                        that.errors.file = "Empty file given";
                        return;
                    }
                    that.file_contents = contents_array;
                    //that.workflow.workflow[0].complete = true;
                    parseNewData();
                    that.goodFile=true;
                    $scope.$apply();
                };
                r.readAsText(f);
            };

            this.upload = function() {
                if(!that.uploadable || !that.uploadable.success || !that.selected_dataset) {
                    return;
                }
                datasets.addUploadableRecords(that.selected_dataset, that.uploadable);
            };

            this.resetBrowse = function(){
                document.getElementById("browse-add-to-data").value="";
            };

            function getLabDatasets() {
                return $q(function(resolve, reject) {
                    datasets.getLabDatasets(datasets.labid)
                        .then(function(response) {
                            that.lab_datasets = [];
                            for(var i = 0; i < response.length; i++) {
                                if(!response[i].can_edit) continue;
                                that.lab_datasets.push(response[i]);
                            }
                            if(that.query_datasetid) {
                                for(var i = 0; i < that.lab_datasets.length; i++) {
                                    if(that.lab_datasets[i].id == that.query_datasetid) {
                                        that.selectDataset(that.lab_datasets[i]);
                                    }
                                }
                            }
                            resolve(that.lab_datasets);
                        }, function(msg) {
                            reject(msg);
                        });
                });
            }

            function parseNewData() {
                if(!that.selected_dataset || !that.file_contents) {
                    return;
                }
                that.changeMode("parsing-data");
                that.uploadable = datasets.createUploadable(that.selected_dataset, that.file_contents);
                that.changeMode("preview");

            }

            datasets.getLab()
                .then(function(lab) {
                    that.lab = lab;
                });

            getLabDatasets()
                .then(function() {
                    that.initial_loading_datasets = false;
                });
        }]);

        angular.bootstrap(document.getElementById("add-to-dataset-app"), ["addToDatasetApp"]);
    }
    /* /add to dataset */







    /* dataset */
    if($("#single-dataset-app")) {
        var app = angular.module("singleDatasetApp", ["datasetsApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("singleDatasetController", ["datasets", "$log", "$location", "$window", "$uibModal", function(datasets, $log, $location, $window, $uibModal) {
            var that = this;
            var dataset_id = $location.search().datasetid;
            this.dataset = null;
            this.page = 1;
            this.total_count = 0;
            this.per_page = 20;
            this.query = "";
            this.mode = "data";

            // if #upload, then switch modes to show associated files section
            var hash = $location.hash();
            if (hash == 'upload') {
                this.mode = 'associated-files';
            }

            this.DownloadAssociatedFile = function(link) {
                $window.location.href = '/php/file-download.php?type=associated&filename=' + link;
            }

            this.edit = {
                name: "",
                description: "",
                publications: ""
            };
            this.initial_load = true;
            this.default_ilx_count = 0;
            this.portalName = datasets.portalName;
            this.updating_info = false;
            this.error_info = false;

            this.changeMode = function(mode) {
                that.mode = mode;
            this.AssociatedFiles();

            };

        that.csv_options = {
            ilx: false,
            template_only: false
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



            this.defaultILX = function() {
                return datasets.defaultILX;
            };

            this.changeILX = function(field) {
                var modalInstance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/choose-ilx-modal.html",
                    controller: "chooseILXModalController"
                });
                modalInstance.result.then(function(data) {
                    datasets.updateFieldILX(that.dataset.lab_id, that.dataset.template, field, data.ilx, true)
                        .then(function(response) {
                            that.refreshDataset();
                        });
                });
            };

            this.updateDatasetFields = function() {
                that.updating_info = true;
                that.error_info = false;
                datasets.editDataset(that.dataset, that.edit.name, null, that.edit.description, that.edit.publications, null, null)
                    .then(function(response) {
                        that.updating_info = false;
                        that.refreshDataset();
                    }, function() {
                        that.updating_info = false;
                        that.error_info = true;
                    });
            };

            this.refreshDataset = function() {
                datasets.getDataset(dataset_id)
                    .then(function(response) {
                        that.dataset = response;
                        search();
                        that.edit.name = that.dataset.name;
                        that.edit.description = that.dataset.description;
                        that.edit.publications = that.dataset.publications;
                        updateWidths();
                        that.initial_load = false;
                        refreshDefaultILXCount();
                    }, function(err) {
                        that.initial_load = false;
                    });
            };


            this.AssociatedFiles = function() {
                datasets.getAssociatedFiles(dataset_id)
                    .then(function(response) {
                        that.dictionary = response.data.dictionary;
                        that.methodology = response.data.methodology;
                    });
            };

            this.changeDataPage = function() {
                search();
            };

            this.searchQuery = function() {
                search();
            };

            this.deleteRecord = function(record) {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/delete-confirm.html",
                    controller: "deleteConfirmControllerGeneric",
                    resolve: {
                        text: function() {
                            return "Are you sure you want to delete this record?";
                        },
                        button_text: function() {
                            return null;
                        }
                    }
                });

                modal_instance.result.then(function() {
                    datasets.deleteRecord(that.dataset, record._id)
                        .then(function() {
                            search();
                        });
                });
            };

            this.deleteDataset = function() {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/delete-confirm.html",
                    controller: "deleteConfirmControllerGeneric",
                    resolve: {
                        text: function() {
                            $modal_text = "<h2>Delete entire dataset</h2>";
                            $modal_text += "<p>This process will delete the entire dataset and all data associated information ";
                            $modal_text += "(name, description), dataset metadata (metadata editor entries), and ";
                            $modal_text += "dataset-associated documents (e.g. data dictionary).</p>";
                            $modal_text += "<p><strong>Warning</strong>: This process permanently removes this entire dataset from the ODC-SCI.</p>";
                            $modal_text += "<p>Are you sure you want to delete this dataset?</p>";

                            return $modal_text;
                        },
                        button_text: function() {
                            return "Delete entire dataset";
                        }
                    }
                });

                modal_instance.result.then(function() {
                    datasets.deleteDataset(that.dataset)
                        .then(function(response) {
                            // if user is moderator/pi, send back to admin page
                            if (that.dataset.is_moderator_viewing) {
                                $window.location = "/" + that.portalName + "/lab/admin?labid=" + datasets.labid;    
                            } else {
                                $window.location = "/" + that.portalName + "/lab?labid=" + datasets.labid;
                            }
                        });
                }, function() {

                });
            };

            this.deleteAllRecords = function() {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/delete-confirm.html",
                    controller: "deleteConfirmControllerGeneric",
                    resolve: {
                        text: function() {
                            $modal_text = "<h2>Delete records only</h2>";
                            $modal_text += "<p>This process will only delete the records (i.e. data) of your dataset. ";
                            $modal_text += "The process will keep the existing dataset name, dataset description, ";
                            $modal_text += "dataset metadata (in the metadata editor), any dataset-associated documents ";
                            $modal_text += "(e.g. data dictionary) that you have already uploaded, and any CDE mapping to ";
                            $modal_text += "the current column headers.</p>";
                            $modal_text += "<p><strong>Warning</strong>: This process will delete your existing data; we ";
                            $modal_text += "recommend that you download a copy before continuing.</p>";
                            $modal_text += "<p>Are you sure you want to delete this dataset?</p>";

                            return $modal_text;
                        },
                        button_text: function() {
                            return "Delete records";
                        }
                    }
                });

                modal_instance.result.then(function() {
                    datasets.deleteAllRecords(that.dataset)
                        .then(function() {
                            search();
                        });
                });
            };

            this.submitDataset = function() {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/delete-confirm.html",
                    controller: "deleteConfirmControllerGeneric",
                    resolve: {
                        text: function() {
                            return "Are you sure you want to share this dataset?  This will make your dataset visible to other members of your lab.";
                        },
                        button_text: function() {
                            return "Submit";
                        }
                    }
                });

                modal_instance.result.then(function() {
                    datasets.editDatasetStatus(that.dataset, "approved-internal")
                        .then(function(dataset) {
                            that.dataset.lab_status = dataset.lab_status;
                            that.dataset.lab_status_pretty = dataset.lab_status_pretty;
                            that.dataset.lab_status_color = dataset.lab_status_color;
                        });
                });
            };

            this.unsubmitDataset = function() {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/delete-confirm.html",
                    controller: "deleteConfirmControllerGeneric",
                    resolve: {
                        text: function() {
                            return "Are you sure you want to stop sharing this dataset?";
                        },
                        button_text: function() {
                            return "Unsubmit";
                        }
                    }
                });

                modal_instance.result.then(function() {
                    datasets.editDatasetStatus(that.dataset, "not-submitted")
                        .then(function(dataset) {
                            that.dataset.lab_status = dataset.lab_status;
                            that.dataset.lab_status_pretty = dataset.lab_status_pretty;
                            that.dataset.lab_status_color = dataset.lab_status_color;
                        });
                });
            };


            this.showDictionaryRequirements = function() {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/dictionary-requirements-modal.html",
                    controller: "deleteConfirmController",
                    resolve: {
                        host: function() {
                            if (window.location.href.indexOf("odc-sci"))
                                return "https://odc-sci.org";
                            else
                                return "https://odc-tbi.org";
                        }
                    }
                });
            };

            this.getDefaultILXCount = function() {
                return that.default_ilx_count;
            };

            function refreshDefaultILXCount() {
                var count = 0;
                if(!that.dataset) {
                    that.default_ilx_count = count;
                    return;
                }
                for(var i = 0; i < that.dataset.template.fields.length; i++) {
                    if(that.dataset.template.fields[i].termid.ilx == that.defaultILX()) {
                        count += 1;
                    }
                }
                that.default_ilx_count = count;
            };

            function search() {
                that.dataset.data = {};
                var offset = (that.page - 1) * that.per_page;
                datasets.search(that.dataset, that.query, offset, that.per_page, true)
                    .then(function() {
                        if(that.total_count != that.dataset.data.count) {
                            that.page = 1;
                            that.total_count = that.dataset.data.count;
                        }
                    });
            }

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
            
            this.AssociatedFiles();
            this.refreshDataset();
        }]);

        app.controller("AssociatedfilesController", ["$http", "$scope", "$rootScope", "$location", "$uibModal", "datasets", "$log", function($http, $scope, $rootScope, $location, $uibModal, datasets, $log) {

            this.errors_file = {
                hasError: function() {
                    if(
                        this.could_not_read_file ||
                        this.empty_file ||
                        this.blank_headers_count > 0 ||
//                        this.invalid_header.length > 0 ||
                        this.repeated_header.length > 0 ||
                        this.no_data_provided ||
                        this.fieldmismatch.length > 0 ||
                        this.not_csv
                    ) return true;
                    return false;
                },
                reset: function() {
                    this.could_not_read_file = false;
                    this.empty_file = false;
                    this.blank_headers_count = 0;
                    this.invalid_header = [];
                    this.repeated_header = [];
                    this.no_data_provided = false;
                    this.fieldmismatch = [];
                    this.not_csv = false;
                }   
            };

            this.resetBrowse = function(){
                document.getElementById("fileChoose_dictionary").value="";
                document.getElementById("fileChoose_methodology").value="";
            };

            // validate the Data Dictionary file. modified datasetFileSelect code ...
            var that = this;
            $scope.uploadFile = function(evt) {
                alert("upload");
                $scope.filename100 = '030';
                var f = evt.target.files[0];
                var that = $scope;
            }

            $scope.upload_associated_files = function(type) {
                var fd = new FormData();
                var files = document.getElementById('fileChoose_' + type).files[0];
                fd.append('file',files);

                // AJAX request
                $http({
                    method: 'post',
                    url: '/php/labs/associated-files-upload.php?type=' + type + '&dataset_id=' + $location.search().datasetid,
                    data: fd,
                    headers: {'Content-Type': undefined},
                    })
                .then(function successCallback(response) { 
                    // Store response data
                    if (response.data.status == "success") {
                        amessage = "File has been saved";

                        // reload after showing alert.
                        if(!alert(amessage)){window.location.reload();}
                    } else {
                        amessage = "There was an error saving the file.";
                        alert(amessage);
                    }

                    if (type == 'dictionary') {
                        //$scope.response = amessage;
                        that.dictionary = "ONE";
                        this.dictionary = "TWO";        
                    } else {
                        //$scope.response2 = amessage;
                    }
                });
            }             

            $scope.fileReset = function(type) {
                that.resetBrowse();

                if (type == 'dictionary') {
                    $scope.filename = null;
                    $scope.response = null;    
                } else {
                    $scope.filename2 = null;    
                    $scope.response2 = null;    
                }
            }

            $scope.dictionaryFileSelect = function(evt) {
                that.errors_file.reset();
  
                var f = evt.target.files[0];
                that.filename = f.name;
                
                $scope.filename = that.filename;
                

                if(!f) {
                    that.errors_file.could_not_read_file = true;
                    return;
                }
                
                // check file extension
                if (f.name.substring(f.name.length - 3, f.name.length).toLowerCase() != "csv") {
                    $scope.response = "Only CSV files are allowed." ;
                    $scope.fatal = true;
                    $scope.$evalAsync();
                    return;
                }

                var r = new FileReader();
                r.onload = function(e) {
                    var contents_array = CSVtoArray(e.target.result, ",");

                    // contents_array[0] is 1st line. [0][0] is first field.
                    // if XLS, [0][0] should have [Content_Types].xml
                    if (contents_array[0][0].indexOf("[Content_Types].xml") != -1) {
                        that.errors_file.not_csv = true;
                        that.errors_file.could_not_read_file = true;
                        $scope.response = "The file could not be read. Is it a csv file?";
                    } else {
                        var required_header = ["VariableName", "Title", "Unit_of_Measure" , "Description", "Comments", "DataType", "PermittedValues", "MinimumValue", "MaximumValue"];
                        let contents_array_trimmed = [];
                        let header_position = [];

                        // make an array of trimmed fields
                        for(i=0; i<contents_array[0].length; i++) {
                            contents_array_trimmed[i] = contents_array[0][i].trim();

                            header_position[contents_array[0][i].trim()] = i;
                        }
                        var difference = required_header.filter(x => !contents_array_trimmed.includes(x));
                        if (difference.length) {
                            $scope.response = "Data dictionary column(s) missing: " + difference.join(", ");
                            $scope.fatal = true;

                            // reset to allow rechecking same file without reload
                            that.resetBrowse();
                        } else {
                            // check to see if all template fields are in dictionary
                            datasets.getDataset($location.search().datasetid)
                                .then(function(response) {
                                    $scope.response = '';
                                    $scope.fatal = false;
                                    let warnings = 0;
                                    let template_fields = [];
                                    let file_variables = [];
                                    const array1 = ["Title", "VariableName", "Description"];

                                    for (i=0; i<response.template.fields.length; i++) {
                                        template_fields[i] = response.template.fields[i].name.trim();
                                    }

                                    // need to read the lines of the file ...
                                    for (i=1; i<contents_array.length; i++) {
                                        file_variables[i-1] = contents_array[i][header_position["VariableName"]].trim();
                                    }
                                    
                                    // check for missing fields
                                    var difference = template_fields.filter(x => !file_variables.includes(x));
                                    
                                    if (difference.length) {
                                        $scope.response = 'All dataset fields must be in the dictionary. <br />Field(s) missing: ' + difference.join(", ");
                                        $scope.fatal = true;

                                        // reset to allow rechecking same file without reload
                                        that.resetBrowse();
                                    } else {
                                        let linez = [];
                                        // check Title, VariableName, "Description" to see if all fields are filled in
                                        for (i=1; i<contents_array.length; i++) {
                                            array1.forEach(function(x){
                                                if ((typeof contents_array[i][header_position[x]] == 'undefined') || (contents_array[i][header_position[x]].trim() == '')) {
                                                    linez.push(x);
                                                }
                                            });

                                            // if it's the last line and is missing those fields, then skip the error
                                            if (i == ((contents_array.length - 1))) {
                                                if (linez.length) {
                                                    let last_line = [];
                                                    required_header.forEach(function(x) {

                                                    if ((typeof contents_array[i][header_position[x]] == 'undefined') || (contents_array[i][header_position[x]].trim() == '')) {
                                                            last_line.push(x);
                                                        }
                                                    })

                                                    // if last line is totally blank, then don't report error
                                                    if (last_line.length == required_header.length) {
                                                        linez.length = 0;
                                                        //break;
                                                    } 
                                                }
                                            } else {
                                                if (linez.length) {
                                                    $scope.response += "Line " + (i+1) + " has missing field data: " + linez.join(", ") + ".<br />";
                                                    linez.length = 0;
                                                    warnings++;
                                                    if (warnings == 5) {
                                                        $scope.response += "Note: There may be other lines missing Titles or Descriptions. When you submit the dataset for publication, every entry must have Title and Description filled in. <br />";
                                                        break;
                                                    }
                                                    //i=contents_array.length;
                                                }                                                        
                                            }
                                        }
                                    }
                                });
                        }
                    }
                    $scope.$evalAsync();
                };
                r.readAsText(f);
            };

            $scope.methodologyFileSelect = function(evt) {
                var f = evt.target.files[0];
                that.filename = f.name;
                $scope.filename2 = f.name;
                var extn = f.name.substring(f.name.length - 4, f.name.length).toLowerCase();

                // allow pdf, doc, or docx
                if (!((extn == '.pdf') || (extn == '.doc') || (extn == 'docx'))) {
                    $scope.response2 = "Only .pdf, .doc, or .docx files are allowed.";
                    $scope.$evalAsync();
                    return;
                } else {
                    $scope.response2 = "File format has been validated.";
                }
                $scope.$evalAsync();
            }
       }]);

        angular.bootstrap(document.getElementById("single-dataset-app"), ["singleDatasetApp"]);

        /* handle scrolling on top of table */
        var scroll1_selector = "#dataset-table-scroll-top";
        var scroll2_selector = "#dataset-table-wrapper table";

        $(scroll2_selector).on("resize", function() {
        });
    }
    /* /dataset */


    datasets_app.controller("deleteConfirmController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
        $scope.delete = function() {
            $uibModalInstance.close();
        };
        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };
    }]);

//datasetsApp









    /* admin module */
    if($("#doi-management-app").length) {
        var app = angular.module("doiManagementApp", ["datasetsApp", "ui.bootstrap", "ui.sortable"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });
            
        // as long as controller is in PHP file, then I guess this part will run.
        // can I take the data and use it elsewhere, or just in the controller???
        app.controller("doiController", ["$location", "$scope", "datasets", "$http", "$log", "$uibModal", "$parse", function($location, $scope, datasets, $http, $log, $uibModal, $parse) {
            $scope.importPubs = function () {
                var postData = {
                    "pub": $scope.importbox,
                    "include_authors": $scope.include_authors,
                    "dataset_id": $location.search().datasetid
                };

                var promise = $http.post("/api/1/datasets/doi/importpubs", postData);
                promise.then(function(response) {
                // update the overview page ... MESSY since "importpubs" has to query outside resource to get data ...
                });

                if (alert('Data has been added. Close this alert to reload the new data.')){}
                else    window.location.reload(); 

                /* This version gives an OK and Cancel button
                var r = confirm("Successful Message!");
                if (r == true){
                  window.location.reload();
                }
                */
                return promise;
            }

            $scope.parentObj = {};
            // get Key/values from DB
            var keyvalue_types = ['license', 'contributor', 'keyword', 'funding', 'publication', 'abstract', 'overview', 'notes'];

            // function to format author list for citation. will be called onload and when add/update author
            $scope.refreshAuthors = function() {            
                datasets.getDOIAuthors($location.search().datasetid)
                .then(function(keyvalues) {
                    var authorArray = new Array();
                    var formattedArray = new Array();
                    var contactArray = new Array();
                    var formattedArray2 = new Array();
                    var positions = new Array();

                    for (i=0; i<keyvalues.length; i++) {
                        if (!authorArray[keyvalues[i].position]) {
                            authorArray[keyvalues[i].position] = new Array();
                            positions.push(keyvalues[i].position);
                        }
                        authorArray[keyvalues[i].position][keyvalues[i].subtype] = keyvalues[i].text;
                    }

                    var authorString = "";

                    positions.forEach(function(i) {
                        formattedArray.push(build_author_string(authorArray[i]));

                        if (authorArray[i].contact == 1) {
                            formattedArray2.push(build_contact_string(authorArray[i]));
                        }
                    })

                    $scope.citation_authors = formattedArray.join(", ");
                    $scope.contact_authors = formattedArray2.join(", ");
                });
            };
            $scope.refreshAuthors();

            function build_author_string(author) {
                auth_string = author.lastname + ", " + author.firstname.substring(0,1)  + ".";
                if (!((author.middleinitial == null) || (author.middleinitial == '')))
                    auth_string += " " + author.middleinitial.substring(0,1) + ".";
                return auth_string;                
            }

            function build_contact_string(author) {
                return author.firstname + " " + author.lastname + " (" + author.email + ")";
            }

            datasets.getDOIKeyvalues($location.search().datasetid)
            .then(function(keyvalues) {
                var keyvalues_array = new Array();
                $scope.keyvalues = [];

                keyvalue_types.forEach(function(type) {
                    $scope.keyvalues[type] = [];
                    keyvalues_array[type] = new Array();
                });
            
                for (i=0; i<keyvalues.length; i++) {
                    keyvalue_types.forEach(function(type) {
                        if (keyvalues[i].type == type) {
                            if (!keyvalues_array[type][keyvalues[i].position]) {
                                keyvalues_array[type][keyvalues[i].position] = new Array();
                            }
                            keyvalues_array[type][keyvalues[i].position][keyvalues[i].subtype] = keyvalues[i].text;
                            keyvalues_array[type][keyvalues[i].position]['position'] = keyvalues[i].position;
                        }
                    });
                }

                var agency_array = [];
                var funding_array = keyvalues_array.funding;

                //build array of agencies
                for (i=0; i<funding_array.length; i++) {
                    agency_array.push(keyvalues_array.funding[i]["agency"]);
                 //   alert(keyvalues_array.funding[i]["agency"]);
                }

                // get list of unique agencies
                var agency_unique = uniqueArray1(agency_array);
                var agency_identifier = new Array();

                // initialize the counter for identifiers per agency ...
                agency_unique.forEach (function (agencyy) {
                    agency_identifier[agencyy] = 0;
                });

                agency_unique.forEach (function (agencyy) {
                    funding_array.forEach (function (fun) {
                        if (fun.agency == agencyy) {
                            agency_identifier[agencyy]++;
                        }
                    });
                });
    
                var fund_string = '';

                agency_unique.forEach (function (agencyy) {
                    // write out agency name + grant(s)
                    fund_string += agencyy;
                    if (agency_identifier[agencyy] >= 2) {
                        fund_string += " grants ";
                    } else 
                        fund_string += " ";

                    var id_array = [];

                    funding_array.forEach (function (fun) {
                        if (fun.agency == agencyy) {
                            id_array.push(fun.initials);
                        }
                    });
        
                    if (id_array.length > 2)
                        fund_string += id_array.join(", ");
                    else if (id_array.length == 2) {
                        fund_string += id_array.join(" and ");
                    } else
                        fund_string += id_array[0];
        
                    fund_string += "; ";
                });
              
                 $scope.fund_string = fund_string.substring(0, fund_string.length - 2);

                function uniqueArray1( ar ) {
                  var j = {};

                  ar.forEach( function(v) {
                    j[v+ '::' + typeof v] = v;
                  });

                  return Object.keys(j).map(function(v){
                    return j[v];
                  });
                } 

                function getLength(arr) {
                    return Object.keys(arr).length;
                }

                var agency_unique = uniqueArray1(agency_array);

                if (keyvalues_array["abstract"][0]) {
                    $scope.study_purpose = keyvalues_array["abstract"][0]["study_purpose"];
                    $scope.data_collected = keyvalues_array["abstract"][0]["data_collected"];
                    $scope.primary_conclusion = keyvalues_array["abstract"][0]["primary_conclusion"];
                    $scope.data_usage_notes = keyvalues_array["abstract"][0]["data_usage_notes"];
                    $scope.conclusions = keyvalues_array["abstract"][0]["conclusions"];
                } else {
                    $scope.study_purpose = '';
                    $scope.data_collected = '';
                    $scope.primary_conclusion = '';
                    $scope.data_usage_notes = '';
                    $scope.conclusions = '';
                }
                
                // not sure why I have to handle license like this since the doi, year, citation, title didn't need special treatment ...
                if (keyvalues_array["license"][0])
                    $scope.license = keyvalues_array["license"][0]["license"];
                else
                    $scope.license = '';

                if (keyvalues_array["overview"][0]) {
                    $scope.doi = keyvalues_array["overview"][0]["doi"];
                    $scope.title = keyvalues_array["overview"][0]["title"];
                    $scope.year = keyvalues_array["overview"][0]["year"];
                    $scope.citation = keyvalues_array["overview"][0]["citation"];
                }

                if (keyvalues_array["notes"][0]) {
                    $scope.notes = keyvalues_array["notes"][0]["notes"];
                } else {
                    $scope.notes = '';
                }

                var auth_array = [];

                var keyword_array = [];
                for(i=0; i<keyvalues_array["keyword"].length; i++) {
                    keyword_array.push(keyvalues_array["keyword"][i]["keyword"]);
                }
                $scope.keywords = keyword_array.join(", ");

                var contribs = new Array();

                $scope.listat = contribs;
                keyvalue_types.forEach(function(type) {
                    $scope.listat[type] = keyvalues_array[type];
                    $scope.parentObj[type] = keyvalues_array[type];
                });

                $scope.sortableOptions = {
                    start: function(event, ui) {
                            // Create a temporary attribute on the element with the old index
                            $(this).attr('data-id', ui.item.index());
                    },
                    stop: function (e, ui) {
                        sort_type = ui.item.sortable.source[0]["id"].replace(/sortable_/, '');

                    // looks like the one being moved
                        model = ui.item.sortable.model
                        
                    // all of the items
                        sourceModel = ui.item.sortable.sourceModel

                        var postData = {
                            dataset_id: $location.search().datasetid,
                            current: $(this).attr('data-id'),
                            desired: ui.item.index(),
                            type: sort_type
                        };

                        var promise = $http.post("/api/1/datasets/doi/keyvalues/multipleMove", postData);

                        if (sort_type == 'contributor')
                            $scope.refreshAuthors();
                        else if (sort_type == 'keyword')
                            window.location.reload();
                    }

                };
            });
        }]);

        app.controller("doiOverviewController", ["$location", "$scope", "datasets", "$http", "$log", "$uibModal", function($location, $scope, datasets, $http, $log, $uibModal) {
            $scope.showOverviewForm = function (ov) {
                $scope.typee = 'overview';
                if (ov) {
                    $scope.title = ov.title;
                    $scope.year = ov.year;
                    $scope.doi = ov.doi;

                    if ($scope.doi || $scope.year || $scope.citation || $scope.title) {
                        $scope.isUpdate = true;
                    } else {
                        $scope.isUpdate = false;
                    }                
                }

                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-overview-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        overviewForm: function () {
                            return $scope.overviewForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };

        }]);

        app.controller("doiAbstractController", ["$location", "$scope", "datasets", "$http", "$log", "$uibModal", function($location, $scope, datasets, $http, $log, $uibModal) {
            $scope.showAbstractForm = function (abs) {
                    $scope.typee = "abstract";
                if (abs) {                
                    $scope.study_purpose = abs.study_purpose;
                    $scope.data_collected = abs.data_collected;
                    $scope.primary_conclusion = abs.primary_conclusion;
                    $scope.data_usage_notes = abs.data_usage_notes;
                    $scope.position = 0;

                    if ($scope.data_collected || $scope.study_purpose || $scope.data_usage_notes) {
                        $scope.isUpdate = true;
                    } else {
                        $scope.isUpdate = false;
                    }
                }

                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-abstract-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        abstractForm: function () {
                            return $scope.abstractForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };
        }]);

        app.controller("doiNotesController", ["$location", "$scope", "datasets", "$http", "$log", "$uibModal", function($location, $scope, datasets, $http, $log, $uibModal) {
            $scope.showNotesForm = function (note) {
                    $scope.typee = "notes";
                if (note) {                
                    $scope.notes = note.notes;
                    $scope.position = 0;

                    if ($scope.notes) {
                        $scope.isUpdate = true;
                    } else {
                        $scope.isUpdate = false;
                    }
                }

                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-notes-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        notesForm: function () {
                            return $scope.notesForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };
        }]);

        app.controller("TypeaheadCtrl", ["$location", "$scope", "datasets", "$http", "$log", "$uibModal", function($location, $scope, datasets, $http, $log, $uibModal) {
            $scope.showKeywordsForm = function () {
                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-keywords-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        keywordsForm: function () {
                            return $scope.keywordsForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });                
            }

            var _selected;

            // keep original position
//            var starting_position = next_position_keyword.value;
            var starting_position = 0;

            $scope.deleteKeyValueById = function(position, dataset_id) {
                var postData = {
                    position: position,
                    dataset_id: dataset_id,
                    type: "keyword"
                };
                var promise = $http.post("/api/1/datasets/doi/keyvalues/multipleDelete", postData);
                promise.then(function(response) {
                    window.location.reload();

//                    $scope.parentObj.keyword.splice(postData.position, 1);
                });
            }
            $scope.addItem = function() {
                var postData = {
                    dataset_id: $location.search().datasetid,
                    text: this.asyncSelected,
                    data: {
                        "keyword": this.asyncSelected,
                    },                    
                    type: "keyword",
                    position: starting_position++ // update position in case more than one keyword added
                };

                var promise = $http.post("/api/1/datasets/doi/keyvalues/multipleAdd", postData);
                promise.then(function(response) {
                    alert("Keyword has been added. ");
                   // window.location.reload();
                    $scope.parentObj.keyword[response.data.data.position] = postData.data;
                    $scope.parentObj.keyword[response.data.data.position]['position'] = response.data.data.position;
                    if (response.data.data.position == 0)
                        $scope.keywords = postData.data.keyword;
                    else
                        $scope.keywords += ", " + postData.data.keyword;

                });

                return promise;
            };

            $scope.selected = undefined;
            $scope.getTerm = function(val) {
              return $http.get('/php/autocomplete.php', {
                    params: {
                        term: val,
                        sensor: false
                    }
                }).then(function(response){

                    return response.data.map(function(item){
                        return item[0];
                    });
                });
            };
             $scope.ngModelOptionsSelected = function(value) {
                if (arguments.length) {
                  _selected = value;
                } else {
                  return _selected;
                }
              };

              $scope.modelOptions = {
                debounce: {
                  default: 500,
                  blur: 250
                },
                getterSetter: true
              };

        }]);
  
        app.controller("doiPublicationsController", ["$scope", "datasets", "$http", "$log", "$uibModal", function($scope, datasets, $http, $log, $uibModal) {
            $scope.showPublicationsForm = function (pub, indexx) {
                $scope.typee = 'publication';
                $scope.publication_title = pub.publication_title;
                $scope.publication = pub.publication;
                $scope.publication_doi = pub.publication_doi;
                $scope.publication_pmid = pub.publication_pmid;
                $scope.relevance = pub.relevance;
                $scope.position = indexx;
       
                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-publications-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        publicationsForm: function () {
                            return $scope.publicationsForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };

        }]);

        app.controller("doiLicenseController", ["$scope", "datasets", "$http", "$log", "$uibModal", function($scope, datasets, $http, $log, $uibModal) {
            $scope.showLicenseForm = function () {
                $scope.typee = 'license';
                if ($scope.license) {
                    $scope.isUpdate = true;
                } else {
                    $scope.isUpdate = false;
                }

                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-license-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        licenseForm: function () {
                            return $scope.licenseForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };

        }]);

        app.controller("doiContributorsController", ["$location", "$scope", "datasets", "$http", "$log", "$uibModal", function($location, $scope, datasets, $http, $log, $uibModal) {
            $scope.removeHTTPS = function(value) {
                return value.replace("https://orcid.org/", "");
            }

            $scope.showContributorsForm = function (contributor, indexx) {
                $scope.typee = "contributor";
                if (contributor) {
                    $scope.name = contributor.name;
                    $scope.firstname = contributor.firstname;
                    $scope.lastname = contributor.lastname;
                    $scope.middleinitial = contributor.middleinitial;
                    if (contributor.author)
                        $scope.author = true;
                    else
                        $scope.author = false;
                    if (contributor.contact)
                        $scope.contact = true;
                    else
                        $scope.contact = false;
                    $scope.orcid = contributor.orcid;
                    $scope.affiliation = contributor.affiliation;
                    $scope.email = contributor.email;
                    $scope.position = indexx;
//                    $scope.position = contributor.position;
                    $scope.isUpdate = true;
                } else {
                    $scope.name = "";
                    $scope.firstname = "";
                    $scope.lastname = "";
                    $scope.middleinitial = "";
                    $scope.author = false;
                    $scope.contact = false;
                    $scope.orcid = "";
                    $scope.affiliation = "";
                    $scope.email = "";
                    $scope.isUpdate = false;
                }

                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-contributors-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        contributorsForm: function () {
                            return $scope.modalForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };

        }]);

        app.controller("doiFundingController", ["$scope", "datasets", "$http", "$log", "$uibModal", function($scope, datasets, $http, $log, $uibModal) {
            $scope.showFundingForm = function (fund, indexx) {
                $scope.typee = "funding";
                if (fund) {
                    $scope.initials = fund.initials;
                    $scope.identifier = fund.identifier;
                    $scope.agency = fund.agency;
                    $scope.position = indexx;
//                    $scope.position = fund.position;
                    $scope.isUpdate = true;
                } else {
                    $scope.initials = "";
                    $scope.identifier = "";
                    $scope.agency = "";
                    $scope.isUpdate = false;
                }

                var modalInstance = $uibModal.open({
                    backdrop: 'static', 
                    keyboard: false,
                    templateUrl: '/templates/labs/doi-funding-component.html',
                    controller: keyvalueModalInstanceCtrl,
                    scope: $scope,
                    resolve: {
                        modalForm: function () {
                            return $scope.modalForm;
                        }
                    }
                });

                modalInstance.result.then(function (selectedItem) {
                    $scope.selected = selectedItem;
                }, function () {
                    $log.info('Modal dismissed at: ' + new Date());
                });
            };

        }]);

        // build postData for "add" and "update" to use
        postDataBuild = function($scope, dataset_id) {
            switch ($scope.typee) {
                case "funding":
                    var starting_position = 0;
                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "initials": $scope.initials,
                            "agency": $scope.agency,
                            "identifier": $scope.identifier
                        },
                        type: "funding",
                        position: $scope.position // update position in case more than one keyword added
                    };
                    break;

                case "abstract":
                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "study_purpose": $scope.study_purpose,
                            "data_collected": $scope.data_collected,
                            "primary_conclusion": $scope.primary_conclusion,
                            "data_usage_notes": $scope.data_usage_notes,
                            "conclusions": $scope.conclusions,
                        },
                        type: "abstract",
                        position: 0
                    };
                    break;

                case "notes":
                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "notes": $scope.notes
                        },
                        type: "notes",
                        position: 0
                    };
                    break;

                case "overview":
                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "year": $scope.year,
                            "title": $scope.title,
                            "doi": $scope.doi,
                        },
                        type: "overview",
                        position: 0
                    };
                    break;

                case "license":
                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "license": $scope.license,
                        },
                        type: "license",
                        position: 0
                    };
                    break;

                case "contributor":
                    if (($scope.name == null) || ($scope.name == ''))
                        $scope.name = $scope.firstname + ' ' + $scope.lastname;

                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "name": $scope.name,
                            "firstname": $scope.firstname,
                            "lastname": $scope.lastname,
                            "middleinitial": $scope.middleinitial,
                            "author": $scope.author,
                            "contact": $scope.contact,
                            "orcid": $scope.orcid,
                            "affiliation": $scope.affiliation,
                            "email": $scope.email,
                        },
                        type: "contributor",
                        position: $scope.position
                    };
                    break;

                case "publication":
                    var postData = {
                        dataset_id: dataset_id,
                        data: {
                            "publication_doi": $scope.publication_doi,
                            "publication_pmid": $scope.publication_pmid,
                            "publication": $scope.publication,
                            "publication_title": $scope.publication_title,
                            "relevance": $scope.relevance,
                        },
                        type: "publication",
                        position: $scope.position
                    };
                    break;

            }
            return postData;
        };

        var keyvalueModalInstanceCtrl = function ($location, $scope, $uibModalInstance, $log, $http) {
            $scope.add = function() {
                postData = postDataBuild($scope, $location.search().datasetid);

                if ($scope.form.modalForm.$valid) {

                    var promise = $http.post("/api/1/datasets/doi/keyvalues/multipleAdd", postData);
                    promise.then(function(response) {
                        // update the overview page
                        alert("Data has been saved.");
                        if (($scope.typee == 'overview') || ($scope.typee == 'abstract'))
                            window.location.reload();

                        switch ($scope.typee) {
                            case "funding":
                                $scope.parentObj.funding.push(postData.data);
                                $scope.initials = "";
                                $scope.agency = "";
                                $scope.identifier = "";
                                break;

                            case "abstract":
                                $scope.parentObj.abstract.push(postData.data);
                                break;

                            case "notes":
                                $scope.parentObj.notes.push(postData.data);
                                break;

                            case "contributor":
                                $scope.refreshAuthors();
                                $scope.$apply;

                                $scope.parentObj.contributor.push(postData.data);
                                $scope.name = "";
                                $scope.firstname = "";
                                $scope.lastname = "";
                                $scope.middleinitial = "";
                                $scope.author = false;
                                $scope.contact = false;
                                $scope.orcid = "";
                                $scope.affiliation = "";
                                break;
                        }

                    });
//                                $uibModalInstance.close('closed');

                    return promise;
                }
            }

            $scope.update = function() {
                postData = postDataBuild($scope, $location.search().datasetid);

                var promise = $http.post("/api/1/datasets/doi/keyvalues/multipleUpdate", postData);
                promise.then(function(response) {
             
                    switch ($scope.typee) {
                        case "publication":
                            $scope.parentObj.publication[$scope.position] = postData.data;
                            $scope.parentObj.publication[$scope.position]['position'] = $scope.position;
                            break;

                        case "funding":
                            $scope.parentObj.funding[$scope.position] = postData.data;
                            $scope.parentObj.funding[$scope.position]['position'] = $scope.position;
                            break;

                        case "abstract":
                            $scope.parentObj.abstract[0] = postData.data;
                            $scope.parentObj.abstract[0]['position'] = $scope.position;
                            break;

                        case "notes":
                            $scope.parentObj.notes[0] = postData.data;
                            $scope.parentObj.notes[0]['position'] = $scope.position;
                            break;

                        case "overview":
                            $scope.parentObj.overview[0] = postData.data;
                            $scope.parentObj.overview[0]['position'] = $scope.position;
                            break;

                        case "license":
                            $scope.parentObj.license[0] = postData.data;
                            break;

                        case "contributor":
                            $scope.refreshAuthors();
                            $scope.$apply;

                            $scope.parentObj.contributor[$scope.position] = postData.data;
                            $scope.parentObj.contributor[$scope.position]['position'] = $scope.position;
                            break;
                    }

                });
                alert("Data has been updated");
                $uibModalInstance.dismiss('update'); 
            }

            $scope.delete = function() {
                var postData = {
                    dataset_id: $location.search().datasetid,
                    position: $scope.position,
                    type: $scope.typee
                };

                var promise = $http.post("/api/1/datasets/doi/keyvalues/multipleDelete", postData);
                promise.then(function(response) {
                    // update the overview page
                    switch ($scope.typee) {
                        case "funding":
                            $scope.parentObj.funding.splice($scope.position, 1);
                            break;

                        case "contributor":
                            $scope.refreshAuthors();
                            $scope.$apply;

                            $scope.parentObj.contributor.splice($scope.position, 1);
                            break;

                        case "publication":
                            $scope.parentObj.publication.splice($scope.position, 1);
                            break;

                        case "keyword":
                            $scope.parentObj.keyword.splice($scope.position, 1);
                            break;
                    }
                });
                alert("Data has been removed");
                    $uibModalInstance.close('removed');
//                $uibModalInstance.dismiss('cancel'); 
            }

            $scope.cancel = function () {
                $uibModalInstance.dismiss('cancel'); 
            };

            // case where author is unchecked
            $scope.checkAuthor = function(author) {
                if (author == false) {
                    $scope.contact = false;
                }

            }

            // case where contact is checked
            $scope.checkContact = function(contact) {
                if (contact == true) {
                    $scope.author = true;
                }
            }
        }

        angular.bootstrap(document.getElementById("doi-management-app"), ["doiManagementApp"]);
    }
    /* /doi module */

    /* template */
    if($("#single-template-app")) {
        var app = angular.module("singleTemplateApp", ["datasetsApp", "errorApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("singleTemplateController", ["datasets", "$log", "$location", "$uibModal", "$window", "$http", "errorModalCaller", function(datasets, $log, $location, $uibModal, $window, $http, errorModalCaller) {
            var that = this;
            var template_id = $location.search().templateid;
            this.template = null;
            this.mode = "add-field";
            this.errors = {};
            this.new_field = {};
            this.new_field_type_filter;
            this.type_filters = {};
            this.terms = {};
            this.change_name_mode = false;
            this.portalName = datasets.portalName;
            this.edit = {
                name: ""
            };
            this.initial_load = true;

            this.changeMode = function(mode) {
                that.mode = mode;
            };

            this.toggleChangeName = function() {
                that.change_name_mode = !that.change_name_mode;
            };

            this.changeFieldILX = function(field) {
                datasets.getCDEFromModal()
                    .then(function(term) {
                        datasets.updateFieldILX(that.template.labid, that.template, field, term.ilx, true)
                            .then(function(response) {
                                that.refreshTemplate();
                            });
                    });
            };

            this.deleteTemplate = function() {
                var modal_instance = $uibModal.open({
                    animation: true,
                    templateUrl: "/templates/labs/delete-confirm.html",
                    controller: "deleteConfirmControllerGeneric",
                    resolve: {
                        text: function() {
                            return "Are you sure you want to delete this template?";
                        },
                        button_text: function() {
                            return null;
                        }
                    }
                });
                modal_instance.result.then(function() {
                    datasets.deleteDatasetTemplate(that.template, datasets.labid, true)
                        .then(function() {
                            $window.location = "/" + that.portalName + "/lab?labid=" + datasets.labid;
                        });
                }, function() {

                });
            };

            this.updateName = function() {
                datasets.updateTemplateName(datasets.labid, that.template, that.edit.name, true)
                    .then(function() {
                        that.refreshTemplate();
                    });
            };

            this.refreshTemplate = function() {
                datasets.getTemplate(template_id)
                    .then(function(template) {
                        that.template = template;
                        that.edit.name = template.name;
                        that.initial_load = false;
                    }, function(err) {
                        that.initial_load = false;
                    });
            };

            this.moveField = function(field, direction) {
                datasets.moveField(that.template, datasets.labid, field, direction, true)
                    .then(function() {
                        that.refreshTemplate();
                    });
            };

            this.deleteField = function(field) {
                var modalInstance = $uibModal.open({
                    animation: true,
                    templateUrl: "deleteFieldConfirm.html",
                    controller: "deleteConfirmController"
                });
                modalInstance.result.then(function() {
                    datasets.deleteField(that.template, datasets.labid, field, true)
                        .then(function() {
                            that.refreshTemplate();
                        });
                });
            };

            this.toggleSubjectField = function(field) {
                datasets.toggleSubjectField(datasets.labid, that.template, field, true)
                    .then(function(response) {
                        field.annotaions = response.data.data.annotations;
                        that.refreshTemplate();
                    });
            };

            this.isSubjectField = function(field) {
                return datasets.isSubjectField(field);
            };

            this.addField = function() {
                that.errors.field_name = "";
                that.errors.field_type = "";
                var form_check = true;
                if(!that.new_field.name) {
                    form_check = false;
                    that.errors.field_name = "Name required";
                } else if(that.new_field.name == "notes") {
                    form_check = false;
                    that.errors.field_name = "Name cannot be 'notes'";
                } else if(that.new_field.name[0] == "_") {
                    form_check = false;
                    that.errors.field_name = "Name cannot begin with _";
                }
                if(!that.new_field.termid) {
                    form_check = false;
                    that.errors.field_type = "Field type required";
                }
                if(!form_check) return;

                datasets.addField(that.template, datasets.labid, that.new_field.name, that.new_field.termid.ilx, 1, 1, true)
                    .then(function() {
                        that.refreshTemplate();
                        errorModalCaller.call("Field has been added");
                    }, function(e) {
                        errorModalCaller.call(e.data.errormsg);
                    });
            };

            this.filterFieldTypes = function(filter) {
                if(!filter) filter = "";
                getCDEs(filter, false);
            };

            this.changeNewFieldType = function() {
                var new_field_type = that.terms[that.new_field_type_ilx];
                if(!new_field_type) return;
                for(var i = 0; i < new_field_type.annotations.length; i++) {
                    if(new_field_type.annotations[i].annotation_term_ilx == datasets.annotationDefaultValueILX) {
                        that.new_field.name = new_field_type.annotations[i].value;
                        break;
                    }
                }
                that.new_field.termid = that.terms[that.new_field_type_ilx];
            };

            this.addCDE = function() {
                var modalInstance = $uibModal.open({
                    animation: true,
                    templateUrl: "addCDE.html",
                    controller: "addCDEController"
                });
                modalInstance.result.then(function(term) {
                    if(term) {
                        that.type_filters.domain = null;
                        that.type_filters.subdomain = null;
                        that.type_filters.assessmentdomain = null;
                        getCDEByILX(term.ilx);
                        getCDEs("", true);
                    }
                });
            };

            this.toggleFilterVals = function(val) {
                if(that.type_filters.domain) {
                    if(that.type_filters.subdomain) {
                        if(that.type_filters.assessmentdomain == val) {
                            that.type_filters.assessmentdomain = null;
                        } else {
                            that.type_filters.assessmentdomain = val;
                        }
                    } else {
                        that.type_filters.subdomain = val;
                    }
                } else {
                    that.type_filters.domain = val;
                }
                that.filterFieldTypes(that.new_field_type_filter);
            };

            this.clearFilterVals = function() {
                if(that.type_filters.asessmentdomain != null) {
                    that.type_filters.assessmentdomain = null;
                } else if(that.type_filters.subdomain != null) {
                    that.type_filters.subdomain = null;
                    that.type_filters.assessmentdomain = null;
                } else {
                    that.type_filters.domain = null;
                    that.type_filters.subdomain = null;
                    that.type_filters.assessmentdomain = null;
                }
                that.filterFieldTypes(that.new_field_type_filter);
            };

            this.submitTemplate = function(action) {
                that.errors.submit_template = "";
                datasets.submitTemplate(datasets.labid, that.template, action)
                    .then(function(response) {
                        that.refreshTemplate();
                        if(action == "submit") {
                            errorModalCaller.call("Template has been enabled");
                        } else {
                            errorModalCaller.call("Template has been disabled");
                        }
                    }, function(e) {
                        errorModalCaller.call("An error occured");
                        that.errors.submit_template = e.data.errormsg;
                    });
            };

            this.valueRestrictions = function(field_type) {
                return datasets.valueRestrictions(field_type);
            };

            function getCDEByILX(ilx) {
                $http.get("/api/1/term/search_by_annotation?type=cde&count=1&term=" + ilx)
                    .then(function(response) {
                        if(response.data.data.length > 0) {
                            var term = parseCDE(response.data.data[0]);
                            that.terms = {};
                            that.terms[term.ilx] = term;
                            that.new_field_type_ilx = ilx;
                            that.changeNewFieldType();
                        }
                    });
            }

            function getCDEs(query, append) {
                var source_param = "&annotation_ids[]=" + datasets.annotationSourceILX + "&annotation_labels[]=" + datasets.annotationSourceValue;
                var domain_param = "";
                var subdomain_param = "";
                var assessmentdomain_param = "";
                if(that.type_filters.domain) {
                    domain_param = "&annotation_ids[]=" + datasets.annotationDomainILX + "&annotation_labels[]=" + that.type_filters.domain;
                    if(that.type_filters.subdomain) {
                        subdomain_param = "&annotation_ids[]=" + datasets.annotationSubdomainILX + "&annotation_labels[]=" + that.type_filters.subdomain;
                        if(that.type_filters.assessmentdomain) {
                            assessmentdomain_param = "&annotation_ids[]=" + datasets.annotationAssessmentdomainILX + "&annotation_labels[]=" + that.type_filters.assessmentdomain;
                        }
                    }
                }
                $http.get("/api/1/term/search_by_annotation?type=cde&count=100&term=" + query + source_param + domain_param + subdomain_param + assessmentdomain_param)
                    .then(function(response) {
                        var raw_terms = response.data.data;
                        var terms = {};
                        if(append) terms = that.terms;
                        for(var i = 0; i < raw_terms.length; i++) {
                            terms[raw_terms[i].ilx] = parseCDE(raw_terms[i]);
                        }
                        that.terms = terms;
                    });
                refreshFilters(query);
            }

            function parseCDE(rawCDE) {
                return rawCDE;
            }

            function refreshFilters(query) {
                if(!query) query = "";
                var source_param = "&annotation_ids[]=" + datasets.annotationSourceILX + "&annotation_labels[]=" + datasets.annotationSourceValue;
                var domain_param = "";
                var subdomain_param = "";
                var assessmentdomain_param = "";
                var request_ilx = datasets.annotationDomainILX;
                if(that.type_filters.domain) {
                    domain_param = "&annotation_ids[]=" + datasets.annotationDomainILX + "&annotation_labels[]=" + that.type_filters.domain;
                    if(that.type_filters.subdomain) {
                        subdomain_param = "&annotation_ids[]=" + datasets.annotationSubdomainILX + "&annotation_labels[]=" + that.type_filters.subdomain;
                        if(that.type_filters.assessmentdomain) {
                            assessmentdomain_param = "&annotation_ids[]=" + datasets.annotationAssessmentdomainILX + "&annotation_labels[]=" + that.type_filters.assessmentdomain;
                        }
                    }
                }
                var request_ilx_param = "&annotation_request_id=" + request_ilx;
                $http.get("/api/1/term/search_by_annotation/values?type=cde&term=" + query + source_param + domain_param + subdomain_param + request_ilx_param)
                    .then(function(response) {
                        that.filter_vals = response.data.data;
                    });
            }

            getCDEs("", true);

            this.refreshTemplate();
        }]);

        app.controller("addCDEController", ["$scope", "$http", "$uibModalInstance", "datasets", "$log", function($scope, $http, $uibModalInstance, datasets, $log) {
            $scope.term = null;
            $scope.portalName = datasets.portalName;
            $scope.timeseries = false;
            $scope.domain = "";
            $scope.subdomain = "";
            $scope.assessmentDomain = "";
            $scope.defaultValue = "";
            $scope.valueRestrictions = [""];
            $scope.valueRestrictionsInUse = false;
            $scope.valueRange = {start: "", end: "", step: ""};
            $scope.errors = {};

            $scope.cancel = function() {
                $uibModalInstance.dismiss();
            };

            $scope.done = function() {
                $uibModalInstance.close($scope.term);
            };

            $scope.changeValueRestrictions = function(e) {
                var remove = [];
                for(var i = $scope.valueRestrictions.length - 2; i >= 0; i--) {
                    if(!$scope.valueRestrictions[i]) {
                        remove.push(i);
                    }
                }
                for(var i = 0; i < remove.length; i++) {
                    $scope.valueRestrictions.splice(remove[i], 1);
                }
                if($scope.valueRestrictions[$scope.valueRestrictions.length - 1]) {
                    $scope.valueRestrictions.push("");
                }
                $scope.valueRestrictionsInUse = false;
                for(var i = 0; i < $scope.valueRestrictions.length; i++) {
                    if($scope.valueRestrictions[i]) {
                        $scope.valueRestrictionsInUse = true;
                        break;
                    }
                }
            };

            $scope.addTerm = function() {
                if(!verifyFields()) return;
                $http.post("/api/1/ilx/add", {"term": $scope.label})
                    .then(function(response) {
                        var fragment = response.data.data.fragment;
                        $http.post("/api/1/term/add", {
                            ilx: fragment,
                            label: $scope.label,
                            definition: $scope.definition,
                            cid: datasets.cid,
                            type: "cde"
                        })
                            .then(function(response) {
                                $scope.term = response.data.data;

                                /* add source annotation */
                                $http.post("/api/1/term/add-annotation", {
                                    tid: $scope.term.id,
                                    annotation_tid: datasets.annotationSourceID,
                                    term_version: 1,
                                    annotation_term_version: 1,
                                    value: datasets.annotationSourceValue
                                });

                                /* add time series annotation */
                                if($scope.timeseries) {
                                    $http.post("/api/1/term/add-annotation", {
                                        tid: $scope.term.id,
                                        annotation_tid: datasets.annotationMultipleValuesID,
                                        term_version: 1,
                                        annotation_term_version: 1,
                                        value: 1
                                    });
                                }

                                /* domain annotation */
                                if($scope.domain) {
                                    $http.post("/api/1/term/add-annotation", {
                                        tid: $scope.term.id,
                                        annotation_tid: datasets.annotationDomainID,
                                        term_version: 1,
                                        annotation_term_version: 1,
                                        value: $scope.domain
                                    });
                                }

                                /* subdomain annotation */
                                if($scope.subdomain) {
                                    $http.post("/api/1/term/add-annotation", {
                                        tid: $scope.term.id,
                                        annotation_tid: datasets.annotationSubdomainID,
                                        term_version: 1,
                                        annotation_term_version: 1,
                                        value: $scope.subdomain
                                    });
                                }

                                /* assessment domain annotation */
                                if($scope.assessmentDomain) {
                                    $http.post("/api/1/term/add-annotation", {
                                        tid: $scope.term.id,
                                        annotation_tid: datasets.annotationAssessmentdomainID,
                                        term_version: 1,
                                        annotation_term_version: 1,
                                        value: $scope.assessmentDomain
                                    });
                                }

                                /* assessment default value */
                                if($scope.defaultValue) {
                                    $http.post("/api/1/term/add-annotation", {
                                        tid: $scope.term.id,
                                        annotation_tid: datasets.annotationDefaultValueID,
                                        term_version: 1,
                                        annotation_term_version: 1,
                                        value: $scope.defaultValue
                                    });
                                }

                                if($scope.valueRestrictionsInUse) {
                                    /* value restrictions */
                                    var used = [];
                                    for(var i = 0; i < $scope.valueRestrictions.length; i++) {
                                        var value = $scope.valueRestrictions[i];
                                        if(!value || used.indexOf(value) !== -1) continue;
                                        used.push(value);
                                        $http.post("/api/1/term/add-annotation", {
                                            tid: $scope.term.id,
                                            annotation_tid: datasets.valueRestrictionID,
                                            term_version: 1,
                                            annotation_term_version: 1,
                                            value: value
                                        });
                                    }
                                } else {
                                    /* value ranges */
                                    if($scope.valueRange.start && $scope.valueRange.end && $scope.valueRange.step) {
                                        var value = "[" + $scope.valueRange.start + ", " + $scope.valueRange.end + ", " + $scope.valueRange.step + "]";
                                        $http.post("/api/1/term/add-annotation", {
                                            tid: $scope.term.id,
                                            annotation_tid: datasets.valueRangeID,
                                            term_version: 1,
                                            annotation_term_version: 1,
                                            value: value
                                        });
                                    }
                                }
                            });
                    });
            };

            function verifyFields() {
                $scope.errors = {};
                if(!$scope.valueRestrictionsInUse && ($scope.valueRange.start || $scope.valueRange.end || $scope.valueRange.step)) {
                    if(!$scope.valueRange.start || !$scope.valueRange.end || !$scope.valueRange.step) {
                        $scope.errors.valueRange = "All value range fields must be filled";
                        return false;
                    }
                    if($scope.valueRange.end < $scope.valueRange.start) {
                        $scope.errors.valueRange = "Required value range end must be larger than start";
                        return false;
                    }
                    if($scope.valueRange.step <= 0) {
                        $scope.errors.valueRange = "Step value must be greater than zero";
                        return false;
                    }
                }
                return true;
            }
        }]);

        angular.bootstrap(document.getElementById("single-template-app"), ["singleTemplateApp"]);
    }
    /* /template */
});
