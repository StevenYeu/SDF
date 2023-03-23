<?php

class DbObj extends Connection {
    public function __construct(){
        $this->connect();
    }

    public function __destruct() {
        $this->close();
    }

    /*
     * Removes white spaces from list of arrays where the field is a string
     *
     * arrays (list of arrays): Single entity extra field at a time.
     *
     */
    static public function trimFields($arrays) {
        foreach ($arrays as &$array) {
            foreach ($array as $key => &$value) {  // ampersand to alter memory in-place
                if (!is_numeric($value))
                    $array[$key] = trim($value, 'U');
            }
        }
    }

    static public function getCuratorById($uid) {
        $user = 'machine';
        if ( $uid != 0 ) {
            $u = new User();
            $u->getByID($uid);
            $user = $u->firstname . " " . $u->lastname;
        }
        return $user;
    }

    static public function termForExport($termObj){
        $term = array();

        foreach($termObj as $key => $val) {
            if(!is_array($val)  && in_array($key, Term::$export_fields)) {
                $term[$key] = $val;
            }
        }

        $synonyms = array();
        foreach ($termObj['synonyms'] as $syn) {
            $syn2 = array();
            foreach ($syn as $key => $val){
                if (in_array($key, TermSynonym::$export_fields)){
                    $syn2[$key] = $val;
                }
            }
            $synonyms[] = $syn2;
        }
        $term["synonyms"] = $synonyms;

        $existing_ids = array();
        foreach ($termObj['existing_ids'] as $eid) {
            $eid2 = array();
            foreach ($eid as $key => $val){
                if (in_array($key, TermExistingId::$export_fields)){
                    $eid2[$key] = $val;
                }
            }
            $existing_ids[] = $eid2;
        }
        $term["existing_ids"] = $existing_ids;

        $superclasses = array();
        foreach ($termObj['superclasses'] as $sup) {
            $sup2 = array();
            foreach ($sup as $key => $val){
                if (in_array($key, TermSuperclass::$export_fields)){
                    $sup2[$key] = $val;
                }
            }
            $superclasses[] = $sup2;
        }
        $term["superclasses"] = $superclasses;

        $relationships = array();
        foreach ($termObj['relationships'] as $rel) {
            $rel2 = array();
            foreach ($rel as $key => $val){
                if (in_array($key, TermRelationship::$export_fields)){
                    $rel2[$key] = $val;
                }
            }
            $relationships[] = $rel2;
        }
        $term["relationships"] = $relationships;

        $annotations = array();
        foreach ($termObj['annotations'] as $ann) {
            $ann2 = array();
            foreach ($ann as $key => $val){
                if (in_array($key, TermAnnotation::$export_fields)){
                    $ann2[$key] = $val;
                }
            }
            $annotations[] = $ann2;
        }
        $term["annotations"] = $annotations;

        $ontologies = array();
        foreach ($termObj['ontologies'] as $ont) {
            $ont2 = array();
            foreach ($ont as $key => $val){
                if (in_array($key, TermOntology::$export_fields)){
                    $ont2[$key] = $val;
                }
            }
            $ontologies[] = $ont2;
        }
        $term['ontologies']= $ontologies;

        return $term;

    }

    static public function termForElasticSearch($termObj, $mem_efficient = false){
        $term = array();
        foreach($termObj as $key => $val) {
            if(!is_array($val)  && in_array($key, Term::$elasticsearch_fields)) {
                $term[$key] = utf8_encode(stripslashes($val));
            }
        }

        $synonyms = array();
        if($mem_efficient) $termObj->getSynonyms();
        foreach ($termObj->synonyms as $syn) {
            $syn2 = array();
            foreach ($syn as $key => $val){
                if (in_array($key, TermSynonym::$elasticsearch_fields)){
                    $syn2[$key] = utf8_encode(stripslashes($val));
                }
            }
            $synonyms[] = $syn2;
        }
        if($mem_efficient) $termObj->synonyms = Array();
        $term["synonyms"] = $synonyms;

        $existing_ids = array();
        if($mem_efficient) $termObj->getExistingIds();
        foreach ($termObj->existing_ids as $eid) {
            $eid2 = array();
            foreach ($eid as $key => $val){
                if (in_array($key, TermExistingId::$elasticsearch_fields)){
                    $eid2[$key] = utf8_encode(stripslashes($val));
                }
            }
            $existing_ids[] = $eid2;
        }
        if($mem_efficient) $termObj->existing_ids = Array();
        $term["existing_ids"] = $existing_ids;

        $superclasses = array();
        if($mem_efficient) $termObj->getSuperclasses();
        foreach ($termObj->superclasses as $sup) {
            $sup2 = array();
            foreach ($sup as $key => $val){
                if (in_array($key, TermSuperclass::$elasticsearch_fields)){
                    $sup2[$key] = utf8_encode(stripslashes($val));
                }
            }
            $superclasses[] = $sup2;
        }
        if($mem_efficient) $termObj->superclasses = Array();
        $term["superclasses"] = $superclasses;

        $ancestors = array();
        if($mem_efficient) $termObj->getAncestors();
        foreach ($termObj->ancestors as $ans1) {
            //considering multiple parents
            foreach ($ans1 as $ans){
                if (strlen($ans['parent_ilx']) > 0){
                    $ans2 = array();
                    $ans2['ilx'] = utf8_encode($ans['parent_ilx']);
                    $ans2['label'] = utf8_encode($ans['parent_label']);

                    $ancestors[] = $ans2;
                }
            }
        }
        if($mem_efficient) $termObj->ancestors = Array();
        $term["ancestors"] = $ancestors;

        $relationships = array();
        if($mem_efficient) $termObj->getRelationships();
        foreach ($termObj->relationships as $rel) {
            if($rel['withdrawn'] != '0')
                continue;
            $rel2 = array();
            foreach ($rel as $key => $val){
                if (in_array($key, TermRelationship::$elasticsearch_fields)){
                    $rel2[$key] = utf8_encode(stripslashes($val));
                }
            }
            $relationships[] = $rel2;
        }
        if($mem_efficient) $termObj->relationships = Array();
        $term["relationships"] = $relationships;

        $annotations = array();
        if($mem_efficient) $termObj->getAnnotations();
        foreach ($termObj->annotations as $ann) {
            if($ann['withdrawn'] != '0')
                continue;
            $ann2 = array();
            foreach ($ann as $key => $val){
                if (in_array($key, TermAnnotation::$elasticsearch_fields)){
                    $ann2[$key] = utf8_encode(stripslashes($val));
                }
            }
            $annotations[] = $ann2;
        }
        if($mem_efficient) $termObj->annotations = Array();
        $term["annotations"] = $annotations;

        $ontologies = array();
        if($mem_efficient) $termObj->getOntologies();
        foreach ($termObj->ontologies as $ont) {
            $ont2 = array();
            foreach ($ont as $key => $val){
                if (in_array($key, TermOntology::$elasticsearch_fields)){
                    $ont2[$key] = utf8_encode(stripslashes($val));
                }
            }
            $ontologies[] = $ont2;
        }
        if($mem_efficient) $termObj->ontologies = Array();
        $term['ontologies']= $ontologies;

        return $term;
    }

    static public function printableTerm ($term) {
        $synonyms = Array();
        if(sizeof($term->synonyms) > 0){
            foreach($term->synonyms as $column){
                $synonyms[] = DbObj::toArray('TermSynonym', $column);
            }
        }

        $superclasses = Array();
        if(sizeof($term->superclasses) > 0){
            foreach($term->superclasses as $column){
                $superclasses[] = $column;
            }
        }

        $existing_ids = Array();
        if(sizeof($term->existing_ids) > 0){
            foreach($term->existing_ids as $column){
                $existing_ids[] = DbObj::toArray('TermExistingId', $column);
            }
        }

        $relationships = Array();
        if(sizeof($term->relationships) > 0){
            foreach($term->relationships as $column){
                $relationships[] = $column;
            }
        }

        $annotations = Array();
        if(sizeof($term->annotations) > 0){
            foreach($term->annotations as $column){
                $annotations[] = $column;
            }
        }

        $mappings = Array();
        if(sizeof($term->mappings) > 0){
            foreach($term->mappings as $column){
                $mappings[] = $column;
            }
        }

        $ontologies = Array();
        if(sizeof($term->ontologies) > 0){
            foreach($term->ontologies as $column){
                $ontologies[] = $column;
            }
        }

        $return_values = Array();
        foreach(Term::$properties as $name){
            $return_values[$name] = $term->$name;
        }

        $return_values['synonyms'] = $synonyms;
        $return_values['superclasses'] = $superclasses;
        $return_values['existing_ids'] = $existing_ids;
        $return_values['relationships'] = $relationships;
        $return_values['mappings'] = $mappings;
        $return_values['annotations'] = $annotations;
        if ($term->type == 'annotation'){
            $return_values['annotation_type'] = $term->annotation_type;
        }
        $return_values['ontologies'] = $ontologies;

        return $return_values;
    }

    static public function verifyUserCommunity($dbObj, $uid, $cid) {
        //print 'uid:' . $uid . " cid:" . $cid . " ";
        $return = array('user_ok'=>false,'community_ok'=>false,'level_ok'=>false);
        if (!isset($uid) || !isset($cid)) {
            if (isset($uid)) $return['user_ok'] = true;
            return $return;
        }

        $result = $dbObj->select('users u, community_access c', array('*'), 'si', array($uid), 'where u.guid = ' .$uid . ' and u.guid = c.uid');
        if (count($result) > 0) {
            $return['user_ok'] = true;
            foreach ($result as $row) {
                //print_r($row);
                if ($row['cid'] == $cid) {
                    $return['community_ok'] = true;
                    $return['level_ok'] = true;
                    break;
                }
            }
        }
        return $return;
    }

    static public function toArray($class, $object) {
        $array = (array) $object;

        $return = array();
        foreach ($array as $name => $value) {
            if (in_array($name, $class::$properties)) {
                $return[$name] = stripslashes($value);
            }
        }

        return $return;
    }

    static public function createArrayFromRow($class, $vars) {
        $array = array();
        foreach ($vars as $name => $value) {
            if (in_array($name, $class::$properties)) {
                $array[$name] = stripslashes($value);
            }
        }
        return $array;
    }

    /*
     * Filters for only accepted fields
     *
     * args (array of list of arrays) : Entities extra metadata fields not in the base terms table.
     */
    static public function verifyRalatedTables ($args) {
        $superclasses = array();
        $synonyms = array();
        $existing_ids = array();
        $ontologies = array();
        $relationships = array();
        $annotations = array();
        $mappings = array();
        foreach( $args as $field => $val ) {
            if ( in_array($field, Term::$properties) )
                continue;
            if ( in_array($field, array_keys( Term::$related_map)) ) {
                $class = Term::$related_map[$field];
                foreach ($val as $arr) {
                    $arr2 = array();
                    foreach ($arr as $k=>$v) {
                        if (in_array($k, $class::$properties)) {
                            if (!is_numeric($v)) $v = trim($v);
                            $arr2[$k] = $v;
                        }
                        // else { return array("status"=>false,"msg"=>"unknown field: " . $field . "." . $k); }
                    }
                    switch ($field) {
                        case 'synonyms':
                            DbObj::trimFields($arr2);
                            $synonyms[] = $arr2;
                            break;
                        case 'superclasses':
                            DbObj::trimFields($arr2);
                            $superclasses[] = $arr2;
                            break;
                        case 'existing_ids':
                            DbObj::trimFields($arr2);
                            $existing_ids[] = $arr2;
                            break;
                        case 'ontologies':
                            $ontologies[] = $arr2;
                            break;
                        case 'relationships':
                            DbObj::trimFields($arr2);
                            $relationships[] = $arr2;
                            break;
                        case 'annotations':
                            DbObj::trimFields($arr2);
                            $annotations[] = $arr2;
                            break;
                            // TODO make sure below works
                        case 'annotation_type':
                            $annotation_type = $v;
                            break;
                        case 'mappings':
                            $mappings[] = $arr2;
                            break;
                    }
                }
            } else {
                return array("status"=>false,"msg"=>"unknown field: " . $field);
            }
        }
        return array('status'=>true,'synonyms'=>$synonyms,'superclasses'=>$superclasses,'existing_ids'=>$existing_ids,
            'relationships'=>$relationships,'annotations'=>$annotations,'ontologies'=>$ontologies,
            'annotation_type'=>$annotation_type,'mappings'=>$mappings);
    }
}

class Term {
    public $id;
    public $ilx;
    public $curie;
    public $orig_uid;
    public $orig_cid;
    public $uid;
    public $cid;
    public $label;
    public $definition;
    public $comment;
    public $version;
    public $orig_time;
    public $time;
    public $type;
    public $status;
    public $display_superclass;
    public $var_fields = array();
    public $synonyms = array();
    public $superclasses = array();
    public $ancestors = array();
    public $existing_ids = array();
    public $ontologies = array();
    public $relationships = array();
    public $annotations = array();
    public $communities = array();
    public $mappings = array();
    public $annotation_type;
    static public $related_map = array('superclasses'=>'TermSuperclass', 'synonyms'=>'TermSynonym', 'existing_ids'=>'TermExistingId',
        'relationships'=>'TermRelationship', 'annotations'=>'TermAnnotation', 'ontologies'=>'TermOntology',
        'annotation_type'=>'TermAnnotationType', 'mappings'=>'TermMapping');
    static public $properties = array('id','orig_uid','uid','orig_cid','cid','ilx', 'curie', 'label','type','definition','comment','version','status','display_superclass','orig_time','time');
    public $dbObj;
    static public $required = array('label','ilx');
    static public $elasticsearch_fields = array('ilx','label','type','definition','comment','status');
    static public $export_fields = array('ilx','label','definition','comment','version','type');
    /**
     * @var string
     */

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, Term::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    static public function createFromRow2($vars) {
        $array = array();
        foreach ($vars as $name => $value) {
            if (in_array($name, Term::$properties)) {
                $array[$name] = stripslashes($value);
            }
        }
        return $array;
    }

    public function getByIlx($ilx) {
        $result = $this->dbObj->select('terms', array('*'), 's', array($ilx), 'where ilx=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

    public function getTidByCurie($curie) {
        $tid = 0;
        $result = $this->dbObj->select('term_existing_ids', array('tid'), 's', array($curie), 'where curie=? limit 1');

        if (count($result) > 0) {
            $tid = $result[0]['tid'];
        }
        return $tid;
    }

    public function getById($id) {
        $result = $this->dbObj->select('terms', array('*'), 's', array($id), 'where id=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

    public function getByLabel($label) {
        //$label = $this->dbObj->mysqli->escape_string($label);
        $results = $this->dbObj->select('terms', array('*'), 's', array($label), 'where label=? limit 1');
        if (count($results) > 0) {
            $this->createFromRow($results[0]);
        }
    }

    static public function getByLabelUid($dbObj, $label, $uid) {
        $terms = array();
        //$label = $dbObj->mysqli->escape_string($label);
        $results = $dbObj->select('terms', array('*'), 'si', array($label, $uid), 'where label=? and orig_uid=?');
        if (count($results) > 0) {
            foreach ($results as $result) {
                //print_r($result);
                $terms[] = Term::createFromRow2($result);
            }
        }
        return $terms;
    }

    // used for bulk upload only. elastic is used otherwise.
    static public function getMatches($dbObj, $label) {
        $terms = array();

        $sql = "select * from terms where LOWER(label) = 'LOWER(" . $label . ")'";
        //print "\n" . $sql . "\n";
        $result = $dbObj->mysqli->query($sql);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $terms[] = $row;
            }
        }

        return $terms;
    }

    public function insertDB() {
        $this->time = time();
        $this->id = $this->dbObj->insert('terms', '', array());

        $array = get_object_vars($this);
        $types = '';
        $columns = array();
        $params = array();
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if (in_array($field, Term::$properties) && $field != 'id' && !empty($value)) {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('terms', $types, $columns, $params, 'where id=?');
    }

    public function updateRowDB() {
        $types = '';
        $columns = array();
        $params = array();
        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if ( $field !== 'id' && in_array($field, Term::$properties) && !in_array($field,array_keys(Term::$related_map)) && !empty($value)) {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('terms', $types, $columns, $params, 'where id=?');
    }

    public function updateDB($field, $value) {
        if (in_array($field, Term::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('terms', 'si', array($field), array("$value",$this->id), 'where id=?');
        }
    }

    public function joinToOntology($ont_id) {
        if ($ont_id > 0) {
            $sql = "insert into term_ontology_join (tid, ontology_id) values (" . $this->id . ", " . $ont_id . ")";
            //print $sql . "\n";
            $this->dbObj->mysqli->query($sql);
        }
    }

    public function getOntologies () {
        $sql = "select distinct o.id, o.url from term_ontologies o, term_ontology_join j where j.tid = " . $this->id . " and j.ontology_id = o.id";
        //print "\n" . $sql . "\n";
        $columns = array();
        $result = $this->dbObj->mysqli->query($sql);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $columns[] = $row;
                $this->ontologies[] = $row;
            }
        }

        return $columns;
    }

    public function disjoinFromOntologies () {
        $sql = "delete from term_ontology_join where tid = " . $this->id;
        //print $sql . "\n";
        $this->dbObj->mysqli->query($sql);
    }

    static public function exists($dbObj, $term_id) {
        $result = $dbObj->select('terms', array('*'), 'i', array($term_id), 'where id=?');

        $columns = array();
        if (count($result) > 0) {
            return true;
        }

        return false;
    }

    static public function ilxExists($dbObj, $ilx_id) {
        $result = $dbObj->select('terms', array('*'), 's', array($ilx_id), 'where ilx=? limit 1');
        if (count($result) > 0)
            return true;
        return false;
    }

    public function getAnnotationType() {
        $sql = "select type from term_annotation_types where annotation_tid = " . $this->id . " limit 1";
        //print "\n" . $sql . "\n";

        $result = $this->dbObj->mysqli->query($sql);
        if (count($result) > 0) {
            foreach ($result as $row) {
                $this->annotation_type = $row['type'];
            }
        }
    }

    public function updateAnnotationType($type){
        $sql = "update term_annotation_types set type = '" . $type . "' where annotation_tid = " . $this->id;
        //print($sql);
        $this->dbObj->mysqli->query($sql);
    }

    public function getRelationships() {
        $cxn = new Connection();
        $cxn->connect();
        $sql = "
            SELECT CONCAT(UPPER(substring_index(t1.ilx, '_', 1)), ':', substring_index(t1.ilx, '_', -1)) as term1_curie, tr.term1_id, t1.ilx as term1_ilx, t1.label as term1_label, t1.type as term1_type, t1.definition as term1_definition, t1.version as term1_version,
                   CONCAT(UPPER(substring_index(r.ilx, '_', 1)), ':', substring_index(r.ilx, '_', -1)) as relationship_term_curie, tr.relationship_tid as relationship_term_id, r.ilx as relationship_term_ilx, r.label as relationship_term_label, r.type as relationship_term_type, r.definition as relationship_term_definition, r.version as relationship_term_version,
                   CONCAT(UPPER(substring_index(t2.ilx, '_', 1)), ':', substring_index(t2.ilx, '_', -1)) as term2_curie, tr.term2_id, t2.ilx as term2_ilx, t2.label as term2_label, t2.type as term2_type, t2.definition as term2_definition, t2.version as term2_version,
                   tr.*
            FROM term_relationships tr
            join (select * from terms where terms.status >= 0) t1 on t1.id = term1_id
            join (select * from terms where terms.status >= 0) t2 on t2.id = term2_id
            join (select * from terms where terms.status >= 0) r on r.id = relationship_tid
            WHERE term1_id=? OR relationship_tid=? OR term2_id=? limit 1000";
        $this->relationships = $cxn->selectFull('iii', array($this->id, $this->id, $this->id), $sql);
        $cxn->close();
    }

    public function getAnnotations() {
        $cxn = new Connection();
        $cxn->connect();
        $sql = "
            SELECT CONCAT(UPPER(substring_index(t.ilx, '_', 1)), ':', substring_index(t.ilx, '_', -1)) as term_curie, ta.tid as term_id, t.ilx as term_ilx, t.label as term_label, t.type as term_type, t.definition as term_definition, t.version as term_version,
                   CONCAT(UPPER(substring_index(a.ilx, '_', 1)), ':', substring_index(a.ilx, '_', -1)) as annotation_term_curie, ta.annotation_tid as annotation_term_id, a.ilx as annotation_term_ilx, a.label as annotation_term_label, a.type as annotation_term_type, a.definition as annotation_term_definition, a.version as annotation_term_version,
                   ta.*
            FROM term_annotations as  ta 
            join (select * from terms where terms.status >= 0) AS t on t.id = ta.tid 
            join (select * from terms where terms.status >= 0) AS a on a.id = ta.annotation_tid
            where ta.tid=? or ta.annotation_tid=? limit 1000;";
        $this->annotations = $cxn->selectFull('ii', array($this->id, $this->id), $sql);
        $cxn->close();
    }

    public function getCommunities(){
        $results = $this->dbObj->select('term_communities', array('*'), 'i', array($this->id), 'where tid=?');

        $communities = array();
        if (count($results) > 0) {
            foreach ($results as $result){
                $communities[] = $result;
            }
        }
        $this->communities = $communities;
    }

    public function getMappings($tmid) {
        $sql = "select * from term_mappings where id =" . $tmid . " order by source, view_name, column_name, value";

        $mappings = array();
        if ($result = $this->dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                //print_r($row);
                if ($row['status'] < 0) { continue; }

                if ($row['id'] == $tmid) {
                    $sql2 = "select * from term_mapping_logs where tmid = " . $row['id'] . " order by id desc";
                    $logs = array();
                    if ($result2 = $this->dbObj->mysqli->query($sql2)) {
                        while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                            $row2['curator'] = DbObj::getCuratorById($row2['uid']);
                            $logs[] = $row2;
                        }
                    }
                    $row['curation_logs'] = $logs;
                }

                $mappings[] = $row;
                if($row['concept_id'] != "") {
                    $ilx = explode(":", $row['concept_id']);
                    $this->ilx = strtolower($ilx[0])."_".$ilx[1];
                }
            }
        }

        $this->mappings = $mappings;
        //print_r($this);
    }

    public function getExistingIds() {
        $result = $this->dbObj->select('term_existing_ids', array('*'), 'i', array($this->id), 'where tid=? order by id asc');

        $columns = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $column = new TermExistingId($this->dbObj);
                $column->createFromRow($row);
                $columns[] = $column;
            }
        }
        $this->existing_ids = $columns;
    }

    public function getSynonyms() {
        $result = $this->dbObj->select('term_synonyms', array('*'), 'i', array($this->id), 'where tid=? order by id asc');

        $columns = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $column = new TermSynonym($this->dbObj);
                $column->createFromRow($row);
                $columns[] = $column;
            }
        }
        $this->synonyms = $columns;
    }

    // assuming multiple parents, but return only one..
    public function getAncestors(){
        $parents = array();

        $first_parents = Term::getTermParent($this->dbObj, $this->id);
        for ($j=0; $j<sizeof($first_parents); $j++) {
            $parents['parent'.$j][] = $first_parents[$j];
            $id = $first_parents[$j]['parent_tid'];
            for ($i=0; $i<50; $i++) {
                $ps = Term::getTermParent($this->dbObj, $id);
                //print_r($parent[0]);
                $id = $ps[0]['parent_tid'];
                if ($ps[0] != null) {
                    $parents['parent'.$j][] = $ps[0];
                }
            }
        }
        $this->ancestors = $parents;
    }

    public function getSuperclasses() {
        //select * from terms where id in (select distinct `superclass_tid` from term_superclasses where tid = 63)
        //$result = $this->dbObj->select('terms', array('id', 'ilx', 'label', 'definition'), 'i', array($this->id), 'where id in (select distinct superclass_tid from term_superclasses where tid=?)');

        $result1 = $this->dbObj->select('term_superclasses', array('superclass_tid', 'tid'), 'i', array($this->id), 'where tid =?');
        $superclass_tids = array();
        if (count($result1) > 0) {
            foreach ($result1 as $row1) {
                $superclass_tids[] = $row1['superclass_tid'];
            }

        }

        $stid_str = implode(",", $superclass_tids);
        //print $stid_str . "\n";
        $columns = array();
        $sql = "select id, ilx, label, definition, status from terms where id in (" . $stid_str . ")";
        if ($result = $this->dbObj->mysqli->query($sql)) {
            foreach ($result as $row) {
                if ($row['status'] < 0) { continue; }

                $columns[] = $row;
            }
        }

        //print_r($columns);
        $this->superclasses = $columns;
    }

    public function getTermList() {
        $result = $this->dbObj->select('terms', array('*'));

        $rows = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                if ($row['status'] < 0) { continue; }

                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function getTermListByType($type = "term") {
        $result = $this->dbObj->select('terms', array('*'), 's', array($type), "where type =?");

        $rows = array();
        if (count($result) > 0) {
            foreach ($result as $row) {
                if ($row['status'] < 0) { continue; }

                $rows[] = $row;
            }
        }
        return $rows;
    }

    static public function getAnnotationTermList($dbObj){

        $sql = "select t.id, t.ilx, t.label, t.version, t.definition, t.type as type, tt.type as annotation_type from terms t, term_annotation_types tt where t.id = tt.annotation_tid";
        //print $sql . "\n";
         $columns = array();
        if ($result = $dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                if ($row['type'] != 'annotation') { continue; }

                $columns[] = $row;
            }
        }
        return $columns;
    }

    public function searchTerm($term) {
        $term = trim($term);

        $rows = array();
        $map = array();
        $ids = array();

        //find matching from terms, synonyms, and existing ids
        //find term ids for above, then select those terms
        $sql = <<<EOT
        select id, 'term' from terms WHERE INSTR(label, '$term') > 0 or instr(definition, '$term')
        union
        select tid, 'synonym'  from term_synonyms where instr(literal, '$term') > 0
        union
        select tid, 'existing id'  from term_existing_ids where instr(curie, '$term') > 0 or instr(iri, '$term') > 0
        LIMIT 200
EOT;

        if ($result = $this->dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_NUM)) {
                $map[$row[0]] = $row[1];
                $ids[] = $row[0];
            }
        }
        $id_str = implode(",",$ids);
        $sql = "SELECT id, label, ilx, definition FROM terms WHERE id IN ($id_str)";

        if ($result = $this->dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                if ($row['status'] < 0) { continue; }

                $row['type'] = $map[$row['id']];
                $row['definition'] = stripslashes($row['definition']);
                $rows[] = $row;
            }
        }

        return $rows;
    }

    static public function getTermTypeCounts(){
        $dbObj = new DbObj();

        $sql = "SELECT type,COUNT(*) as count FROM terms GROUP BY type ORDER BY count DESC";
        $types = array();
        if ($result = $dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                //print_r($row);
                $types[] = $row;
            }
        }

        return $types;
    }

    static public function getTermParent($dbObj, $term_id) {
        $sql = "select s.tid as term_id, s.superclass_tid as parent_tid, t.ilx as parent_ilx, t.label as parent_label, t.definition as parent_definition, t.status as parent_status, t.display_superclass as parent_display " .
            "from term_superclasses s, terms t where s.tid = " . $term_id . " and s.superclass_tid = t.id";
        $results = array();
        if ($result = $dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                //print_r($row);
                if ($row['parent_status'] < 0) { break; }
                $results[] = $row;
            }
        }
        return $results;
    }

    static public function getTermChildren($dbObj, $parent_ilx) {
        //get parent_id first
        $parent_tid = 0;
        $sql1 = "select id from terms where ilx='" . $parent_ilx . "'";
        if ($result1 = $dbObj->mysqli->query($sql1)) {
            $row1 = $result1->fetch_array(MYSQLI_ASSOC);
            $parent_tid = $row1['id'];
        }
        if ($parent_tid == 0){
            return null;
        }

        $sql = "select s.superclass_tid as parent_tid, t.status as status, t.id as id, t.ilx as ilx, t.label as label, t.definition as definition  " .
            "from term_superclasses s, terms t where s.superclass_tid = " . $parent_tid . " and s.tid = t.id and t.status=0";
        //print $sql;
        $results = array();
        if ($result = $dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                //print_r($row);
                if ($row['status'] < 0) { continue; }
                $row['parent_ilx'] = $parent_ilx;
                $results[] = $row;
            }
        }
        return $results;
    }

    static public function getTermCount($dbObj, $type){
        $count = 0;
        if (isset($type)){
            $sql = "select count(*) as count from terms WHERE type = '".$type ."'";
        } else {
            $sql = "select count(*) as count from terms";
        }
        if ($result = $dbObj->mysqli->query($sql)) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $count = $row['count'];
        }
        return intval($count);
    }

    static public function getTermByID($id) {
        $dbobj = new DbObj();
        $term = new Term($dbobj);
        $term->getById($id);
        if(!$term->id) return NULL;
        return $term;
    }

    public function arrayForm() {
        $this->getAnnotations();
        return Array (
            "id" => $this->id,
            "ilx" => $this->ilx,
            "label" => $this->label,
            "definition" => $this->definition,
            "type" => $this->type,
            "cid" => $this->cid,
            "annotations" => $this->annotations,
        );
    }

    public function termDBO() {
        return TermDBO::loadBy(Array("id"), Array($this->id));
    }

    static public function checkExisting($label, $uid, $type) {
        return TermDBO::loadBy(array("label", "uid", "type"), array($label, $uid, $type));
    }

    public function insertTermUpdateLog ($changed_type, $changed_des, $comment, $uid) {
        $sql = "
            INSERT INTO term_update_logs (ilx,term_label,term_des,changed_type,changed_des,comment,update_time,uid)
            VALUES ('" . $this->ilx . "','". $this->label . "','" . $this->definition . "','" . $changed_type . "','" . $changed_des . "','" . $comment . "'," . time() . "," . $uid . ");
        ";
        $this->dbObj->mysqli->query($sql);
    }
}

class TermRelationship {
    public $id;
    public $term1_id;
    public $term2_id;
    public $relationship_tid;
    public $upvote = 0;
    public $downvote = 0;
    public $curator_status = 0;
    public $withdrawn = 0;
    public $term2_version;
    public $term1_version;
    public $relationship_term_version;
    public $comment = "";
    public $orig_uid;
    public $orig_time;
    static public $properties = array('id','term1_id','term2_id', 'relationship_tid','upvote',
        'downvote','curator_status','withdrawn','term1_version','term2_version','relationship_term_version',
        'comment','orig_uid','orig_time');
    static public $elasticsearch_fields = array('term1_ilx','term1_label','term2_ilx','term2_label','relationship_term_ilx','relationship_term_label');
    static public $export_fields = array('term1_ilx','term1_label','term2_ilx','term2_label','relationship_term_ilx','relationship_term_label');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermRelationship::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_relationships', '', array());
        $this->orig_time = time();
        $this->updateRowDB();
//        $array = get_object_vars($this);
//        foreach ($array as $field => $value) {
//            $this->updateDB($field, $value);
//        }
    }

    public function updateRowDB() {
        $types = '';
        $columns = Array();
        $params = Array();
        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if (in_array($field, TermRelationship::$properties) && $field != 'id') {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('term_relationships', $types, $columns, $params, 'where id=?');
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermRelationship::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_relationships', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function getById($id) {
        $result = $this->dbObj->select('term_relationships', array('*'), 'i', array($id), 'where id=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

    public function incrementVote($id, $vote) {
        $sql = "update term_relationships set " . $vote . " = " . $vote . " + 1 where id = " . $id;
        //print $sql . "\n";
        $this->dbObj->mysqli->query($sql);
        $this->getById($id);
    }

    public function decrementVote($id, $vote) {
        $sql = "update term_relationships set " . $vote . " = " . $vote . " - 1 where id = " . $id;
        //print $sql . "\n";
        $this->dbObj->mysqli->query($sql);
        $this->getById($id);
    }

    static public function exists($dbObj, $term1_id, $term2_id, $relationship_tid) {
        $result = $dbObj->select('term_relationships', array('*'), 'iii', array($term1_id,$term2_id,$relationship_tid), 'where term1_id=? and term2_id=? and relationship_tid=?');

        if (count($result) > 0) {
            return true;
        }

        return false;
    }

}

class TermAnnotation {
    public $id;
    public $tid;
    public $annotation_tid;
    public $value;
    public $comment = "";
    public $upvote = 0;
    public $downvote = 0;
    public $curator_status;
    public $withdrawn;
    public $term_version;
    public $annotation_term_version;
    public $orig_uid;
    public $orig_time;
    static public $properties = array('id','tid','annotation_tid','value','comment','upvote',
        'downvote','curator_status','withdrawn','term_version','annotation_term_version',
        'orig_uid','orig_time');
    static public $elasticsearch_fields = array('term_ilx','term_label','annotation_term_ilx','annotation_term_label', 'value');
    static public $export_fields = array('term_ilx','term_label','annotation_term_ilx','annotation_term_label', 'value');

    private $_term;
    private $_term_annotation;

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermAnnotation::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_annotations', '', array());
        $this->time = time();
        $this->updateRowDB();
//        $array = get_object_vars($this);
//        foreach ($array as $field => $value) {
//            $this->updateDB($field, $value);
//        }
    }

    public function updateRowDB() {
        $types = '';
        $columns = Array();
        $params = Array();
        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if (in_array($field, TermAnnotation::$properties) && $field != 'id') {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('term_annotations', $types, $columns, $params, 'where id=?');
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermAnnotation::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_annotations', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function getById($id) {
        $result = $this->dbObj->select('term_annotations', array('*'), 'i', array($id), 'where id=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

    public function incrementVote($id, $vote) {
        $sql = "update term_annotations set " . $vote . " = " . $vote . " + 1 where id = " . $id;
        //print $sql . "\n";
        $this->dbObj->mysqli->query($sql);
        $this->getById($id);
    }

    public function decrementVote($id, $vote) {
        $sql = "update term_annotations set " . $vote . " = " . $vote . " - 1 where id = " . $id;
        //print $sql . "\n";
        $this->dbObj->mysqli->query($sql);
        $this->getById($id);
    }

    static public function exists($dbObj, $tid, $annotation_tid, $value) {
        $result = $dbObj->select('term_annotations', array('*'), 'iis', array($tid,$annotation_tid,$value), 'where tid=? and annotation_tid=? and value=?');

        if (count($result) > 0) {
            return true;
        }

        return false;
    }

    public function term() {
        if(is_null($this->_term)) {
            $this->_term = TermDBO::loadBy(Array("id"), Array($this->tid));
        }
        return $this->_term;
    }

    public function termAnnotation() {
        if(is_null($this->_term_annotation)) {
            $this->_term_annotation = TermDBO::loadBy(Array("id"), Array($this->annotation_tid));
        }
        return $this->_term_annotation;
    }

}

class TermVoteLogs {
    public $id;
    public $uid;
    public $prop_table;
    public $prop_table_id;
    public $vote;
    public $time;
    static public $properties = array('id','uid','prop_table','prop_table_id','vote','time');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_vote_logs', '', array());
        $this->time = time();

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermVoteLogs::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_vote_logs', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function getUserVote($uid, $table, $table_id){
        if ($uid == "") { $uid = 0; }

        $sql = "select * from term_vote_logs where uid=" .
                $uid . " and prop_table= '" . $table .
                "' and prop_table_id= " . $table_id . " limit 1";
        //$result = $this->dbObj->select('term_vote_logs', array('*'), 'isi', array($uid, "$table", $table_id), 'where uid=? and prop_table=? and prop_table_id=? limit 1');
        //print $sql;
        $result = $this->dbObj->mysqli->query($sql);
        if (count($result) > 0) {
            foreach($result as $row) {
                $this->createFromRow($row);
            }
        }
    }

    public function changeVote($new_vote) {
        //$value = $this->dbObj->mysqli->escape_string($value);
        $this->dbObj->update('term_vote_logs', 'si', array("vote"), array("$new_vote",$this->id), 'where id=?');
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermVoteLogs::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function getById($id) {
        $result = $this->dbObj->select('term_vote_logs', array('*'), 'i', array($id), 'where id=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

}

class TermVersion {
    public $id;
    public $uid;
    public $cid;
    public $tid;
    public $version;
    public $term_info;
    static public $term_properties = array('label','ilx','type','definition','comment','status','display_superclass');
    static public $other_properties = array('annotation_type');
    public $time;
    static public $properties = array('id','uid','cid','tid', 'version','term_info','time');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_versions', '', array());
        $this->time = time();

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermVersion::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_versions', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function createFromRow2($vars) {
        $array = array();
        foreach ($vars as $name => $value) {
            if (in_array($name, TermVersion::$properties)) {
                $array[$name] = stripslashes($value);
            }
        }
        return $array;
    }

    public function getByTid($tid) {
        $array = array();
        $result = $this->dbObj->select('term_versions', array('*'), 'i', array($tid), 'where tid=? order by id desc');
        if (count($result) > 0) {
            foreach($result as $row) {
                $array[] = $this->createFromRow2($row);
            }
        }
        //print_r($array);
        return $array;
    }

    public function getByTidVersion($tid, $version) {
        $array = array();
        $result = $this->dbObj->select('term_versions', array('*'), 'ii', array($tid,$version), 'where tid=? and version=? limit 1');
        if (count($result) > 0) {
           $array = $this->createFromRow2($result[0]);
        }
        return $array;
    }



}

class TermVarField {
    public $id;
    public $tid;
    public $name;
    public $value;
    public $version;
    public $time;
    static public $properties = array('id','tid','name','value','version','time');
    public $dbObj;

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermVarField::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_var_fields', '', array());
        $this->time = time();

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if ($field != 'id' && in_array($field, TermVarField::$properties)) {
            //print $field . "::" . $value . " ";
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_var_fields', 'si', array($field), array("$value",$this->id), 'where id=?');
        }
    }

    static public function deleteDB($dbObj, $tid, $version){
        $dbObj->delete("term_var_fields", "ii", array($tid, $version), "where tid=? and version=?");
    }

}

class TermSynonym {
    public $id;
    public $tid;
    public $literal;
    public $type;
    public $time;
    public $version;
    static public $properties = array('id','tid','literal','type','version','time');
    public $dbObj;
    static public $elasticsearch_fields = array('literal','type');
    static public $export_fields = array('literal','type');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermSynonym::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_synonyms', '', array());
        $this->time = time();
        $this->updateRowDB();
//        $array = get_object_vars($this);
//        foreach ($array as $field => $value) {
//            $this->updateDB($field, $value);
//        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermSynonym::$properties) && $field != 'id') {
            $this->dbObj->update('term_synonyms', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function updateRowDB() {
        $types = '';
        $columns = Array();
        $params = Array();
        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if (in_array($field, TermSynonym::$properties) && $field != 'id' && !empty($value)) {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('term_synonyms', $types, $columns, $params, 'where id=?');
    }

    static public function deleteDB($dbObj, $tid, $version){
        $dbObj->delete("term_synonyms", "ii", array($tid, $version), "where tid=? and version=?");
    }

}

class TermExistingId {
    public $id;
    public $tid;
    public $curie;
    public $iri;
    public $curie_catalog_id;
    public $version;
    public $time;
    public $preferred;
    static public $properties = array('id','tid','curie','iri','curie_catalog_id','version','time','preferred');
    public $dbObj;
    static public $elasticsearch_fields = array('curie','iri','preferred');
    static public $export_fields = array('curie','iri','preferred');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermExistingId::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_existing_ids', '', array());
        $this->time = time();
        $this->updateRowDB();
//        $array = get_object_vars($this);
//        foreach ($array as $field => $value) {
//            $this->updateDB($field, $value);
//        }
    }

    public function updateRowDB() {
        $types = '';
        $columns = Array();
        $params = Array();
        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if (in_array($field, TermExistingId::$properties) && $field != 'id' && !empty($value)) {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('term_existing_ids', $types, $columns, $params, 'where id=?');
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermExistingId::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_existing_ids', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    static public function deleteDB($dbObj, $tid, $version){
        $dbObj->delete("term_existing_ids", "ii", array($tid, $version), "where tid=? and version=?");
    }

    /*
     * Checks to see if IRI is already taken for a term.
     *
     * $tid: term id
     * $iri: existing iri for term
     *
     * returns:
     * False | if no rows found
     * MySQLi Object | if rows found
     */
    static public function exists($iri) {
        $cxn = new Connection();
        $cxn->connect();
        # Curie isn't unique so iri is needed
        $sql = "SELECT * from term_existing_ids where iri = ?";
        $result = $cxn->selectFull('s', array($iri), $sql);
        $cxn->close();
        return $result;  # if no row found it returns false
    }

    public function getDB() {
        $eids = array();
        $results = $this->dbObj->select('term_existing_ids', array('*'));
        if (count($results) > 0) {
            foreach ($results as $result) {
                $eids[] = DbObj::createArrayFromRow('TermExistingId', $result);
            }
        }
        return $eids;

    }

    static public function getExternalCurieCounts($dbObj) {
        $sql = "SELECT curie FROM term_existing_ids WHERE curie not REGEXP '^ILX:' and curie not regexp '^NLXWIKI:';";
        $results = array();
        $count = 0;
        if ($result = $dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $ar = explode(":", $row['curie']);
                if ($ar[0] == '') {continue;}
                if (array_key_exists($ar[0], $results)){
                    $results[$ar[0]] = $results[$ar[0]] + 1;
                } else{
                    $results[$ar[0]] = 0;
                }
                $count++;
            }
        }
        $results['count'] = $count;
        return $results;

    }

    static public function getExternalCurieCountsByType($dbObj, $type) {
        if (isset($type)){
            $sql = "SELECT ex.curie AS 'curie' FROM term_existing_ids AS ex JOIN terms AS t ON ex.tid = t.id WHERE ex.preferred = '1' and t.type = '".$type."' and ex.curie not REGEXP '^ILX:' and ex.curie not regexp '^NLXWIKI:';";
        } else {
            $sql = "SELECT ex.curie AS 'curie' FROM term_existing_ids AS ex WHERE ex.preferred = '1' and ex.curie not REGEXP '^ILX:' and ex.curie not regexp '^NLXWIKI:';";
        }
        $results = array();
        $count = 0;
        if ($result = $dbObj->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $ar = explode(":", $row['curie']);
                if ($ar[0] == '') {continue;}
                if (array_key_exists($ar[0], $results)){
                    $results[$ar[0]] = $results[$ar[0]] + 1;
                } else{
                    $results[$ar[0]] = 0;
                }
                $count++;
            }
        }
        $results['count'] = $count;
        return $results;

    }
}

class TermSuperclass {
    public $id;
    public $ilx;
    public $tid;
    public $superclass_tid;
    public $version;
    public $time;
    static public $properties = array('id','tid','superclass_tid', 'ilx', 'version','time');
    public $dbObj;
    static public $elasticsearch_fields = array('ilx','label');
    static public $export_fields = array('ilx','label');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermSuperclass::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_superclasses', '', array());
        $this->time = time();
        $array = get_object_vars($this);
        // Wanted to init array first to check only if empty to avoid slowdown in interface.
        if(is_null($array['superclass_tid']) && !is_null($array['ilx'])) {
            $result = $this->dbObj->select('terms', array('*'), 's', array($array['ilx']), 'where ilx=? limit 1');
            $array['superclass_tid'] = strval($result[0]['id']);  // used by DB
            $this->superclass_tid = strval($result[0]['id']);  // used by return
        }
        $this->updateRowDB();
//        foreach ($array as $field => $value) {
//            $this->updateDB($field, $value);
//        }
    }

    public function updateRowDB() {
        $types = '';
        $columns = Array();
        $params = Array();
        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            // If entity fields exist, not an id field, and the value is not NULL then add to entity row
            if (in_array($field, TermSuperclass::$properties) && $field != 'id' && $field != 'ilx' && !empty($value)) {
                $types .= 's';  # Every field has been inserted as a sting this whole time???  # todo see if this can be corrected
                $columns[] = $field;
                $params[] = $value;
            }
        }
        $params[] = $this->id;
        $types .= 'i';
        $this->dbObj->update('term_superclasses', $types, $columns, $params, 'where id=?');
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermSuperclass::$properties) && $field != 'id' && $field != 'ilx') {
            $this->dbObj->update('term_superclasses', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    static public function deleteDB($dbObj, $tid, $version){
        $dbObj->delete("term_superclasses", "ii", array($tid, $version), "where tid=? and version=?");
    }

}

class CurieCatalog {
    public $id;
    public $uid;
    public $prefix;
    public $namespace;
    public $name;
    public $description;
    public $homepage;
    public $logo;
    public $type;
    public $source_uri;
    static public $properties = array('id','uid','prefix','namespace','name','description','homepage','logo','type','source_uri');


    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, CurieCatalog::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_curie_catalog', '', array());

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, CurieCatalog::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_curie_catalog', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function getDB() {
        $catalog = array();
        $results = $this->dbObj->select('term_curie_catalog', array('*'));
        if (count($results) > 0) {
            foreach ($results as $result) {
                $catalog[] = DbObj::createArrayFromRow('CurieCatalog', $result);
            }
        }
        return $catalog;
    }

    public function getByUserPrefix($uid, $prefix) {
        if ($uid == "") { $uid = 0; }
        $result = $this->dbObj->select('term_curie_catalog', array('*'), 'is', array($uid, $prefix), 'where uid=? and prefix=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

    static public function getPrefixToName($dbObj){
        $prefix2name = array();

        $results = $dbObj->select('term_curie_catalog', array('prefix', 'name'), 'ss', 'where 1');
        if (count($results) > 0) {
            foreach ($results as $result) {
                $prefix2name[$result['prefix']] = $result['name'];
            }
        }

        return $prefix2name;
    }

    static function getByPrefix($dbObj, $prefix) {
        $object = array();

        $sql = "SELECT * FROM term_curie_catalog WHERE prefix ='" . $prefix . "'";

        if ($result = $dbObj->mysqli->query($sql)) {
            $object = $result->fetch_array(MYSQLI_ASSOC);
        }
        //print_r($object);
        return $object;
    }
}

class TermOntology {
    public $id;
    public $url;
    static public $properties = array('id','url');
    static public $elasticsearch_fields = array('url');
    static public $export_fields = array('url');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermOntology::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_ontologies', '', array());

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermOntology::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_ontologies', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function getDB() {
        $ontologies = array();
        $results = $this->dbObj->select('term_ontologies', array('*'));
        if (count($results) > 0) {
            foreach ($results as $result) {
                $ontologies[] = DbObj::createArrayFromRow('TermOntology', $result);
            }
        }
        return $ontologies;
    }

    public function getByUrl($url) {
        $result = $this->dbObj->select('term_ontologies', array('*'), 's', array($url), 'where url=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

}

class TermAnnotationType {
    public $id;
    public $annotation_tid;
    public $type;
    static public $properties = array('id','type','annotation_tid');
    static public $elasticsearch_fields = array();

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermAnnotationType::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_annotation_types', '', array());

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermAnnotationType::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_annotation_types', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function getByTid($tid) {
        $result = $this->dbObj->select('term_annotation_types', array('*'), 'i', array($tid), 'where annotation_tid=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }
    }

}

class TermMapping {
    public $id;
    public $tid;
    public $source_id;
    public $source;
    public $value;
    public $matched_value;
    public $is_ambiguous;
    public $is_whole;
    public $snippet;
    public $method;
    public $relation;
    public $curation_status;
    public $view_name;
    public $view_id;
    public $column_name;
    public $concept;
    public $concept_id;
    public $iri;
    public $uid;
    public $curation_logs = array();
    static public $properties = array('id','tid','source_id','source','value','matched_value','is_ambiguous','is_whole',
        'snippet','method','relation','curation_status','view_name','view_id','column_name','concept','concept_id','iri','uid');

    public static $allowed_relations = Array("exact","part of","sub class of");


    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermMapping::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_mapping', '', array());

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function getById ($id) {
        $result = $this->dbObj->select('term_mappings', array('*'), 'i', array($id), 'where id=? limit 1');

        if (count($result) > 0) {
            $this->createFromRow($result[0]);
        }

    }

    public function updateDB($field, $value) {
        if (in_array($field, TermMapping::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_mappings', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    static public function deleteDB($dbObj, $id){
        $dbObj->delete("term_mappings", "i", array($id), "where id=?");
    }

    public function getByTid($tid, $from, $size, $curation_status) {
        $mappings = array();
        $count = 0;
        $sql = "select count(*) as count from term_mappings where tid = " . $tid . " and view_id = 'foundry'";
        if($curation_status != "all") $sql .= " and curation_status = '". $curation_status . "'";
        if ($result = $this->dbObj->mysqli->query($sql)) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $count = $row['count'];
        }

        if ($count <= $from) {
            return array('count'=>$count,'mappings'=>$mappings,'from'=>$from,'size'=>$size, 'curation_status'=>$curation_status);
        }

        if($curation_status == "all")
            $results = $this->dbObj->select('term_mappings', array('*'), 'i', array($tid),
                'where tid=? and view_id="foundry" order by source, view_name, column_name, value limit ' . $size . ' offset ' . $from);
        else
            $results = $this->dbObj->select('term_mappings', array('*'), 'is', array($tid, $curation_status),
                'where tid=? and view_id="foundry" and curation_status=? order by source, view_name, column_name, value limit ' . $size . ' offset ' . $from);
        if (count($results) > 0) {
            foreach ($results as $result) {
                //print_r($result);
                $tml = new TermMappingLogs($this->dbObj);
                $result['curation_logs'] = $tml->getByTermMappingId($result['id']);
                $mappings[] = $result;
            }
        }

        return array('count'=>$count,'mappings'=>$mappings,'from'=>$from,'size'=>$size,'curation_status'=>$curation_status);
    }

}

class TermMappingDBO extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "term_mappings";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "tid"               => self::fieldDef("tid", "i", true),
            "source_id"         => self::fieldDef("source_id", "s", true),
            "source"            => self::fieldDef("source", "s", true),
            "value"             => self::fieldDef("value", "s", true),
            "matched_value"     => self::fieldDef("matched_value", "s", true),
            "is_ambiguous"      => self::fieldDef("is_ambiguous", "s", true, Array("allowed_values" => Array("true", "false"))),
            "is_whole"          => self::fieldDef("is_whole", "s", true, Array("allowed_values" => Array("true", "false"))),
            "snippet"           => self::fieldDef("snippet", "s", true),
            "relation"          => self::fieldDef("relation", "s", true, Array("allowed_values" => Array("exact", "part of", "sub class of"))),
            "method"            => self::fieldDef("method", "s", true, Array("allowed_values" => Array("semi-automated", "user-contributed"))),
            "curation_status"   => self::fieldDef("curation_status", "s", true, Array("allowed_values" => Array("submitted", "matched", "pending", "rejected", "approved"))),
            "view_id"           => self::fieldDef("view_id", "s", true),
            "view_name"         => self::fieldDef("view_name", "s", true),
            "column_name"       => self::fieldDef("column_name", "s", true),
            "concept"           => self::fieldDef("concept", "s", true),
            "concept_id"        => self::fieldDef("concept_id", "s", true),
            "iri"               => self::fieldDef("iri", "s", true),
            "uid"               => self::fieldDef("uid", "i", true),
            "existing_id"       => self::fieldDef("existing_id", "s", true),
        );
    }
    public function _get_is_ambiguous($val) { return $val == "true" ? true : false; }
    public function _set_is_ambiguous($val) { return $val ? 'true' : 'false'; }
    public function _get_is_whole($val) { return $val == "true" ? true : false; }
    public function _set_is_whole($val) { return $val ? 'true' : 'false'; }

    public static function createNewObj(User $user, Term $term, $source_id, $source, $value, $matched_value, $is_ambiguous, $is_whole, $snippet, $relation, $method, $curation_status, $view_id, $view_name, $column_name, $concept, $concept_id, $iri, $existing_id) {
        $obj = self::insertObj(Array(
            "id" => NULL,
            "tid" => $term->id,
            "source_id" => $source_id,
            "source" => $source,
            "value" => $value,
            "matched_value" => $matched_value,
            "is_ambiguous" => $is_ambiguous,
            "is_whole" => $is_whole,
            "snippet" => $snippet,
            "relation" => $relation,
            "method" => $method,
            "curation_status" => $curation_status,
            "view_id" => $view_id,
            "view_name" => $view_name,
            "column_name" => $column_name,
            "concept" => $concept,
            "concept_id" => $concept_id,
            "iri" => $iri,
            "uid" => $user->id,
            "existing_id" => $existing_id,
        ));

        return $obj;
    }

    public static function deleteObj($obj) {

    }

    public function arrayForm() {
        $array_form = $this->arrayFormAll();
        $array_form["source_level_1"] = $array_form["view_name"];
        $array_form["source_level_2"] = $array_form["column_name"];
        unset($array_form["id"]);
        unset($array_form["view_name"]);
        unset($array_form["concept_name"]);
        return $array_form;
    }
}
TermMappingDBO::init();

class TermMappingLogs {
    public $id;
    public $tmid;
    public $uid;
    public $notes;
    public $curation_status;
    public $relation;
    public $concept;
    public $concept_id;
    public $time;
    static public $properties = array('id','tmid','uid','notes','curation_status','time','relation','concept','concept_id');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermMappingLogs::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_mapping_logs', '', array());
        $this->time = time();

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermMappingLogs::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_mapping_logs', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    static public function deleteDB($dbObj, $tmid){
        $dbObj->delete("term_mapping_logs", "i", array($tmid), "where tmid=?");
    }

    public function getByTermMappingId($tmid) {
        $mappings = array();

        $results = $this->dbObj->select('term_mapping_logs', array('*'), 'i', array($tmid), 'where tmid=?');
        if (count($results) > 0) {
            foreach ($results as $result) {
                //print_r($result);
                $result['curator'] = DbObj::getCuratorById($result['uid']);
                $mappings[] = $result;
            }
        }

        return $mappings;
    }

}

class TermMappingDeletes {
    public $id;
    public $tmid;
    public $uid;
    public $notes;
    public $time;
    public $tm_fields;
    static public $properties = array('id','tmid','uid','notes','time','tm_fields');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_mapping_deletes', '', array());
        $this->time = time();

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermMappingDeletes::$properties) && $field != 'id') {
            //$value = $this->dbObj->mysqli->escape_string($value);
            $this->dbObj->update('term_mapping_deletes', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

}

class TermAffiliates {
    public $id;
    public $name;
    public $description;
    public $url;
    public $logo;
    static public $properties = array('id','name','description','url','logo');

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    static function getList($dbObj){
        $list = array();

        $results = $dbObj->select('term_affiliates', array('*'), 'where 1');
        if (count($results) > 0) {
            foreach ($results as $result) {
                //print_r($result);
                $list[] = $result;
            }
        }

        return $list;
    }
}

class TermCommunity {
    public $id;
    public $tid;
    public $cid;
    public $uid_suggested;
    public $uid_curated;
    public $status;
    public $time_suggested;
    public $time_curated;
    public $notes;
    public $scicrunch_maintainer;
    public $uid_maintainer;
    public $time_maintainer;
    static public $properties = array('id','tid','cid','uid_suggested','uid_curated','status','time_suggested','time_curated','notes','scicrunch_maintainer','uid_maintainer','time_maintainer');
    static public $required = array('tid','cid');
    public $communityTerms = array();

    public function __construct($dbObj) {
        $this->dbObj = $dbObj;
    }

    public function insertDB() {
        $this->id = $this->dbObj->insert('term_communities', '', array());
        $this->time_suggested = time();

        $array = get_object_vars($this);
        foreach ($array as $field => $value) {
            $this->updateDB($field, $value);
        }
    }

    public function updateDB($field, $value) {
        if (in_array($field, TermCommunity::$properties) && $field != 'id') {
            $this->dbObj->update('term_communities', 'si', array("$field"), array("$value",$this->id), 'where id=?');
        }
    }

    public function createFromRow($vars) {
        foreach ($vars as $name => $value) {
            if (in_array($name, TermCommunity::$properties)) {
                $this->$name = stripslashes($value);
            }
        }
    }

    public function getById($id) {
        $results = $this->dbObj->select('term_communities', array('*'), 'i', array($id), 'where id=?');

        if (count($results) > 0) {
            $this->createFromRow($results[0]);
        }
    }

    public function getByTidCid($tid, $cid){
        $results = $this->dbObj->select('term_communities', array('*'), 'ii', array($tid, $cid), 'where tid=? and cid=? limit 1');

        if (count($results) > 0) {
            $this->createFromRow($results[0]);
        }
    }

    public function forPrint() {
        $return_values = array();
        foreach ($this as $key=>$val){
            if (in_array($key, TermCommunity::$properties) ){
                $return_values[$key] = $val;
                if ($key == 'uid_suggested') {
                    $results = $this->dbObj->select('users', array('*'), 'i', array($val), 'where guid=? limit 1');
                    if (count($results) > 0) {
                        $return_values['user_suggested'] = $results[0]['firstName'] . " " . $results[0]['lastName'];
                    }
                }
                if ($key == 'time_suggested'){
                    $return_values['date_suggested'] = gmdate("Y-m-d H:i:s", $val);
                }
            }
        }
        return $return_values;
    }

    // public function getCommunityTerms($cid){
    //     $ilxes = array();
    //
    //     $sql = "select * from term_communities where cid=" . $cid . " and status in('approved','suggested')";
    //     $results = $this->dbObj->mysqli->query($sql);
    //     if (count($results) > 0) {
    //         foreach ($results as $row) {
    //             //print_r($row);
    //             $term = new Term($this->dbObj);
    //             $term->getById($row['tid']);
    //             $term->getAncestors();
    //             foreach ($term->ancestors as $ancestors){
    //                 //print_r($ancestor);
    //                 foreach ($ancestors as $ancestor) {
    //                     if (!in_array($ancestor['parent_ilx'], $ilxes)) {
    //                         $ilxes[] = $ancestor['parent_ilx'];
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     //print_r($ilxes);
    //     $this->communityTerms = $ilxes;
    // }

    /* term_communities table is broken; need to pull directly from terms table. */
    public function getCommunityTerms($cid){
        $cxn = new Connection();
        $cxn->connect();
        $results = $cxn->select("terms", Array("*"), "i", Array($cid), "where orig_cid = ? and status = '0' order by orig_time");
        $cxn->close(); // Close mysql connections.
        return $results;
    }
}

class TermDBO extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "terms";
    protected static $_primary_key_field = "id";

    protected static $_cache = Array();
    const MAX_CACHE_COUNT = 10;

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "ilx"                   => self::fieldDef("ilx", "s", true),
            "orig_uid"              => self::fieldDef("orig_uid", "i", true),
            "orig_cid"              => self::fieldDef("orig_cid", "i", true),
            "uid"                   => self::fieldDef("uid", "i", true),
            "cid"                   => self::fieldDef("cid", "i", true),
            "label"                 => self::fieldDef("label", "s", true),
            "definition"            => self::fieldDef("definition", "s", true),
            "comment"               => self::fieldDef("comment", "s", true),
            "type"                  => self::fieldDef("type", "s", true),
            "version"               => self::fieldDef("version", "i", true),
            "status"                => self::fieldDef("status", "i", true),
            "display_superclass"    => self::fieldDef("display_superclass", "s", true),
            "orig_time"             => self::fieldDef("orig_time", "i", true),
            "time"                  => self::fieldDef("time", "i", true),
        );
    }

    private $_annotations;

    public static function createNewObj() {

    }

    public static function deleteObj($obj) {

    }

    public function arrayForm() {
        return Array (
            "id" => $this->id,
            "ilx" => $this->ilx,
            "label" => $this->label,
            "definition" => $this->definition,
            "type" => $this->type,
            "cid" => $this->cid,
            "annotations" => $this->annotations(),
        );
    }

    public function ilxFormatted() {
        return strtoupper(str_replace("_", ":", $this->ilx));
    }

    public function annotations() {
        if(is_null($this->_annotations)) {
            $cxn = new Connection();
            $cxn->connect();

            $sql = "select * from term_annotations where tid=" . $this->id . " or annotation_tid=" . $this->id;

            $columns = array();
            if ($result = $cxn->mysqli->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $column = $row;
                    if ($row['tid'] != $this->id){
                        $sql1 = "select * from terms where id=" . $row['tid'];
                        //print $sql1 . "\n";
                        if ($result1 = $cxn->mysqli->query($sql1)) {
                            while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) {
                                //print_r($row1);
                                if ($row1['curator_status'] < 0) { continue; }

                                $column['term_label'] = $row1['label'];
                                $column['term_ilx'] = $row1['ilx'];
                                $column['term_curie'] = $row1['curie'];
                                $column['term_type'] = $row1['type'];
                                $column['term_definition'] = $row1['definition'];
                            }
                        }

                    }
                    if ($row['annotation_tid'] != $this->id){
                        $sql2 = "select * from terms where id=" . $row['annotation_tid'];
                        //print $sql2 . "\n";
                        if ($result2 = $cxn->mysqli->query($sql2)) {
                            while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
                                //print_r($row1);
                                if ($row2['status'] < 0) { continue; }

                                $column['annotation_term_label'] = $row2['label'];
                                $column['annotation_term_ilx'] = $row2['ilx'];
                                $column['annotation_term_curie'] = $row2['curie'];
                                $column['annotation_term_type'] = $row2['type'];
                                $column['annotation_term_definition'] = $row2['definition'];
                            }
                        }
                    }
                    $columns[] = $column;
                }
            }

            //add synonyms as annotation
            //$term = array();
            //$term['tid'] = $this->id;
            //$term['term_version'] = $this->version;
            //$record = $term;
            //$record['annotation_term_ilx'] = '';
            //$record['annotation_tid'] = 0;
            //foreach ($this->synonyms as $syn){
            //    $record['value'] = $syn->literal;
            //    $record['annotation_term_label'] = 'Synonym';
            //    if ($syn->type == 'abbrev'){
            //        $record['annotation_term_label'] = 'Abbreviation';
            //    }
            //    $columns[] = $record;
            //}

            $cxn->close();
            $this->_annotations = $columns;
        }
        return $this->_annotations;
    }

    public static function batchUpsert() {
        require_once __DIR__ . "/../api-classes/term/term_elasticsearch.php";

        $lock_name = "lock-batch-elastic-upsert";
        $lock_obj = ServerCache::loadByName($lock_name);
        $seconds_in_week = 86400 * 7;
        $locked = true;
        if(is_null($lock_obj)) {
            $locked = false;
        } elseif($lock_obj->timestamp + $seconds_in_week < time()) {
            $locked = false;
            ServerCache::deleteObj($lock_obj);
        }
        if($locked) {
            return;
        }

        $lock_obj = ServerCache::createNewObj($lock_name, "");
        if(is_null($lock_obj)) {
            return;
        }

        while(true) {
            $flagged_terms = TermFlag::loadArrayBy(Array("flag", "active"), Array("elastic-upsert", 1), Array("limit" => 100));
            if(empty($flagged_terms)) break;
            $term_ids = Array();
            foreach($flagged_terms as $ft) {
                $term_ids[] = $ft->termid;
            }
            termElasticUpsertBulk(NULL, NULL, $term_ids, true);
            foreach($flagged_terms as $ft) {
                $ft->active = false;
                $ft->updateDB();
            }
            unset($term_ids);
            unset($flagged_terms);
        }

        $lock_obj = ServerCache::loadByName($lock_name);
        if(!is_null($lock_obj)) {
            ServerCache::deleteObj($lock_obj);
        }
    }

    public static function loadBy($fields, $values, $options, $exactly_one) {
        return parent::loadBy($fields, $values, $options, $exactly_one); // disable for now
        if(count($fields) == 1 && count($values) == 1 && $fields[0] == "id") {
            if(!isset(self::$_cache[$values[0]])) {
                $term = parent::loadBy($fields, $values, $options, $exactly_one);
                if(is_null($term)) {
                    return NULL;
                }
                if(count(self::$_cache) >= self::MAX_CACHE_COUNT) {
                    $lowest_count = MAX_INT;
                    $lowest_id = NULL;
                    foreach(self::$_cache as $termid => $cache) {
                        if($cache["count"] < $lowest_count) {
                            $lowest_count = $cache["count"];
                            $lowest_id = $termid;
                        }
                    }
                    unset(self::$_cache[$lowest_id]);
                }
                self::$_cache[$term->id] = Array("count" => 0, "term" => $term);
            }
            self::$_cache[$values[0]]["count"] += 1;
            return self::$_cache[$values[0]]["term"];
        } else {
            $term = parent::loadBy($fields, $values, $options, $exactly_one);
            return $term;
        }
    }
}
TermDBO::init();

?>
