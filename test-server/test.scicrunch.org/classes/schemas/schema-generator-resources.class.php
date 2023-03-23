<?php

class SchemaGeneratorResources
{
    private static $generateFunctionMapping = Array(
        "dataset" => "generateDataset",
        "data set" => "generateDataset",
        "database" => "generateDataset",
        "catalog" => "generateDataset",
        "data repository" => "generateDataset",
        "image repository" => "generateDataset",
        //////////////////////////////////////////
        "podcast" => "generateCreativeWork",
        //////////////////////////////////////////
        // "source code" => "generateSoftwareSourceCode",
        // "software" => "generateSoftwareApplication",
        "source code" => "generateCreativeWork",
        "software" => "generateCreativeWork",
        //////////////////////////////////////////
        "portal" => "generateWebsite",
        //////////////////////////////////////////
        "service" => "generateService",
        //////////////////////////////////////////
        "facility" => "generateOrganization",
        "institution" => "generateOrganization",
        "university" => "generateOrganization",
        "organization" => "generateOrganization",
        "*" => "generateThing"
    );

    private static function generateService($columns)
    {
        $schema = new ServiceSchema();
        $schema = self::completeService($columns, $schema);
        return $schema;
    }

    private static function generateOrganization($columns)
    {
        $schema = new OrganizationSchema();
        $schema = self::completeOrganization($columns, $schema);
        return $schema;
    }

    private static function generateDataset($columns)
    {
        $schema = new DatasetSchema();
        $schema = self::completeCreativeWork($columns, $schema);
        return $schema;
    }

    private static function generateSoftwareSourceCode($columns)
    {
        $schema = new SoftwareSourceCodeSchema();
        $schema = self::completeCreativeWork($columns, $schema);
        return $schema;
    }

    private static function generateSoftwareApplication($columns)
    {
        $schema = new SoftwareApplicationSchema();
        $schema = self::completeCreativeWork($columns, $schema);
        return $schema;
    }

    private static function generateWebsite($columns)
    {
        $schema = new WebsiteSchema();
        $schema = self::completeCreativeWork($columns, $schema);
        return $schema;
    }

    private static function generateCreativeWork($columns)
    {
        $schema = new CreativeWorkSchema();
        $schema = self::completeCreativeWork($columns, $schema);
        return $schema;
    }

    private static function generateThing($columns)
    {
        $schema = new ThingSchema();
        $schema = self::completeThing($columns, $schema);
        return $schema;
    }

    private static function completeThing($columns, $schema)
    {
        if ($schema instanceof ThingSchema) {
            $schema->name = (string)(simplexml_load_string($columns['Resource Name']));
            $schema->name = $schema->name ? $schema->name : $columns['Resource Name'];
            $schema->alternateName = array();
            $schema->alternateName[] = (string)(simplexml_load_string($columns['Resource ID']));
            $schema->alternateName = array_merge($schema->alternateName, explode(', ', (string)simplexml_load_string($columns['Alternate IDs'])));
            $schema->alternateName[] = $columns['proper_citation'];
            $schema->alternateName[] = $columns['abbreviation'];
            $schema->alternateName = array_merge($schema->alternateName, explode(', ', $columns['synonym']));
            $schema->alternateName = array_filter($schema->alternateName);
            $schema->description = $columns['Description'];
            $schema->mainEntityOfPage = $columns['url'];
        }
        return $schema;
    }

    private static function completeService($columns, $schema)
    {
        $schema = self::completeThing($columns, $schema);
        return $schema;
    }

    private static function completeOrganization($columns, $schema)
    {
        $schema = self::completeThing($columns, $schema);
        return $schema;
    }

    private static function completeCreativeWork($columns, $schema)
    {
        $schema = self::completeThing($columns, $schema);
        if ($schema instanceof CreativeWorkSchema) {
            if (!empty($columns['Keywords'])) {
                $schema->keywords = $columns['Keywords'];
            }
        }
        return $schema;
    }

    public static function generateRelationship($schema, $resourceID, $relationships)
    {
        $output = [];
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        foreach ($relationships as $relationship) {
            $fromResourceID = $relationship['id1'];
            $toResourceID = $relationship['id2'];
            switch ($relationship['forward']) {
                case 'has parent organization':
                case 'is affiliated with':
                    if ($resourceID == $fromResourceID) {
                        $referenceSchema = AbstractSchema::buildReferenceSchema(
                            AbstractSchema::buildResourceURL($protocol, $toResourceID));
                        if ($schema instanceof CreativeWorkSchema) {
                            $schema->sourceOrganizationSchema = $referenceSchema;
                        } else if ($schema instanceof OrganizationSchema) {
                            $schema->parentOrganizationSchema = $referenceSchema;
                        } else if ($schema instanceof ServiceSchema) {
                            $schema->providerSchema = $referenceSchema;
                        }
                        $output[] = $referenceSchema;
                    } else {
                        $referenceSchema = AbstractSchema::buildReferenceSchema(
                            AbstractSchema::buildResourceURL($protocol, $fromResourceID));
                        $output[] = $referenceSchema;
                    }
                    break;
                case 'lists':
                case 'uses':
                    if ($resourceID == $fromResourceID) {
                        $referenceSchema = AbstractSchema::buildReferenceSchema(
                            AbstractSchema::buildResourceURL($protocol, $toResourceID));
                        if ($schema instanceof CreativeWorkSchema) {
                            $schema->hasPartSchema[] = $referenceSchema;
                        }
                        $output[] = $referenceSchema;
                    } else {
                        $referenceSchema = AbstractSchema::buildReferenceSchema(
                            AbstractSchema::buildResourceURL($protocol, $fromResourceID));
                        if ($schema instanceof CreativeWorkSchema) {
                            $schema->isPartOfSchema[] = $referenceSchema;
                        }
                        $output[] = $referenceSchema;
                    }
                default:
                    break;
            }
        }
        return $output;
    }

    public static function generate($columns)
    {
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $type = strtolower((string)$columns['resource_type']);

        $schemaResults = array();

        foreach (self::$generateFunctionMapping
            as $keyword => $functionName)
        {
            if (strpos($type, $keyword) || $keyword == "*") {
                $resourceSchema = self::$functionName($columns);
                break;
            }
        }

        $resourceID = (string)(simplexml_load_string($columns['Resource ID']));
        $resourceID = $resourceID ? $resourceID : $columns['Resource ID'];
        $resourceSchema->id = AbstractSchema::buildResourceURL($protocol, $resourceID);
        $resourceReferenceSchema = AbstractSchema::buildReferenceSchema($resourceSchema->id);

        // get the relationships from database
        $convertedID = \helper\getIDFromRID($resourceID);
        require_once $_SERVER[DOCUMENT_ROOT] . '/classes/resource-relationship.class.php';
        $relationships = array();
        $relationshipResult = ResourceRelationship::loadByID($convertedID, 0, 10, false);
        foreach($relationshipResult as $relationshipEntry){
            $relationshipString = ResourceRelationship::lookUpRelationshipString($relationshipEntry->getRelTypeID());
            $relationships[] = Array(
                        "id1" => $relationshipEntry->getID1(),
                        "id2" => $relationshipEntry->getID2(),
                        "forward" => $relationshipString["forward"],
                        "reverse" => $relationshipString["reverse"],
                    );
        }
        $schemaResults = array_merge($schemaResults, self::generateRelationship($resourceSchema, $resourceID, $relationships));

        $pmidXml = (string)simplexml_load_string($columns["Mentioned In Literature"]);
        $pmids = preg_split('/\D/', $pmidXml, NULL, PREG_SPLIT_NO_EMPTY);
        $publicationSchemas = array();
        foreach ($pmids as $pmid) {
            $schema = new CreativeWorkSchema();
            $schema->id = AbstractSchema::buildPMIDURL($protocol, $pmid);
            $schema->mentionsSchema[] = $resourceReferenceSchema;
            $publicationSchemas[] = $schema;
        }
        $schemaResults = array_merge($schemaResults, $publicationSchemas);
        // echo $resourceSchema->generateJSON();
        // var_dump($resourceSchema);
        $schemaResults[] = $resourceSchema;

        return $schemaResults;
    }
}
