<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

include '../../classes/classes.php';
\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    header('location:/');
    exit();
}

function validPortalName($portal_name){
    return Community::uniquePortalName($portal_name) && Community::validPortalName($portal_name);

}

$vars['uid'] = $_SESSION['user']->id;
$vars['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
$vars['description'] = filter_var($_POST['description'], FILTER_SANITIZE_STRING);
$vars['address'] = filter_var($_POST['address'], FILTER_SANITIZE_STRING);
$vars['shortName'] = filter_var($_POST['short'], FILTER_SANITIZE_STRING);
$vars['portalName'] = filter_var($_POST['short'], FILTER_SANITIZE_STRING);
$vars['url'] = filter_var($_POST['url'], FILTER_SANITIZE_URL);
$vars['private'] = 1;
$vars['access'] = filter_var($_POST['join'], FILTER_SANITIZE_STRING);
$vars['ga_code'] = filter_var($_POST['gacode'], FILTER_SANITIZE_STRING);
$vars['mailchimp_api_key'] = filter_var($_POST['mailchimp_api_key'], FILTER_SANITIZE_STRING);
$vars['mailchimp_default_list'] = filter_var($_POST['mailchimp_default_list'], FILTER_SANITIZE_STRING);
$vars['resourceView'] = 1;
$vars['dataView'] = 1;
$vars['literatureView'] = 1;
$vars['about_home_view'] = 0;
$vars['about_sources_view'] = 1;
$vars['verified'] = 0;
$vars['front_page_visible'] = 0;
$vars['search_name_comm_resources'] = "";
$vars['search_name_more_resources'] = "";
$vars['search_name_literature'] = "";

if(!validPortalName($vars['portalName'])){
    header('location:' . $_SERVER["HTTP_REFERER"]);
    exit;
}



$key='file';
if ($_FILES[$key] && $_FILES[$key]['error'] != 4) {
    $allowedExts = array("jpg", "jpeg", "gif", "png");
    $extension = end(explode(".", $_FILES[$key]["name"]));
    if (($_FILES[$key]["size"] < 5000000)&& in_array(strtolower($extension), $allowedExts)) {
        if ($_FILES[$key]["error"] > 0) {
            //header('location:http://scicrunch.com/finish?status=fileerror&type=community&title=' . $name . '&community=' . $portalName);
            exit();
        } else {
            $name = $vars['portalName'] . '_' . rand(0, 1000000) . '.png';
            file_put_contents('../../upload/community-logo/' . $name, file_get_contents($_FILES[$key]["tmp_name"]));
            @unlink($_FILES[$key]["tmp_name"]);
            $vars['logo'] = $name;
        }
    } else {
        //header('location:http://scicrunch.com/finish?status=filetype&type=community&title=' . $name . '&community=' . $portalName);
        exit();
    }
} else {
    function get_gravatar($email,$s = 300, $d = 'identicon', $r = 'g', $img = false, $atts = array()) {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }

    $name = $vars['portalName'] . '_' . rand(0, 1000000) . '.png';
    file_put_contents('../../upload/community-logo/' . $name, file_get_contents(get_gravatar($vars['portalName'].'@scicrunch.org',50,'identicon','g')));
    $vars['logo'] = $name;
}


$community = new Community();
if($_SESSION["user"]->role < 1) $vars = \helper\sanitizeHTMLString($vars);
$community->create($vars);

$community->insertDB();

if($community->id == 0) {
    header("location:" . $_SERVER["HTTP_REFERER"]);
    exit;
}

$community->join($_SESSION['user']->id, $_SESSION['user']->getFullName(), 4);
$_SESSION['user']->levels[$community->id] = 4;

$vars['uid'] = 0;
$vars['cid'] = $community->id;
$vars['disabled'] = 0;

$vars['component'] = 0;
$vars['position'] = 0;
$vars['image'] = null;
$vars['text1'] = null;
$vars['text2'] = null;
$vars['text3'] = null;
$vars['color1'] = null;
$vars['color2'] = null;
$vars['color3'] = null;
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;

$component = new Component();


//header
$vars['component'] = 0;
$vars['position'] = 0;
$vars['image'] = null;
$vars['text1'] = null;
$vars['text2'] = null;
$vars['text3'] = null;
$vars['color1'] = '72c02c';
$vars['color2'] = null;
$vars['color3'] = null;
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

//footer
$vars['component'] = 92;
$vars['position'] = 0;
$vars['image'] = null;
$vars['text1'] = null;
$vars['text2'] = null;
$vars['text3'] = null;
$vars['color1'] = '72c02c';
$vars['color2'] = '585f69';
$vars['color3'] = '3e4753';
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

//Breadcrumbs
$vars['component'] = 100;
$vars['position'] = 0;
$vars['image'] = null;
$vars['text1'] = null;
$vars['text2'] = null;
$vars['text3'] = null;
$vars['color1'] = 'ffffff';
$vars['color2'] = '72c02c';
$vars['color3'] = '585f69';
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

//Search Box
$vars['component'] = 101;
$vars['position'] = 0;
$vars['image'] = null;
$vars['text1'] = 'Search through ' . $community->shortName;
$vars['text2'] = 'Selecting a term from the dropdown will search for that entity exactly';
$vars['text3'] = null;
$vars['color1'] = '585f69';
$vars['color2'] = '72c02c';
$vars['color3'] = null;
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

$vars['component'] = 12;
$vars['position'] = 0;
$vars['image'] = null;
$vars['text1'] = 'Discover New Things';
$vars['text2'] = 'Search for data related to your query';
$vars['text3'] = null;
$vars['color1'] = 'ffffff';
$vars['color2'] = '72c02c';
$vars['color3'] = null;
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

//Goto Component
$vars['component'] = 21;
$vars['position'] = 1;
$vars['image'] = '/' . $community->portalName . '/register';
$vars['text1'] = 'Join our Community';
$vars['text2'] = 'Joining our community let\'s you submit resources to our registry and get updates from us.';
$vars['text3'] = 'Join Now';
$vars['color1'] = '72c02c';
$vars['color2'] = '5fb611';
$vars['color3'] = null;
$vars['icon1'] = 'fa fa-plus';
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

//Service Boxes
$vars['component'] = 31;
$vars['position'] = 2;
$vars['image'] = null;
$vars['text1'] = '<h2>Search our Categories</h2><p>Search through data in a way unique to ' . $community->name . ' and find what is relevant to you.</p>';
$vars['text2'] = '<h2>Share Resources</h2><p>Join the largest scientific resource registry and add, share, and search for new resources with your community.</p>';
$vars['text3'] = '<h2>Receive Updates</h2><p>Join our community to receive updates, learn about our organization, see our tutorials and ask us questions.</p>';
$vars['color1'] = null;
$vars['color2'] = null;
$vars['color3'] = null;
$vars['icon1'] = 'fa fa-search';
$vars['icon2'] = 'fa fa-globe';
$vars['icon3'] = 'fa fa-database';
$component->create($vars);
$component->insertDB();

//Counter
$vars['component'] = 11;
$vars['position'] = 3;
$vars['image'] = null;
$vars['text1'] = null;
$vars['text2'] = null;
$vars['text3'] = null;
$vars['color1'] = null;
$vars['color2'] = null;
$vars['color3'] = null;
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

//Page Box
$vars['component'] = 34;
$vars['position'] = 4;
$vars['image'] = null;
$vars['text1'] = 'About Us';
$vars['text2'] = $community->description;
$vars['text3'] = null;
$vars['color1'] = '72c02c';
$vars['color2'] = null;
$vars['color3'] = null;
$vars['icon1'] = null;
$vars['icon2'] = null;
$vars['icon3'] = null;
$component->create($vars);
$component->insertDB();

$category = new Category();
$category->create(array(
    'uid' => $_SESSION['user']->id,
    'cid' => $community->id,
    'position' => 0,
    'category' => 'Resources',
    'source' => 'nlx_144509-1'
));
$category->insertDB();

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'community-create',
    'content' => 'Successfully created the community: ' . $community->name
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

header('location:/' . $community->portalName . '/account/communities/' . $community->portalName . '?tutorial=true');

?>
