<?php

class SchemaGeneratorSources
{
    public static function getSourceRRID($source)
    {
        $rootID = SchemaGeneratorSources::getSourceRootID($source);
        $url = Connection::environment() . '/v1/federation/data/nlx_144509-1.xml?q=*&count=1&filter=original_id:' . $rootID;
        $xml = simplexml_load_file($url);
        $row = $xml->result->results->row;
        foreach ($row->data as $data) {
            $name = (string) $data->name;
            $value = (string) $data->value;
            if ($name == 'Resource ID') {
                $id = (string)simplexml_load_string($value);
            }
        }
        return $id;
    }

    public static function getSourceRootID($source)
    {
        $splits = explode('-', $source->nif);
        $rootID = join('-', array_slice($splits, 0, count($splits) - 1));
        return $rootID;
    }

    public static function generateDataFeed($source, $portal)
    {
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $nlxView = $source->nif;
        $id = SchemaGeneratorSources::getSourceRRID($source);
        if (!empty($id) && $id) {
            $referenceSchema = AbstractSchema::buildReferenceSchema(AbstractSchema::buildResourceURL($protocol, $id));
        }
        $dataFeed = new DataFeedSchema();
        $dataFeed->id = AbstractSchema::buildSourceTableView($protocol, $portal, $nlxView);
        if (!empty($referenceSchema)) {
            $dataFeed->isPartOfSchema[] = $referenceSchema;
        }
        return $dataFeed;
    }

    public static function genreatePropertyValueTable($table, $portal, $source)
    {
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $rtnval = array();
        $schemas = array();
        $referenceSchemas = array();
        foreach ($table as $row) {
            $dataFeedItem = new DataFeedItemSchema();

            $firstValue = reset($row);
            $dataFeedItem->name = $firstValue;

            $firstValue = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $firstValue);
            $firstValueXML = simplexml_load_string($firstValue);
            if ($firstValueXML) {
                if (!empty($firstValueXML['href'])) {
                    $dataFeedItem->mainEntityOfPage = (string)$firstValueXML['href'];
                }
                $dataFeedItem->name = (string)$firstValueXML;
            }

            $uuid = $row['v_uuid'];
            if (!empty($uuid)) {
                if (!empty($portal) && !empty($source)) {
                    // build a query
                    $queryURL = 
                        AbstractSchema::buildSourceTableViewQuery($protocol, $portal, $source, $uuid);
                    $dataFeedItem->id = $queryURL;
                }
            }
            $dataFeedItemReferenceSchema = AbstractSchema::buildReferenceSchema($dataFeedItem->id);

            foreach ($row as $name => $value) {
                if (empty($value)) {
                    continue;
                }
                if ($name == 'v_uuid') {
                    continue;
                }
                if ($name == 'Reference') {
                    if (preg_match('/PMID: ([0-9]+)/', $value, $pmid)){
                        $pmid = $pmid[1];
                        if (!is_numeric($pmid)) {
                            $pmid = NULL;
                        }
                    };
                    if (!empty($pmid)) {
                        if (isset($referenceSchemas[$pmid])) {
                            $referenceSchemas[$pmid]->mentionsSchema[] = $dataFeedItemReferenceSchema;
                        } else {
                            $pmidURL = AbstractSchema::buildPMIDURL($protocol, $pmid);
                            $reference = new CreativeWorkSchema();
                            $reference->id = $pmidURL;
                            $reference->mentionsSchema[] = $dataFeedItemReferenceSchema;
                            $referenceSchemas[$pmid] = $reference;
                        }
                    }
                    continue;
                }
                $propertyValueSchema = new PropertyValueSchema();
                $propertyValueSchema->name = $name;
                $propertyValueSchema->value = $value;
                $value = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $value);
                $valueXML = simplexml_load_string($value);
                if ($valueXML) {
                    if (!empty($valueXML['href'])) {
                        $propertyValueSchema->id = (string)$valueXML['href'];
                    }
                    $propertyValueSchema->value = (string)$valueXML;
                }
                $dataFeedItem->itemSchema[] = $propertyValueSchema;
            }

            $schemas[] = $dataFeedItem;
        }
        $rtnval['schemas'] = $schemas;
        $rtnval['schemas_extras'] = $referenceSchemas;
        return $rtnval;
    }

    public static function generateTableViewReference($source, $portal)
    {
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $nlxView = $source->nif;
        $referenceSchema = AbstractSchema::buildReferenceSchema(AbstractSchema::buildSourceTableView($protocol, $portal, $nlxView));
        return $referenceSchema;
    }

}
