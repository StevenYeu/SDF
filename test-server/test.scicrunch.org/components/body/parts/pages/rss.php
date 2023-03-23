<?php

function htmlent($text) {
    return htmlentities($text, ENT_HTML401);
}

header('Content-Type: application/xml; charset=ISO-8859-1');

$holder = new Component_Data();
if($thisComp->icon1 == 'event1'){
    $datas = $holder->orderTime($thisComp->component,$community->id);
} else {
    $datas = $holder->getByComponentNewest($thisComp->component, $community->id, 0, 100);
}

$url = Community::fullURLStatic($community).'/about/'.$thisComp->text2;

$file = '<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">';
$file .= '<channel><title>'.$community->name.' '.htmlent($thisComp->text1).'</title>';
$file .= '<link>'.$url.'</link>';
$file .= '<description>'.str_replace('&nbsp;',' ',htmlent($thisComp->text3)).'</description>';
foreach($datas as $data){
    $file .= '<item>';
    $file .= '<title>'.htmlent($data->title).'</title>';
    if($data->link){
        $file .= '<link>'.htmlent($data->link).'</link>';
        $file .= '<guid>'.htmlent($data->link).'</guid>';
    } else {
        $file .= "<link>".$url."/".$data->id."</link>";
        $file .= "<guid>".$url."/".$data->id."</guid>";
    }
    $extras = '';

    $splits = explode(':',$data->content);

    if($data->content && count($splits)==2 && strlen($data->content)<100) {
        $extras .= ' '.str_replace('&nbsp;',' ',$data->content).'.';
    }
    if($data->icon) {
        $extras .= ' '.str_replace('&nbsp;',' ',$data->icon).'.';
    }
    if($data->color) {
        $extras .= ' '.str_replace('&nbsp;',' ',$data->color).'.';
    }

    $file .= '<description>'.str_replace('&nbsp;',' ',htmlent($data->description)).htmlent($extras).'</description>';

    $file .= "<pubDate>".date("r", $data->time)."</pubDate>";

    $file .= '</item>';
}
$file .= '</channel></rss>';

$xml = simplexml_load_string(\helper\cleanXML($file));
echo $xml->asXML();

?>
