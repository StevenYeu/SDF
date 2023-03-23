<?php
require_once __DIR__ . '/../lib/elastic/vendor/autoload.php';
use Elasticsearch;
use Elasticsearch\ClientBuilder;

class ResourceElasticsearch extends Connection {
    private $index = 'scicrunch';
    private $type = 'resource';
    private $client = null;
    private $types = array();
    private $relationship_types = array();


    public function __construct(){
        $this->connect();
        $this->client = Elasticsearch\ClientBuilder::create()->setHosts($config['elastichosts'])->build();

    }

    public function __destruct() {
        $this->close();
    }

    public function upsert($rid) {
        if (strstr($rid, 'SRC_')) { return null; }

        $return = $this->select('resources', array('*'), 's', array($rid), 'where rid=? limit 1');

        $body = array();
        if (count($return) > 0) {
            $row = $return[0];

            $body['rid'] = $rid;
            $body['original_id'] = $row['original_id'];
            $body['type'] = $this->types[$row['typeID']];

            $last_version = 0;
            $return = $this->select('resource_versions', array('*'), 'i', array($row['id']), 'where rid=?');
            if (count($return) > 0) {
                foreach ($return as $row2) {
                    //print_r($row2);
                    if ($row2['version'] > $last_version && $row2['status'] == 'Curated'){
                        $last_version = $row2['version'];
                    }
                }
            }
            //skip if not curated
            if ($last_version == 0){
                return null;
            }

            $this->getTypes();
            $this->getRelationshipTypes();

            //get field data
            $return = $this->select('resource_columns', array('*'), 'ii', array($row['id'], $last_version), 'where rid=? and version=?');
            if (count($return) > 0) {
                foreach ($return as $row3) {
                    if (strlen(trim($row3['value'])) > 0){
                        $body[$row3['name']] = $row3['value'];
                    }
                }
            }

            //get relationship data
            $return = $this->select('resource_relationships', array('*'), 'ss', array($rid, $rid), 'where id1=? or id2=?');
            if (count($return) > 0) {
                $relationships = array();
                foreach ($return as $row4) {
                    if ($rid == $row4['id1']) {
                        $relationships[] = array('id'=>$row4['id1'], 'relationship'=>$this->relationship_types[$row4['reltype_id']]['forward'], 'id2'=>$row4['id2']);
                    } else {
                        $relationships[] = array('id'=>$row4['id2'], 'relationship'=>$this->relationship_types[$row4['reltype_id']]['reverse'], 'id2'=>$row4['id1']);
                    }
                }
                $body['relationships'] = $relationships;
            }

            print_r($body);

//             $param['index'] = $this->index;
//             $param['type'] = $this->type;
//             $param['id'] = $rid;
//             $param['body'] = $body;

//             $response = array();
//             try {
//                 $response = $this->client->index($param);
//             } catch (Exception $e) {
//                 print_r($e);
//             }

//             return $response;

        }

    }

    public function getTypes() {
        $return = $this->select('resource_type', array('*'), null, array(), '');

        $finalArray = array();
        if (count($return) > 0) {
            foreach ($return as $row) {
                $finalArray[$row['id']] = $row['name'];
            }
        }

        $this->types = $finalArray;
    }

    public function getRelationshipTypes() {
        $return = $this->select('resource_relationship_strings', array('*'), null, array(), '');

        $finalArray = array();
        if (count($return) > 0) {
            foreach ($return as $row) {
                $finalArray[$row['id']] = array('forward'=>$row['forward'], 'reverse'=>$row['reverse']);
            }
        }

        $this->relationship_types = $finalArray;
    }

}
?>
