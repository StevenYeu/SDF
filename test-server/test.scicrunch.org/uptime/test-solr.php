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
    $response['Access Error'] = 'Parameter "host" is required. Please specify a Solr host name. Example call:
        http://' . $_SERVER['HTTP_HOST'] . '/uptime/test-solr.php?host=tatoo2.crbs.ucsd.edu';

    echo json_encode($response);
    exit();
}

$hosts = array_merge($CONFIG['SOLR_HOSTS'], $CONFIG['SOLR_LIT_HOSTS']);
if ( !in_array($host, $hosts)  ) {
    $response['Access Error'] = $host . " is not a Solr Services host.";

    echo json_encode($response);
    exit;
}

$debug = false;
if ($_REQUEST['debug'] == 1) {
    $debug = true;
}

$queries = array(
    "nlx_154697-1/select?q=multi_species%3Amus%20musculus&wt=json&indent=true",
    "nif-0000-00001-1/select?q=neuron_class%3Apyramidal&wt=json&indent=true",
    "nif-0000-00001-1/select?q=subject%3Avertebrate&wt=json&indent=true",
    "nif-0000-00006-1/select?q=species%3Amonkey&wt=json&indent=true",
    "nif-0000-00004-1/select?q=name%3Aneuron&wt=json&indent=true",
    "nif-0000-00007-1/select?q=species%3Amouse&wt=json&indent=true",
    "nif-0000-00007-1/select?q=multi_anatomiclocation%3Ahippocampus&wt=json&indent=true",
    "nif-0000-00007-1/select?q=multi_structure%3Amitochondrion&wt=json&indent=true",
    "SCR_013729-1/select?q=title%3Anuclear%20receptor&wt=json&indent=true",
    "SCR_013729-1/select?q=description%3Apilot%20receptor&wt=json&indent=true",
    "SCR_001944-1/select?q=tier%3ASOP&wt=json&indent=true",
    "SCR_001944-1/select?q=source%3AUK&wt=json&indent=true",
);

$testQueries = array(
    //"nif-0000-00006-1/select?q=species%3Amito&wt=json&indent=true", // 0
    "nlx_154697-1/select?q=*%3Amouse&wt=json&indent=true", // 400
    "SCR_001944-2/select?q=tier%3ASOP&wt=json&indent=true", // 404
    "SCR_001944-1/select?q=sourc%3AUK&wt=json&indent=true",
    "nif-0000-00007-2/select?q=organ%3Abrian&wt=json&indent=true",
    "nif-0000-00006-1/select?q=title%3Anuclear%20receptor&wt=json&indent=true",
    "SCR_001944-2/select?q=tier%3ASOP&wt=json&indent=true", //breaks solr 404
    "SCR_001944-2/select?q=isoform%3Aalpha&wt=json&indent=true", //breaks solr 404
);

$service = "/dbfederation/";
$port = 8080;

if ( in_array($host, $CONFIG['SOLR_LIT_HOSTS']) ) {
    $queries = array(
        "collection1/select?q=title:mouse&wt=json&indent=true",
        "collection1/select?q=title:human&wt=json&indent=true",
        "collection1/select?q=title:brain&wt=json&indent=true",
        "collection1/select?q=year:2010&wt=json&indent=true",
        "collection1/select?q=journal:PloS%20one&wt=json&indent=true",
        "collection1/select?q=author:habu&wt=json&indent=true",
    );

    $testQueries = array(
        "collection1/select?q=species%3Amouse&wt=json&indent=true", // 400
        "collection1/select?q=organ%3Abrian&wt=json&indent=true",
        "collection/select?q=anatomiclocation%3Ahippocampus&wt=json&indent=true", //404
    );

    $service = "/literature/";
}

$randKey = array_rand($queries);
$query = $queries[$randKey];
if ( $debug ) {
    $randKey = array_rand($testQueries);
    $query = $testQueries[$randKey];
}
$qparts = explode("/", $query);

$url = 'http://' . $host . ":" . $port . $service . $query;

$errors = array();
$warnings = array();
$status = 'fail';
$count = 0;
$error = '';

$datetime = $tester->getDateTime();
$tester->callService($url, $TIMEOUT);
$code = $tester->getHttpCode();
$content = json_decode($tester->getContentBody());

/*
 * - timeout
 * - return code 0
 * - return code 400
 * - return code 404
 * - return code 200
 */
if ( $tester->getTotalTime() >= 5 ) {
    $errors[] = "ERROR: timeout; > 5 sec";
} else {
    if ($tester->getTotalTime() > 0.5) {
        $warnings[] = 'WARNING: response time; > 0.5 sec';
    }

    if ( $code == 0 ) {
        $q = $content->responseHeader->params->q;
        $found = $content->response->numFound;
        $errors[] = "ERROR: $q; $found found";
    }
    elseif ( $code == 400 ) {
        $q = $content->responseHeader->params->q;
        $error = $content->error->msg;
        $errors[] = "ERROR: $q; $error";
    }
    elseif ( $code == 404 ) {
        $matches = array();
        preg_match("/^.+<b>message<\/b>.*<u>(.+)?<\/u>.+<b>description<\/b>.+<u>(.+)?<\/u>/s", $tester->getContentBody(), $matches);
        $msg = $matches[1];
        $error = $matches[2];
        $errors[] = "ERROR: $msg; $error";
    }
    elseif ( $code == 200 ) {
        $status = 'pass';
        $count = $content->response->numFound;
    }
    else {
        $errors[] = "ERROR: $tester->getContentHeader(); $tester->getContentBody()";
    }
}

$error = implode(", ", $errors);
$warning = implode(", ", $warnings);

$response['timestamp'] = $tester->getTimestamp();;
$response['testRun'] = 'Solr';
$response['status'] = $status;
$response['httpStatusCode'] = $code;
$response['targetURL'] = $url;
$response['host'] = $host;
$response['query'] = $query;
$response['dataSource'] = $qparts[0];
$response['searchTerm'] = $qparts[1];
$response['date'] = $datetime[0];
$response['time'] = $datetime[1];
$response['resultCount'] = $count;
$response['responseTime'] = $tester->getTotalTime();
$response['httpReferer'] = $_SERVER['HTTP_REFERER'];
$response['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
$response['warnings'] = $warning;
$response['errors'] = $error;

echo json_encode($response);

/*
For Solr we can use the following REST query:
http://tatoo1.crbs.ucsd.edu:8080/dbfederation/nlx_154697-1/select?q=species%3Amouse&wt=json&indent=true

This would take a similar list as with the query services - however here (due to Solr's query syntax)
we will need to specify a field along with the query parameter. This can be used on production (tattoo1)
and stage (tattoo2) as well.

This query is one of the ones that will not work outside of UCSD.

I don't want to do something that will cause Solr issues... However, a bad query gives a 400:
http://tatoo1.crbs.ucsd.edu:8080/dbfederation/nlx_154697-1/select?q=*%3Amouse&wt=json&indent=true

 */
?>
