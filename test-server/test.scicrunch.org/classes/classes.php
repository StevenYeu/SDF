<?php

/****************************************************************************************************
Classes should be added to the /classes directory.  Classes have the name ${name}.class.php.

After a class is added create an entry inside the $GLOBALS["class-name"] associative array.
As long as the /classes/classes.php file is included and the class is registered in the
$GLOBALS["class-name"] array, the class will be available anywhere in the code, but it's file will
not be interpreted until the first time it is called by the script.

If creating a class that will map to a MySQL table, it may be helpful to extend from the DBObject3
class.
****************************************************************************************************/

require __DIR__ . "/../lib/vendor/autoload.php";

define("MAXINT", 9223372036854775807);
date_default_timezone_set('America/Los_Angeles');

function getEnvironment($docroot){
    $default_env = "http://skab.crbs.ucsd.edu:8080/services";
    try {
        $file_contents = file_get_contents($docroot . "/vars/data_statuses.php");
        if($file_contents === false) throw new Exception("could not read file");
        $statuses = unserialize($file_contents);
        if($statuses === false) throw new Exception("could not unserialize");
        foreach($statuses as $env => $status){
            if($status['status'] == "up") return $env;
        }
        return $default_env;    // default if all fail
    } catch (Exception $e){
        return $default_env;
    }
}

if(!isset($docroot)) $docroot = $_SERVER['DOCUMENT_ROOT'];
$GLOBALS["DOCUMENT_ROOT"] = $docroot;
if(!defined("CONFIG_FILE")) define("CONFIG_FILE", __DIR__ . "/../config.php");

$config = require CONFIG_FILE;
$GLOBALS["config"] = $config;
define("HOSTENV", $config["hostenv"]);
define("ENVIRONMENT", $config["environment"]);
define("APIURL", $config["apiurl"]);
define("SPARCAPIURL", $config["sparcapiurl"]);
define("GACODE", $config["gacode"]);
define("VERSION", $config["version"]);
define("HOSTNAME", $config["mysql-hostname"]);
define("USERNAME", $config["mysql-username"]);
define("PASSWORD", $config["mysql-password"]);
define("DATABASE_NAME", $config["mysql-database-name"]);
define("FQDN", $config["fqdn"]);
define("PROTOCOL", $config["protocol"]);
define("CAPTCHA_KEY", $config["captcha-key"]);
define("CAPTCHA_SECRET_KEY", $config["captcha-secret-key"]);
define("MAILGUN_SECRET_KEY", $config["mailgun-secret-key"]);
define("ILX_FRAGMENT_PREFIX", $config["ilx-fragment-prefix"]);
define("ORCID_CLIENT_ID", $config["orcid-client-id"]);
define("ORCID_CLIENT_SECRET", $config["orcid-client-secret"]);
define("PUBLICENVIRONMENT", PROTOCOL . "://" . FQDN . "/api/1/dataservices");
if(isset($config["betaenvironment"])) define("BETAENVIRONMENT", $config["betaenvironment"]);

require_once $GLOBALS["DOCUMENT_ROOT"] . '/classes/helper.namespace.php';
require_once $GLOBALS["DOCUMENT_ROOT"] . '/classes/search.namespace.php';
require_once $GLOBALS["DOCUMENT_ROOT"] . '/classes/api-permission-actions.namespace.php';

$GLOBALS["class-map"] = Array(
    'Connection' => '/classes/connection.class.php',
    'DBObject' => '/classes/dbobject.class.php',
    'DBObject2' => '/classes/dbobject2.class.php',
    'DBObject2Field' => '/classes/dbobject2-field.class.php',
    'DBObject3' => '/classes/dbobject3.class.php',
    'User' => '/classes/user.class.php',
    'Collection' => '/classes/collection.class.php',
    'Item' => '/classes/collection.class.php',
    'Community' => '/classes/community.class.php',
    'Category' => '/classes/community.class.php',
    'Search' => '/classes/search.class.php',
    'Snippet' => '/classes/snippet.class.php',
    'Component' => '/classes/component.class.php',
    'Component_Data' => '/classes/component.data.class.php',
    'Tag' => '/classes/component.extras.class.php',
    'Extended_Data' => '/classes/component.extras.class.php',
    'Notification' => '/classes/notification.class.php',
    'Page' => '/classes/page.class.php',
    'Resource' => '/classes/resource.class.php',
    'ResourceDBO' => '/classes/resource.class.php',
    'Columns' => '/classes/resource.class.php',
    'Resource_Type' => '/classes/resource.class.php',
    'Resource_Fields' => '/classes/resource.class.php',
    'Form_Relationship' => '/classes/resource.class.php',
    'Sources' => '/classes/source.class.php',
    'Saved' => '/classes/saved.class.php',
    'View' => '/classes/custom.view.class.php',
    'View_Column' => '/classes/custom.view.class.php',
    'Error' => '/classes/error.class.php',
    'ErrorDBO' => '/classes/error.class.php',
    'Challenge' => '/classes/challenge.class.php',
    'D3RCelpp' => '/classes/d3r-celpp.class.php',
    'BaseSearch' => '/classes/base_search.class.php',
    'ResourceMention' => '/classes/resource-mention.class.php',
    'ResourceRelationship' => '/classes/resource-relationship.class.php',
    'ResourceRelationshipString' => '/classes/resource-relationship-string.class.php',
    'ResourceUserRelationship' => '/classes/resource-user-relationship.class.php',
    'RRIDMap' => '/classes/rridmap.class.php',
    'APIKey' => '/classes/api-keys.class.php',
    'APIKeyPermission' => '/classes/api-keys-permission.class.php',
    'IlxIdentifier' => '/classes/ilx-identifier.class.php',
    'Subscription' => '/classes/subscription.class.php',
    'DbObj' => '/classes/term.class.php',
    'Term' => '/classes/term.class.php',
    'TermDBO' => '/classes/term.class.php',
    'TermRelationship' => '/classes/term.class.php',
    'TermAnnotation' => '/classes/term.class.php',
    'TermVoteLogs' => '/classes/term.class.php',
    'TermVersion' => '/classes/term.class.php',
    'TermVarField' => '/classes/term.class.php',
    'TermSynonym' => '/classes/term.class.php',
    'TermExistingId' => '/classes/term.class.php',
    'TermSuperclass' => '/classes/term.class.php',
    'TermOntology' => '/classes/term.class.php',
    'TermAnnotationType' => '/classes/term.class.php',
    'TermMapping' => '/classes/term.class.php',
    'TermMappingDBO' => '/classes/term.class.php',
    'TermMappingLogs' => '/classes/term.class.php',
    'TermMappingDeletes' => '/classes/term.class.php',
    'CurieCatalog' => '/classes/term.class.php',
    'APIReturnData' => '/classes/api-return-data.php',
    'EntityMapping' => '/classes/entitymapping.class.php',
    'RRIDFailureLog' => '/classes/rrid-failure-log.class.php',
    'SearchFederationFailureLog' => '/classes/search-federation-failure-log.class.php',
    'ResourceSuggestion' => '/classes/resource-suggestion.class.php',
    'UserMessage' => '/classes/user-message.class.php',
    'UserMessageConversationUser' => '/classes/user-message-conversation-user.class.php',
    'UserMessageConversation' => '/classes/user-message-conversation.class.php',
    'UserMessageConversationForeignReference' => '/classes/user-message-conversation.class.php',
    'UserMessageExtradata' => '/classes/user-message-extradata.class.php',
    'SystemMessage' => "/classes/system-message.class.php",
    'CommunityAccessRequest' => '/classes/community-access-request.class.php',
    'MailcountLog' => '/classes/mailcount-log.class.php',
    'Challenge_Submission' => '/classes/challenge.submission.class.php',
    'Dataset' => '/classes/dataset.class.php',
    'DatasetField' => '/classes/dataset-field.class.php',
    'MongoDataset' => '/classes/mongodataset.class.php',
    'DatasetMetadataField' => '/classes/dataset-metadata-field.class.php',
    'DatasetMetadata' => '/classes/dataset-metadata.class.php',
    'DatasetFieldTemplate' => '/classes/dataset-field-template.class.php',
    'DatasetFlags' => '/classes/dataset-flags.class.php',
    'DatasetAssociatedFiles' => '/classes/dataset-associated-files.class.php',
    'DatasetStatus' => '/classes/dataset-status.class.php',
    'Lab' => '/classes/lab.class.php',
    'LabMembership' => '/classes/lab-membership.class.php',
    'LabMembershipRole' => '/classes/lab-membership-role.class.php',
    'CommunityDataset' => '/classes/community-dataset.class.php',
    'CommunityDatasetRequiredField' => '/classes/community-dataset-required-field.class.php',
    'RRIDReport' => '/classes/rrid-report.class.php',
    'RRIDReportFreeze' => '/classes/rrid-report-freeze.class.php',
    'RRIDReportItem' => '/classes/rrid-report-item.class.php',
    'RRIDReportItemUserData' => '/classes/rrid-report-item-user-data.class.php',
    'RRIDReportItemSubtype' => '/classes/rrid-report-item-subtype.class.php',
    'RRIDReportItemSubtypeUserData' => '/classes/rrid-report-item-subtype-user-data.class.php',
    'DataTypes' => '/classes/data-types.class.php',
    'Uptime' => '/classes/uptime.class.php',
    'ActionKey' => '/classes/action-key.class.php',
    'ResourceElasticsearch' => '/classes/resource-elasticsearch.class.php',
    'ComponentDataMulti' => '/classes/component-data-multi.class.php',
    'APIKeyLog' => '/classes/api-key-log.class.php',
    'UserDBO' => '/classes/user.class.php',
    'RRIDMention' => '/classes/rrid-mention.class.php',
    'UsersExtraData' => '/classes/users-extra-data.class.php',
    'ServerCache' => '/classes/server-cache.class.php',
    'RRIDMentionsLiteratureRecord' => '/classes/rrid-mentions-literature-record.class.php',
    'RRIDMentionsGrantInfo' => '/classes/rrid-mentions-grant-info.class.php',
    'WorldCatInterface' => '/classes/world-cat-interface.class.php',
    'DatasetFieldAnnotation' => '/classes/dataset-field-annotation.class.php',
    'RRIDPrefixRedirect' => '/classes/rrid-prefix-redirect.class.php',
    'TermFlag' => '/classes/term-flag.class.php',
    'TermDBO' => '/classes/term.class.php',
    'ElasticRRIDManager' => '/classes/elastic-view.class.php',
    'ElasticRRIDResults' => '/classes/elastic-view.class.php',
    'ElasticRRIDRecord' => '/classes/elastic-view.class.php',
    'ElasticRRIDField' => '/classes/elastic-view.class.php',
    'ElasticPMIDManager' => '/classes/elastic-pubmed.class.php',
    'ElasticPMIDResults' => '/classes/elastic-pubmed.class.php',
    'ElasticPMIDRecord' => '/classes/elastic-pubmed.class.php',
    'ElasticPMIDField' => '/classes/elastic-pubmed.class.php',
    'ElasticInterLexManager' => '/classes/elastic-interlex.class.php',
    'ElasticInterLexResults' => '/classes/elastic-interlex.class.php',
    'ElasticInterLexecord' => '/classes/elastic-interlex.class.php',
    'ElasticInterLexField' => '/classes/elastic-interlex.class.php',
    'DatasetUploadQueue' => '/classes/dataset-upload-queue.class.php',
    'RRIDRating' => '/classes/rrid-rating.class.php',
    'HistoryRecord'  => '/classes/history-record.class.php',
    'DatasetDoiKeyValues' => '/classes/dataset-doi-keyvalues.class.php',
    'DatasetDoiTextFields' => '/classes/dataset-doi-textfields.class.php',
    'ScicrunchLogs' => '/classes/scicrunch-logs.class.php',
);

spl_autoload_register(function($class) {
    if(isset($GLOBALS["class-map"][$class])) {
        require_once $GLOBALS["DOCUMENT_ROOT"] . $GLOBALS["class-map"][$class];
    }
});
?>
