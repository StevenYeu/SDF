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
    $response['Access Error'] = 'Parameter "host" is required. Please specify a Postgres host name. Example call:
        http://' . $_SERVER['HTTP_HOST'] . '/uptime/test-postgres.php?host=postgres-stage.neuinfo.org';

    echo json_encode($response);
    exit();
}
if ( !in_array($host, $CONFIG['PG_HOSTS']) ) {
    $response['Access Error'] = $host . " is not a PostgreSQL host.";

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
$connect_params = "host=".$host." dbname=".$CONFIG['PG_CONFIG']['DB']." user=".$CONFIG['PG_CONFIG']['USER']." password=".$CONFIG['PG_CONFIG']['PWD'];
$url = $host . ":" . $CONFIG['PG_CONFIG']['PORT'] . "/" . $CONFIG['PG_CONFIG']['DB'];

// Connecting, selecting database
$dbconn = pg_connect($connect_params . " connect_timeout=$TIMEOUT");
if (!$dbconn) {
    $status = 'fail';
    $errors[] = "ERROR: db connect failed; " . pg_last_error();
}

$query = "SELECT count(*) FROM ONTOLOGY";
$dataSource = "Ontology";
$searchTerm = "count(*)";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());
while ($row = pg_fetch_row($result)) {
  $count = $row[0];
}
$end = microtime(true);
$responseTime = $end - $start;
// Free resultset
pg_free_result($result);

// Closing connection
pg_close($dbconn);

if ( $responseTime >= 5 ) {
    $status = 'fail';
    $errors[] = "ERROR: timeout; > 5 sec";
} elseif ($responseTime > 0.5) {
    $warnings[] = 'WARNING: response time; > 0.5 sec';
}

$error = implode(", ", $errors);
$warning = implode(", ", $warnings);

$response['timestamp'] = $tester->getTimestamp();;
$response['testRun'] = 'PostgreSQL';
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
$response['httpReferer'] = $_SERVER['HTTP_REFERER'];
$response['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
$response['warnings'] = $warning;
$response['errors'] = $error;
// $response['failureMode'] = '';

echo json_encode($response);

?>
