<?php

use Symfony\Component\HttpFoundation\Request;

$app->get($AP.'/viewstatuses/{nif_id}', function(Request $request, $nif_id) use($app) {
    require_once __DIR__."/get-view-statuses.php";
    $content = getTableInfo();
    $nifIdInfo = getNifIdInfo($content);
    return $app->json($nifIdInfo[$nif_id], 200);
});

$app->get($AP.'/viewstatuses', function(Request $request) use($app) {
    require_once __DIR__."/get-view-statuses.php";
    $content = getTableInfo();
    return $app->json($content, 200);
});

?>
