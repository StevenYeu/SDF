<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    $varsR['uid'] = 0;
} else {
    $varsR['uid'] = $_SESSION['user']->id;
}

$typeID = filter_var($_GET['typeID'], FILTER_SANITIZE_NUMBER_INT);
$type = filter_var($_GET['type'], FILTER_SANITIZE_STRING);
$cid = filter_var($_GET['cid'], FILTER_SANITIZE_STRING);

$community = new Community();
$community->getByID($cid);

foreach ($_POST as $key => $value) {
    //echo $key.':'.$value."\n";
    if ($key == 'g-recaptcha-response') {
        $capcha = $value;
    } elseif ($key == 'email') {
        $varsR['email'] = filter_var($value, FILTER_SANITIZE_EMAIL);
    } else {
        $splits = explode('-', $key);
        if (count($splits) > 1) {
            $vars[str_replace('_', ' ', $splits[0])][1] = filter_var($value, FILTER_SANITIZE_STRING);
        } else {
            if(is_array($value)) {
                $vars[str_replace('_', ' ', $key)][0] = implode(",", $value);
            } else {
                $vars[str_replace('_', ' ', $key)][0] = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }
    }
}

if (isset($capcha)) {
    $file = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . CAPTCHA_SECRET_KEY . '&response=' . $capcha . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
    $json = json_decode($file);
    if ($json->success == false) {
        echo('Please insert captcha!');
        if ($cid == 0)
            header('location:/create/resource?form=' . $type . '&section=1');
        else
            header('location:/' . $community->portalName . '/about/resource?form=' . $type . '&section=1');
        exit();
    }
}

$varsR['type'] = $type;
$varsR['typeID'] = $typeID;
$varsR['cid'] = $cid;

$holder = new Resource_Fields();
if($typeID!=0) {
    $fields = $holder->getPage2($cid, $typeID);

    foreach ($fields as $field) {
        $vars[$field->name] = array('', '');
    }
}

/* get funding types */
$std_fields = $holder->getPage1();
$funding_types = Array();
foreach($std_fields as $sf) {
    if($sf->type == "funding-types") {
        if(isset($vars[$sf->name])) {
            $this_funding_types = explode(",", $vars[$sf->name][0]);
            $funding_str = "";
            for($i = 0; $i < count($this_funding_types); $i++) {
                if($i % 2 == 0) {
                    $funding_str = $this_funding_types[$i] . "|||";
                } else {
                    $funding_str .= $this_funding_types[$i];
                    $funding_types[] = $funding_str;
                    $funding_str = "";
                }
            }
            unset($vars[$sf->name]);
        }
    }
}


if($_SESSION["user"]->role < 1) $vars = \helper\sanitizeHTMLString($vars);
$resource = new Resource();
$resource->create($varsR);
$resource->insertDB();

$vars['original_id'] = array($resource->original_id, NULL);
$vars['rid'] = array($resource->rid, NULL);
$resource->columns = $vars;
$resource->insertColumns2();

// resource image
if(isset($_FILES["resource-image"])){
    $resource->setImage($_FILES["resource-image"], $_SERVER, $_SESSION['user']);
}

// set community relationship if available
$community->addSubmittedBy($resource, $_SESSION["user"], NULL);

/* add funding types */
require_once __DIR__ . "/../../api-classes/add_delete_resource_relationship.php";
foreach($funding_types as $ft) {
    addDeleteResourceRelationship($_SESSION["user"], NULL, "add", $resource->rid, $resource->rid, $ft, "funding", "is funded by");
}

if (isset($_SESSION['user'])) {
    $notification = new Notification();
    $notification->create(array(
        'sender' => 0,
        'receiver' => $_SESSION['user']->id,
        'view' => 0,
        'cid' => $resource->cid,
        'timed' => 0,
        'start' => time(),
        'end' => time(),
        'type' => 'resource-submit',
        'content' => 'Successfully submitted resource: ' . $resource->rid
    ));
    $notification->insertDB();
    $_SESSION['user']->last_check = time();
}

if ($cid == 0)
    header('location:/create/resource?form=' . $type . '&rid=' . $resource->rid . '&section=2');
else
    header('location:/' . $community->portalName . '/about/resource?form=' . $type . '&rid=' . $resource->rid . '&section=2');

?>
