<?php
header('Content-Type: application/json');

$docroot = "..";
$CONFIG = include_once($docroot . "/uptime/config.php");
include_once($docroot . "/classes/uptime.class.php");

$tester = new Uptime();
$datetime = $tester->getDateTime();
$TIMEOUT = $CONFIG['TIMEOUT'];
$response = array();

$host = trim($_REQUEST['host']);
if (!isset($host)) {
    $response['Access Error'] = 'Parameter "host" is required. Please specify a MySQL host name. Example call:
        http://' . $_SERVER['HTTP_HOST'] . '/uptime/test-mysql.php?host=dev-db.crbs.ucsd.edu';

    echo json_encode($response);
    exit();
}
if ( !in_array($host, $CONFIG['MS_HOSTS']) ) {
    $response['Access Error'] = $host . " is not a MySQL host.";

    echo json_encode($response);
    exit;
}

$debug = false;
if ($_REQUEST['debug'] == 1) {
    $debug = true;
}

$errors = array();
$warnings = array();
$status = 'pass';
$count = 0;
$error = '';

$start = microtime(true);
$mysqli = new mysqli($host, $CONFIG['MS_CONFIG']['USER'], $CONFIG['MS_CONFIG'][$host], $CONFIG['MS_CONFIG']['DB']);
$url = $host . "/" . $CONFIG['MS_CONFIG']['DB'];

if (mysqli_connect_errno()) {
    $status = 'fail';
    $errors[] = "ERROR: db connect failed; " . mysqli_connect_error();
}

mysqli_options($mysqli,MYSQLI_OPT_CONNECT_TIMEOUT, $TIMEOUT);

$query = "SELECT count(*) AS count FROM communities";
$dataSource = "Communities";
$searchTerm = "count(*)";
$result = $mysqli->query($query);
if(!$result){
    $status = 'fail';
    $errors[] = "ERROR: no result set; " . $mysqli->error;
}

if($result->num_rows > 0) {
    while($row = $result->fetch_object()) {
        $count = $row->count;
    }
}
else {
    $warnings[] = 'WARNING: query result; 0 rows';
}
$end = microtime(true);
$responseTime = $end - $start;

//$result->free();

$hangingProcesses = 0;
$query2 = 'SELECT count(*) AS count FROM information_schema.processlist WHERE command="Query" AND time > 3600';
$result = $mysqli->query($query2);
if($result->num_rows > 0) {
    while($row = $result->fetch_object()) {
        $hangingProcesses = $row->count;
    }
}
//$result->free();

$mysqli->close();

if ( $responseTime >= 5 ) {
    $status = 'fail';
    $errors[] = "ERROR: timeout; > 5 sec";
} elseif ($responseTime > 0.5) {
    $warnings[] = 'WARNING: response time; > 0.5 sec';
}

$error = implode(", ", $errors);
$warning = implode(", ", $warnings);

$response['timestamp'] = $tester->getTimestamp();;
$response['testRun'] = 'MySQL';
$response['status'] = $status;
$response['httpStatusCode'] = 0;
$response['targetURL'] = $url;
$response['host'] = $host;
$response['query'] = $query;
$response['dataSource'] = $dataSource;
$response['searchTerm'] = $searchTerm;
$response['date'] = $datetime[0];
$response['time'] = $datetime[1];
$response['resultCount'] = $count;
$response['responseTime'] = $responseTime;
$response['hangingProcesses'] = $hangingProcesses;
$response['httpReferer'] = $_SERVER['HTTP_REFERER'];
$response['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
$response['warnings'] = $warning;
$response['errors'] = $error;
// $response['failureMode'] = '';

echo json_encode($response);

?>
