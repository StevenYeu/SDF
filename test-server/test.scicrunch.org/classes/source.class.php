<?php

class Sources extends Connection {

    public $id;
    public $nif;
    public $source;
    public $view;
    public $description;
    public $description_encoded;
    public $image;
    public $data;
    public $created;
    public $updated;
    public $data_last_updated;
    public $categories;

    private static $_all_sources;

    public function create($vars){
        $this->nif = $vars['nif'];
        $this->source = $vars['source'];
        $this->view = $vars['view'];
        $this->description = $vars['description'];
        $this->description_encoded = $vars['description_encoded'];
        $this->image = $vars['image'];
        $this->data = $vars['data'];
        $this->data_last_updated = $vars['data_last_updated'];
        $this->active = isset($vars['active']) ? $vars['active'] : 1;

        $this->created = time();
    }

    public function updateData($vars){
        $this->nif = $vars['nif'];
        $this->source = $vars['source'];
        $this->view = $vars['view'];
        $this->description = $vars['description'];
        $this->description_encoded = $vars['description_encoded'];
        $this->image = $vars['image'];
        $this->data = $vars['data'];
        if(isset($vars['data_last_updated']) && $vars['data_last_updated'] > 0) $this->data_last_updated = $vars['data_last_updated'];
        $this->active = isset($vars['active']) ? $vars['active'] : 1;
        if(isset($vars['active'])) $this->active = $vars['active'];

        $this->updated = time();
    }

    public function createFromRow($vars){
        $this->id = $vars['id'];
        $this->nif = $vars['nif'];
        $this->source = $vars['source'];
        $this->view = $vars['view'];
        $this->description = $vars['description'];
        $this->description_encoded = $vars['description_encoded'];
        $this->image = $vars['image'];
        $this->data = $vars['data'];
        $this->created = $vars['created'];
        $this->updated = $vars['updated'];
        $this->data_last_updated = $vars['data_last_updated'];
        $this->active = $vars['active'];

        $this->getCategories();
    }

    public function insertDB(){
        $this->connect();
        $this->id = $this->insert('scicrunch_sources','issssisiiiii',array(null,$this->nif,$this->source,$this->view,$this->description,$this->description_encoded,$this->image,$this->data,$this->created,$this->updated,$this->data_last_updated,$this->active));
        $this->close();
    }

    public function updateDB(){
        $this->connect();
        $this->update('scicrunch_sources','ssssisiiiii',array('nif','source','view','description','description_encoded','image','data','data_last_updated','active','updated'),array($this->nif,$this->source,$this->view,$this->description,$this->description_encoded,$this->image,$this->data,$this->data_last_updated,$this->active,$this->updated,$this->id),'where id=?');
        $this->close();
    }

    public function getAllSources(){
        if(is_null(self::$_all_sources)) {
            $this->connect();
            $return = $this->select('scicrunch_sources',array('*'),null,array(),'order by source asc, view asc');
            $this->close();

            if(count($return)>0){
                foreach($return as $row){
                    $source = new Sources();
                    $source->createFromRow($row);
                    $finalArray[$source->nif] = $source;
                }
                self::$_all_sources = $finalArray;
            } else {
                self::$_all_sources = array();
            }
        }
        return self::$_all_sources;
    }

    public static function getAllSourcesStatic() {
        $s = new Sources();
        return $s->getAllSources();
    }

    public function getTitle(){
        return $this->source.': '.$this->view;
    }

    public function getRecentlyAdded($offset,$limit){
        $this->connect();
        $return = $this->select('scicrunch_sources',array('*'),null,array(),'order by created desc limit '.$offset.','.$limit);
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $source = new Sources();
                $source->createFromRow($row);
                $finalArray[] = $source;
            }
            return $finalArray;
        } else return array();
    }

    public function setActive($active) {
        if($active) {
            $active_state = 1;
        } else {
            $active_state = 0;
        }
        $cxn = new Connection();
        $cxn->connect();
        $cxn->update('scicrunch_sources', 'ii', Array('active'), Array($active_state, $this->id), 'where id=?');
        $cxn->close();
        $this->active = $active_state;
    }

    public function setDescriptionEncoded($encoded){
        if ($encoded){
            $this->description_encoded = 1;
        } else {
            $this->description_encoded = 0;
        }
        $this->connect();
        $this->update('scicrunch_sources', 'ii', Array('description_encoded'),
            Array($this->description_encoded, $this->id), 'where id=?');
        $this->close();
    }

    public function getByID($id){
        if (!is_numeric($id)) {
            return NULL;
        }
        $cxn = new Connection();
        $cxn->connect();
        $result = $cxn->select('scicrunch_sources',array('*'),'i',array($id),'where id=?');
        $cxn->close();
        if (count($result) == 0) {
            return NULL;
        }
        $source = new Sources();
        $source->createFromRow($result[0]);
        return $source;
    }

    public function getByView($nif){
        $this->connect();
        $return = $this->select('scicrunch_sources',array('*'),'s',array($nif),'where nif=? order by id desc limit 1');
        $this->close();

        if(count($return)>0){
            $this->createFromRow($return[0]);
        }
    }

    public function getByViews($nifs) {
        $sources = Array();
        foreach($nifs as $nif) {
            $source = new Sources();
            $source->getByView($nif);
            if($source->nif) $sources[$source->nif] = $source;
        }
        return $sources;
    }

    public function getCategories() {
        $this->connect();
        $this->categories = $this->select("scicrunch_sources_categories", Array("parentCategory", "category"), "s", Array($this->nif), "where viewid=?");
        $this->close();
    }

    static public function updateViewCategories($categoriesArray) {
        // updates all the categories in the table scicrunch_sources_categories
        // array must be a list of associative arrays with three fields: viewid, category, parentcategory

        if(empty($categoriesArray)) return; // return if empty, something bad probably happened with the search
        foreach($categoriesArray as $ca) {
            if(!isset($ca["category"]) || !isset($ca["parentCategory"]) || !isset($ca["viewid"])) return;   // return if not every record has required fields
        }

        $timestamp = time();

        $cxn = new Connection();
        $cxn->connect();

        // clear the categories table
        $stmt = $cxn->mysqli->prepare("TRUNCATE TABLE scicrunch_sources_categories");
        $stmt->execute();
        $stmt->close();

        foreach($categoriesArray as $ca) {
            $cxn->insert("scicrunch_sources_categories", "isssi", Array(NULL, $ca["viewid"], $ca["parentCategory"], $ca["category"], $timestamp));
        }
        $cxn->close();
    }

}

?>
