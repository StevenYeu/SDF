<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api-classes/term/term_by_ilx.php";

session_start();
$ilx = $_GET["ilx"];
//echo $ilx;

$api_key = null;
$user = $_SESSION['user'];

$term = getTermByIlx($user, $api_key, $ilx, 1, 1);
$term = DbObj::termForExport($term);

function array2xml($array, $xml = false){

    if($xml === false){
        $xml = new SimpleXMLElement('<term/>');
    }

    foreach($array as $key => $value){
        if(is_array($value)){
            array2xml($value, $xml->addChild($key));
        } else {
            $xml->addChild($key, $value);
        }
    }

    return $xml;
}

$xml = array2xml($term, false);

// Create a new DOMDocument object
$doc = new DOMDocument('1.0');
// add spaces, new lines and make the XML more readable format
$doc->formatOutput = true;

// Get a DOMElement object from a SimpleXMLElement object
$domnode = dom_import_simplexml($xml);
$domnode->preserveWhiteSpace = false;
// Import node into current document
$domnode = $doc->importNode($domnode, true);
// Add new child at the end of the children
$domnode = $doc->appendChild($domnode);

// Dump the internal XML tree back into a string
$saveXml = $doc->saveXML();

echo("<pre>".htmlspecialchars($saveXml)."</pre>");
exit();
?>
