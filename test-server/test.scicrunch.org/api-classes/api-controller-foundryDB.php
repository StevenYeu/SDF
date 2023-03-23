<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->get($AP."/foundryDB/UI/MAIN/resources", function(Request $request) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/data/foundry-db.php";
    $results = retrieveResources();
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/foundryDB/UI/MAIN/statistics", function(Request $request) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/data/foundry-db.php";
    $results = retrieveStatistics();
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/foundryDB/UI/LOG/{resource}", function(Request $request, $resource) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/data/foundry-db.php";
    $results = retrieveLogs($resource);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/foundryDB/UI/MAIN/systemStatus", function(Request $request) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/data/foundry-db.php";
    $results = retrieveSystemStatus();
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

/** Start of API Controller **/

$app->get($AP."/foundryDB/UI/Controller/Ingest/{resourceID}", function(Request $request, $resourceID) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
    $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
    $results = execute($apiKey, 'ingest', $resourceID);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

// $app->get($AP."/foundryDB/UI/Controller/Reprocess", function(Request $request, $resourceID) use($app) {
//     require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
//     $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
//     $results = execute($apiKey, 'reprocess', $resourceID);
//     return appReturn($app, APIReturnData::build($results, true, 200), false);
// });

$app->get($AP."/foundryDB/UI/Controller/ReprocessErrors", function(Request $request, $resourceID) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
    $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
    $results = execute($apiKey, 'run', $resourceID);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/foundryDB/UI/Controller/Hold/{resourceID}", function(Request $request, $resourceID) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
    $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
    $results = execute($apiKey, 'hold_source', $resourceID);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/foundryDB/UI/Controller/Clear/{resourceID}", function(Request $request, $resourceID) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
    $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
    $results = execute($apiKey, 'clear_source', $resourceID);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

$app->get($AP."/foundryDB/UI/Controller/Recover", function(Request $request) use($app) {
    require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
    $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
    $results = execute($apiKey, 'recover', $resourceID);
    return appReturn($app, APIReturnData::build($results, true, 200), false);
});

// $app->get($AP."/foundryDB/UI/Controller/Index/{resourceID}", function(Request $request, $resourceID) use($app) {
//     require_once __DIR__ . "/../foundry-dashboard/pythonControllerAPI/controller.php";
//     $apiKey = '6116426cce83959b8487375fe6721b69c617ce9eca1e96d3d2a271b33f7db6a9';
//     $results = execute($apiKey, 'index', $resourceID);
//     return appReturn($app, APIReturnData::build($results, true, 200), false);
// });


/** End of API Controller **/

?>
