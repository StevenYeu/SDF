<?php
// include '../../classes/classes.php';
// include './classes/connection.class.php';
// $db = new Connection();
// $db.connect();

function buildSupportingDocuments($pmidFiles, $doiFiles, $urlFiles, $pdfFiles, $imageFiles) {
  $supportingDocs = ['pmids' => [], 'dois' => [], 'pdfs' => [], 'images' => [], 'urls' => []];

  for ($i = 0; $i < count($pmidFiles); $i++) {
    $supportingDocs['pmids'][] = $pmidFiles[$i];
  }

  for ($i = 0; $i < count($doiFiles); $i++) {
    $supportingDocs['dois'][] = $doiFiles[$i];
  }

  for ($i = 0; $i < count($urlFiles); $i++) {
    $supportingDocs['urls'][] = $urlFiles[$i];
  }

  for ($i = 0; $i < count($pdfFiles); $i++) {
    $supportingDocs['pdfs'][] = $pdfFiles[$i];
  }

  for ($i = 0; $i < count($imageFiles); $i++) {
    $supportingDocs['images'][] = $imageFiles[$i];
  }

  return json_encode($supportingDocs);
}

// Record Type Mappings
$recordTypeMap = ['Validation' => 'validation',
                  'Discontinued' => 'discontinued',
                  'Issue' => 'other',
                  'Contaminated' => 'contaminated',
                  'Misidentified' => 'misidentified'];

// Staging table in Resource Watch DB
$table = 'stage_validation_issue_table';

// Form data
$rrid = $_POST['rrid'];
$vendor = ($_POST['vendor'] == 'N/A') ? NULL : $_POST['vendor'];
$catalogNumber = ($_POST['catalogNumber'] == 'N/A') ? NULL : $_POST['catalogNumber'];
$submissionType = $_POST['submissionType'];
$description = $_POST['description'];
$pmidFiles = isset($_POST['pmidSupportingDocs']) ? $_POST['pmidSupportingDocs'] : [];
$doiFiles = isset($_POST['doiSupportingDocs']) ? $_POST['doiSupportingDocs'] : [];
$pdfFiles = isset($_POST['pdfSupportingDocs']) ? $_POST['pdfSupportingDocs'] : [];
$imageFiles = isset($_POST['imageSupportingDocs']) ? $_POST['imageSupportingDocs'] : [];
$urlFiles = isset($_POST['urlSupportingDocs']) ? $_POST['urlSupportingDocs'] : [];

// Resource Watch DB Fields
$date = new DateTime();
$version = 1;
$recordType = $recordTypeMap[$_POST['submissionType']];
$insertTimeStamp = $date->format('Y-m-d H:i:s:ms');
$originalMessage = filter_var($description, FILTER_SANITIZE_STRING);
$originalContent = filter_var($description, FILTER_SANITIZE_STRING);
$scope = 'RRID';
$process = 'UISubmission';
$user = $_POST['userID'];
$modifiedTimeStamp = $insertTimeStamp;
$modifiedByUser = $user;
$curationStatus = 'submitted';
$displayMessage = filter_var($description, FILTER_SANITIZE_STRING);
$supportingDocumentation = buildSupportingDocuments($pmidFiles, $doiFiles, $urlFiles, $pdfFiles, $imageFiles);
$locatorID = $user . '_' . $insertTimeStamp;


// Insert form data into Resource Watch DB
$sql = "INSERT INTO $table (locatorID, version, rrid, vendor, catalogNumber, recordType, originalMessage, originalContent, scope, ";
$sql .= "insertTimeStamp, process, user, modifiedTimeStamp, modifiedByUser, curationStatus, displayMessage, supportingDocumentation) ";
$sql .= "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$CONFIG = include_once(__DIR__ . "/config.php");
$conn = new mysqli($CONFIG['host'], $CONFIG['user'], $CONFIG['password'], $CONFIG['ResourceWatchDB'], $CONFIG['port']);

if ($conn->connect_error) {
  exit('Could not connect to database');
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssssss", $locatorID, $version, $rrid, $vendor, $catalogNumber, $recordType, $originalMessage,
                                $originalContent, $scope, $insertTimeStamp, $process, $user,
                                $modifiedTimeStamp, $modifiedByUser, $curationStatus, $displayMessage,
                                $supportingDocumentation);

$stmt->execute();


// Create submission directory under user directory
$insert_id =  $stmt->insert_id;
$userDir = $_SERVER['DOCUMENT_ROOT'] . '/upload-rw/user-' . $_POST['userID'];
$submissionDir = $userDir . '/submission-' . $insert_id;

// Create submission directory
if (is_dir($userDir)) {
  if ((count($imageFiles) > 0) || (count($pdfFiles) > 0)) {
    if (!is_dir($submissionDir)) {
      mkdir($submissionDir, 0777, true);
    }
    // Move files to submission directory
    for ($i = 0; $i < count($pdfFiles); $i++) {
      $filePath = $userDir . '/' . $pdfFiles[$i];
      $fileDestintation = $submissionDir . '/' . $pdfFiles[$i];
      rename($filePath, $fileDestintation);
    }

    for ($i = 0; $i < count($imageFiles); $i++) {
      $filePath = $userDir . '/' . $imageFiles[$i];
      $fileDestintation = $submissionDir . '/' . $imageFiles[$i];
      rename($filePath, $fileDestintation);
    }
  }
}

header('Location: /ResourceWatch/Confirmation?rrid=' . $rrid . '&vendor=' . $vendor . '&catalogNumber-' . $catalogNumber);

?>
