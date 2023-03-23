<?php

ini_set('auto_detect_line_endings',TRUE);
include '../../classes/classes.php';
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/term/curie_catalog.php";
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/term/term_exists.php";
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/term/term_by_label.php";
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/ilx_add.php";
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/term/add_term.php";
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/term/term_ontologies.php";

\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    header('location:/');
    exit();
}

$user = $_SESSION['user'];
$cid = $_REQUEST['cid'];
$filename = $_FILES['file']['name'];
$destination = $GLOBALS["DOCUMENT_ROOT"] . "/upload/term/tmp/" . $filename;
//echo $destination;
$status = move_uploaded_file( $_FILES['file']['tmp_name'] , $destination );

$api_key = NULL;
$curie_catalog = getCurieCatalog($user, $api_key);
$prefix2id = array();
$ns2id = array();
$prefix2ns = array();
for ($i=0; $i<count($curie_catalog); $i++){
    $prefix2id[trim($curie_catalog[$i]["prefix"])] = trim($curie_catalog[$i]["id"]);
    $ns2id[trim($curie_catalog[$i]["namespace"])] = trim($curie_catalog[$i]["id"]);
    $prefix2ns[trim($curie_catalog[$i]["prefix"])] = trim($curie_catalog[$i]["namespace"]);
}
//print_r($prefix2id);

$ontologies = getTermOntologies($user, $api_key);
$url2id = array();
for ($i=0; $i<count($ontologies); $i++){
    $url2id[trim($ontologies[$i]['url'])] = trim($ontologies[$i]['id']);
}
//print_r($url2id);

$errors = array();
$types = array("term", "relationship", "annotation", "cde");
$ilxes = array();

// Read JSON file
$json = file_get_contents($destination);

//Decode JSON
$json_data = json_decode($json,true);

//print_r($json_data);
foreach ($json_data as $index=>$data){
    $count = $index + 1;
//     echo $count;
//     print_r($data);

    $label = trim($data['label']);
    if (strlen($label) == 0) {
        $errors[] = "Entry " . $count . " missing label. Label is required.";
        continue;
    }

    $existing = termExists($user, $api_key, $label, $user->id);
    if (count($existing) > 0) {
        $errors[] = 'Term "' . $label . '" (' . $existing[0]['ilx'] . ") already exists and you are the owner.";
        continue;
    }

    $ok = true;
    $obj = array();
    $obj["label"] = $label;

    if (isset($data['definition']) && trim($data['definition']) != "") {
        $obj["definition"] = trim($data['definition']);
    }
    if (isset($data['comment']) && trim($data['comment']) != "") {
        $obj["comment"] = trim($data['comment']);
    }

    $obj["type"] = isset($data['type']) ? trim($data['type']) : "";
    if ($obj['type'] != "" && !in_array(trim($obj["type"]), $types)) {
        $errors[] = 'Term "' . $label . '" type (' . $obj["type"] . ") is not allowed. Only the following types are allowed: term, relationship, annotation, cde.";
        $ok = false;
    }

    $ontology_urls = $data['ontologies'];
    for ($i=0; $i<count($ontology_urls); $i++){
        $url = isset($ontology_urls[$i]['url']) ? trim($ontology_urls[$i]['url']) : "";
        if ($url != "" &&  array_key_exists($url, $url2id) ){
            $obj["ontologies"][] = array("url"=>$url, "id"=>$url2id[$url]);
        }
        elseif ($url == "") {
        }
        elseif (!array_key_exists($url, $url2id)) {
            $errors[] = 'Term "' . $label . '" ontology URL does not exist in SciCrunch term. Please add it before using it\nYour URL: ' .
                $ontology_urls[$i]['url'];
            $ok = false;
        }
        else {
             $errors[] = 'Term "' . $label . '" ontology URL does not exist in SciCrunch term. Please add it before using it\nYour URL: ' .
                $ontology_urls[$i]['url'];
            $ok = false;
        }
    }

    $super_label = isset($data['superclass']['label']) ? trim($data['superclass']['label']) : "";
    if ($super_label != ""){
        $super = termLookup($user, $api_key, $super_label);
        //print_r($super);
        if (!isset($super['id']) || $super['id'] == 0){
            $errors[] = 'Term "' . $label . '" superclass label (' . $data['superclass']['label'] . ") does not exist in SciCrunch. Please add it before using it.";
            $ok = false;
        } else {
            $obj["superclasses"][] = array("superclass_tid"=>$super['id']);
        }
    }

    $obj["synonyms"] = array();
    for ($i=0; $i<count($data['synonyms']); $i++){
        if (trim($syn['literal']) == ''){
            continue;
        }
        $syn = $data['synonyms'][$i];
        $obj["synonyms"][] = array("literal"=>trim($syn['literal']), "type"=>trim($syn['type']));
    }

    $obj["existing_ids"] = array();
    for ($i=0; $i<count($data['existing_ids']); $i++){
        $eid = $data['existing_ids'][$i];
        $curie = trim($eid['curie']);
        $iri = trim($eid['iri']);
        if ($curie == '' && $iri == '') {
            continue;
        }

        $preferred = 0;
        if ( $eid['preferred'] == 1 ) {$preferred = 1;}
        $ccid = 0;
        $num = 0;
        $prefix = '';
        $arr = array();
        if (isset($curie) && count($curie) > 0) {
            $tmp = explode(":", $curie);
            $num = trim($tmp[1]);
            $prefix = trim($tmp[0]);
            $ccid = $prefix2id[$prefix];

            if (isset($iri) && count($iri > 0)) {
                if (strpos($iri, $prefix2ns[$prefix]) !== false) {
                    $arr['iri'] = $iri;
                }
            }

            if ($ccid == 0) {
                $errors[] = 'Term "' . $label . '" curie prefix ('. $prefix . ') does not exist in SciCrunch Curie Catalog. Please add it before using it.';
                $ok = false;
            } else {
                $arr['curie'] = $curie;
                $arr['preferred'] = $preferred;
                if (!isset($arr['iri'])) {
                    $arr['iri'] = $prefix2ns[$prefix] . $num;
                }
            }
        }

        $obj["existing_ids"][] = $arr;
    }

//        print_r($obj);
//        continue;

        if ($ok) {
            $return = ilxAdd($user,$api_key,$label,NULL,NULL);
            //print_r($return);
            $ilx = $return->data->fragment;
            $obj["ilx"] = $ilx;
            $obj['orig_cid'] = $cid;
            if ($obj['type'] == ""){
                $obj['type'] = 'term';
            }

            //add term
            $term = addTerm($user, $api_key, $cid, $obj, 1);
//            print_r($term);
            $ilxes[] = array('label'=>$term['label'], 'ilx'=>$term['ilx']);
        }
}
if (count($ilxes) > 0) {
    echo "The following terms were successfull uploaded. ILX identifiers:\n";// . implode(", ", $ilxes) . "\n\n";
    foreach ($ilxes as $ilx){
        //echo $_SERVER['SERVER_NAME'] . "/scicrunch/about/term/ilx/" . $ilx . "\n";
        echo '"' . $ilx['label'] . '": ' . $ilx['ilx'] . "\n";
    }
}

if (count($errors) > 0) {
    echo "\nThe following terms were not uploaded. Please fix the errors and upload again:\n";
    echo implode("\n", $errors);
}

exit;


?>
