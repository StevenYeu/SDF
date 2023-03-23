$(function() {
    var datasets_update_app = angular.module("datasetsUpdateApp", ["ui.bootstrap", "resourceDirectives", "errorApp"]);

    datasets_update_app.factory("datasets", ["$http", "$q", "$uibModal", "$log", "$window", function($http, $q, $uibModal, $log, $window) {
        var datasets = {};
        datasets.templates = [];
        var per_page = 20;
        var lab = null;
        var keyvalues = null;

        datasets.cid = $("#cid").val();
        datasets.labid = $("#labid").val();

        datasets.isSubjectField = function(field) {
            if(!field.annotations) return false;
            for(var i = 0; i < field.annotations.length; i++) {
                if(field.annotations[i].name == "subject") return true;
            }
            return false;
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

        datasets.deleteAllRecords = function(dataset) {
            return $http.post("/api/1/datasets/records/delete/all", {datasetid: dataset.id});
        };

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


        datasets.isILXFormat = function(s) {
            var regex = /^ilx:\d+$/i
            return regex.test(s);
        };

        datasets.ilxUserToDBFormat = function(ilx) {
            return ilx.replace(/^ilx:/i, "ilx_");
        };


                        
        return datasets;
    }]);

    datasets_update_app.component("autogenRedoTemplateComponent", {
        templateUrl: "/templates/labs/autogen-redo-template-component.html",
        bindings: {
            type: "@",
            labid: "@",
            portalName: "@"
        },
        controller: function($http, $log, $scope, datasets, $uibModal, $q, $window, errorModalCaller) {
            var that = this;
            this.mode = "naming";
            this.errors = {};
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

            this.changeMode = function(mode) {
                that.mode = mode;
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

            this.datasetFileSelect = function(evt) {
                that.resetFile();
                pageWorkflowClear("select-file");
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

                        let wideSet = new Set();
                        var WideTest = Object.keys(results.data[0]).forEach(key => {
                            if (key.length > 65) {
                                wideSet.add(key);
                            }
                        });
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
                    if(that.type == 'dataset') {
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

                that.changeMode("uploading");
                var fields = [];
                var header = that.file_contents.header.slice(0);
                header.sort(function(a, b) {
                    if(a.position < b.position) return -1;
                    if(a.position > b.position) return 1;
                    return 0;
                });
                var subject_name = "";
                for(var i = 0; i < header.length; i++) {
                    var h = header[i];
                    if(!datasets.validTemplateFieldName(h.name) || h.name === "notes") {
                        continue;
                    }
                    if(h.id == that.subject_index) {
                        subject_name = h.name;
                    }
                    fields.push({name: h.name, ilx: h.term.ilx, required: false, queryable: true});
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
                    labid: that.labid
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
                }

                post_data.data = submit_data;

                $http.post("/api/1/datasets/full-upload", post_data)
                    .then(function(response) {
                        that.new_dataset = {};
                        that.new_template = {};
                        that.new_dataset.id = response.data.data.datasetid;
                        that.new_dataset.added_records = response.data.data["added-records"];
                        that.new_template.id = response.data.data.templateid;

                        document.getElementById('prepare-your-data').style.display = 'none';
                        document.getElementById('upload-help-buttons').style.display = 'none';
                        that.changeMode("post-upload");

                    }, function() {
                        that.changeMode("post-upload-error");
                        document.getElementById('step4').classList.remove("is-active");
                        document.getElementById('step4').classList.add("is-complete");
                    });
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

/*
    var datasets_update_app = angular.module("datasetsUpdateApp", ["ui.bootstrap", "resourceDirectives", "errorApp"]);

    datasets_update_app.factory("datasets", ["$http", "$q", "$uibModal", "$log", "$window", function($http, $q, $uibModal, $log, $window) {



    if($("#add-to-dataset-app")) {
        var app = angular.module("addToDatasetApp", ["datasetsApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("addToDatasetController", ["$log", "$q", "$loca
*/
    /* update dataset */
    
    if($("#update-dataset-app")) {
        var app = angular.module("updateDatasetApp", ["datasetsApp"])
            .config(function($locationProvider) {
                $locationProvider.html5Mode({"enabled": true, "requireBase": false});
            });

        app.controller("updateDatasetController", ["$log", "$q", "$location", "$window", "$scope", "datasets", function($log, $q, $location, $window, $scope, datasets) {
            var that = this;
            this.lab_datasets = null;
            this.selected_dataset = null;
            this.file_contents;
            this.errors = {};
            this.uploadable = null;
            this.query_datasetid = $location.search().datasetid;
            this.new_dataset = !!$location.search().new;

//            this.show_other_datasets = !this.query_datasetid;
            this.show_other_datasets = false;
            this.labid = datasets.labid;
            this.portalName = datasets.portalName;
            this.initial_loading_datasets = true;
            this.loading_single_data = false;


            this.goodFile=false;
            this.filename="";
            this.mode = "lab-confirmed";

            this.changeMode = function(mode) {
                that.mode = mode;
            };

                datasets.getDataset(this.query_datasetid).then(function(dataset) {
                    that.loading_single_dataset = false;
                    that.selected_dataset = dataset;
                    
                    //that.workflow.workflow[1].complete=true;
                    //parseNewData();
                });
                    console.log(that.selected_dataset);
            
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
/*

            datasets.getLab()
                .then(function(lab) {
                    that.lab = lab;
                });

            getLabDatasets()
                .then(function() {
                    that.initial_loading_datasets = false;
                });
*/                
        }]);

        angular.bootstrap(document.getElementById("update-dataset-app"), ["updateDatasetApp"]);
    }

    /* /update dataset */


});
