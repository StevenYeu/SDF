<?php
header('Content-Type: application/json');

$docroot = "..";
$CONFIG = include_once($docroot . "/uptime/config.php");
include_once($docroot . "/classes/uptime.class.php");

$tester = new Uptime();
$TIMEOUT = $CONFIG['TIMEOUT'];
$response = array();

$host = trim($_REQUEST['host']);
if (!isset($host)) {
    $response['Access Error'] = 'Parameter "host" is required. Please specify a Data Service host name. Example call:
        http://' . $_SERVER['HTTP_HOST'] . '/uptime/test-ontology-services.php?host=matrix.neuinfo.org';

    echo json_encode($response);
    exit();
}
if ( !in_array($host, $CONFIG['ONTO_HOSTS']) ) {
    $response['Access Error'] = $host . " is not an Ontology Services host.";

    echo json_encode($response);
    exit;
}

$debug = false;
if ($_REQUEST['debug'] == 1) {
    $debug = true;
}

$queries = array(
    "vocabulary/term/brain",
    "vocabulary/term/human",
    "vocabulary/term/projection",
    "vocabulary/term/sequence",
    "vocabulary/term/nos",
    "vocabulary/term/quantitative",
    "vocabulary/term/gene",
    "vocabulary/term/age",
    "vocabulary/term/bodies",
    "vocabulary/term/mouse",
    "vocabulary/term/aging",
    "vocabulary/term/number",
    "vocabulary/term/protein",
    "vocabulary/term/blood",
    "vocabulary/term/rat",
    "vocabulary/term/cerebral%20cortex",
    "vocabulary/term/alzheimer's%20disease",
    "vocabulary/term/fetal%20alcohol%20syndrome",
    "vocabulary/term/hippocampus",
    "vocabulary/term/intracellular",
    "vocabulary/term/ph",

    "vocabulary/autocomplete/brai",
    "vocabulary/autocomplete/hum",
    "vocabulary/autocomplete/projecti",
    "vocabulary/autocomplete/seq",
    "vocabulary/autocomplete/no",
    "vocabulary/autocomplete/quanti",
    "vocabulary/autocomplete/ge",
    "vocabulary/autocomplete/ag",
    "vocabulary/autocomplete/bod",
    "vocabulary/autocomplete/mou",
    "vocabulary/autocomplete/agi",
    "vocabulary/autocomplete/num",
    "vocabulary/autocomplete/prot",
    "vocabulary/autocomplete/blo",
    "vocabulary/autocomplete/ra",
    "vocabulary/autocomplete/cerebral",
    "vocabulary/autocomplete/alzheime",
    "vocabulary/autocomplete/fetal",
    "vocabulary/autocomplete/hippoc",
    "vocabulary/autocomplete/intra",
    "vocabulary/autocomplete/p",

    "vocabulary/id/COGAT:CAO_00589",
    "vocabulary/id/NIFEXT:5890",
    "vocabulary/id/PR:000003537",
    "vocabulary/id/UBERON:0004069",
    "vocabulary/id/SAO:925531236",
    "vocabulary/id/BIRNLEX:3075_2",
    "vocabulary/id/DOID:0060145",
    "vocabulary/id/NLXCHEM:20090603",
    "vocabulary/id/GO:0006915",
    "vocabulary/id/BIRNLEX:464",
    "vocabulary/id/BIRNLEX:12582",
    "vocabulary/id/NIFEXT:3118",
    "vocabulary/id/UBERON:0001723",
    "vocabulary/id/BIRNLEX:1055",
    "vocabulary/id/BIRNLEX:11003",

);

$testQueries = array(
    "vocabulary/term/control",
    "vocabulary/term/activity",
    "vocabulary/term/expression",

    "vocabulary/id/control",
    "vocabulary/id/activity",
    "vocabulary/id/expression",

    "vocabulary/autocomplete/fariba", // returns empty
    "vocabulary/autocomplete/fana",    // returns empty

    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf", // slow
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",
    "graph/neighbors/BFO1:%23Entity?depth=7&blankNodes=false&direction=BOTH&project=*&relationshipType=subClassOf",

    // some good ones
    "vocabulary/id/NIFGA:birnlex_1055",
    "vocabulary/id/BIRNOBI:birnlex_11003",
    "vocabulary/autocomplete/agi",
    "vocabulary/autocomplete/num",
    "vocabulary/term/alzheimer's%20disease",
    "vocabulary/term/fetal%20alcohol%20syndrome",
);

$randKey = array_rand($queries);
$query = $queries[$randKey];
if ( $debug ) {
    $randKey = array_rand($testQueries);
    $query = $testQueries[$randKey];
}
$qparts = explode("/", $query);
end($qparts);         // move the internal pointer to the end of the array
$key = key($qparts);  // fetches the key of the element pointed to by the internal pointer
$searchTerm = $qparts[$key];

$port = 9000;
$service = "/scigraph/";
$url = 'http://' . $host . ':' . $port . $service . $query;

$errors = array();
$warnings = array();
$status = 'fail';
$count = 0;
$error = '';

$datetime = $tester->getDateTime();
$tester->callService($url, $TIMEOUT);
$code = $tester->getHttpCode();

if ( $tester->getTotalTime() >= 5 ) {
    $errors[] = "ERROR: timeout; > 5 sec";
    $code = 0;
} else {
    if ($tester->getTotalTime() > 0.5) {
        $warnings[] = 'WARNING: response time; > 0.5 sec';
    }

    if ( $code == 404 ) {
        $matches = array();
        preg_match("/<p>(.+)?<\/p>/s", $tester->getContentBody(), $matches);
        $error = $matches[1];
        preg_replace("/\<.*\>?/sg", "//", $error);
        $errors[] = "ERROR: not found; $error";
    }
    elseif ( $code == 500 ) {
        $errors[] = "ERROR: internal server error; $tester->getContentBody()";
    }
    elseif ( $code == 200 ) {
        $status = 'pass';
        $content = json_decode($tester->getContentBody());
        $count = sizeof($content);
    }
    else {
        $errors[] = "ERROR: $tester->getContentHeader(); $tester->getContentBody()";
    }
}

$error = implode(", ", $errors);
$warning = implode(", ", $warnings);

$response['timestamp'] = $tester->getTimestamp();
$response['testRun'] = 'Ontology Service';
$response['status'] = $status;
$response['httpStatusCode'] = $code;
$response['targetURL'] = $url;
$response['host'] = $host;
$response['query'] = $query;
$response['dataSource'] = '';
$response['searchTerm'] = $searchTerm;
$response['date'] = $datetime[0];
$response['time'] = $datetime[1];
$response['resultCount'] = $count;
$response['responseTime'] = $tester->getTotalTime();
$response['httpReferer'] = $_SERVER['HTTP_REFERER'];
$response['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
$response['warnings'] = $warning;
$response['errors'] = $error;

echo json_encode($response);

?>
