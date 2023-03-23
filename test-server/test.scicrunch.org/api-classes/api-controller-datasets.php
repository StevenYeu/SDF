<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post($AP."/datasets/add", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $name = aR($request->request->get("name"), "s");
    $long_name = aR($request->request->get("long_name"), "s");
    $description = aR($request->request->get("description"), "s");
    $publications = aR($request->request->get("publications"), "s");
    $metadata = $request->request->get("metadata");
    $template_id = $request->request->get("template_id");
    return appReturn($app, addDataset($app["config.user"], $app["config.api_key"], $template_id, $name, $long_name, $description, $publications, $metadata), true);
});

$app->post($AP."/datasets/delete", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->request->get("datasetid");
    return appReturn($app, deleteDataset($app["config.user"], $app["config.api_key"], $datasetid), false);
});

$app->post($AP."/datasets/edit", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $name = aR($request->request->get("name"), "s");
    $long_name = aR($request->request->get("long_name"), "s");
    $description = aR($request->request->get("description"), "s");
    $publications = aR($request->request->get("publications"), "s");
    $metadata = $request->request->get("metadata");
    $lab_status = aR($request->request->get("lab_status"), "s");
    return appReturn($app, editDataset($app["config.user"], $app["config.api_key"], $datasetid, $name, $long_name, $description, $publications, $metadata, $lab_status), true);
});

$app->post($AP."/datasets/fields/add", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $ilxid = aR($request->request->get("ilxid"), "s");
    $required = aR($request->request->get("required"), "i");
    $queryable = aR($request->request->get("queryable"), "i");
    return appReturn($app, addDatasetField($app["config.user"], $app["config.api_key"], $template_id, $name, $ilxid, $required, $queryable), true);
});

$app->post($AP."/datasets/fields/add/bulk", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $fields = $request->request->get("fields");
    foreach($fields as &$field) {
        $field["name"] = aR($field["name"], "s");
        $field["ilxid"] = aR($field["ilxid"], "s");
        $field["required"] = aR($field["required"], "i");
        $field["queryable"] = aR($field["queryable"], "i");
    }
    return appReturn($app, addDatasetFieldBulk($app["config.user"], $app["config.api_key"], $template_id, $fields), false, true);
});

$app->post($AP."/datasets/fields/add/multiple", function(Request $request) use($app) {
    /* for fields with multiple flag */
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $ilxid = aR($request->request->get("ilxid"), "s");
    $required = aR($request->request->get("required"), "i");
    $queryable = aR($request->request->get("queryable"), "i");
    $suffixes = $request->request->get("suffixes");
    return appReturn($app, addDatasetFieldMultiple($app["config.user"], $app["config.api_key"], $template_id, $name, $ilxid, $required, $queryable, $suffixes), true);
});

$app->post($AP."/datasets/fields/delete", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    return appReturn($app, deleteDatasetField($app["config.user"], $app["config.api_key"], $template_id, $name), false);
});

$app->post($AP."/datasets/fields/move", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $direction = aR($request->request->get("direction"), "s");
    return appReturn($app, moveDatasetField($app["config.user"], $app["config.api_key"], $template_id, $name, $direction), false);
});

$app->post($AP."/datasets/fields/name", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $new_name = aR($request->request->get("new_name"), "s");
    return appReturn($app, updateDatasetFieldName($app["config.user"], $app["config.api_key"], $template_id, $name, $new_name), true);
});

$app->post($AP."/datasets/fields/ilx", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $new_ilx = aR($request->request->get("ilx"), "s");
    return appReturn($app, updateDatasetFieldILX($app["config.user"], $app["config.api_key"], $template_id, $name, $new_ilx), true);
});

$app->post($AP."/datasets/records/add", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $record_fields = $request->request->get("fields");
    return appReturn($app, addDatasetRecord($app["config.user"], $app["config.api_key"], $datasetid, $record_fields), false);
});

$app->post($AP."/datasets/records/add/multiple", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $record_fields_array = $request->request->get("records");
    return appReturn($app, addMultipleDatasetRecords($app["config.user"], $app["config.api_key"], $datasetid, $record_fields_array), false);
});

$app->post($AP."/datasets/records/delete", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $recordid = $request->request->get("recordid");
    return appReturn($app, deleteDatasetRecord($app["config.user"], $app["config.api_key"], $datasetid, $recordid), false);
});

$app->post($AP."/datasets/records/delete/all", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    return appReturn($app, deleteAllDatasetRecords($app["config.user"], $app["config.api_key"], $datasetid), false);
});

$app->get($AP."/datasets", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $portalname = $request->query->get("portalname");
    return appReturn($app, getCommunityDatasets($app["config.user"], $app["config.api_key"], $portalname), false, true);
});

$app->get($AP."/datasets/fields", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("datasetid");
    return appReturn($app, getDatasetFields($app["config.user"], $app["config.api_key"], $datasetid), false, true);
});

$app->get($AP."/datasets/search", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("datasetid");
    $query = $request->query->get("q");
    $offset = $request->query->get("offset");
    $count = $request->query->get("count");
    return appReturn($app, datasetSearch($app["config.user"], $app["config.api_key"], $datasetid, $query, $offset, $count), false);
});

$app->get($AP."/datasets/metadatafields", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $labid = $request->query->get("labid");
    return appReturn($app, datasetMetadataFields($app["config.user"], $app["config.api_key"], $labid), false, true);
});

//AO
$app->get($AP."/datasets/check-template", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("datasetid");
    $portalname = $request->query->get("portalname");
    return appReturn($app, checkDatasetTemplate($app["config.user"], $app["config.api_key"], $datasetid, $portalname), false);
});

$app->post($AP."/datasets/submit", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $portalname = aR($request->request->get("portalname"), "s");
    return appReturn($app, submitDataset($app["config.user"], $app["config.api_key"], $datasetid, $portalname), false);
});

$app->post($AP."/datasets/withdraw", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $portalname = aR($request->request->get("portalname"), "s");
    return appReturn($app, withdrawDataset($app["config.user"], $app["config.api_key"], $datasetid, $portalname), false);
});

$app->get($AP."/datasets/info", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("datasetid");
    return appReturn($app, singleDatasetInfo($app["config.user"], $app["config.api_key"], $datasetid), true);
});

$app->post($AP."/datasets/template/add", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $name = aR($request->request->get("name"), "s");
    $labid = aR($request->request->get("labid"), "i");
    $required_fields_name = aR($request->get("required-fields-name"), "s");
    return appReturn($app, addDatasetTemplate($app["config.user"], $app["config.api_key"], $labid, $name, $required_fields_name), true);
});

$app->post($AP."/datasets/template/delete", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    return appReturn($app, deleteDatasetTemplate($app["config.user"], $app["config.api_key"], $template_id), false);
});

$app->post($AP."/datasets/template/copy", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    return appReturn($app, copyDatasetTemplate($app["config.user"], $app["config.api_key"], $template_id, $name), true);
});

$app->post($AP."/datasets/template/submit", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    return appReturn($app, submitDatasetTemplate($app["config.user"], $app["config.api_key"], $template_id, true), true);
});

$app->post($AP."/datasets/template/unsubmit", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    return appReturn($app, submitDatasetTemplate($app["config.user"], $app["config.api_key"], $template_id, false), true);
});

$app->post($AP."/datasets/template/name", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $new_name = aR($request->request->get("name"), "s");
    return appReturn($app, updateDatasetTemplateName($app["config.user"], $app["config.api_key"], $template_id, $new_name), true);
});

$app->get($AP."/datasets/template", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = $request->query->get("template_id");
    return appReturn($app, getDatasetTemplate($app["config.user"], $app["config.api_key"], $template_id), true);
});

$app->get($AP."/datasets/template/bylab", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $labid = $request->query->get("labid");
    $result = getDatasetTemplateByLab($app["config.user"], $app["config.api_key"], $labid);
    $new_data = Array();
    if($result->success) {
        foreach($result->data as $d) {
            $new_data[] = $d->arrayForm(true);
        }
    }
    $new_result = APIReturnData::build($new_data, $result->success, $result->status_code, $result->status_msg);
    return appReturn($app, $new_result);
});

$app->get($AP."/datasets/id", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $labid = $request->query->get("labid");
    $dataset_name = $request->query->get("datasetname");
    return appReturn($app, getDatasetID($app["config.user"], $app["config.api_key"], $labid, $dataset_name));
});

$app->post($AP."/datasets/change-lab-status", function(Request $request) use($app) {
    require_once __DIR__ . "/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $status = aR($request->request->get("status"), "s");
    return appReturn($app, changeLabStatus($app["config.user"], $app["config.api_key"], $datasetid, $status), true);
});

$app->post($AP."/datasets/change-curation-status", function(Request $request) use($app) {
    require_once __DIR__ . "/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $status = aR($request->request->get("status"), "s");
    return appReturn($app, changeCurationStatus($app["config.user"], $app["config.api_key"], $datasetid, $status), true);
});

$app->post($AP."/datasets/change-editor-status", function(Request $request) use($app) {
    require_once __DIR__ . "/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    $status = aR($request->request->get("status"), "s");
    return appReturn($app, changeEditorStatus($app["config.user"], $app["config.api_key"], $datasetid, $status), true);
});

$app->post($AP."/datasets/request-doi", function(Request $request) use($app) {
    require_once __DIR__ . "/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    return appReturn($app, requestDOI($app["config.user"], $app["config.api_key"], $datasetid), true);
});
/*
$app->post($AP."/datasets/request-doi", function(Request $request) use($app) {
    require_once __DIR__ . "/datasets.php";
    $datasetid = aR($request->request->get("datasetid"), "i");
    var_dump($datasetid);
    exit;
    return appReturn($app, requestDOI($app["config.user"], $app["config.api_key"], $datasetid), true);
});
*/
$app->post($AP."/datasets/field/annotation/add", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $annotation_name = aR($request->request->get("annotation_name"), "s");
    $annotation_value = aR($request->request->get("annotation_value"), "s");
    return appReturn($app, addDatasetFieldAnnotation($app["config.user"], $app["config.api_key"], $template_id, $name, $annotation_name, $annotation_value), true);
});

$app->post($AP."/datasets/field/annotation/remove", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $template_id = aR($request->request->get("template_id"), "i");
    $name = aR($request->request->get("name"), "s");
    $annotation_name = aR($request->request->get("annotation_name"), "s");
    return appReturn($app, removeDatasetFieldAnnotation($app["config.user"], $app["config.api_key"], $template_id, $name, $annotation_name), true);
});

$app->post($AP."/datasets/community-required-field/{dataset_type_name}/{name}/add", function(Request $request, $dataset_type_name, $name) use($app) {
    require_once __DIR__."/datasets.php";
    $cid = aR($request->request->get("cid"), "i");
    $ilx = aR($request->request->get("ilx"), "s");
    $multi = aR($request->request->get("multi"), "i");
    $multi_suffixes = $request->request->get("multi_suffixes");
    foreach($multi_suffixes as &$ms) {
        $ms = aR($ms, "s");
    }
    return appReturn($app, addCommunityDatasetRequiredField($app["config.user"], $app["config.api_key"], $cid, $ilx, aR($dataset_type_name, "s"), aR($name, "s"), $multi, $multi_suffixes), true);
});

$app->post($AP."/datasets/community-required-field/{dataset_type_name}/{name}/remove", function(Request $request, $dataset_type_name, $name) use($app) {
    require_once __DIR__."/datasets.php";
    $cid = aR($request->request->get("cid"), "i");
    return appReturn($app, removeCommunityDatasetRequiredField($app["config.user"], $app["config.api_key"], $cid, aR($dataset_type_name, "s"), aR($name, "s")), false);
});

$app->post($AP."/datasets/community-required-field/{dataset_type_name}/{name}/move", function(Request $request, $dataset_type_name, $name) use($app) {
    require_once __DIR__."/datasets.php";
    $cid = aR($request->request->get("cid"), "i");
    $direction = aR($request->request->get("direction"), "s");
    return appReturn($app, moveCommunityDatasetRequiredField($app["config.user"], $app["config.api_key"], $cid, aR($dataset_type_name, "s"), aR($name, "s"), $direction), true);
});

$app->post($AP."/datasets/community-required-field/{dataset_type_name}/{name}/make-subject", function(Request $request, $dataset_type_name, $name) use($app) {
    require_once __DIR__."/datasets.php";
    $cid = aR($request->request->get("cid"), "i");
    return appReturn($app, makeSubjectCommunityDatasetRequiredField($app["config.user"], $app["config.api_key"], $cid, aR($dataset_type_name, "s"), aR($name, "s")), true);
});

$app->get($AP."/datasets/community-required-field", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $cid = $request->query->get("cid");
    return appReturn($app, getCommunityDatasetRequiredFields($app["config.user"], $app["config.api_key"], $cid), false, true);
});

$app->get($AP."/datasets/validate", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("datasetid");
    return appReturn($app, datasetValidate($app["config.user"], $app["config.api_key"], $datasetid), false);
});

$app->get($AP."/datasets/check-name", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $name = $request->query->get("name");
    $labid = $request->query->get("labid");
    return appReturn($app, checkDatasetName($app["config.user"], $app["config.api_key"], $name, $labid), false);
});

$app->post($AP."/datasets/full-upload", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $labid = aR($request->request->get("labid"), "i");
    $data = $request->request->get("data");
    return appReturn($app, fullDatasetUpload($app["config.user"], $app["config.api_key"], $labid, $data));
});

$app->post($AP."/datasets/full-update", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $labid = aR($request->request->get("labid"), "i");
    $data = $request->request->get("data");

    return appReturn($app, fullDatasetUpdate($app["config.user"], $app["config.api_key"], $labid, $data));
});

$app->post($AP."/datasets/full-append", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $labid = aR($request->request->get("labid"), "i");
    $data = $request->request->get("data");

    return appReturn($app, fullDatasetAppend($app["config.user"], $app["config.api_key"], $labid, $data));
});

$app->get($AP."/datasets/doi", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("datasetid");
    return appReturn($app, getDatasetDoi($app["config.user"], $app["config.api_key"], $datasetid), false);
});

$app->get($AP."/datasets/doi/keyvalues", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = $request->query->get("dataset_id");

    $result = getDatasetDoiKeyValues($app["config.user"], $app["config.api_key"], $dataset_id);
    $new_data = Array();
    if($result->success) {
        foreach($result->data as $d) {
            $new_data[] = $d->arrayForm(true);
        }
    }
    $new_result = APIReturnData::build($new_data, $result->success, $result->status_code, $result->status_msg);
    return appReturn($app, $new_result);
});

$app->get($AP."/datasets/doi/authors", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = $request->query->get("dataset_id");

    $result = getDatasetDoiAuthors($app["config.user"], $app["config.api_key"], $dataset_id);

    $new_data = Array();
    if($result->success) {
        foreach($result->data as $d) {
            $new_data[] = $d; //->arrayForm(true);
                    
        }
    }

    $new_result = APIReturnData::build($new_data, $result->success, $result->status_code, $result->status_msg);
    return appReturn($app, $new_result);
});


$app->get($AP."/datasets/doi/keyvalues/{type}", function(Request $request, $type) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = $request->query->get("dataset_id");

    $result = getDatasetDoiKeyValuesByType($app["config.user"], $app["config.api_key"], $dataset_id, $type);
    $new_data = Array();
    if($result->success) {
        foreach($result->data as $d) {
            $new_data[] = $d->arrayForm(true);
        }
    }
    $new_result = APIReturnData::build($new_data, $result->success, $result->status_code, $result->status_msg);
    return appReturn($app, $new_result);
});

$app->post($AP."/datasets/doi/keyvalues/add", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = aR($request->request->get("dataset_id"), "i");
    $text = aR($request->request->get("text"), "s");
    $type = aR($request->request->get("type"), "s");
    $position = aR($request->request->get("position"), "i");

    return appReturn($app, addDatasetDoiKeyValues($app["config.user"], $app["config.api_key"], $dataset_id, $text, $type, $position), true);
});

// try to save json data ...
$app->post($AP."/datasets/doi/keyvalues/multipleAdd", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = aR($request->request->get("dataset_id"), "i");
    $data = $request->request->get("data");
    $type = aR($request->request->get("type"), "s");
    $position = aR($request->request->get("position"), "i");

    return appReturn($app, addDatasetDoiMultipleKeyValues($app["config.user"], $app["config.api_key"], $dataset_id, $data, $type, $position), true);
});

$app->post($AP."/datasets/doi/keyvalues/multipleDelete", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = aR($request->request->get("dataset_id"), "i");
    $type = aR($request->request->get("type"), "s");
    $position = aR($request->request->get("position"), "i");

    return appReturn($app, deleteDatasetDoiMultipleKeyValues($app["config.user"], $app["config.api_key"], $dataset_id, $type, $position), false, true);
});

$app->post($AP."/datasets/doi/keyvalues/multipleUpdate", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = aR($request->request->get("dataset_id"), "i");
    $data = $request->request->get("data");
    $type = aR($request->request->get("type"), "s");
    $position = aR($request->request->get("position"), "i");

    return appReturn($app, updateDatasetDoiMultipleKeyValues($app["config.user"], $app["config.api_key"], $dataset_id, $data, $type, $position), false, true);
});

$app->post($AP."/datasets/doi/keyvalues/multipleMove", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = aR($request->request->get("dataset_id"), "i");
    $desired = $request->request->get("desired");
    $current = $request->request->get("current");
    $type = aR($request->request->get("type"), "s");

    return appReturn($app, moveDatasetDoiMultipleKeyValues($app["config.user"], $app["config.api_key"], $dataset_id, $desired, $current, $type), false, true);
});

$app->post($AP."/datasets/doi/keyvalues/update", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $id = aR($request->request->get("id"), "i");
    $text = aR($request->request->get("text"), "s");
    $type = aR($request->request->get("type"), "s");
    $position = aR($request->request->get("position"), "i");
    return appReturn($app, updateDatasetDoiKeyValues($app["config.user"], $app["config.api_key"], $id, $text, $type, $position), true);
});



$app->get($AP."/datasets/doi/text", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $datasetid = $request->query->get("dataset_id");
    $result = getDatasetDoiText($app["config.user"], $app["config.api_key"], $datasetid);
    $new_data = Array();
    if($result->success) {
        foreach($result->data as $d) {
            $new_data[] = $d->arrayForm(true);
        }
    }
    $new_result = APIReturnData::build($new_data, $result->success, $result->status_code, $result->status_msg);
    return appReturn($app, $new_result);
});



$app->post($AP."/datasets/doi/importpubs", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = aR($request->request->get("dataset_id"), "i");
    $pub = aR($request->request->get("pub"), "s");
    $include_authors = aR($request->request->get("include_authors"), "i");

    return appReturn($app, importPub($app["config.user"], $app["config.api_key"], $dataset_id, $pub, $include_authors), false, true);
});


$app->get($AP."/datasets/associated-files", function(Request $request) use($app) {
    require_once __DIR__."/datasets.php";
    $dataset_id = $request->query->get("dataset_id");
    return appReturn($app, getAssociatedFiles($app["config.user"], $app["config.api_key"], $dataset_id), false);
});
?>
