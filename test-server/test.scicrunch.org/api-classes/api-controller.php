<?php

/****************************************************************************************************
All api files are paths are included here
For convenience they're broken into small api-controller-* files
This file also includes extra api functions
****************************************************************************************************/

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

include __DIR__ . "/api-controller-user.php";
include __DIR__ . "/api-controller-ilx.php";
include __DIR__ . "/api-controller-key.php";
include __DIR__ . "/api-controller-subscription.php";
include __DIR__ . "/api-controller-resource.php";
include __DIR__ . "/api-controller-data.php";
include __DIR__ . "/api-controller-rrid.php";
include __DIR__ . "/api-controller-entitymapping.php";
include __DIR__ . "/api-controller-community.php";
include __DIR__ . "/api-controller-term.php";
include __DIR__ . "/api-controller-usermessages.php";
include __DIR__ . "/api-controller-nifservices.php";
include __DIR__ . "/api-controller-systemmessages.php";
include __DIR__ . "/api-controller-datasets.php";
include __DIR__ . "/api-controller-labs.php";
include __DIR__ . "/api-controller-viewstatus.php";
include __DIR__ . "/api-controller-rrid-report.php";
include __DIR__ . "/api-controller-recommendations.php";
include __DIR__ . "/api-controller-scigraphservices.php";
include __DIR__ . "/api-controller-sparcscigraph.php";
include __DIR__ . "/api-controller-elasticsearch.php";
include __DIR__ . "/api-controller-scicrunch-data.php";
include __DIR__ . "/api-controller-rrid-mentions.php";
include __DIR__ . "/api-controller-d3r-celpp.php";
include __DIR__ . "/api-controller-grants.php";
include __DIR__ . "/api-controller-resourcewatch.php";
include __DIR__ . "/api-controller-foundryDB.php";

/******************************************************************************************************************************************************************************************************/

/**
 * aR
 * handles input sanitzation
 * just a wrapper for the \helper version
 *
 * @param mixed the argument that's being sanitized
 * @param string the type of argument to be sanitized. s - string, f - float, i (default) - integer
 * @return mixed the sanitized argument
 */
function aR($request, $type){   // aR - argumentRequest
    return \helper\aR($request, $type);
}

/**
 * appReturn
 * unwraps an APIReturnData and returns it for the silex application
 *
 * @param \Silex\Application
 * @param APIReturnData the results from the api endpoint
 * @param bool should it call arrayForm() on the returned object
 * @param bool overrides previous argument if true, call arrayForm() on an array of objects
 * @return Symfony\Component\HttpFoundation\Result
 */
function appReturn($app, $results, $array_form = false, $array_of_array_form = false){
    if($results->success){
        if($array_of_array_form){
            $data = $results->data;
            $fdata = Array();
            if(!empty($data) && is_array($data)){
                foreach($data as $datum){
                    $fdata[] = $datum->arrayForm();
                }
            }
            return $app->json($fdata, $results->status_code);
        }
        elseif($array_form){
            return $app->json($results->data->arrayForm(), $results->status_code);
        }
        else{
            return $app->json($results->data, $results->status_code);
        }
    }
    else{
        return $app->json($results->status_msg, $results->status_code);
    }
}

?>
