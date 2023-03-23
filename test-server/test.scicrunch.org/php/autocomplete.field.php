<?php
include '../classes/classes.php';
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$query = rawurldecode($_GET['term']);
$display = rawurldecode($_GET['display']);
$category = rawurldecode($_GET['category']);

$splits = explode('"', $query);
$ids = explode('"', $display);
if (count($splits) > 1)
    $text = true;
else
    $text = false;



if ($category && $category != 'all') {
    $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits[count($splits) - 1]) . '.json?category=' . rawurlencode($category));
} else {
    $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits[count($splits) - 1]) . '.json');
}

$json = json_decode($file);

foreach ($json as $t) {
    $splits[count($splits) - 1] = (string)$t->completion;
    $ids[count($splits) - 1] = \helper\getIRIFragment((string)$t->concept->curie);
    $autocomplete[] = array((string)$t->completion, (string)$t->concept->categories[0], (string)$t->concept->curie, '', (string)$t->type, '0', join('"', $splits), $text, join('"', $ids), (string)$t->type);
//    $autocomplete[] = array("1111");
}


if (count($autocomplete) == 0) {

    $splits = explode(' ', $query);
    $ids = explode(' ', $display);
    $splits2 = explode('+', $splits[count($splits) - 1]);
    if ($category && $category != 'all') {
        if (count($splits2) > 1)
            $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[1]) . '?category=' . rawurlencode($category));
        else
            $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[0]) . '?category=' . rawurlencode($category));
    } else {
        //echo 'http://matrix.neuinfo.org:9000/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[0]);

        if (count($splits2) > 1)
            $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[1]) . '.json');
        else
            $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[0]) . '.json');
    }
    $json = json_decode($file);
    foreach ($json as $t) {
        if (count($splits2) > 1) {
            $splits[count($splits) - 1] = '+' . (string)$t->completion;
            $ids[count($splits) - 1] = '+' . \helper\getIRIFragment((string)$t->concept->curie);
        } else {
            $splits[count($splits) - 1] = (string)$t->completion;
            $ids[count($splits) - 1] = \helper\getIRIFragment((string)$t->concept->curie);
        }
        $autocomplete[] = array((string)$t->completion, (string)$t->concept->categories[0], (string)$t->concept->curie, '', (string)$t->type, '0', join(' ', $splits), $text, join(' ', $ids), (string)$t->type);
    }
}
$frmt_autocomplete = Array();
foreach($autocomplete as $ac){
    $frmt_autocomplete [] = $ac[0];
}

// Manu
if ($category == "resource") {
        $frmt_autocomplete1 = Array();
	$file = file_get_contents("licenses.json");
	$json = json_decode($file);
	foreach ($json->licenses as $t) {
	    $frmt_autocomplete1 [] = (string)$t->licenseId;
	}
	echo json_encode($frmt_autocomplete1);
} else {
   echo json_encode($frmt_autocomplete);
}


?>
