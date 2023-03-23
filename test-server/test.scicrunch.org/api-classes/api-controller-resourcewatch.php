<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->get($AP."/resource-watch/UI/DB/{rrid}", function(Request $request, $rrid) use($app) {
    require_once __DIR__."/../resource-watch/data/resource-watch-DB.php";
    $results = retrieveValidationIssueInfo($rrid);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/resource-watch/UI/ES/{rrid}", function(Request $request, $rrid) use($app) {
    require_once __DIR__."/../resource-watch/data/query-resolver.php";
    $results = retrieveResolverInfo($rrid);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

?>
