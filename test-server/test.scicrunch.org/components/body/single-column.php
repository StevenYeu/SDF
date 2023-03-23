<?php
$inblock = false;
$componentCount = 0;
$componentTotal = count($components['body']);
if($community->portalName == "dknet") {
    //include "parts/rows/temp-dknet3-preview.php";
}
if($community->portalName == "legacy-niddk") {
    include "parts/rows/temp-dknet-preview.php";
}
foreach($components['body'] as $component){

    switch($component->component){
        case 10:
//            include 'parts/parralax/parralax-slider.php';
            break;
        case 11:
//            include 'parts/parralax/parralax-counter.php';
            break;
        case 12:
//            include 'parts/parralax/search-banner.php';
            break;
        case 13:
// Manu            include 'parts/parralax/image-slider.php';
            break;
        case 14:
//            include 'parts/parralax/parralax-counter-dark.php';
            break;
        case 15:
//            include 'parts/parralax/highlight-panel-1.php';
            break;
        case 16:
//            include 'parts/parralax/highlight-panel-2.php';
            break;
        case 17:
//            include 'parts/parralax/term-search-banner.php';
            break;
        case 21:
            include 'parts/blocks/goto-block.php';
            break;
        case 22:
//            include 'parts/blocks/news-thumbnails.php';
            break;
        case 23:
//            include 'parts/blocks/calendar-block.php';
            break;
        case 24:
            include 'parts/rows/search-bar.php';
            break;
        case 25:
            include 'parts/blocks/text-block.php';
            break;
        case 26:
//            include 'parts/blocks/video-text.php';
            break;
        case 27:
//            include 'parts/blocks/rss-block.php';
            break;
        case 30:
//            include 'parts/rows/categories-block.php';
            break;
        case 31:
//            include 'parts/rows/services-block.php';
            break;
        case 32:
//            include 'parts/rows/white-services-block.php';
            break;
        case 33:
//            include 'parts/rows/works-thumbnails.php';
            break;
        case 34:
            include 'parts/rows/page-box.php';
            break;
        case 35:
//            include 'parts/rows/dynamic-services.php';
            break;
        case 36:
//            include 'parts/rows/search-image.php';
            break;
        case 37:
//            include 'parts/rows/sponsors-box.php';
            break;
        case 38:
//            include 'parts/rows/news-feed.php';
            break;
        case 39:
//            include 'parts/rows/labs-block.php';
            break;
        case 40:
//            include 'parts/rows/rin-block.php';
            break;
        case 41:
           include 'parts/parralax/software-search.php'; // added by Steven
           break;

    }
    $componentCount++;
}

?>
