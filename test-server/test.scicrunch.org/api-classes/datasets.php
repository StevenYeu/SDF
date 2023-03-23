<?php

function addDataset($user, $api_key, $template_id, $name, $long_name, $description, $publications, $metadata) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if(!$lab->uniqueDatasetName($name)) {
        return APIReturnData::quick400("Dataset name is already taken");
    }

    $dataset = Dataset::createNewObj($template, $cuser, $name, $long_name, $description, $publications, $metadata);

    if(is_null($dataset)) return APIReturnData::quick400("Could not create dataset");
    return APIReturnData::build($dataset, true);
}

function deleteDataset($user, $api_key, $datasetid) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    Dataset::deleteObj($dataset);

    return APIReturnData::build(NULL, true);
}

function editDataset($user, $api_key, $datasetid, $name=NULL, $long_name=NULL, $description=NULL, $publications=NULL, $metadata=NULL, $lab_status=NULL) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    /* update fields */
    if(!is_null($name)) $dataset->name = $name;
    if(!is_null($long_name)) $dataset->long_name = $long_name;
    if(!is_null($description)) $dataset->description = $description;
    if(!is_null($publications)) $dataset->publications = $publications;
    $dataset->updateDB();

    /* update metadata */
    $existing_metadata = $dataset->metadata();
    if(is_array($metadata)) {
        foreach($metadata as $md_id => $md) {
            if(!isset($existing_metadata[$md_id])) continue;
            if(is_null($existing_metadata[$md_id]["obj"])) {
                $metadata_field = DatasetMetadataField::loadBy(Array("id"), Array($md_id));
                if(is_null($metadata_field)) continue;
                DatasetMetadata::createNewObj($dataset, $metadata_field, $md["val"]);
            } else {
                $existing_metadata[$md_id]["obj"]->val = $md["val"];
                $existing_metadata[$md_id]["obj"]->updateDB();
            }
        }
    }

    return APIReturnData::build($dataset, true);
}

function addDatasetField($user, $api_key, $template_id, $name, $ilxid, $required, $queryable) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!DatasetField::checkUniqueNameStatic($name, $template)) {
        return APIReturnData::quick400("Name is already used by this template");
    }

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $dataset_field = DatasetField::createNewObj($template, $name, $ilxid, $required, $queryable, false);

    if(is_null($dataset_field)) return APIReturnData::quick400("Could not create dataset field");
    return APIReturnData::build($dataset_field, true);
}

function addDatasetFieldBulk($user, $api_key, $template_id, $fields) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    /* validate fields */
    $seen_names = Array();
    foreach($fields as $field) {
        if(!$field["name"] || !$field["ilxid"]) {
            return APIReturnData::quick400("name and ilxid must be set for each field");
        }
        if(isset($seen_names[$field["name"]])) {
            return APIReturnData::quick400("duplicated field name");
        }
        if(!DatasetField::checkUniqueNameStatic($name, $template)) {
            return APIReturnData::quick400("duplicated field name");
        }
        $seen_names[$field["name"]] = true;
    }

    foreach($fields as $field) {
        $field["required"] = 1;
        $dataset_field = DatasetField::createNewObj($template, $field["name"], $field["ilxid"], $field["required"], $field["queryable"], false);
        if(is_null($dataset_field)) return APIReturnData::quick400("Could not create dataset field");

        // can I change position from here?
        if ($field['position'] != $dataset_field->position) {
            DatasetField::movePositionOneQuery($template_id, $field['position'], $dataset_field->position);
            //var_dump($field['position']);
            //var_dump($dataset_field->position);
        }
    }

    return APIReturnData::build(true, true);
}

function addDatasetFieldMultiple($user, $api_key, $template_id, $name, $ilxid, $required, $queryable, $suffixes) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!DatasetField::checkUniqueNameStatic($name, $template)) {
        return APIReturnData::quick400("Name is already used by this template");
    }

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $new_field = DatasetField::createNewObj($template, $name, $ilxid, $required, $queryable, true, $suffixes);
    if(is_null($new_field)) return APIReturnData::quick400("Could not create dataset field");

    return APIReturnData::build($new_field, true);
}

function deleteDatasetField($user, $api_key, $template_id, $name) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $datasetfield = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
    if(is_null($datasetfield)) return APIReturnData::quick400("could not find field");

    DatasetField::deleteObj($datasetfield);

    return APIReturnData::build(NULL, true);
}

function addDatasetRecord($user, $api_key, $datasetid, $record_fields) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $check = $dataset->insertRecord($record_fields, $cuser);
    if(!$check["success"]) {
        $error_message = buildDataRecordErrorMessage($check);
        return APIReturnData::quick400($error_message);
    }
    $dataset->updateRecordFieldSet();

    return APIReturnData::build(NULL, true);
}

function addMultipleDatasetRecords($user, $api_key, $datasetid, $record_fields_array) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $results = Array();
    $check = $dataset->insertManyRecords($record_fields_array, $cuser);
    if($check === false) {
        return APIReturnData::quick400("cannot upload records to dataset");
    }
    foreach($check as $c) {
        $result = Array("success" => $c["success"]);
        if(!$c["success"]) {
            $result["error_message"] = buildDataRecordErrorMessage($c);
        }
        $results[] = $result;
    }
    $dataset->updateRecordFieldSet();

    return APIReturnData::build($results, true, 207);
}

function buildDataRecordErrorMessage($check) {
    $error_message = "Could not add record.";
    if(!empty($check["missing_fields"])) $error_message .= "  Missing fields: " . implode(", ", $check["missing_fields"]) . ".";
    if(!empty($check["bad_fields"])) $error_message .= "  Improperly formatted fields: " . implode(", ", $check["bad_fields"]) . ".";
    if(!empty($check["messages"])) $error_message .= "  " . implode(", ", $check["messages"]) . ".";
    return $error_message;
}

function deleteDatasetRecord($user, $api_key, $datasetid, $recordid) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $dataset->deleteRecord($recordid);
    $dataset->updateRecordFieldSet();

    return APIReturnData::build(NULL, true);
}

function deleteAllDatasetRecords($user, $api_key, $datasetid) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $dataset->deleteAllRecords();
    $dataset->updateRecordFieldSet();

    return APIReturnData::build(NULL, true);
}

function getCommunityDatasets($user, $api_key, $portal_name) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("community-visible", $api_key, $user, $community)) return APIReturnData::quick403();

    $datasets = Dataset::loadArrayBy(Array("cid"), Array($community->id));
    return APIReturnData::build($datasets, true);
}

function getDatasetFields($user, $api_key, $datasetid) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();

    $fields = $dataset->fields();
    return APIReturnData::build($fields, true);
}

function getTemplateFields($user, $api_key, $templateid) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($templateid));
    if(is_null($template)) return APIReturnData::quick400("could not find template");

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $template->lab()))) return APIReturnData::quick403();

    $fields = $template->fields();
    return APIReturnData::build($fields, true);
}

function datasetSearch($user, $api_key, $datasetid, $rquery, $offset, $count) {
    $offset = (int) $offset;
    $count = (int) $count;

    $return_array = Array();

    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();

    $query = (string) $rquery;
    $results = $dataset->searchRecords($query, $offset, $count);

    $count = $dataset->getRecordCount($query);

    $return_array["records"] = $results;
    $return_array["count"] = $count;
    return APIReturnData::build($return_array, true);
}

function datasetMetadataFields($user, $api_key, $labid) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find template");

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    $metadata_fields = DatasetMetadataField::loadArrayBy(Array("labid"), Array($lab->id));
    return APIReturnData::build($metadata_fields, true);
}

//AO
function checkDatasetTemplate($user, $api_key, $datasetid, $portalname) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    $community = new Community();
    $community->getByPortalName($portalname);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("submit-dataset", $api_key, $user, Array("community" => $community, "dataset" => $dataset))) return APIReturnData::quick403();

    $good = $dataset->checkCommunityTemplate($community);

    return APIReturnData::build($good, true);
}

function submitDataset($user, $api_key, $datasetid, $portalname) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    $community = new Community();
    $community->getByPortalName($portalname);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("submit-dataset", $api_key, $user, Array("community" => $community, "dataset" => $dataset))) return APIReturnData::quick403();

    $submission = $dataset->submitToCommunity($community);

    if(is_null($submission)) {
        return APIReturnData::quick400("dataset does not conform to any template required by this community");
    }
    return APIReturnData::build(true, true);
}

function withdrawDataset($user, $api_key, $datasetid, $portalname) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    $community = new Community();
    $community->getByPortalName($portalname);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("submit-dataset", $api_key, $user, Array("community" => $community, "dataset" => $dataset))) return APIReturnData::quick403();

    $submission = CommunityDataset::loadBy(Array("datasetid", "cid"), Array($dataset->id, $community->id));
    CommunityDataset::deleteObj($submission);

    return APIReturnData::build(true, true);
}

function moveDatasetField($user, $api_key, $template_id, $name, $direction) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    if($direction !== "up" && $direction !== "down") return APIReturnData::quick400("direction field must be up or down");

    $datasetfield = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
    if(is_null($datasetfield)) return APIReturnData::quick400("could not find field");

    if($direction == "up") {
        $datasetfield->movePositionUp();
    } elseif($direction == "down") {
        $datasetfield->movePositionDown();
    }

    return APIReturnData::build(true, true);
}

function singleDatasetInfo($user, $api_key, $datasetid) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    $check_user = \APIPermissionActions\getUser($api_key, $user);

    $dataset->canEditSave($check_user);
    $dataset->canDeleteDatasetAdmin($check_user);

    return APIReturnData::build($dataset, true);
}

function curateDataset($user, $api_key, $datasetid, $portalname, $status) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    $community = new Community();
    $community->getByPortalName($portalname);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("community-moderator", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $submission = CommunityDataset::loadBy(Array("cid", "datasetid"), Array($community->id, $dataset->id));

    $submission->curated = $status;
    $submission->updateDB();

    return APIReturnData::build(true, true);
}

function addMetadataField($user, $api_key, $labid, $name) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not load lab");

    if(!\APIPermissionActions\checkAction("lab-moderator", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    $new_metadata = DatasetMetadataField::createNewObj($lab, $name, DatasetMetadataField::TYPE_STRING, "", 1);

    if(is_null($new_metadata)) return APIReturnData::quick400("could not add metadata field");
    return APIReturnData::build(true, true);
}

function moveMetadataField($user, $api_key, $labid, $name, $direction) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not load lab");

    if(!\APIPermissionActions\checkAction("lab-moderator", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    if($direction !== "up" && $direction !== "down") return APIReturnData::quick400("invalid direction, must be up or down");

    $metadata = DatasetMetadataField::loadBy(Array("lab", "name"), Array($lab->id, $name));
    if(is_null($metadata)) return APIReturnData::quick400("could not find metadata field");

    if($direction == "up") {
        $metadata->movePositionUp();
    } else {    // down
        $metadata->movePositionDown();
    }

    return APIReturnData::build(true, true);
}

function deleteMetadataField($user, $api_key, $labid, $name) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not load lab");

    if(!\APIPermissionActions\checkAction("lab-moderator", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    $metadata = DatasetMetadataField::loadBy(Array("labid", "name"), Array($lab->id, $name));
    if(is_null($metadata)) return APIReturnData::quick400("could not find metadata field");

    DatasetMetadataField::deleteObj($metadata);

    return APIReturnData::build(true, true);
}

function addDatasetTemplate($user, $api_key, $labid, $name, $required_fields_name) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not load lab");

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $template = DatasetFieldTemplate::createNewObj($cuser, $lab, $name);
    if(is_null($template)) return APIReturnData::quick400("could not create template");

    if($required_fields_name) {
        $required_fields = CommunityDatasetRequiredField::loadArrayBy(Array("cid", "dataset_type_name"), Array($lab->community()->id, $required_fields_name));
        if(!empty($required_fields)) {
            usort($required_fields, function($a, $b) {
                if($a->position < $b->position) return -1;
                if($a->position > $b->position) return 1;
                return 0;
            });
            foreach($required_fields as $rf) {
                $new_field_api = addDatasetField($user, $api_key, $template->id, $rf->name, $rf->term()->ilx, true, true);
                if($rf->subject && $new_field_api->success) {
                    $new_field = $new_field_api->data;
                    addDatasetFieldAnnotation($user, $api_key, $template->id, $new_field->name, "subject");
                }
            }
        }
    }

    return APIReturnData::build($template, true);
}

function deleteDatasetTemplate($user, $api_key, $template_id) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    DatasetFieldTemplate::deleteObj($template);

    return APIReturnData::build(true, true);
}

function getDatasetTemplate($user, $api_key, $template_id) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template) || !$template->active) return APIReturnData::quick400("could not find tempate");

    return APIReturnData::build($template, true);
}

function getDatasetTemplateByLab($user, $api_key, $labid) {
    $templates = DatasetFieldTemplate::loadArrayBy(Array("labid", "active"), Array($labid, 1));

    return APIReturnData::build($templates, true);
}

function addDatasetTemplateToCommunity($user, $api_key, $dataset_template_id, $cid) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($dataset_template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");

    $community = Community::getByIDStatic($cid);
    if(is_null($community)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("community-moderator", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $community_dataset_template = CommunityDatasetTemplate::createNewObj($community, $template);

    if(is_null($community_dataset_template)) return APIReturnData::quick400("could not add dataset template");

    return APIReturnData::build($community_dataset_template, true);
}

function removeDatasetTemplateFromCommunity($user, $api_key, $dataset_template_id, $cid) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($dataset_template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");

    $community = Community::getByIDStatic($cid);
    if(is_null($community)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("community-moderator", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $community_dataset_template = CommunityDatasetTemplate::loadBy(Array("dataset_fields_template_id", "cid"), Array($template->id, $community->id));
    if(is_null($community_dataset_template)) return APIReturnData::quick400("could not find community template");

    CommunityDatasetTemplate::deleteObj($community_dataset_template);

    return APIReturnData::build(true, true);
}

// All "lab_status" changes start here. Some status changes will kick off other steps, depending on the status and also the community
// Note: In ODC, there may be LabStatus, CurationStatus and/or EditorStatus (just ODC-SCI)
function changeLabStatus($user, $api_key, $datasetid, $status) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");
    $lab = $dataset->lab();
    $community = new Community();
    $community->getByID($dataset->lab()->community()->id);

    if($status === Dataset::LAB_STATUS_PENDING || $status === Dataset::LAB_STATUS_NOTSUBMITTED || $status === Dataset::LAB_STATUS_APPROVED) {
        if(!\APIPermissionActions\checkAction("dataset-owner", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();
    } elseif($status === Dataset::LAB_STATUS_REJECTED || $status === Dataset::LAB_STATUS_REQUESTDOI || $status === Dataset::LAB_STATUS_APPROVEDINTERNAL || $status === Dataset::LAB_STATUS_APPROVEDCOMMUNITY) {
        if(!\APIPermissionActions\checkAction("lab-moderator", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    } elseif ($dataset->lab_status === Dataset::LAB_STATUS_APPROVED) {
        return APIReturnData::quick400("Cannot Change Status of Dataset with DOI");
    } else {
        return APIReturnData::quick400("invalid status");
    }

    // Set the "lab_status"
    $dataset->lab_status = $status;
    $dataset->updateDB();

    // if "Share to Lab", notify PI
    if ($status === Dataset::LAB_STATUS_APPROVEDINTERNAL) {
        $subject = strtoupper($community->portalName) . " Dataset: " . $dataset->id . " Share to Lab Notification";
        $html_message = Array("Dataset: " . $dataset->name . " has been Shared to the Lab. No action is necessary.",
            '<a href="' . $community->fullURL() . '/lab/admin?labid=' . $lab->id . '">Go to admin dashboard</a>'
        );
        $text_message = "Dataset: " . $dataset->name . " has been Shared to the Lab. No action is necessary. Follow this link ' . $community->fullURL() . '/lab/admin?labid=' . $lab->id . ' to go to the admin dashboard.';";

        $moderator_emails = $community->getModeratorEmails();
        foreach($moderator_emails as $email) {
            \helper\sendEmail($email, \helper\buildEmailMessage($html_message, 1, $community), $text_message, $subject, NULL);
        }
    }

    // if DOI approved, then also update curation status 
    elseif ($status === Dataset::LAB_STATUS_APPROVED) {
        DatasetStatus::createNewObj($datasetid, 'published');
        $dataset->curation_status = 'published';
        $dataset->updateDB();

        //this will update files and get the year, publishDate that we need
        include $_SERVER["DOCUMENT_ROOT"] . "/php/labs/doi_xml.php";
        moveDOIFiles($datasetid, $dataset->lab()->community()->portalName);

        $xml_file = $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/dataset_" . $datasetid . "/xml_" . $datasetid . ".xml";
        $doi_array = DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "type", "subtype"), Array($datasetid, 'overview', 'hidden_doi'));
        updateDOI($xml_file, $doi_array[0]->text, $community->fullURL(), $datasetid, TRUE);
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, date("Y-m-d"), 'overview', 'date_published', 0);  
    } 

    // if unsubmit, then remove curation and editor status
    elseif ($status === Dataset::LAB_STATUS_NOTSUBMITTED) {
        DatasetStatus::createNewObj($datasetid, 'published');
        $dataset->curation_status = NULL;
        $dataset->editor_status = NULL;
        $dataset->updateDB();
    }

    return APIReturnData::build($dataset, true);
}

// after DOI is minted, various files need to generated for listings and download
function moveDOIFiles($dataset_id, $portalName) {
    $base_dir = $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/";
    $public_dir = $base_dir . "public/";
    $version = 1;
    
    $dictionary = DatasetAssociatedFiles::loadBy(Array("dataset_id", "type"), Array($dataset_id, 'dictionary'));
    $methodology = DatasetAssociatedFiles::loadBy(Array("dataset_id", "type"), Array($dataset_id, 'methodology'));

    // create public/dataset_* directory AND version directory if doesn't exist
    if (!is_dir($public_dir . "/dataset_" . $dataset_id)) {
        mkdir($public_dir . "/dataset_" . $dataset_id, 0777, true);
        mkdir($public_dir . "/dataset_" . $dataset_id . "/v" . $version, 0777, true);
    }

    // copy metadata file to public location
    $metadata_file = "metadata_" . $dataset_id . ".html";
    if (is_file($base_dir . "/dataset_" . $dataset_id . "/" . $metadata_file))
        copy($base_dir . "/dataset_" . $dataset_id . "/" . $metadata_file, $public_dir . "/dataset_" . $dataset_id . "/v" . $version . "/metadata_" . $dataset_id . ".html");

    // copy stub file to public location
    $stub_file = "stub_" . $dataset_id . ".html";
    if (is_file($base_dir . "/dataset_" . $dataset_id . "/" . $stub_file))
        copy($base_dir . "/dataset_" . $dataset_id . "/" . $stub_file, $public_dir . "/dataset_" . $dataset_id . "/v" . $version . "/stub_" . $dataset_id . ".html");

    // copy zip file to public location
    $zip_file = $portalName . "_" . $dataset_id . ".zip";
    if (is_file($base_dir . "/dataset_" . $dataset_id . "/" . $zip_file))
        copy($base_dir . "/dataset_" . $dataset_id . "/" . $zip_file, $public_dir . "/dataset_" . $dataset_id . "/v" . $version . "/" . $zip_file);
}

// only ODC-SCI has editor 
function changeEditorStatus($user, $api_key, $datasetid, $status) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/php/labs/odc_config.php";

    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");
    $lab = $dataset->lab();

    DatasetStatus::createNewObj($datasetid, $status);
    $dataset->editor_status = $status;
    $dataset->updateDB();

    // send email to curation team to let them know editor has approved, and it's their turn ...
    if ($status == 'approved') {
        $curation_team_email = $conf['odc-sci']['curation_team']['email'];

        $community = new Community();
        $community->getByID($dataset->lab()->community()->id);

        $subject = strtoupper($community->portalName) . " Dataset: " . $dataset->id . " is ready for Curation Team Approval";
        $html_message = Array("Dataset: " . $dataset->name . " is ready for Curation Team Approval",
            '<a href="' . PROTOCOL . "://" . FQDN . '/php/labs/curator.php' . '">Go to curation dashboard</a>'
        );
        $text_message = "Dataset: " . $dataset->name . ' is ready for Curation Team Approval. Follow this link ' . PROTOCOL . "://" . FQDN . '/php/labs/curator.php' . ' to go to the curation dashboard.';

        foreach($curation_team_email as $email) {
            \helper\sendEmail($email, \helper\buildEmailMessage($html_message, 1, $community), $text_message, $subject, NULL);
        }
    }

    return APIReturnData::build($dataset, true);
}

function changeCurationStatus($user, $api_key, $datasetid, $status) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");
    $lab = $dataset->lab();

    DatasetStatus::createNewObj($datasetid, $status);
    $dataset->curation_status = $status;
    $dataset->updateDB();

    // send email to lab pi if approved. don't email if just changing lock/unlock
    if ($status == 'curation-approved') {

        $community = Community::getByIDStatic($dataset->lab()->cid);
        $lab = Lab::loadBy(Array("id"), Array($dataset->lab()->id));

        $subject = strtoupper($community->portalName) . " Dataset: " . $dataset->id . " Needs final approval for DOI";
        $html_message = Array("Dataset: " . $dataset->id . " Needs final approval for DOI",
            '<a href="' . $community->fullURL() . '/lab/admin?labid=' . $lab->id . '">Go to admin dashboard</a>'
        );
        $text_message = 'Dataset needs final approval for DOI. Follow this link ' . $community->fullURL() . '/lab/admin?labid=' . $lab->id . ' to go to the admin dashboard.';

        $lab_emails = $lab->managerEmails();
        foreach($lab_emails as $email) {
            \helper\sendEmail($email, \helper\buildEmailMessage($html_message, 1, $community), $text_message, $subject, NULL);
        }
    }

    return APIReturnData::build($dataset, true);
}

function requestDOI($user, $api_key, $datasetid) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/php/labs/odc_config.php";

    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");
    //$lab = $dataset->lab();

    $templatefields = DatasetFieldTemplate::loadBy(Array("id"), Array($dataset->dataset_fields_template_id));
    //$lab = Lab::loadBy(Array("id"), Array($templatefields->labid));

    // can get this from dataset ...
    $community = new Community();
    $community->getByID($dataset->lab()->community()->id);
    //$community = $dataset->lab()->id;

    // if ODC-SCI send email to Editor; for TBI skip Editor email
    if ($community->portalName == 'odc-sci') {
        $editorial_team_email = $conf['odc-sci']['editorial_team']['email'];

        $subject = strtoupper($community->portalName) . ": Dear Editor, A DOI Request has been made for Dataset: " . $dataset->id;
        $html_message = Array("Dataset: " . $dataset->name . " has made a DOI Request.",
            '<a href="' . PROTOCOL . "://" . FQDN . '/php/labs/curator.php">Go to curation dashboard</a>');
        $text_message = "Dataset: " . $dataset->name . ' has made a DOI Request. Follow this link ' . PROTOCOL . "://" . FQDN . '/php/labs/curator.php to go to the curation dashboard.';
        foreach($editorial_team_email as $email) {
            \helper\sendEmail($email, \helper\buildEmailMessage($html_message, 1, $community), $text_message, $subject, NULL);
        }
    } 

    // all ODC should send email to curation team.
    $curation_team_email = $conf[$community->portalName]['curation_team']['email'];

    $subject = strtoupper($community->portalName) . ": A DOI Request has been made for Dataset: " . $dataset->id;
    $html_message = Array("Dataset: " . $dataset->name . " has made a DOI Request.");
    $text_message = "Dataset: " . $dataset->name . ' has made a DOI Request. ';

    // if ODC-SCI, let curation team know that no action is necessary; for TBI, direct them to dashboard
    if ($community->portalName == 'odc-sci') {
        $html_message[] = 'No action is necessary at this time.';
        $text_message .= "No action is necesary at this time.";
    } else {
        $html_message[] = '<a href="' . PROTOCOL . "://" . FQDN . '/php/labs/curator.php">Go to curation dashboard</a>';
        $text_message .= 'Follow this link ' . PROTOCOL . "://" . FQDN . '/php/labs/curator.php to go to the curation dashboard.';
    }

    foreach($curation_team_email as $email) {
        \helper\sendEmail($email, \helper\buildEmailMessage($html_message, 1, $community), $text_message, $subject, NULL);
    }

    // save overview information
    DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $dataset->lab()->name, 'overview', 'lab', 0);  
    DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $community->name, 'overview', 'community', 0);  
    DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $dataset->record_count, 'overview', 'recordcount', 0);
    DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $templatefields->nfields(), 'overview', 'fields', 0);

    // save CSV file and get some metadata ... where does it save??
    // need to make zip file from Dictionary and CSV. Include methodology if exists ...
    // how do i deal with 'hidden doi' and doi that is needed on overview page? if public, on user facing overview, show doi. else nothing? 

    // the 'dataset-csv.php' file will save csv vs export as downloadable file if $saveonly exists.
    $saveonly["datasetid"] = $datasetid;
    $saveonly["filesizee"] = 0;
    $version = 1;

    $base_dir = $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/";
    if (!is_dir($base_dir . "/dataset_" . $datasetid)) {
        mkdir($base_dir . "/dataset_" . $datasetid, 0777, true);
    }

    $saveonly["path"] = $base_dir ."/dataset_" . $datasetid . "/";
    include $_SERVER["DOCUMENT_ROOT"] . "/php/dataset-csv.php";
    DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $saveonly["filesizee"], 'overview', 'filesize', 0);
    DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $saveonly["outfile"], 'overview', 'csv', 0);

    $zip = new ZipArchive();
    $filename = $saveonly["path"] . $community->portalName . "_" . $datasetid . ".zip";

    if (is_file($filename))
        unlink($filename);

    if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
        exit("cannot open <$filename>\n");
    }

    $zip->addFile($saveonly["path"] . $saveonly["outfile"], $community->portalName . "_". $datasetid . ".csv");

    $associated_files = getAssociatedFiles($user, $api_key, $datasetid);
    if (isset($associated_files->data->dictionary) && $associated_files->data->dictionary != '')
        $zip->addFile($saveonly["path"] . $associated_files->data->dictionary, "dictionary.csv");
    if (isset($associated_files->data->methodology) && $associated_files->data->methodology != '')
        $zip->addFile($saveonly["path"] . $associated_files->data->methodology, "methodology.csv");
    $zip->close();

    DatasetStatus::createNewObj($datasetid, Dataset::CURATION_STATUS_REQUESTDOI_LOCKED);
    $dataset->curation_status = Dataset::CURATION_STATUS_REQUESTDOI_LOCKED;

    // If ODC-SCI, set editor_status to 'submitted'; set editor_status to "approved", curation_status to 'request-doi-locked'
    if ($community->portalName == 'odc-sci') {
        $dataset->editor_status = 'submitted';
        $dataset->updateDB();
    } elseif ($community->portalName == 'odc-tbi') {
        $dataset->editor_status = 'approved';
        $dataset->curation_status = 'request-doi-locked';
        $dataset->updateDB();
    }
    $meta = [
            "creator" => $dataset->user()->firstname . " " . $dataset->user()->lastname,
            'title' => $dataset->name,
            'publisher' => strtoupper($community->portalName),
            'publicationyear' => date("Y"),
            'resourcetype' => 'Text'
        ];

    if ($new_doi = getDOI($meta, $conf['reserve_url'])) {
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $new_doi, 'overview', 'hidden_doi', 0);
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, date('Y-m-d'), 'overview', 'doi_issued', 0);
    }

    return APIReturnData::build($dataset, true);    
}

function getDOI($meta, $reserve_url) {
    $str = \helper\format_ezid_metadata($meta);

    // add the _status to get "reserved" vs default "public"
    $str .= "\n_status: reserved";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $reserve_url);
    curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS["config"]["ezid-userpwd"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
      array('Content-Type: text/plain; charset=UTF-8',
            'Content-Length: ' . strlen($str)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($ch);

    return \helper\regexp_doi($output);
}

function updateDOI($xml_file, $doi, $portalURL, $dataset_id, $published) {
    $xml = file_get_contents($xml_file);
    $xml = str_replace("%", "%25", $xml);
    $xml = str_replace(":", "%3A", $xml);
    $xml = str_replace("\n", "%0A", $xml);
    $xml = str_replace("\r", "%0D", $xml);
    $str = 'datacite:' . $xml;

    // if just an update, $published = FALSE. if actually publishing it, then $published = TRUE
    if ($published) {
        $str .= "\n_target: ". $portalURL . "/data/" . $dataset_id;
        $str .= "\n_status: public";
    }

    $ch = curl_init();             
    curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/id/doi:' . $doi);
    curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS["config"]["ezid-userpwd"]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
      array('Content-Type: text/plain; charset=UTF-8',
            'Content-Length: ' . strlen($str)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $output = curl_exec($ch);

    if (substr($output, 0, 7) == 'success')
        return "success";
    else 
        return $output;
}

function submitDatasetTemplate($user, $api_key, $template_id, $submit) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if($submit) {
        if(empty($template->fields())) return APIReturnData::quick400("Cannot activate template with no fields");
        if(!$template->hasSubjectField()) return APIReturnData::quick400("Templates must have a subject field");
    }

    $template->submitted = $submit;
    $template->updateDB();
    return APIReturnData::build($template, true);
}

function addDatasetFieldAnnotation($user, $api_key, $template_id, $name, $annotation_name, $annotation_value) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
    if(is_null($field)) return APIReturnData::quick400("could not find dataset field");

    DatasetFieldAnnotation::upsert($field, $annotation_name, $annotation_value);

    return APIReturnData::build($field, true);
}

function removeDatasetFieldAnnotation($user, $api_key, $template_id, $name, $annotation_name) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
    if(is_null($field)) return APIReturnData::quick400("could not find dataset field");

    $annotation = DatasetFieldAnnotation::loadBy(Array("dataset_field_id", "name"), Array($field->id, $annotation_name));
    if(is_null($annotation)) return APIReturnData::quick400("could not find dataset field annotation");

    DatasetFieldAnnotation::deleteObj($annotation);

    return APIReturnData::build($field, true);
}

function addCommunityDatasetRequiredField($user, $api_key, $cid, $ilx, $dataset_type_name, $name, $multi, $multi_suffixes) {
    $term = TermDBO::loadBy(Array("ilx", "type"), Array($ilx, "cde"));
    if(is_null($term)) {
        return APIReturnData::quick400("invalid ilx");
    }

    $community = new Community();
    $community->getByID($cid);
    if(!\APIPermissionActions\checkAction("community-datasets-required-fields-update", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $existing_count = CommunityDatasetRequiredField::getCount(Array("cid", "dataset_type_name"), Array($cid, $dataset_type_name));
    $is_subject = $existing_count == 0;

    $obj = CommunityDatasetRequiredField::createNewObj($community, $term, $name, $dataset_type_name, $is_subject, $multi, $multi_suffixes);
    if(is_null($obj)) {
        return APIReturnData::quick400("could not add required field");
    }
    return APIReturnData::build($obj, true);
}

function removeCommunityDatasetRequiredField($user, $api_key, $cid, $dataset_type_name, $name) {
    $community = new Community();
    $community->getByID($cid);
    if(!\APIPermissionActions\checkAction("community-datasets-required-fields-update", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $obj = CommunityDatasetRequiredField::loadBy(Array("cid", "dataset_type_name", "name"), Array($community->id, $dataset_type_name, $name));
    if(is_null($obj)) {
        return APIReturnData::quick400("could not find required field");
    }
    CommunityDatasetRequiredField::deleteObj($obj);
    return APIReturnData::build(true, true);
}

function moveCommunityDatasetRequiredField($user, $api_key, $cid, $dataset_type_name, $name, $direction) {
    $community = new Community();
    $community->getByID($cid);
    if(!\APIPermissionActions\checkAction("community-datasets-required-fields-update", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $obj = CommunityDatasetRequiredField::loadBy(Array("cid", "dataset_type_name", "name"), Array($community->id, $dataset_type_name, $name));
    if(is_null($obj)) {
        return APIReturnData::quick400("could not find required field");
    }
    if($direction == "up") {
        $obj->movePositionUp();
    } elseif($direction == "down") {
        $obj->movePositionDown();
    }

    return APIReturnData::build($obj, true);
}

function makeSubjectCommunityDatasetRequiredField($user, $api_key, $cid, $dataset_type_name, $name) {
    $community = new Community();
    $community->getByID($cid);
    if(!\APIPermissionActions\checkAction("community-datasets-required-fields-update", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $obj = CommunityDatasetRequiredField::loadBy(Array("cid", "dataset_type_name", "name"), Array($community->id, $dataset_type_name, $name));
    if(is_null($obj)) {
        return APIReturnData::quick400("could not find required field");
    }
    $all_fields = CommunityDatasetRequiredField::loadArrayBy(Array("cid", "dataset_type_name"), Array($obj->cid, $obj->dataset_type_name));
    foreach($all_fields as $field) {
        if($obj->id == $field->id) continue;
        if($field->subject) {
            $field->subject = false;
            $field->updateDB();
        }
    }
    if(!$obj->subject) {
        $obj->subject = true;
        $obj->updateDB();
    }
    return APIReturnData::build($obj, true);
}

function getCommunityDatasetRequiredFields($user, $api_key, $cid) {
    $community = new Community();
    $community->getByID($cid);
    if(!\APIPermissionActions\checkAction("community-datasets-required-fields-get", $api_key, $user, $community)) return APIReturnData::quick403();

    $fields = CommunityDatasetRequiredField::loadArrayBy(Array("cid"), Array($community->id));
    return APIReturnData::build($fields, true);
}

function datasetValidate($user, $api_key, $datasetid) {
    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();

    $check = $dataset->validateAllRecords();

    return APIReturnData::build($check, true);
}

function getDatasetID($user, $api_key, $labid, $dataset_name) {
    $cxn = new Connection();
    $cxn->connect();
    $id = $cxn->select(
        "datasets d inner join dataset_fields_templates t on d.dataset_fields_template_id = t.id",
        Array("d.id"),
        "is",
        Array($labid, $dataset_name),
        "where t.labid = ? and d.name = ?"
    );
    $cxn->close();
    if(empty($id)) return APIReturnData::quick404();
    $dataset = Dataset::loadBy(Array("id"), Array($id[0]["id"]));

    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset))) return APIReturnData::quick403();

    return APIReturnData::build($dataset->id, true);
}

function updateDatasetFieldName($user, $api_key, $template_id, $name, $new_name) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!DatasetField::checkUniqueNameStatic($new_name, $template)) {
        return APIReturnData::quick400("Name is already used by this template");
    }

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    $dataset_field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
    if(is_null($dataset_field)) return APIReturnData::quick400("could not find field");

    $dataset_field->name = $new_name;
    $dataset_field->updateDB();

    return APIReturnData::build($dataset_field, true);
}

function updateDatasetFieldILX($user, $api_key, $template_id, $name, $ilx) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    $dataset_field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
    if(is_null($dataset_field)) return APIReturnData::quick400("could not find field");

    $term = TermDBO::loadBy(Array("ilx"), Array($ilx));
    if(is_null($term) || $term->type !== "cde") {
        return APIReturnData::quick400("Could not find ilx identifier");
    }

    $dataset_field->termid = $term->id;
    $dataset_field->updateDB();

    return APIReturnData::build($dataset_field, true);
}

function updateDatasetTemplateName($user, $api_key, $template_id, $new_name) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $template->name = $new_name;
    $template->updateDB();

    return APIReturnData::build($template, true);
}

function copyDatasetTemplate($user, $api_key, $template_id, $name) {
    $template = DatasetFieldTemplate::loadBy(Array("id"), Array($template_id));
    if(is_null($template)) return APIReturnData::quick400("could not find template");
    $lab = $template->lab();

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $new_template = $template->copyTemplate();
    if(is_null($new_template)) return APIReturnData::quick400("could not copy template");
    $new_template->name = $name;
    $new_template->updateDB();

    return APIReturnData::build($new_template, true);
}

function checkDatasetName($user, $api_key, $name, $labid) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) {
        return APIReturnData::quick400("invalid lab");
    }
    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    return APIReturnData::build($lab->uniqueDatasetName($name), true);
}

function fullDatasetUpload($user, $api_key, $labid, $data) {
    
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) {
        return APIReturnData::quick400("invalid lab");
    }
    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    /* validate */
    if(!$data["template"]["name"] || empty($data["template"]["fields"])) {
        return APIReturnData::quick400("invalid template data");
    }
    if(!$data["template_only"] && (!$data["dataset"]["name"] || !$data["dataset"]["long_name"] || !$data["dataset"]["description"])) {
        return APIReturnData::quick400("invalid dataset data");
    }

    /* create template */
    $template_data = $data["template"];
    $template = DatasetFieldTemplate::createNewObj($cuser, $lab, aR($template_data["name"], "s"));
    if(is_null($template)) {
        return APIReturnData::quick400("could not create template");
    }

    /* add fields */
    $subject_field = NULL;
    foreach($template_data["fields"] as $field) {
        $dataset_field = DatasetField::createNewObj($template, aR($field["name"], "s"), aR($field["ilx"], "s"), aR($field["required"], "i"), aR($field["queryable"], "i"), false);
        if(is_null($dataset_field)) {
            DatasetTemplate::deleteObj($template);
            return APIReturnData::quick400("could not create template field");
        }
        if($dataset_field->name == $template_data["subject"]) {
            $subject_field = $dataset_field;
        }
    }

    /* select subject */
    if(is_null($subject_field)) {
        DatasetTemplate::deleteObj($template);
        return APIReturnData::quick400("no subject field");
    }
    DatasetFieldAnnotation::upsert($subject_field, "subject", NULL);

    /* submit template */
    if(empty($template->fields()) || !$template->hasSubjectField()) {
        DatasetTemplate::deleteObj($template);
        return APIReturnData::quick400("cannot submit dataset");
    }
    $template->submitted = true;
    $template->updateDB();

    if($data["template_only"]) {
        $return_data = Array(
            "templateid" => $template->id,
        );
        return APIReturnData::build($return_data, true);
    }
    /* create dataset */
    $dataset_data = $data["dataset"];
    $dataset = Dataset::createNewObj($template, $cuser, aR($dataset_data["name"], "s"), aR($dataset_data["long_name"], "s"), aR($dataset_data["description"], "s"), "");

    if(is_null($dataset)) {
        DatasetFieldTemplate::deleteObj($template);
        return APIReturnData::quick400("cannot submit dataset");
    }

    /* add data */
    $records_data = $data["records"];
    $dataset->insertManyRecordsFromString($records_data, $cuser);

    /* response */
    $return_data = Array(
        "templateid" => $template->id,
        "datasetid" => $dataset->id,
    );
    return APIReturnData::build($return_data, true);
}

function fullDatasetUpdate($user, $api_key, $labid, $data) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) {
        return APIReturnData::quick400("invalid lab");
    }

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    /* Load dataset .... trying $data["dataset"]["id"] ...*/
    $dataset = Dataset::loadBy(Array("id"), Array($data["dataset"]["id"]));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    $old_template = DatasetFieldTemplate::loadBy(Array("id"), Array($dataset->template()->id));
    
    // generate a template name for this new template. template name is 64 char max, so do substr on dataset name + time + " template"
    $data["template"]["name"] = substr($dataset->name, 0, 44) . "_" . time() . " template";

    /* create template and save old templat_id as "parent_id" */
    $template_data = $data["template"];
    $template = DatasetFieldTemplate::createNewObj($cuser, $lab, aR($template_data["name"], "s"), $old_template);
    if(is_null($template)) {
        return APIReturnData::quick400("could not create template");
    }

    foreach($template_data["fields"] as $field) {
        $field["required"] = 0;
        $dataset_field = DatasetField::createNewObj($template, aR($field["name"], "s"), aR($field["ilx"], "s"), aR($field["required"], "i"), aR($field["queryable"], "i"), false);
        if(is_null($dataset_field)) {
            DatasetTemplate::deleteObj($template);
            return APIReturnData::quick400("could not create template field");
        }
        
        if($dataset_field->name == $template_data["subject"]) {
            $subject_field = $dataset_field;
        }
    }

    /* select subject */
    if(is_null($subject_field)) {
        DatasetTemplate::deleteObj($template);
        return APIReturnData::quick400("no subject field");
    }
    DatasetFieldAnnotation::upsert($subject_field, "subject", NULL);

    /* reassign any non default terms */
    $dataset_fields = DatasetFieldTemplate::loadBy(Array("id"), Array($old_template->id));
    $old_fields = $dataset_fields->fields();

    $default_term = TermDBO::loadBy(Array("ilx"), Array($GLOBALS["config"]["dataset-config"]["term"]["ilx"]["default"]));
    foreach ($old_fields as $field) {
        // if non default field, then lookup in new template and update
        if ($field->termid != $default_term->id) {
            $find_field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $field->name));
            // if no field found, then it was deleted
            if (!is_null($find_field)) {
                $find_field->termid = $field->termid;
                $find_field->updateDB();
            }
        }
    }

    /* submit template */
    if(empty($template->fields()) || !$template->hasSubjectField()) {
        DatasetTemplate::deleteObj($template);
        return APIReturnData::quick400("cannot submit dataset");
    }

    $template->submitted = true;
    $template->updateDB();

    $dataset->dataset_fields_template_id = $template->id;
    $dataset->updateDB();

    deleteAllDatasetRecords($user, $api_key, $data['dataset']['id']);

    $records_data = $data["records"];
    $dataset->insertManyRecordsFromString($records_data, $cuser);

    /* response */
    $return_data = Array(
        "templateid" => $template->id,
        "datasetid" => $data['dataset']['id'],
    );
    return APIReturnData::build($return_data, true);
}

function fullDatasetAppend($user, $api_key, $labid, $data) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) {
        return APIReturnData::quick400("invalid lab");
    }

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    /* Load dataset .... trying $data["dataset"]["id"] ...*/
    $dataset = Dataset::loadBy(Array("id"), Array($data["dataset"]["id"]));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

    $old_template = DatasetFieldTemplate::loadBy(Array("id"), Array($dataset->template()->id));
    
    $template_data = $data["template"];


    if ($data['template']['subject'] != $data['dataset']['old_subject']) {
        // MUST do the delete first since cannot have more than one annotation for "subject"
        foreach ($old_template->fields() as $field) {
            if ($field->name == $data['dataset']['old_subject']) {
                $field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($old_template->id, $field->name));
                if(is_null($field)) return APIReturnData::quick400("could not find dataset field");

                $annotation = DatasetFieldAnnotation::loadBy(Array("dataset_field_id", "name"), Array($field->id, "subject"));
                if(is_null($annotation)) return APIReturnData::quick400("ccould not find dataset field annotation");

                DatasetFieldAnnotation::deleteObj($annotation);
                break;
            }

        }

        // for the add
        foreach ($old_template->fields() as $field) {
            // find the subject field
            if ($field->name == $template_data["subject"]) {
                $field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($old_template->id, $field->name));
                if(is_null($field)) return APIReturnData::quick400("could not find dataset field");

                DatasetFieldAnnotation::upsert($field, "subject", NULL);
             }
        }
    }

    $records_data = $data["records"];
    $dataset->insertManyRecordsFromString($records_data, $cuser);

    /* response */
    $return_data = Array(
        "templateid" => $old_template->id,
        "datasetid" => $data['dataset']['id'],
    );
    return APIReturnData::build($return_data, true);
}
function getDatasetDoiKeyValues($user, $api_key, $dataset_id) {
//    $dataset = Dataset::loadBy(Array("dataset_id"), Array($dataset_id));
//    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

//    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset_id))) return APIReturnData::quick403();

    $keyvalues = DatasetDoiKeyValues::loadArrayBy(Array("dataset_id"), Array($dataset_id));
    return APIReturnData::build($keyvalues, true);
}

function getDatasetDoiAuthors($user, $api_key, $dataset_id) {
//    $dataset = Dataset::loadBy(Array("dataset_id"), Array($dataset_id));
//    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

//    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset_id))) return APIReturnData::quick403();
    //$keyvalues = DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "type"), Array($dataset_id, "contributor"));
    $keyvalues = DatasetDoiKeyValues::authorsOnly(Array("dataset_id", "type"), Array($dataset_id, "contributor"));

    return APIReturnData::build($keyvalues, true);
}

function getDatasetDoiKeyValuesByType($user, $api_key, $dataset_id, $type) {
//    $dataset = Dataset::loadBy(Array("dataset_id"), Array($dataset_id));
//    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

//    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset_id))) return APIReturnData::quick403();
    $keyvalues = DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "type"), Array($dataset_id, $type));
    return APIReturnData::build($keyvalues, true);
}

function getDatasetDoiKeyValuesBySubtype($user, $api_key, $dataset_id, $subtype) {
//    $dataset = Dataset::loadBy(Array("dataset_id"), Array($dataset_id));
//    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

//    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset_id))) return APIReturnData::quick403();
    $keyvalues = DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "subtype"), Array($dataset_id, $subtype));
    return APIReturnData::build($keyvalues, true);
}

function addDatasetDoiKeyValues($user, $api_key, $dataset_id, $text, $type, $position) {
    $dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
//    if(is_null($keyvalues)) return APIReturnData::quick400("could not find key values");

//    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

  //  $dataset_field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
  //  if(is_null($dataset_field)) return APIReturnData::quick400("could not find field");

    //createNewObj(Dataset $dataset, $dataset_id, $text, $type, $position) {
    $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $text, $type, $position);
    return APIReturnData::build($keyvalues, true);
}

function addDatasetDoiMultipleKeyValues($user, $api_key, $dataset_id, $data, $type, $position) {
    $dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
//    if(is_null($keyvalues)) return APIReturnData::quick400("could not find key values");

//    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

  //  $dataset_field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
  //  if(is_null($dataset_field)) return APIReturnData::quick400("could not find field");

    //createNewObj(Dataset $dataset, $dataset_id, $text, $type, $position) {

    // some have position of only 0, which means just count the type
    // however, some have subtypes that differ, so then you have to consider subtype
    if ($type == "funding") {
        $subtype = 'agency';
    } elseif ($type == "contributor") {
        $subtype = 'name';
    }
    
    if ($subtype)
        $position = count(DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "type", "subtype"), Array($dataset_id, $type, $subtype)));
    else    
        $position = count(DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "type"), Array($dataset_id, $type)));

    if ($type == 'overview')
        $position = 0;

    foreach ($data as $subtype=>$value) {
        $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, \helper\sanitizeHTMLString($value), $type, $subtype, $position);
    }

    if(is_null($keyvalues)) return APIReturnData::quick400("could not add key values");
    return APIReturnData::build($keyvalues, true);
}

function deleteDatasetDoiKeyValuesById($user, $api_key, $id) {
    $keyvalues = DatasetDoiKeyValues::loadBy(Array("id"), Array($id));
    DatasetDoiKeyValues::deleteObj($keyvalues);

    return APIReturnData::build(NULL, true);
}
 
function deleteDatasetDoiMultipleKeyValues($user, $api_key, $dataset_id, $type, $position) {
    // get multiple records
    $cxn = new Connection();
    $cxn->connect();
    $ids = $cxn->select("dataset_doi_keyvalues ddk", Array("ddk.id"), "isi",
        Array($dataset_id, $type, $position), "where ddk.dataset_id = ? AND ddk.type = ? AND ddk.position = ?"
    );

    if(empty($ids)) return APIReturnData::quick404();
    // loop thru the ids and delete individually
    foreach ($ids as $id) {
        $keyvalues = DatasetDoiKeyValues::loadBy(Array("id"), Array($id['id']));
        DatasetDoiKeyValues::deleteObj($keyvalues);
    }
    
    // move the following out of the loop; only need to run it once ...        
        $cxn->updateNonDiscreteValue("update dataset_doi_keyvalues set position = position - 1 where dataset_id=? AND type=? AND position > ?", 'isi', Array( $dataset_id, $type, $position));

    $cxn->close();

    return APIReturnData::build(NULL, true);
}

function updateDatasetDoiMultipleKeyValues($user, $api_key, $dataset_id, $data, $type, $position) {
    $cxn = new Connection();
    $cxn->connect();

    $in_array_check['contributor'] = array("name", "firstname", "lastname", "middleinitial", "initials", "author", "contact", "orcid", "affiliation", "email");
    $in_array_check['funding'] = array("initials", "agency", "identifier");
    $in_array_check['publication'] = array("publication", "publication_title", "publication_doi", "publication_pmid", "relevance");
    $in_array_check['author'] = array("name", "contact");
    $in_array_check['abstract'] = array("study_purpose", "data_collected", "primary_conclusion", "data_usage_notes", "conclusions");
    $in_array_check['notes'] = array("notes");
    $in_array_check['license'] = array("license");
    $in_array_check['overview'] = array("doi", "title", "year", "citation", "hidden_doi", "filesize");

    foreach ($data as $subtype=>$value) {
        if (in_array($subtype, $in_array_check[$type])) {
//            die($dataset_id . " - " . $type . " - " . $position . " - " . $subtype);
            $ids = $cxn->select("dataset_doi_keyvalues ddk", Array("ddk.id"), "isis",
                Array($dataset_id, $type, $position, $subtype), "where ddk.dataset_id = ? AND ddk.type = ? AND ddk.position = ? AND ddk.subtype = ?"
            );

            if(empty($ids)) {
                    $dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
                    $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, \helper\sanitizeHTMLString($value), $type, $subtype, $position);

/*
                // orcid is optional, so it would have been empty if none was provided at first. So, now do an ADD vs UPDATE
                if (($type == 'contributor') && ($subtype == 'orcid')) {
                    $dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
                    $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $value, $type, $subtype, $position);
                } else
                    return APIReturnData::quick404();
*/                    
            } else { 
                foreach ($ids as $id) {
                    $keyvalues = DatasetDoiKeyValues::loadBy(Array("id"), Array($id['id']));
                    // IF pertains to someone unchecking the contact author box
                    if ($type=='author' && $subtype=='contact') {
                        DatasetDoiKeyValues::deleteObj($keyvalues);
                    } else {
                        $keyvalues->text = \helper\sanitizeHTMLString($value);
                        $keyvalues->updateDB();
                    }
                }
            }
        }    
    }

    $cxn->close();
    return APIReturnData::build(NULL, true);
}

function updateDatasetDoiKeyValues($user, $api_key, $id, $text, $type, $position) {
    $keyvalues = DatasetDoiKeyValues::loadBy(Array("id"), Array($id));
    if(is_null($keyvalues)) return APIReturnData::quick400("could not find key values");

//    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

  //  $dataset_field = DatasetField::loadBy(Array("dataset_fields_template_id", "name"), Array($template->id, $name));
  //  if(is_null($dataset_field)) return APIReturnData::quick400("could not find field");

    $keyvalues->text = \helper\sanitizeHTMLString($text);
    $keyvalues->position = $position;
    $keyvalues->updateDB();

    return APIReturnData::build($keyvalues, true);
}

function moveDatasetDoiMultipleKeyValues($user, $api_key, $id, $desired, $current, $type) {
//    $keyvalues = DatasetDoiKeyValues::loadBy(Array("id"), Array($id));
//    if(is_null($keyvalues)) return APIReturnData::quick400("could not find key values");


//    alert(ui.item.index() + " used to be " + $(this).attr('data-id'));
// 0                            current: $(this).attr('data-id'),
// 2                            desired: ui.item.index(),

    $move = $desired > $current ? 'down' : 'up';
          
    $cxn = new Connection();
    $cxn->connect();

    // temporarily set position = -1 for the item being moved
    $cxn->update('dataset_doi_keyvalues', 'iisi', Array('position'), Array(-1, $id, $type, $current), 'where dataset_id=? AND type=? AND position=?');

    // single query to move stack up/down
    if ($move == 'down') {
        $cxn->updateNonDiscreteValue("update dataset_doi_keyvalues set position = position - 1 where dataset_id=? AND type=? AND position > ? AND position <= ?", 'isii', Array( $id, $type, $current, $desired));
    } else {
        $cxn->updateNonDiscreteValue("update dataset_doi_keyvalues set position = position + 1 where dataset_id=? AND type=? AND position >= ? AND position < ?", 'isii', Array( $id, $type, $desired, $current));
    }

    // set final position for item being moved
    $cxn->update('dataset_doi_keyvalues', 'iisi', Array('position'), Array($desired, $id, $type, -1), 'where dataset_id=? AND type=? AND position=?');

    $cxn->close();

    return APIReturnData::build(NULL, true);
}


function getDatasetDoiText($user, $api_key, $dataset_id) {
//    $dataset = Dataset::loadBy(Array("dataset_id"), Array($dataset_id));
//    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");
//    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset_id))) return APIReturnData::quick403();
    $textfields = DatasetDoiTextFields::loadArrayBy(Array("dataset_id"), Array($dataset_id));
    return APIReturnData::build($textfields, true);
}

function importPub ($user, $api_key, $dataset_id, $pub, $include_authors) {
    require_once __DIR__ . "/elasticsearch_wrapper.php";
    $dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
    if(is_null($dataset)) return APIReturnData::quick400("could not find dataset");

//    if(!\APIPermissionActions\checkAction("dataset-visible", $api_key, $user, Array("dataset" => $dataset_id))) return APIReturnData::quick403();
    $post_string = '{
      "query": {
      "bool" : {
        "should" : [ 
          { "match_phrase" : {
              "dc.identifier" : {
                "query" : "PMID"
            } }   },
          { "match_phrase" : {
              "dc.alternateIdentifiers.curie" : {
                "query" : "PMID"
            } }   }
          ]
      } 
      }
    }';

    // start with DOI regexp. this should work work https://doi:, doi:, or just the string
    $pattern = "/(10\.[0-9]{4}.*)/";
    if (preg_match($pattern, $pub, $matches)) {
        $subtype = 'publication_doi';
    } else {
        // if no DOI match, then try PMID. this should work for pmid:### or ###
        $pattern = '/(\d*)$/';
        if (preg_match($pattern, $pub, $matches)) {
            $subtype = 'publication_pmid';
        } else 
            return;
    }

    // get position for new entry
    $pub_position = count(DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "subtype"), Array($dataset_id, "publication")));

    // save doi or pmid, or blank
    $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $matches[1], "publication", $subtype, $pub_position);

    // setup for ES query
    $post_string = str_replace("PMID", $matches[1], $post_string);
    $url = $GLOBALS["config"]["elastic-search"]["pubmed"]["base-url"] . "/pubmed/_search";
    $http_login = $GLOBALS["config"]["elastic-search"]["pubmed"]["user"] . ":" . $GLOBALS["config"]["elastic-search"]["pubmed"]["password"];
    $method = "POST";

    $es_data = elasticRequest($url, $http_login, $method, $get_params, $post_string);

    if (!$es_data['success']) {
        $url = $GLOBALS["config"]["elastic-search"]["pubmed"]["base-url"] . "/LIT_biorxiv_pr/_search";
        $es_data = elasticRequest($url, $http_login, $method, $get_params, $post_string);
    }

    if ($es_data['success']) {
        $json_obj = json_decode($es_data['body']);

        // save Title
        $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $json_obj->hits->hits[0]->_source->dc->title, "publication", "publication", $pub_position);

        if ($include_authors) {
            // save name info
            foreach ($json_obj->hits->hits[0]->_source->dc->creators as $creator) {
                $position = count(DatasetDoiKeyValues::loadArrayBy(Array("dataset_id", "type", "subtype"), Array($dataset_id, "contributor", "name")));

                foreach (array("name", "familyName", "givenName", "initials", "affiliation") as $f) {
                    if (isset($creator->$f)) {
                        if ($f == 'familyName')
                            $field = 'lastname';
                        elseif ($f == 'givenName')
                            $field = 'firstname';
                        else
                            $field = $f;
                        $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $creator->$f, "contributor", $field, $position);
                    }
                }
                if (isset($creator->initials)) {
                    $mi = substr($creator->initials, 1, 1);
                    $keyvalues = DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $mi, "contributor", 'middleinitial', $position);
                }
            }
        }
    }

    return APIReturnData::build(json_encode($es_data), true);
}

function getAssociatedFiles($user, $api_key, $dataset_id) {
    $dictionary = DatasetAssociatedFiles::loadBy(Array("dataset_id", "type"), Array($dataset_id, 'dictionary'));
    $methodology = DatasetAssociatedFiles::loadBy(Array("dataset_id", "type"), Array($dataset_id, 'methodology'));

    $files['dictionary'] = $dictionary;
    $files['methodology'] = $methodology;

 //  if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    return APIReturnData::build($files, true);

}

?>
