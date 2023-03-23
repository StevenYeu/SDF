<?php
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$file = "./not-fetched.txt";
$content = file_get_contents($file);

$lines = explode("\n", $content);
foreach ($lines as $line){
   preg_match("/id:\"(.+)\"/", $line, $matches);
   //print $matches[1] . "\n";

   $update = "update _term_upload_list set no_json = '1', exclude = '1' where id = '" . $matches[1] . "'";
   //print $update . "\n";
   $mysqli->query($update);
}

$file = "./skipped.txt";
$content = file_get_contents($file);

$lines = explode("\n", $content);
foreach ($lines as $line){
   preg_match("/\(Id:(.+)\)/", $line, $matches);
   //print $matches[1] . "\n";
   if (strlen($matches[1]) < 2) { continue; }

   $update = "update _term_upload_list set tbd = '1', exclude = '1' where id = '" . $matches[1] . "'";
   //print $update . "\n";
   $mysqli->query($update);
}


$mysqli->close();
?>
