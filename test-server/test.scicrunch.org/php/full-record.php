<?php
include '../classes/classes.php';
$view = filter_var($_GET['view'],FILTER_SANITIZE_STRING);
$uuid = filter_var($_GET['uuid'],FILTER_SANITIZE_STRING);

$url_path = explode("/", parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH));
$portal_name = $url_path[1];
$community = new Community();
$community->getByPortalName($portal_name);
if(!$community->id && $community->id !== 0) $community->getByID(0);

$url = Connection::environment().'/v1/federation/data/'.$view.'.xml?q=*&filter=v_uuid:'.$uuid;
$xml = simplexml_load_file($url);
echo '<div class="close dark less-right">X</div>';
if($xml){?>
<table class="table-responsive" style="width:100%">
    <?php
    foreach($xml->result->results->row->data as $data){
        if($data->name == "v_uuid") continue;
        if($data->name == "Reference" || $data->name == "Mentioned In Literature" || $data->name == "Reference/Provider") $data->value = \helper\checkLongURL($data->value, $community, $view, $uuid, $data->name);
        if($data->name == "Comments" && strpos($data->value,"Problematic cell line") !== false) $data->value = "<span style='color:red'>" . $data->value . "</span>";
        echo '<tr><th style="background:#f7f7f7;padding:10px;border:1px solid #999">'.$data->name.'</th><td style="padding:10px;border:1px solid #999">'.$data->value.'</td></tr>';
    }
    ?>
</table>
<?php }
else{
    echo '<h2>No available data</h2>';
}

?>
