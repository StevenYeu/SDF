<?php

$community = $data["community"];
$thisComp = $data["component"];
$component_data = $data["component-data"];
$vars = $data["vars"];


ob_start();
$baseURL = '/'.$community->portalName.'/about/';
$searchURL = '/'.$community->portalName.'/about/search';
switch($thisComp->icon1){
    case "files1":
        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/pages/table-display.php';
        break;

    case "challenge1":
        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/pages/challengeset1.php';
        break;

    default:
        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/pages/article-view.php';
        break;
}
$html = ob_get_clean();

$breadcrumbs = Array(
    Array("text" => "Home", "url" => $community->fullURL()),
    Array("text" => $thisComp->text1, "url" => $community->fullURL() . "/about/" . $thisComp->text2),
    Array("text" => $component_data->title, "active" => true),
);
if($community->rinStyle()) {
    if(count($breadcrumbs) == 3 && $breadcrumbs[1]["text"] == "dkNET Blog")
        $rin_data = Array(
            "title" => "Blog",
            "breadcrumbs" => $breadcrumbs,
            "html-body" => $html,
        );
    else if(count($breadcrumbs) == 3 && $breadcrumbs[1]["text"] == "dkNET News")
        $rin_data = Array(
            "title" => "News",
            "breadcrumbs" => $breadcrumbs,
            "html-body" => $html,
        );
    else
        $rin_data = Array(
            "title" => $component_data->title,
            "breadcrumbs" => $breadcrumbs,
            "html-body" => $html,
        );
    echo \helper\htmlElement("rin-style-page", $rin_data);
} else {
    echo \helper\rinBreadCrumbsToNormalBreadCrumbs($breadcrumbs);
    echo $html;
}

?>
