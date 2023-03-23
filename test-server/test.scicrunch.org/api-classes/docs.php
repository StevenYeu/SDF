<?php

use Swagger\Annotations as SWG;

/**
 *  @SWG\Swagger(
 *      basePath="/api/1",
 *      @SWG\Info(
 *          title="SciCrunch API Docs",
 *          description="API endpoints for <a href=""https://scicrunch.org"">scicrunch.org</a>, <a href=""https://dknet.org"">dknet.org</a> and <a href=""https://neuinfo.org"">neuinfo.org</a>.<br/>All API calls require the 'key' field to be set with an API key",
 *          version=1.0,
 *      ),
 *
 *      @SWG\Definition(
 *          definition="object",
 *          type="object",
 *          @SWG\Property(property="data", type="string"),
 *          @SWG\Property(property="success", type="boolean"),
 *      ),
 *
 *      @SWG\Definition(
 *          definition="errorObject",
 *          type="object",
 *          @SWG\Property(property="errormsg", type="string"),
 *      )
 *  )
 *
 */


/**
 *
 * @SWG\Definition(
 *   definition="apiReturn_array_string",
 *   type="object",
 *   @SWG\Property(property="data", type="array", @SWG\Items(type="string")),
 *   @SWG\Property(property="success", type="boolean"),
 * )
 *
 */

//
// Swagger docs - APIKeyPermission in class/api-keys-permission.class.php
//
/**
 *
 * @SWG\Definition(
 *   definition="apiKeyPermission",
 *   type="object",
 *   @SWG\Property(property="permission_type", type="string"),
 *   @SWG\Property(property="permission_data", type="string"),
 *   @SWG\Property(property="active", type="integer"),
 *   @SWG\Property(property="created_time", type="integer"),
 * )
 *
 * @SWG\Definition(
 *   definition="apiReturn_apiKeyPermission",
 *   type="object",
 *   @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/apiKeyPermission")),
 *   @SWG\Property(property="success", type="boolean"),
 * )
 *
 */


?>
