<?php
// Steven Changed "fqdn" from IP address to Domain name
return Array(
    "hostenv" => "stage",
    "environment" => "http://skaa.neuinfo.org:8080/services",
    "betaenvironment" => "http://kaa.neuinfo.org:8080/services",
    "apiurl" => "http://matrix.neuinfo.org:9000",
    "sparcapiurl" => "http://scigraph.scicrunch.io:9000",
    "gacode" => "",
    "version" => "0.61",
    "mysql-hostname" => getenv("MYSQL_HOST"),
    "mysql-username" => getenv("MYSQL_USER"),
    "mysql-password" => getenv("MYSQL_PASSWORD"),
    "mysql-database-name" => getenv("MYSQL_DATABASE"),

    "fqdn" => "sdf.sdsc.edu:443", 
    "protocol" => "https",

    "captcha-key" =>  getenv("CAPTCHA_KEY"),
    "captcha-secret-key" => getenv("CAPTCHA_SECRET"),

    "mailgun-secret-key" => "",
    "ilx-fragment-prefix" => "DEV",

    "orcid-client-id" => "",
    "orcid-client-secret" => "",

    "dataset-config" => Array(
        "term" => Array(
            "ilx" => Array(
                "text" => "ilx_0115028",
                "default" => "ilx_0115028",
                "annotation-value-restriction" => "ilx_0115026",
                "annotation-value-restriction-id" => 15027,
                "annotation-value-range" => "ilx_0115027",
                "annotation-value-range-id" => 15028,
                "annotation-source" => "ilx_0723781",
                "annotation-source-value" => "ODC-SCI",
                "annotation-source-id" => 646915,
                "annotation-domain" => "ilx_0115023",
                "annotation-domain-id" => 15024,
                "annotation-subdomain" => "ilx_0115030",
                "annotation-subdomain-id" => 15034,
                "annotation-assessmentdomain" => "ilx_0115031",
                "annotation-assessmentdomain-id" => 15035,
                "annotation-default-value" => "ilx_0115033",
                "annotation-default-value-id" => 15037,
                "annotation-multiple-values" => "ilx_0112809",
                "annotation-multiple-values-id" => 12810,
            ),
        ),
    ),

    "mongo-datasets" => Array(
        "host" => "",
        "user" => "",
        "password" => "",
        "database" => "",
        "replica-set" => "",
    ),

    "elastic-search" => Array(
        "resolver" => Array(
            "user" => "portal",
            "password" => "d7[if[THX3U.v+8^7E3xXTn7",
            "base-url" => "https://resolver.scicrunch.io",
        ),
        "pubmed" => Array(
            "user" => "portal",
            "password" => "d7[if[THX3U.v+8^7E3xXTn7",
            "base-url" => "https://literature.scicrunch.io",
        ),
        "interlex" => Array(
            "user" => "portal",
            "password" => "d7[if[THX3U.v+8^7E3xXTn7",
            "base-url" => "https://interlex.scicrunch.io",
            "index" => "interlex",
        ),
        "normal" => Array(
            "user" => "portal",
            "password" => "d7[if[THX3U.v+8^7E3xXTn7",
            "base-url" => "https://elastic-prod.scicrunch.io",
        ),
        "elevated" => Array(
            "user" => "portal",
            "password" => "d7[if[THX3U.v+8^7E3xXTn7",
            "base-url" => "https://elastic-prod.scicrunch.io",
        ),
        "api" => Array(
            "user" => "portal",
            "password" => "d7[if[THX3U.v+8^7E3xXTn7",
            "base-url" => "https://elastic.scicrunch.io",
        ),
    ),
    "user-rrid-mentions" => Array(
        31699 => Array(Array("all" => true)),
        34385 => Array(Array("provider" => "Cellosaurus Cell Lines")),
        43 => Array(Array("provider" => "Antibody Registry")),
        34297 => Array(Array("provider" => "WormBase")),
        34295 => Array(Array("provider" => "International Mouse Strain Resource - Jackson Labs")),
        34286 => Array(Array("provider" => "Mutant Mouse Resource and Research Center")),
        32694 => Array(Array("provider" => "Ambystoma Genetic Stock Center")),
        34298 => Array(Array("funder" => "NCI NIH HHS")),
        34296 => Array(Array("journal" => "Scientific reports")),
        34290 => Array(Array("journal" => "Neuron")),
        34288 => Array(Array("journal" => "PloS one")),
        34287 => Array(Array("provider" => "International Mouse Strain Resource - Jackson Labs")),
        34294 => Array(Array("provider" => "Mutant Mouse Resource and Research Center")),
        31749 => Array(Array("provider" => "Bloomington Drosophila Stock Center"), Array("provider" => "FlyBase")),
        35431 => Array(Array("all" => true)),
        35432 => Array(Array("all" => true)),
        35428 => Array(Array("all" => true)),
        35429 => Array(Array("all" => true)),
        35430 => Array(Array("all" => true)),
        35433 => Array(Array("all" => true)),
        35436 => Array(Array("all" => true)),
        35451 => Array(Array("all" => true)),
        35439 => Array(Array("all" => true)),
        35465 => Array(Array("all" => true)),
    ),
    "odc-communities" => Array(),

);

?>
