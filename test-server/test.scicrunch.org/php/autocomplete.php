<?php
include '../classes/classes.php';
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$query = rawurldecode($_GET['term']);
$display = rawurldecode($_GET['display']);
$splits = explode('"', $query);
$ids = explode('"', $display);
if(count($splits)>1)
    $text = true;
else
    $text = false;
$used_query = "";


$spliter = split(':', $splits[count($splits) - 1]);
if (count($spliter) > 1) {
    $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($spliter[1]) . '.json?category=' . strtolower($spliter[0]));
    $used_query = $spliter[1];
} else {
    $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits[count($splits) - 1]).'.json');
    $used_query = $splits[count($splits) - 1];
}

$json = json_decode($file);
//print_r($json);

foreach ($json as $t) {
    if (count($spliter) > 1) {
        $splits[count($splits) - 1] = $spliter[0] . ':' . (string) $t->completion;
        $ids[count($splits) - 1] = $spliter[0].':'.\helper\getIRIFragment((string)$t->concept->iri);
        $autocomplete[] = array((string) $t->completion, (string) $t->concept->categories[0], (string) $t->concept->curie, $spliter[0], (string) $t->type, '1', join('"', $splits),$text,join('"',$ids),(string)$t->type, $used_query);
    } else {
        $splits[count($splits) - 1] = (string) $t->completion;
        $ids[count($splits) - 1] = \helper\getIRIFragment((string)$t->concept->iri);
        $autocomplete[] = array((string) $t->completion, (string) $t->concept->categories[0], (string) $t->concept->curie, '', (string) $t->type, '0', join('"', $splits),$text,join('"',$ids),(string)$t->type, $used_query);
    }
}
if (count($autocomplete) == 0) {

    $splits = explode(' ', $query);
    $ids = explode(' ', $display);
    $splits2 = explode('+', $splits[count($splits) - 1]);
    $spliter = split(':', $splits2[count($splits2) - 1]);
    if (count($spliter) > 1) {
        $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($spliter[1]) . '?category=' . strtolower($spliter[0]));
        $used_query = $spliter[1];
    } elseif(count($splits2) > 1) {
        $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[1]).'.json');
        $used_query = $splits2[1];
    } else {
        $file = file_get_contents(APIURL.'/scigraph/vocabulary/autocomplete/' . rawurlencode($splits2[0]).'.json');
        $used_query = $splits2[0];
    }
    $json = json_decode($file);
    foreach ($json as $t) {
        if (count($spliter) > 1) {
            if (count($splits2) > 1){
                $splits[count($splits) - 1] = '+' . $spliter[0] . ':' . (string) $t->completion;
                $ids[count($splits) - 1] = '+'.$spliter[0].':'.\helper\getIRIFragment((string)$t->concept->iri);
            } else {
                $splits[count($splits) - 1] = $spliter[0] . ':' .(string) $t->completion;
                $ids[count($splits) - 1] = $spliter[0].':'.\helper\getIRIFragment((string)$t->concept->iri);
            }
            $autocomplete[] = array((string) $t->completion, (string) $t->concept->categories[0], (string) $t->concept->curie, $spliter[0], (string) $t->type, '1',join(' ',$splits),$text,join(' ',$ids),(string)$t->type, $used_query);
        } else {
            if (count($splits2) > 1){
                $splits[count($splits) - 1] = '+' . (string) $t->completion;
                $ids[count($splits) - 1] = '+'.\helper\getIRIFragment((string)$t->concept->iri);
            } else {
                $splits[count($splits) - 1] = (string) $t->completion;
                $ids[count($splits) - 1] = \helper\getIRIFragment((string)$t->concept->iri);
            }
            $autocomplete[] = array((string) $t->completion, (string) $t->concept->categories[0], (string) $t->concept->curie, '', (string) $t->type, '0',join(' ',$splits),$text,join(' ',$ids),(string)$t->type, $used_query);
        }
    }
}
//echo $spliter[0];
echo json_encode($autocomplete);

?>
