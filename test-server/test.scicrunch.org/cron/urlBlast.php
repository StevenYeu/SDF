<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
include '../classes/classes.php';
$q = $_GET['q'];

$community = new Community();
$community->getByID(34);
$community->getCategories();

if($q)
    $params = '&q='.$q.'&count=1';
else
    $params = '&q=*&count=1';
foreach ($community->urlTree as $category => $arr) {
    if (count($arr['urls']) > 0) {
        foreach ($arr['urls'] as $url) {
            $multi[] = $url . $params;
        }
    }
    if (count($arr['subcategories']) > 0) {
        foreach ($arr['subcategories'] as $sub => $array) {
            foreach ($array['urls'] as $i => $url) {
                $multi[] = $url . $params;
            }
        }
    }
}

$results = Connection::multi($multi);
foreach ($results as $i => $file) {
    $xml = simplexml_load_string($file);
    if ($xml) {
        echo $multi[$i].' got '. (int)$xml->result['resultCount'].' results'."<br/>";
    } else {
        echo '<span style="color:red">'.$multi[$i].' failed</span>'."<br/>";
        echo $file ."<br/>";
    }
}
?>
