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
    $response['Access Error'] =  'Parameter "host" is required. Please specify a Data Service host name. Example call:
        http://' . $_SERVER['HTTP_HOST'] . '/uptime/test-data-services.php?host=nif-apps1.crbs.ucsd.edu';

    echo json_encode($response);
    exit();
}

//print_r(array_values($CONFIG['DATA_HOSTS']));
/*
if ( !in_array($host, $CONFIG['DATA_HOSTS']) ) {
    $response['Access Error'] =  $host . " is not a Data Services host.";

    echo json_encode($response);
    exit;
}
*/

$debug = false;
if ($_REQUEST['debug'] == 1) {
    $debug = true;
}

$queries = array(
    "nif-0000-00142-1?q=cerebellum",
    "nif-0000-00142-1?q=purkinje",
    "nif-0000-00142-1?q=projection",
    "nif-0000-00142-1?q=human",
    "nif-0000-00142-1?q=expression",
    "nif-0000-00142-1?q=mouse",
    "nif-0000-00142-1?q=nos",
    "nif-0000-00142-1?q=gene",
    "nif-0000-00142-1?q=age",
    "nif-0000-00142-1?q=bodies",
    "nif-0000-00142-1?q=aging",
    "nif-0000-00142-1?q=number",
    "nif-0000-00142-1?q=protein",
    "nif-0000-00142-1?q=blood",
    "nif-0000-00142-1?q=rat",
    "nif-0000-00142-1?q=cerebral%20cortex",
    "nif-0000-00142-1?q=fetal%20alcohol%20syndrome",
    "nif-0000-00142-1?q=alzheimer%27s%20disease",
    "nif-0000-00142-1?q=hippocampus",
    "nif-0000-00142-1?q=intracellular",
    "nif-0000-00142-1?q=ph",

    "nif-0000-37639-1?q=cerebellum",
    "nif-0000-37639-1?q=purkinje",
    "nif-0000-37639-1?q=projection",
    "nif-0000-37639-1?q=human",
    "nif-0000-37639-1?q=expression",
    "nif-0000-37639-1?q=mouse",
    "nif-0000-37639-1?q=nos",
    "nif-0000-37639-1?q=gene",
    "nif-0000-37639-1?q=age",
    "nif-0000-37639-1?q=bodies",
    "nif-0000-37639-1?q=aging",
    "nif-0000-37639-1?q=number",
    "nif-0000-37639-1?q=protein",
    "nif-0000-37639-1?q=blood",
    "nif-0000-37639-1?q=rat",
    "nif-0000-37639-1?q=cerebral%20cortex",
    "nif-0000-37639-1?q=fetal%20alcohol%20syndrome",
    "nif-0000-37639-1?q=alzheimer%27s%20disease",
    "nif-0000-37639-1?q=hippocampus",
    "nif-0000-37639-1?q=intracellular",
    "nif-0000-37639-1?q=ph",
);

$testQueries = array(
    "search?q=this%20:%20is%20nasty",
    "search?q=nasty%20case%20%22%20quote",
    "search?q=%22nasty%20case%20&%20ampresand%22",
    "search?q=muscle",
    "search?q=%22muscle%22"
);

$randKey = array_rand($queries);
$query = $queries[$randKey];
if ( $debug ) {
    $randKey = array_rand($testQueries);
    $query = $testQueries[$randKey];
}
$qparts = explode("?q=", $query);
$dataSource = $qparts[0];
$searchTerm = $qparts[1];

$service = "/v1/federation/data/";
$url = 'http://' . $host . $service . $query;

$errors = array();
$warnings = array();
$status = 'pass';
$count = 0;
$error = '';

$datetime = $tester->getDateTime();
$tester->callService($url, $TIMEOUT);
$code = $tester->getHttpCode();

/*
 * 1. find out if timeout
 * 2. if return code is 500
 * 3. if return code is 404
 * 4. if return code is 200
 */

if ( $tester->getTotalTime() >= 5 ) {
    $errors[] = "ERROR: timeout; > 5 sec";
    $status = 'fail';
} else {
    if ($tester->getTotalTime() > 0.5) {
        $warnings[] = 'WARNING: response time; > 0.5 sec';
    }

    if ( $code == 500 ) {
        $status = 'fail';
        $error = substr($tester->getContentBody(), 0, 1000);
        $matches = array();
        preg_match("/<h1>(.+)?<\/h1>/", $error, $matches);
        $error = $matches[1];
        $errors[] = "ERROR: internal server error; $error";
    }
    elseif ( $code == 404 ) {
        $status = fail;
        $error = substr($tester->getContentBody(), 0, 255);
        $errors[] = "ERROR: internal server error; $error";
    }
    elseif ( $code == 200 ) {
        $xml=simplexml_load_string($tester->getContentBody());
        $temp = $xml->attributes();
        $count = (int) $temp[1];
    }
    else {
        $errors[] = "ERROR: $tester->getContentHeader(); $tester->getContentBody()";
    }
}

$error = implode(", ", $errors);
$warning = implode(", ", $warnings);

$response['timestamp'] = $tester->getTimestamp();
$response['testRun'] = 'Data Service';
$response['status'] = $status;
$response['httpStatusCode'] = $code;
$response['targetURL'] = $url;
$response['host'] = $host;
$response['query'] = $query;
$response['dataSource'] = $dataSource;
$response['searchTerm'] = $searchTerm;
$response['date'] = $datetime[0];
$response['time'] = $datetime[1];
$response['resultCount'] = $count;
$response['responseTime'] = $tester->getTotalTime();
$response['httpReferer'] = $_SERVER['HTTP_REFERER'];
$response['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
$response['warnings'] = $warning;
$response['errors'] = $error;

header('Content-Type: application/json');
echo json_encode($response);

/*
 * failing queries
 * http://nif-services.neuinfo.org/servicesv1/v1/summary?q=muscle
 * http://nif-services.neuinfo.org/servicesv1/v1/query?q=muscle
 *
 * this fails because there is a free double quote "
 * http://nif-services.neuinfo.org/servicesv1/v1/summary?q=a%20bad%20thing%22%20is%20
 *
 * http://nif-services.neuinfo.org/servicesv1/v1/query?q=a%20bad%20thing%22%20is%20 this fails
 *
 * http://nif-services.neuinfo.org/servicesv1/v1/summary?q=%22oops%20&%20no%20apresands%20please%22 fails while
 * http://nif-services.neuinfo.org/servicesv1/v1/query?q=%22oops%20&%20no%20apresands%20please%22 works due to ampresand in quotes "&"
 *
 * http://nif-services.neuinfo.org/servicesv1/v1/summary?q=oops%20:%20no%20apresands%20please fails while
 * http://nif-services.neuinfo.org/servicesv1/v1/query?q=oops%20:%20no%20apresands%20please works because there is a bare colon :
 *
 *
 * here are some examples for the federation search
 *
 * http://nif-services.neuinfo.org/servicesv1/v1/federation/search?q=this%20:%20is%20nasty
 * http://nif-services.neuinfo.org/servicesv1/v1/federation/search?q=nasty%20case%20%22%20quote
 * http://nif-services.neuinfo.org/servicesv1/v1/federation/search?q=%22nasty%20case%20&%20ampresand%22
 *
 * here is a case where we have a massive expansion that returns no results
 * http://nif-services.neuinfo.org/servicesv1/v1/federation/search?q=muscle
 *
 * this works
 * http://nif-services.neuinfo.org/servicesv1/v1/federation/search?q=%22muscle%22
 */
?>
