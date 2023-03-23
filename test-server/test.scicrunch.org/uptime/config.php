<?php
$docroot = "..";
include_once($docroot . "/classes/classes.php");
include_once($docroot. "/config.php");

return array(

    'TIMEOUT' => '6',

    // Ontology services
    'ONTO_HOSTS' => array(
        "matrix.neuinfo.org", // dev
        "trinity.neuinfo.org", // stage
        "cypher.neuinfo.org", // prod
        "prod-scigraph.scicrunch.org",
    ),

    // Data services
    'DATA_HOSTS' => array(
        "nif-apps1.crbs.ucsd.edu/servicesv1",
        "nif-apps2.crbs.ucsd.edu/servicesv1",
        "nif-services.neuinfo.org/servicesv1",
        "skaa.crbs.ucsd.edu:8080/services",
        "skab.crbs.ucsd.edu:8080/services",
    ),

    // Solr services
    'SOLR_HOSTS' => array(
        "tatoo1.crbs.ucsd.edu", // prod
        "tatoo2.crbs.ucsd.edu", // stage
    ),

    'SOLR_LIT_HOSTS' => array(
        "starburst.crbs.ucsd.edu",
        "vivaldi.crbs.ucsd.edu",
    ),

    // PostgreSQL configurations
    'PG_HOSTS' => array(
        'postgres-stage.neuinfo.org', // stage
        'postgres.neuinfo.org', // prod
    ),
    // MySQL configurations
    'MS_HOSTS' => array(
        'dev-db.crbs.ucsd.edu', // dev
        'mysql5-stage.crbs.ucsd.edu', // stage
        'nif-mysql.crbs.ucsd.edu', // prod
    ),
    'PG_CONFIG' => $config['uptime']['PG_CONFIG'],
    'MS_CONFIG' => $config['uptime']['MS_CONFIG'],

    'BASE_URL' => '/uptime/',
    'DATA_SCR' => 'test-data-services.php',
    'ONTO_SCR' => 'test-ontology-services.php',
    'SOLR_SCR' => 'test-solr.php',
    'MS_SCR' => 'test-mysql.php',
    'PG_SCR' => 'test-postgres.php',

    // Uptime Robot configs
    'UR_IDS' => array(
        "matrix.neuinfo.org" => '777180016',
        "trinity.neuinfo.org" => '777180015',
        "cypher.neuinfo.org" => '777180013',
        "nif-apps1.crbs.ucsd.edu" => '777180005',
        "nif-apps2.crbs.ucsd.edu" => '777180009',
        "nif-services.neuinfo.org" => '777180010',
        "tatoo1.crbs.ucsd.edu" => '777180019',
        "tatoo2.crbs.ucsd.edu" => '777180018',
        'postgres-stage.neuinfo.org' => '777181085',
        'postgres.neuinfo.org' => '777181081',
        'dev-db.crbs.ucsd.edu' => '777180027',
        'mysql5-stage.crbs.ucsd.edu' => '777180030',
        'nif-mysql.crbs.ucsd.edu' => '777180034',
        'starburst.crbs.ucsd.edu' => '777220537',
        'vivaldi.crbs.ucsd.edu' => '777220674',
    ),

//uptime robot api keys and ids
/*
 main API key: u261955-e9d7fc8fe0bfcfc073d54ea2

 nif-services: m777180010-e298d08aa506929077498165  id: 777180010
 nif-apps1: m777180005-3b430b3b2cbee2d878b84bba  id: 777180005
 nif-apps2: m777180009-ce7f7167f2d33aa946d9c6c2  id: 777180009

 scigraph cypher: m777180013-406a104a8e78359b93d9a37e  id: 777180013
 scigraph trinity: m777180015-943b6ced5ab039e660a02f7c  id: 777180015
 scigraph matrix: m777180016-4fd168916a8f7f2f3fdbb1ea  id: 777180016

 solr tatoo1: m777180019-bd1904bd2d74709cdf2ebd53  id: 777180019
 solr tatoo2: m777180018-570eb73eaa6fa33e28f76df4  id: 777180018
 solr starburst: id: 777220537
 solr vivaldi: id: 777220674

 postgres prod: m777181081-d9a009d8d405b9186ad47140  id: 777181081
 postgres stage: m777181085-de840040d938bd901c9eb226  id: 777181085

 mysql prod: m777180034-04c953fda1dd1df2ddb865be  id: 777180034
 mysql stage: m777180030-e9f766c4dcffc81e18720a23 id: 777180030
 mysql dev: m777180027-767558c3c6521eda016c2cd3  id: 777180027
*/

);

?>
