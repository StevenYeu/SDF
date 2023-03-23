<?php

use Symfony\Component\HttpFoundation\Request;

$app->get($AP."/grants/pmid/{pmid}", function(Request $request, $pmid) use($app) {
    /* placeholder to return all grants associated with a pmid */
    $result = APIReturnData::build(Array(
        Array("funder" => "NIH", "id" => "123"),
        Array("funder" => "NIH", "id" => "456"),
    ), true);
    return appReturn($app, $result);
});

?>
