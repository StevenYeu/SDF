<?php

$tag = $data["tag"];
$page = $data["page"];
$thisComp = $data["component"];
$community = $data["community"];
$vars = $data["vars"];

ob_start();
$tag_filter = $tag;
$holder = new Component_Data();
$page = $page;
if ($thisComp->icon1 == 'event1') {
    $datas = $holder->orderTime($thisComp->component,$community->id);
} elseif (($thisComp->icon1 == 'series1') || ($thisComp->icon1 == 'challenge1')) {
    $datas = $holder->getByComponentNewest($thisComp->component,$community->id);
} elseif($thisComp->icon1 == "files1"){
    $datas = $holder->getByComponentNewest($thisComp->component, $community->id);
} else {
    $datas = $holder->getByComponentNewest($thisComp->component, $community->id, 10 * $page - 10, 10 * $page, $tag_filter);
}
$count = $holder->getCount($thisComp->component, $community->id);

$searchURL = '/'.$community->portalName.'/about/search';
if ($thisComp->icon1 == 'timeline1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/timeline1.php';
} elseif ($thisComp->icon1 == 'timeline2') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/timeline2.php';
} elseif ($thisComp->icon1 == 'static') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/static.php';
} elseif ($thisComp->icon1 == 'files1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/table.php';
} elseif ($thisComp->icon1 == 'blog1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/blog-view.php';
} elseif ($thisComp->icon1 == 'gallery1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/gallery-view.php';
} elseif ($thisComp->icon1 == 'slideshow1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/slide.share.gallery.php';
} elseif ($thisComp->icon1 == 'contact1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/contact-form.php';
} elseif ($thisComp->icon1 == 'event1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/event-page.php';
} elseif ($thisComp->icon1 == 'table1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/dynamic-table.php';
} elseif ($thisComp->icon1 == 'series1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/series1.php';
} elseif ($thisComp->icon1 == 'challenge1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/challenge1.php';
} elseif ($thisComp->icon1 == 'challengeset1') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/challengeset1.php';
} elseif ($thisComp->icon1 == 'doi') {
    $baseURL = '/'.$community->portalName.'/about/';
    include $_SERVER['DOCUMENT_ROOT'] .'/components/body/parts/pages/doi.php';
}
$html = ob_get_clean();

$breadcrumbs = Array(
    Array("text" => "Home", "url" => $community->fullURL()),
    Array("text" => $thisComp->text1, "active" => true),
);
if($community->rinStyle()) {
    $rin_data = Array(
        "title" => $thisComp->text1,
        "breadcrumbs" => $breadcrumbs,
        "html-body" => $html,
    );
    echo \helper\htmlElement("rin-style-page", $rin_data);
} else {
    echo \helper\rinBreadCrumbsToNormalBreadCrumbs($breadcrumbs);
    echo $html;
}

?>
