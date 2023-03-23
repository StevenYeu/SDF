<?php

$uploadOk = 1;
$uploaded = "False";
$fileSizeLimit = 50; // 50MB
$userID = $_POST['userID'];
$fileName = $_FILES['file']['name'];
$userDir = $_SERVER['DOCUMENT_ROOT'] . '/upload-rw/user-' . $userID;
$destination = $userDir . '/' . $fileName;
$fileType = split('/', $_FILES['file']['type'])[1];

// Check file size
if ((($_FILES["fileToUpload"]["size"] / 1024) / 1024) > $fileSizeLimit) {
  echo "File is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if ($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "pdf" ) {
  echo "Invalid File Type: " . $fileType;
  $uploadOk = 0;
}

if ($uploadOk) {

  // Create User directory
  if (!is_dir($userDir)) {
    mkdir($userDir, 0777, true);
  }

  // Uploads file
  if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
    http_response_code(200);
    echo "File: " . $fileName . " was uploaded";
  } else {
    http_response_code(409);
    echo "File: " . $fileName . " failed to upload";
  }

} else {
  http_response_code(409);
  echo "File: " . $fileName . " failed to upload";
}

?>
