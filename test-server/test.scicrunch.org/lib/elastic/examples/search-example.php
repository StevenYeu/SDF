<?php
require 'vendor/autoload.php';

$hosts = ['http://localhost:9200'];

$client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

/*
GET scicrunch/term/_search
{
  "query": {
    "bool": {
      "must": {
        "query_string": {
          "default_field": "_all",
          "query": "american cockroach"
        }
      }
    }
  }
}
*/

$param['index'] = 'scicrunch';
$param['type'] = 'term';
$param['body']['query']['bool']['must']['query_string']['default_field'] = "_all";
$param['body']['query']['bool']['must']['query_string']['query'] = "american cockroach";
$return = $client->search($param);
//print_r($return);
print json_encode($return);

?>
