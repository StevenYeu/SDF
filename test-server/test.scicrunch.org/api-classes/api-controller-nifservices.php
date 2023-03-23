<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 *  @SWG\Get( path="/dataservices/federation/data/{viewid}", summary="search a single data view", tags={"Data services"},
 *      @SWG\Parameter( name="viewid", description="the view ID (example nlx_144509-1 is the scicrunch registry view ID)", in="path", required=true, type="string"),
 *      @SWG\Parameter( name="q", description="query", in="query", required=false, type="string"),
 *      @SWG\Parameter( name="count", in="query", required=false, type="integer"),
 *      @SWG\Parameter( name="offset", in="query", required=false, type="integer"),
 *      @SWG\Parameter( name="facet", description="Facets specified like facetName:facetValue", in="query", required=false, type="string"),
 *      @SWG\Parameter( name="filter", description="Facets specified like filterName:filterValue", in="query", required=false, type="string"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 *  @SWG\Get( path="/dataservices/federation/search", summary="search all data views", tags={"Data services"},
 *      @SWG\Parameter( name="q", description="query", in="query", required=false, type="string"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 *  @SWG\Get( path="/dataservices/federation/facets/{viewid}", summary="get facets for a data view", tags={"Data services"},
 *      @SWG\Parameter( name="viewid", description="the view ID (example nlx_144509-1 is the scicrunch registry view ID)", in="path", required=true, type="string"),
 *      @SWG\Parameter( name="q", description="query", in="query", required=false, type="string"),
 *      @SWG\Parameter( name="count", in="query", required=false, type="integer"),
 *      @SWG\Parameter( name="offset", in="query", required=false, type="integer"),
 *      @SWG\Parameter( name="facet", description="Facets specified like facetName:facetValue", in="query", required=false, type="string"),
 *      @SWG\Parameter( name="filter", description="Facets specified like filterName:filterValue", in="query", required=false, type="string"),
 *      @SWG\Parameter( name="minCount", description="The minimum threshold for facets to return", in="query", required=false, type="integer"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 *  @SWG\Get( path="/dataservices/literature/search", summary="search the Pubmed literature", tags={"Data services"},
 *      @SWG\Parameter( name="q", description="query", in="query", required=false, type="string"),
 *      @SWG\Parameter( name="count", in="query", required=false, type="integer"),
 *      @SWG\Parameter( name="offset", in="query", required=false, type="integer"),
 *      @SWG\Parameter( name="authorFilter", in="query", required=false, type="boolean"),
 *      @SWG\Parameter( name="yearFilter", in="query", required=false, type="boolean"),
 *      @SWG\Parameter( name="journalFilter", in="query", required=false, type="boolean"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 *  @SWG\Get( path="/dataservices/literature/pmid", summary="retrieve by PMID", tags={"Data services"},
 *      @SWG\Parameter( name="pmid", in="query", required=true, type="integer"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 *
 **/

$app->get($AP."/dataservices/{path}", function(Request $request, $path) use($app) {
    require_once __DIR__ . "/nifservices_wrapper.php";

    $sparc_hardcoded_key="sOgzhaxqXNeFY6AHTPxYnWLSSeYMkAwU";

    if(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) $query_string = "?" . $_SERVER["QUERY_STRING"];
    else $query_string = "";
    $full_path = $path . $query_string;

    if(stristr($full_path,"nlx_152175")) {
        if($app["config.api_key"] != $sparc_hardcoded_key) {
            return APIReturnData::quick403();
        }
    }

    if(stristr($full_path,"SCR_017041")) {
      $api_file = fopen("tmp/api_key", "a+");
      fwrite($api_file,"Hello ");
      $user_key = serialize($app["config.api_key"]);
      $app_key = $app["config.api_key"];
      fwrite($api_file,$user_key);
      fwrite($api_file,$app["config.user"]);
      fwrite($api_file," Bye\n");
      fclose($api_file);

      /* if($app["config.api_key"] != $sparc_hardcoded_key) {
        return APIReturnData::quick403();
      } */


    }

    if(\helper\startsWith($full_path, "v1/")) {
        $full_path = substr($full_path, 3);
    }
    $nif_response = getResponse($app["config.user"], $app["config.api_key"], $full_path);

    if(get_class($nif_response) == "APIReturnData") {
        return appReturn($app, $nif_response);
    } else {
        $response = new Response($nif_response["body"], $nif_response["header"]["http_code"]);
        if(isset($nif_response["header"]["Content-Type"])) $response->headers->set("Content-Type", $nif_response["header"]["Content-Type"]);
        return $response;
    }

})->assert('path', '.+');


?>
