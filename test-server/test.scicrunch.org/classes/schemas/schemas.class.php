<?php

define('__SCHEMAS_ROOT__', dirname(dirname(dirname(__FILE__))).'/classes/schemas' );

require_once __DIR__ . '/abstract-schema.class.php';
require_once __DIR__ . '/id-schema.class.php';

require_once __DIR__ . '/thing-schema.class.php';
require_once __DIR__ . '/intangible-schema.class.php';
require_once __DIR__ . '/service-schema.class.php';
require_once __DIR__ . '/datafeeditem-schema.class.php';
require_once __DIR__ . '/structuredvalue-schema.class.php';
require_once __DIR__ . '/propertyvalue-schema.class.php';

require_once __DIR__ . '/person-schema.class.php';

require_once __DIR__ . '/creativework-schema.class.php';
require_once __DIR__ . '/softwareapplication-schema.class.php';
require_once __DIR__ . '/softwaresourcecode-schema.class.php';
require_once __DIR__ . '/website-schema.class.php';

require_once __DIR__ . '/dataset-schema.class.php';
require_once __DIR__ . '/datafeed-schema.class.php';
require_once __DIR__ . '/scholarlyarticle-schema.class.php';
require_once __DIR__ . '/periodical-schema.class.php';

require_once __DIR__ . '/organization-schema.class.php';
require_once __DIR__ . '/literature-search-schema.class.php';

require_once __DIR__ . '/schema-generator-publication-xml.class.php';

require_once __DIR__ . '/schema-generator-resources.class.php';
require_once __DIR__ . '/schema-generator-sources.class.php';
require_once __DIR__ . '/schema-generator-registry-xml.class.php';
require_once __DIR__ . '/schema-generator-term.class.php';
require_once __DIR__ . '/schema-generator-literature-search.class.php';

?>
