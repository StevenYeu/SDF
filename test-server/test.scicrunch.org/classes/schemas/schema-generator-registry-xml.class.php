<?php

class SchemaGeneratorRegistryXML
{
    public static function isRegistry($xml)
    {
        if (empty($xml)) {
            return false;
        }
        if (empty($xml->result) || empty($xml->result->attributes())) {
            return false;
        }
        $nifId = (string)$xml->result->attributes()->nifId;
        if ($nifId != "nlx_144509-1") {
            return false;
        }
        return true;
    }

    public static function generate($xml)
    {
        if (!SchemaGeneratorRegistryXML::isRegistry($xml)) {
            return NULL;
        }
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $schemas = array();
        foreach ($xml->result->results->row as $row) {
            $dataFeedItem = new DataFeedItemSchema();
            foreach ($row->data as $data) {
                $name = (string) $data->name;
                $value = (string) $data->value;
                if (empty($value)) {
                    continue;
                }
                if ($name == 'Resource ID') {
                    $id = (string)simplexml_load_string($value);
                }
            }
            if (!empty($id)) {
                $url = AbstractSchema::buildResourceURL($protocol, $id);
                $referenceSchema = AbstractSchema::buildReferenceSchema($url);
                $dataFeedItem->itemSchema = $referenceSchema;
                $schemas[] = $dataFeedItem;
            }
        }
        return $schemas;
    }
}
