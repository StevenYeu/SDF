<?php

class Resource extends Connection {

    public $id;
    public $uid;
    public $email;
    public $image;
    public $cid;
    public $version;
    public $rid;
    public $original_id;
    public $pid;
    public $parent;
    public $type;
    public $typeID;
    public $status;
    public $insert_time;
    public $edit_time;
    public $curate_time;
    public $score;
    public $uuid;

    public $columns;
    public $dbTypes = 'iisiississisiiisis';

    private static $_resource_types;

    public function create($vars) {
        $this->uid = $vars['uid'];
        $this->email = $vars['email'];
        $this->cid = $vars['cid'];
        $this->pid = $vars['pid'];
        $this->original_id = $vars['original_id'];
        $this->parent = $vars['parent'];
        $this->type = $vars['type'];
        $this->typeID = $vars['typeID'];
        $this->status = 'Pending';
        $this->insert_time = time();
        $this->image = isset($vars['image']) ? $vars['image'] : NULL;
        $this->uuid = isset($vars['uuid']) ? $vars['uuid'] : NULL;

        $this->version = 1;
    }

    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->email = $vars['email'];
        $this->cid = $vars['cid'];
        $this->rid = $vars['rid'];
        $this->version = $vars['version'];
        $this->original_id = $vars['original_id'];
        $this->pid = $vars['pid'];
        $this->parent = $vars['parent'];
        $this->type = $vars['type'];
        $this->typeID = $vars['typeID'];
        $this->status = $vars['status'];
        $this->insert_time = $vars['insert_time'];
        $this->edit_time = $vars['edit_time'];
        $this->curate_time = $vars['curate_time'];
        $this->image = $vars['image'];
        $this->uuid = $vars['uuid'];
    }

    public function insertDB() {
        $this->connect();
        $this->id = $this->insert('resources', $this->dbTypes, array(null, $this->uid, $this->email, $this->cid, $this->version, $this->rid, $this->original_id, $this->pid, $this->parent, $this->type, $this->typeID, $this->status, $this->insert_time, $this->edit_time, $this->curate_time, $this->image, NULL, $this->uuid));
        $this->rid = 'SCR_' . sprintf("%06d", $this->id);
        if (!$this->original_id) {
            $this->original_id = 'SCR_' . sprintf("%06d", $this->id);
        }
        $this->update('resources', 'ssi', array('rid', 'original_id'), array($this->rid, $this->original_id, $this->id), 'where id=?');
        $this->insert('resource_versions', 'iisiiisiii', array(null, $this->uid, $this->email, $this->cid, $this->id, $this->version, 'Pending', $this->insert_time, 0, $this->insert_time));
        $this->close();
    }

    public function getByCommunity($cid, $status, $offset, $limit) {
        $this->connect();
        $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS *'), 'is', array($cid, $status), 'where cid=? and status=? order by id desc limit ' . $offset . ',' . $limit);
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $resource = new Resource();
                $resource->createFromRow($row);
                $resource->getColumns();
                $finalArray[] = $resource;
            }
        }
        return $finalArray;
    }

    public function getResourceCountByComm($cid) {
        $this->connect();
        $return = $this->select('resources', array('count(id)'), 'i', array($cid), 'where cid=?');
        $count = $return[0]['count(id)'];
        $this->close();

        return $count;
    }

    public function getByUser($uid, $offset=0, $limit=MAXINT) {
        $this->connect();
        $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS *'), 'i', array($uid), 'where uid=? order by id desc limit ' . $offset . ',' . $limit);
        $finalArray['count'] = $this->getTotal();
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $resource = new Resource();
                $resource->createFromRow($row);
                $resource->getColumns();
                $finalArray['results'][] = $resource;
            }
        }
        return $finalArray;
    }

    public function getByRID($rid) {
        $this->connect();
        $return = $this->select('resources', array('*'), 's', array($rid), 'where rid=? limit 1');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getByOriginal($rid) {
        $this->connect();
        $return = $this->select('resources', array('*'), 's', array($rid), 'where original_id=? limit 1');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getByName($rid) {
        $this->connect();
        $rids = $this->select('resource_columns', array('rid'), 's', array($rid), 'where name="Resource Name" and value=? limit 1');
        if(count($rids)>0)
            $return = $this->select('resources', array('*'), 'i', array($rids[0]['rid']), 'where id=? limit 1');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    // Added by Steven. Fetches Tool Data from Local DB
    // 12/05/22 Updated query to pull latest version 
    public function getToolData($rid) {
        $this->connect();
        error_log("****getToolData $sql");
        $return = $this->select(
            'resource_columns LEFT JOIN resources ON resource_columns.rid = resources.id INNER JOIN (
                SELECT
                    resources.rid,
                    MAX(resource_columns. `version`) `version`
                FROM
                    resource_columns
                    LEFT JOIN resources ON resource_columns.rid = resources.id
                GROUP BY
                    resources.rid) max_table ON resources.rid = max_table.rid
            AND resource_columns. `version` = max_table.version',
            array("resource_columns.name","resource_columns.value"),
            's',
            array($rid),
            'where resources.rid=? GROUP BY resource_columns.name '
        );
        $this->close();
        return $return;
    }

    public function searchColumns($query, $offset, $limit, $fields, $facets, $status, $id=NULL) {
        $this->connect();
        $sum = '';
        if ($status) {
            if($id) $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS id as rid'), 'ss', array($status, $id), 'where status=? and uid=? order by rid desc limit ' . $offset. ',' . $limit);
            else $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS id as rid'), 's', array($status), 'where status=? order by rid desc limit ' . $offset . ',' . $limit);
        } else {
            if (count($fields) > 0) {
                $sum = "(";
                foreach ($fields as $field) {
                    $summing[] = 'IF(name="resource_columns.' . $field->name . '",' . $field->weight . ',0)';
                }
                $sum .= join('+', $summing) . ')';

            }
            if ($query == ''){
                if($id) {
                    $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS id as rid,0 as score'), 's', array($id), 'where uid=? order by rid desc limit ' . $offset . ',' . $limit);
                } else {
                    // Changed by Steven -- added status where clause
                    $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS id as rid,0 as score'), null, array(), 'where status="Curated" order by rid desc limit ' . $offset . ',' . $limit);
                }
            } else {
                if (strlen($query) < 4) {
                    if($id){
                        $return = $this->select(
                            'resource_columns LEFT OUTER JOIN resources ON resource_columns.rid=resources.id',
                            Array('SQL_CALC_FOUND_ROWS resource_columns.rid, SUM(' . $sum . ') as score'),
                            'ss',
                            array($id, $query),
                            'WHERE uid=? AND value=? GROUP BY resource_columns.rid ORDER BY SCORE DESC LIMIT ' . $offset . ',' . $limit
                        );
                    }
                    else{

                        // Add If check for supporting only '*' search
                        // Added by Steven
                        if ($query == '*') {
                            $return = $this->select('resources', array('SQL_CALC_FOUND_ROWS id as rid,0 as score'), null, array(), 'where status="Curated" order by rid desc limit ' . $offset . ',' . $limit);
    
                        } else {
                            // Original Query
                            // $return = $this->select(
                            //     'resource_columns',
                            //     array('SQL_CALC_FOUND_ROWS rid,SUM(' . $sum . ') as score'),
                            //     's',
                            //     array($query), 'where value=? group by rid order by score desc limit ' . $offset . ',' . $limit
                            // );
                            // New Query added by Steven
                            $return = $this->select(
                                'resource_columns LEFT OUTER JOIN resources on resource_columns.rid=resources.id',
                                array('SQL_CALC_FOUND_ROWS resource_columns.rid,SUM(' . $sum . ') as score'),
                                'ss',
                                array($query, $query), 'where resources.status="Curated" AND (value=? OR MATCH(value) AGAINST(? IN BOOLEAN MODE)) group by rid order by score desc limit ' . $offset . ',' . $limit
                            );
                        }
   

                    }
                } else {
                    if($id){
                        
                        $return = $this->select(
                            'resource_columns LEFT OUTER JOIN resources ON resource_columns.rid=resources.id',
                            array('SQL_CALC_FOUND_ROWS resource_columns.rid,SUM(MATCH(value) AGAINST(? IN BOOLEAN MODE) * ' . $sum . ') as score'),
                            'sss',
                            array($query, $id, $query),
                            'where uid=? AND resources.status="Curated" AND MATCH(value) AGAINST(? IN BOOLEAN MODE) group by resource_columns.rid order by score desc limit ' . $offset . ',' . $limit
                        );
                        // Changes by Steven -- added status check in where clause
                    }
                    else{
                        // Changes by Steven -- added status check in where clause
                        $return = $this->select(
                            'resource_columns LEFT OUTER JOIN resources on resource_columns.rid=resources.id',
                            array('SQL_CALC_FOUND_ROWS resource_columns.rid as rid,SUM(MATCH(value) AGAINST(? IN BOOLEAN MODE) * ' . $sum . ') as score'),
                            'ssss',
                            array($query, $query, $query, $query),
                            'where resources.status="Curated" AND (MATCH(value) AGAINST(? IN BOOLEAN MODE) or resources.rid=? or resources.original_id=?) group by rid order by score desc limit ' . $offset . ',' . $limit
                        );
                    }
                }
            }
        }
        $finalArray['count'] = $this->getTotal();
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $field) {
                $resource = new Resource();
                $resource->getByID($field['rid']);
                $resource->version = $resource->getLatestVersionNum();
                $resource->getColumns();
                $resource->score = $field['score'];
                $finalArray['results'][] = $resource;
            }
        }
        return $finalArray;
    }

    public function searchByComm($cid, $query, $type, $offset, $limit) {
        $this->connect();
        if ($type)
            $return = $this->select('resources as r left join resource_columns as c on (r.id=c.rid and r.version=c.version)', array('SQL_CALC_FOUND_ROWS r.*'), 'iss', array($cid, $type, '%' . $query . '%'), 'where r.cid=? and r.type=? and c.value like ? group by r.id limit ' . $offset . ',' . $limit);
        else
            $return = $this->select('resources as r left join resource_columns as c on (r.id=c.rid and r.version=c.version)', array('SQL_CALC_FOUND_ROWS r.*'), 'is', array($cid, '%' . $query . '%'), 'where r.cid=? and c.value like ? group by r.id limit ' . $offset . ',' . $limit);
        $finalArray['count'] = $this->getTotal();
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $field) {
                $resource = new Resource();
                $resource->createFromRow($field);
                $resource->getColumns();
                $finalArray['results'][] = $resource;
            }
        }
        return $finalArray;
    }

    public function getByID($id) {
        $this->connect();
        $return = $this->select('resources', array('*'), 'i', array($id), 'where id=? limit 1');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getColumns() {
        $this->connect();
        $return = $this->select('resource_columns', array('*'), 'ii', array($this->id, $this->version), 'where rid=? and version=? order by id asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $column = new Columns();
                $column->createFromRow($row);
                $this->columns[$column->name] = $column->value;
            }
        }
    }

    public function getColumns2() {
        $this->connect();
        $return = $this->select('resource_columns', array('*'), 'ii', array($this->id, $this->version), 'where rid=? and version=? order by id asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $column = new Columns();
                $column->createFromRow($row);
                $this->columns[$column->name] = array($column->value, $column->link);
            }
        }
    }

    public function getVersionColumns($version) {
        $this->connect();
        $return = $this->select('resource_columns', array('*'), 'ii', array($this->id, $version), 'where rid=? and version=? order by id asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $column = new Columns();
                $column->createFromRow($row);
                $this->columns[$column->name] = $column->value;
            }
        }
    }

    public function getVersionInfo($version) {
        $this->connect();
        $return = $this->select('resource_versions', array('*'), 'ii', array($this->id, $version), 'where rid=? and version=? order by id asc limit 1');
        $this->close();

        return $return[0];
    }

    public function getLatestVersionNum() {
        $this->connect();
        $return = $this->select('resource_versions', array('version'), 'i', array($this->id), 'where rid=? order by version desc limit 1');
        $this->close();

        if (count($return) > 0) {
            return $return[0]['version'];
        } else {
            return 0;
        }
    }

    public function getLastCuratedVersionNum(){
        $this->connect();
        $return = $this->select('resource_versions', array('version'), 'i', array($this->id), 'where rid=? and status="curated" order by version desc limit 1');
        $this->close();

        if(count($return) > 0){
            return $return[0]['version'];
        }else{
            return 0;
        }
    }

    public function getVersions() {
        $this->connect();
        $return = $this->select('resource_versions', array('*'), 'i', array($this->id), 'where rid=? order by version desc');
        $this->close();

        return $return;
    }

    public function createVersion($vars) {
        $version = $this->getLatestVersionNum() + 1;
        $timestamp = time();
        $this->connect();
        $this->insert('resource_versions', 'iisiiisiii', array(null, $vars['uid'], $vars['email'], $vars['cid'], $this->id, $version, 'Pending', $timestamp, 0, $timestamp));
        $this->close();
    }

    public function updateVersion($version) {
        $this->connect();
        $this->version = $version;
        $this->update('resources', 'ii', array('version'), array($this->version, $this->id), 'where id=?');
        $this->close();
    }

    private function updateVersionTime($version, $time){
        $this->connect();
        $this->update("resource_versions", "iii", Array("time"), Array($time, $this->id, $version), "where rid=? and version=?");
        $this->close();
    }

    public function updateStatus($status, $version, $uid) {
        $latest_version = $this->getLatestVersionNum();
        $update_time = time();
        $this->connect();
        $this->status = $status;
        $this->update('resource_versions', 'siiii', array('status', 'last_curator', 'curate_time'), array($status, $uid, $update_time, $version, $this->id), 'where version=? and rid=?');
        if($latest_version == $version){
            $this->update('resources', 'si', array('status'), array($status, $this->id), 'where id=?');
        }
        $this->close();
    }

    public function insertColumns() {
        $vars['uid'] = $this->uid;
        $vars['rid'] = $this->id;
        $vars['version'] = $this->getLatestVersionNum();
        foreach ($this->columns as $key => $value) {
            $vars['name'] = $key;
            $vars['value'] = $value;
            $column = new Columns();
            $column->create($vars);
            $column->insertDB();
        }
    }

    public function insertColumns2() {
        $vars['uid'] = $this->uid;
        $vars['rid'] = $this->id;
        $vars['version'] = $this->getLatestVersionNum();

        /* get fields */
        $holder = new Resource_Fields();
        $fields = $holder->getByType($this->typeID, $this->cid);
        $fields_set = Array();
        foreach($fields as $field) {
            $fields_set[$field->name] = $field;
        }

        foreach ($this->columns as $key => $array) {
            if(!isset($fields_set[$key]) || !$fields_set[$key]->validateSingle($array[0])) continue;
            $vars['name'] = $key;
            $vars['value'] = $array[0];
            $vars['link'] = $array[1];
            $column = new Columns();
            $column->create($vars);
            $column->insertDB();
        }
    }

    private function deleteVersionColumns($version){
        if(!$this->id) return false;
        $this->connect();
        $this->delete("resource_columns", "ii", Array($this->id, $version), "where rid=? and version=?");
        $this->close();
        return true;
    }

    public function updateColumns($ver, $args){
        $last_version = $this->getVersionInfo($this->getLatestVersionNum());
        if($last_version['status'] == "Pending" && $last_version['uid'] == $ver['uid']) $need_new_version = false;
        else $need_new_version = true;

        if($need_new_version){
            $this->createVersion($ver);
            $this->updateVersion($this->getLatestVersionNum()); // $this->getLatestVersionNum needs to be called twice, do not assign it to a variable
            $this->updateStatus("Pending", $this->version, 0);
        }else{
            $this->version = $this->getLatestVersionNum();
            $this->deleteVersionColumns($this->version);
            $this->updateVersionTime($this->getLatestVersionNum(), time());
        }
        $this->columns = $args;
        $this->insertColumns2();
    }

    public function isAuthorizedOwner($uid){
        if(is_null($uid)) return false;
        $user = new User();
        $user->getByID($uid);
        if(!$user->id) return false;
        if($user->role > 0) return true;

        if(ResourceUserRelationship::isResourceOwner($this->id, $uid)) return true;

        return false;
    }

    static function isOwnerOfAnyResource($uid){
        $cxn = new Connection();
        $cxn->connect();
        $result = $cxn->select("resource_owners", Array("*"), "i", Array($uid), "where uid=?");
        $cxn->close();

        if(count($result) > 0) return true;
        return false;
    }

    public function setImage(&$file, $server, $user){
        $user_id = !!$user ? $user->id : 0;
        if(!$this->id) throw new Exception("resource not set");
        if(isset($file)){
            if ($file && $file['error'] != 4) {
                $allowedExts = array("jpg", "jpeg", "gif", "png");
                $extension = end(explode(".", $file["name"]));
                if (($file["size"] < 5000000)&& in_array(strtolower($extension), $allowedExts)) {
                    if ($file["error"] > 0) {
                        return false;
                    } else {
                        $name = $this->id . '.png';
                        $full_file_name = $server["DOCUMENT_ROOT"] . "/upload/resource-images/" . $name;
                        file_put_contents($full_file_name, file_get_contents($file["tmp_name"]));
                        @unlink($file["tmp_name"]);
                        $this->image = $name;

                        $this->connect();
                        $this->update("resources", "sii", Array("image", "image_uploader"), Array($this->image, $user_id, $this->id), "where id=?");
                        $this->close();

                        return true;
                    }
                } else {
                    return false;
                }
            }
        }
    }

    public function updateUUID($uuid){
        if($this->id){
            $this->connect();
            $this->uuid = $uuid;
            $this->update("resources", "si", Array("uuid"), Array($uuid, $this->id), "where id=?");
            $this->close();
        }
    }

    public function updateTypeID($type_id) {
        if ($this->id) {
            $this->connect();
            $this->typeID = $type_id;
            $this->update("resources", "si", Array("typeID"), Array($type_id, $this->id), "where id=?");
            $this->close();
        }
    }

    static function nameMap(){
        $cxn = new Connection();
        $cxn->connect();
        $map = $cxn->select('(select * from resource_columns where name = "Resource Name" order by version) x', Array("rid", "value"), "", Array(),"group by rid");
        $cxn->close();
        $reshaped_map = Array();
        foreach($map as $m){
            $reshaped_map[$m['rid']] = $m['value'];
        }
        return $reshaped_map;
    }

    static function updateUUIDs(){
        $cxn = new Connection();
        $cxn->connect();
        $null_resources = $cxn->select("resources", Array("id", "rid"), "", Array(), "where uuid is null");
        $cxn->close();

        foreach($null_resources as $nr){
            $query_url = Connection::environment() . '/v1/federation/data/nlx_144509-1.xml?filter=Resource%20ID:' . $nr['rid'] . '&exportType=all';
            $xml = simplexml_load_file($query_url);
            if($xml){
                foreach($xml->result->results->row->data as $data){
                    if((string) $data->name == "v_uuid"){
                        $resource = new Resource();
                        $resource->getByID($nr['id']);
                        $resource->updateUUID((string) $data->value);
                        break;
                    }
                }
            }
        }
    }

    public static function idExists($id){
        $cxn = new Connection();
        $cxn->connect();
        $resources = $cxn->select("resources", Array("*"), "i", Array($id), "where id=? limit 2");
        $cxn->close();

        return count($resources) === 1;
    }

    public function submitterEmail() {
        if($this->email) return $this->email;
        $user = new User();
        $user->getByID($this->uid);
        if($user->id) {
            return $user->email;
        }
        return NULL;
    }

    public function getByUUID($uuid) {
        $this->connect();
        $return = $this->select("resources", Array("*"), "s", Array($uuid), "where uuid=?");
        $this->close();
        if(count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public static function allowedResourceTypes() {
        if(is_null(self::$_resource_types)) {
            self::$_resource_types = unserialize(file_get_contents(__DIR__ . "/../vars/resource-types.php"));
        }
        return self::$_resource_types;
    }
}

class ResourceDBO extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "resources";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "uid"               => self::fieldDef("uid", "i", true),
            "email"             => self::fieldDef("email", "s", true),
            "cid"               => self::fieldDef("cid", "i", true),
            "version"           => self::fieldDef("version", "i", true),
            "rid"               => self::fieldDef("rid", "s", true),
            "original_id"       => self::fieldDef("original_id", "s", true),
            "pid"               => self::fieldDef("pid", "i", true),
            "parent"            => self::fieldDef("parent", "s", true),
            "type"              => self::fieldDef("type", "s", true),
            "typeID"            => self::fieldDef("typeID", "i", true),
            "status"            => self::fieldDef("status", "s", true),
            "insert_time"       => self::fieldDef("insert_time", "i", true),
            "edit_time"         => self::fieldDef("edit_time", "i", true),
            "curate_time"       => self::fieldDef("curate_time", "i", true),
            "image"             => self::fieldDef("image", "s", true),
            "image_uploader"    => self::fieldDef("image_uploader", "i", true),
            "uuid"              => self::fieldDef("uuid", "s", true),
        );
    }

    private $_columns;
    private $_highest_version;
    private $_highest_curated_version;

    public static function createNewObj() {
        return NULL;
    }

    public static function deleteObj() {
        return;
    }

    public function arrayForm() {
        return Array();
    }

    public function getColumn($name, $curated=false) {
        $columns = $this->columns();
        if($curated) {
            return $columns[$this->_highest_curated_version]["columns"][$name]->value;
        } else {
            return $columns[$this->_highest_version]["columns"][$name]->value;
        }
    }

    public function columns($refresh = false) {
        if(is_null($this->_columns) || $refresh) {
            $this->_highest_version = 0;
            $this->_highest_curated_version = 0;

            $cxn = new Connection();
            $cxn->connect();
            $column_rows = $cxn->select("resource_columns", Array("*"), "i", Array($this->id), "where rid=?");
            $version_rows = $cxn->select("resource_versions", Array("*"), "i", Array($this->id), "where rid=?");
            $cxn->close();

            $this->_columns = Array();
            foreach($version_rows as $vr) {
                if($vr["status"] == "Curated" && $vr["version"] > $this->_highest_curated_version) {
                    $this->_highest_curated_version = $vr["version"];
                }
                if($vr["version"] > $this->_highest_version) {
                    $this->_highest_version = $vr["version"];
                }
                $this->_columns[$vr["version"]] = Array("status" => $vr["status"], "columns" => Array());
            }
            foreach($column_rows as $cr) {
                $col = new Columns();
                $col->createFromRow($cr);
                $this->_columns[$col->version]["columns"][$col->name] = $col;
            }
        }
        return $this->_columns;
    }
}
ResourceDBO::init();

class Columns extends Connection {

    public $id;
    public $rid;
    public $version;
    public $name;
    public $value;
    public $link;
    public $time;

    public $dbTypes = 'iiisssi';

    public function create($vars) {
        $this->rid = $vars['rid'];
        $this->version = $vars['version'];
        $this->name = $vars['name'];
        $this->value = $vars['value'];
        $this->link = $vars['link'];
        $this->time = time();
    }

    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->rid = $vars['rid'];
        $this->version = $vars['version'];
        $this->name = $vars['name'];
        $this->value = $vars['value'];
        $this->link = $vars['link'];
        $this->time = $vars['time'];
    }

    public function insertDB() {
        $this->connect();
        $this->id = $this->insert('resource_columns', $this->dbTypes, array(null, $this->rid, $this->version, $this->name, $this->value, $this->link, $this->time));
        $this->close();
    }

}

class Resource_Type extends Connection {

    public $id;
    public $uid;
    public $cid;
    public $name;
    public $description;
    public $parent;
    public $facet;
    public $url;
    public $time;

    public function create($vars) {
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->name = $vars['name'];
        $this->description = $vars['description'];
        $this->parent = $vars['parent'];
        $this->facet = $vars['facet'];
        $this->url = $vars['url'];
        $this->time = time();
    }

    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->name = $vars['name'];
        $this->description = $vars['description'];
        $this->parent = $vars['parent'];
        $this->facet = $vars['facet'];
        $this->url = $vars['url'];
        $this->time = $vars['time'];
    }

    public function insertDB() {
        $this->connect();
        $this->id = $this->insert('resource_type', 'iiississi', array(null, $this->uid, $this->cid, $this->name, $this->description, $this->parent, $this->facet, $this->url, $this->time));
        $this->close();
    }

    public function updateDB() {
        $this->connect();
        $this->update('resource_type', 'ssissi', array('name', 'description', 'parent', 'facet', 'url'), array($this->name, $this->description, $this->parent, $this->facet, $this->url, $this->id), 'where id=?');
        $this->close();
    }

    public function deleteDB() {
        $this->connect();
        $this->delete('resource_type', 'i', array($this->id), 'where id=?');
        $this->delete('community_relationships', 'i', array($this->id), 'where rid=?');
        $this->close();
    }

    public function getAllNotMade($cid) {
        $this->connect();
        $return = $this->select('resource_type', array('*'), 'i', array($cid), 'where cid != ? order by name asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $type = new Resource_Type();
                $type->createFromRow($row);
                $finalArray[] = $type;
            }
        }
        return $finalArray;
    }

    public function getAll() {
        $this->connect();
        $return = $this->select('resource_type', array('*'), null, array(), 'order by name asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $type = new Resource_Type();
                $type->createFromRow($row);
                $finalArray[] = $type;
            }
        }
        return $finalArray;
    }

    public function getByCommunity($cid) {
        $this->connect();
        $return = $this->select('resource_type', array('*'), 'i', array($cid), 'where cid=?');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $type = new Resource_Type();
                $type->createFromRow($row);
                $finalArray[] = $type;
            }
        }
        return $finalArray;
    }

    public function getByName($name, $cid) {
        $this->connect();
        $return = $this->select('resource_type', array('*'), 'is', array($cid, $name), 'where cid=? and name=? limit 1');

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        } else {
            $return = $this->select('resource_type', array('*'), 's', array($name), 'where cid=0 and name=? limit 1');
            if (count($return) > 0)
                $this->createFromRow($return[0]);
        }
        $this->close();
    }

    public function getByID($id) {
        $this->connect();
        $return = $this->select('resource_type', array('*'), 'i', array($id), 'where id=? limit 1');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function checkValidFields($fields) {
        // get resource fields
        $holder = new Resource_Fields();
        $resource_fields = $holder->getByType($this->id, $this->cid);

        // foreach field, make sure required fields are set
        foreach($resource_fields as $field) {
            if($field->required) {
                if(!isset($fields[$field->name])) return false;
                if(is_array($fields[$field->name])) {
                     if($fields[$field->name][0] == "" && $fields[$field->name][1] == "") return false;
                } else {
                    if($fields[$field->name] == "") return false;
                }
            }
        }
        return true;
    }

}

class Resource_Fields extends Connection {

    public $id, $uid, $tid, $cid, $required, $position;
    public $name, $type, $display, $autocomplete, $alt;
    public $login, $curator, $hidden, $weight, $time;

    public function create($vars) {
        $this->uid = $vars['uid'];
        $this->tid = $vars['tid'];
        $this->cid = $vars['cid'];
        $this->required = $vars['required'];
        $this->position = $vars['position'];
        $this->name = $vars['name'];
        $this->type = $vars['type'];
        $this->display = $vars['display'];
        $this->autocomplete = $vars['autocomplete'];
        $this->alt = $vars['alt'];
        $this->login = $vars['login'];
        $this->curator = $vars['curator'];
        $this->hidden = $vars['hidden'];
        $this->weight = 1;
        $this->time = time();
    }

    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->tid = $vars['tid'];
        $this->cid = $vars['cid'];
        $this->required = $vars['required'];
        $this->position = $vars['position'];
        $this->name = $vars['name'];
        $this->type = $vars['type'];
        $this->display = $vars['display'];
        $this->autocomplete = $vars['autocomplete'];
        $this->alt = $vars['alt'];
        $this->login = $vars['login'];
        $this->curator = $vars['curator'];
        $this->hidden = $vars['hidden'];
        $this->weight = $vars['weight'];
        $this->time = $vars['time'];
    }

    public function updateData($vars) {
        $this->required = $vars['required'];
        $this->name = $vars['name'];
        $this->type = $vars['type'];
        $this->display = $vars['display'];
        $this->autocomplete = $vars['autocomplete'];
        $this->alt = $vars['alt'];
        $this->login = $vars['login'];
        $this->curator = $vars['curator'];
        $this->hidden = $vars['hidden'];
    }

    public function insertDB() {
        $this->connect();
        $this->id = $this->insert('resource_fields', 'iiiiiisssssiiiii', array(null, $this->uid, $this->tid, $this->cid, $this->required, $this->position, $this->name, $this->type, $this->display, $this->autocomplete, $this->alt, $this->login, $this->curator, $this->hidden, $this->weight, $this->time));
        $this->close();
    }

    public function updateDB() {
        $this->connect();
        $this->update('resource_fields', 'sssssiiiii', array('name', 'type', 'display', 'autocomplete', 'alt', 'login', 'curator', 'hidden', 'required'), array($this->name, $this->type, $this->display, $this->autocomplete, $this->alt, $this->login, $this->curator, $this->hidden, $this->required, $this->id), 'where id=?');
        $this->close();
    }

    public function getByID($id) {
        $this->connect();
        $return = $this->select('resource_fields', array('*'), 'i', array($id), 'where id=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function shiftAll($num) {
        $this->connect();
        $this->increment('resource_fields', 'iiii', array('position'), array($num, $this->cid, $this->position, $this->tid), 'where cid=? and position>? and tid=?');
        $this->close();
        $this->position++;
    }

    public function swap($direction) {
        $this->connect();
        if ($direction == 'up') {
            if ($this->position > 0) {
                $this->update('resource_fields', 'iiii', array('position'), array($this->position, $this->cid, (int)($this->position - 1), $this->tid), 'where cid=? and position=? and tid=?');
                $this->update('resource_fields', 'ii', array('position'), array((int)($this->position - 1), $this->id), 'where id=?');
            }
        } else {
            $this->update('resource_fields', 'iiii', array('position'), array($this->position, $this->cid, (int)($this->position + 1), $this->tid), 'where cid=? and position=? and tid=?');
            $this->update('resource_fields', 'ii', array('position'), array((int)($this->position + 1), $this->id), 'where id=?');
        }
        $this->close();
    }

    public function getByType($tid, $cid) {
        $this->connect();
        $return = $this->select('resource_fields', array('*'), 'ii', array($tid, $cid), 'where (tid=0 or tid=?) and (cid=? or cid=0) order by tid asc,cid asc,position asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $field = new Resource_Fields();
                $field->createFromRow($row);
                $finalArray[] = $field;
            }
        }
        return $finalArray;
    }

// Manu -- for OSC fields
    public function getOSCFields() {  //($tid, $cid) {
        $tid=45;
	$cid = 57;
        $this->connect();
        $return = $this->select('resource_fields', array('*'), 'ii', array($tid, $cid), 'where (tid=0 or tid=?) and (cid=? or cid=0) order by tid asc,cid asc,position asc');
        $this->close();
        $osc_fields=array("Resource Name"=>"Name of the product","Description"=>"Description of the product",
                    "Resource URL"=>"Product website", "Keywords"=>"Keywords", "Defining Citation"=>"Cite as",
                    "Funding Information"=>"Funding Information", "Open Science Chain ID"=>"Open Science Chain ID",
                    "License Information"=>"License Information", "Product Type"=>"Product Type");

        if (count($return) > 0) {
            foreach ($return as $row) {

                // Manu
                if (! array_key_exists($row["name"], $osc_fields)) {
                  continue;
                } else {
                  ; //$row["name"] = $osc_fields[$row["name"]];
                }
                echo " ************* ", $row["name"]; 

                $field = new Resource_Fields();
                $field->createFromRow($row);
                $finalArray[] = $field;
                
            }
        }
        return $finalArray;
    }


    public function getPage1() {
        $this->connect();
        $return = $this->select('resource_fields', array('*'), null, array(), 'where tid=0 and cid=0 order by tid asc,cid asc,position asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $field = new Resource_Fields();
                $field->createFromRow($row);
                $finalArray[] = $field;
            }
        }
        return $finalArray;
    }

    public function getPage2($cid, $tid) {
        if($tid == 0) return Array();
        $this->connect();
        $return = $this->select('resource_fields', array('*'), 'iii', array($cid, $tid, $tid), 'where (cid=? and tid=?) or (cid=0 and tid=?) order by tid asc,cid asc,position asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $field = new Resource_Fields();
                $field->createFromRow($row);
                $finalArray[] = $field;
            }
        }
        return $finalArray;
    }

    public function getFormHTML($value, $extra, $type, $curator) {
        if ($this->curator && !$curator)
            return '';
        if ($this->required) {
            $attrs = 'required="required"';
            $req = '<span style="color:#bb0000">*</span>';
            $class = ' required';
        } else {
            $attrs = '';
            $req = '';
            $class = '';
        }

// Manu Start
        $osc_fields=array("Resource Name"=>"Name of the product","Description"=>"Description of the product",
         "Resource URL"=>"Product website", "Keywords"=>"Keywords", "Defining Citation"=>"Cite as",
         "Funding Information"=>"Funding Information");

        $disp_name = '';
        if (array_key_exists($this->name, $osc_fields)) {
           $disp_name = $osc_fields[$this->name];
        } else {
           $disp_name = $this->name;
        } 
// Manu End

        

        switch ($this->type) {
            case 'text':
                if ($this->name == 'Resource Name')
                    $html = '<section><label class="label">' . $disp_name .  $req . '</label>';
                   // Manu orig  $html = '<section><label class="label">' . $type->name . ' Name ' . $req . '</label>';
                   // Manu  $html = '<section><label class="label">' . ' Name of the product' . $req . '</label>';
                else
                    $html = '<section><label class="label">' . $disp_name . ' ' . $req . '</label>';
                   // Manu orig  $html = '<section><label class="label">' . $this->name . ' ' . $req . '</label>';
                $html .= '<label class="input">';


                if ($extra)
                    $html .= '<input ' . $extra . ' type="text" class="review-' . str_replace(' ', '_', $this->name) . $class . '" placeholder="' . $this->alt . '" ' . $attrs . '>';
                elseif ($this->autocomplete) {
                    $html .= '<input type="text" class="resource-field field-autocomplete ' . str_replace(' ', '_', $this->name) . $class . '" category="' . $this->autocomplete . '" name="' . str_replace(' ', '_', $this->name) . '" placeholder="' . $this->alt . '" value="' . $value . '" ' . $attrs . '>';
                    $html .= '<input type="hidden" class="autoValues" name="' . str_replace(' ', '_', $this->name) . '-val"/>';
                    $html .= '<div class="autocomplete_append auto" style="z-index:10"></div>';
                } else
                    $html .= '<input type="text" class="resource-field ' . str_replace(' ', '_', $this->name) . $class . '" name="' . str_replace(' ', '_', $this->name) . '" placeholder="' . $this->alt . '" value="' . $value . '" ' . $attrs . '>';
                $html .= '</label></section>';
                return $html;
                break;
            case 'image':
                return '<section>
                            <label class="label">' . $this->name . '</label>
                            <label for="file" class="input input-file">
                                <div class="button"><input onchange="$(this).parent().next().val($(this).val());" name="' . $this->id . '-image" type="file" id="file"
                                                           class="file-form">Browse
                                </div>
                                <input type="text" class="file-placeholder" readonly value="' . $value . '">
                            </label>
                        </section>';
                break;
            case 'textarea':
                $html = '<section>';
                $html .= '<label class="label">' . $disp_name . ' ' . $req . '</label>';
                // Manu orig $html .= '<label class="label">' . $this->name . ' ' . $req . '</label>';
                $html .= '<label class="textarea">';
                if ($extra)
                    $html .= '<textarea ' . $extra . ' rows="3" class="review-' . str_replace(' ', '_', $this->name) . $class . '" placeholder="' . $this->alt . '" ' . $attrs . '></textarea>';
                else
                    $html .= '<textarea class="resource-field ' . str_replace(' ', '_', $this->name) . $class . '" rows="3" name="' . str_replace(' ', '_', $this->name) . '" placeholder="' . $this->alt . '" ' . $attrs . '>' . $value . '</textarea>';
                $html .= '</label></section>';
                return $html;
                break;
            case 'resource-types':
                $resource_types = Resource::allowedResourceTypes();
                usort($resource_types, function($a, $b) {
                    if($a["label"] < $b["label"]) return -1;
                    if($a["label"] > $b["label"]) return 1;
                    return 0;
                });
                $values = explode(",", $value);
                $html = '<section>';
                $html .= '<label class="label">' . $this->name . ' ' . $req . '</label>';
                $html .= '<select name="' . str_replace(' ', '_', $this->name) . '[]" multiple class="multi-select-resource-types">';
                foreach($resource_types as $rt) {
                    $selected = in_array($values, $rt["label"]) ? "selected" : "";
                    $html .= '<option ' . $selected . ' value="' . $rt["label"] . '">' . $rt["label"] . '</option>';
                }
                $html .= '</select>';
                $html .= '</section>';
                return $html;
                break;
            case 'funding-types':
                $html = '<section>';
                    $html .= '<label class="label">' . $this->name . ' ' . $req . '</label>';
                    $html .= '<div class="funding-fields">';
                        $html .= '<div class="row" style="margin-bottom:20px">';

                            $html .= '<div class="col-md-3">';
                                $html .= '<div class="input-group">';
                                    $html .= '<span class="input-group-addon">Funding agency</span>';
                                    $html .= '<input class="form-control" type="text" name="' . str_replace(' ', '_', $this->name) . '[]" />';
                                $html .= '</div>';
                            $html .= '</div>';

                            $html .= '<div class="col-md-3">';
                                $html .= '<div class="input-group">';
                                    $html .= '<span class="input-group-addon">Funding ID</span>';
                                    $html .= '<input class="form-control" type="text" name="' . str_replace(' ', '_', $this->name) . '[]" />';
                                $html .= '</div>';
                            $html .= '</div>';

                        $html .= '</div>';
                    $html .= '</div>';

                    $html .= '<div class="btn btn-default add-resource-funding-info-field">Add another</div>';
                $html .= '</section>';
                return $html;
                break;
        }
    }

    public static function validate(&$args, $fields, $user=NULL, $resource=NULL){  // this function mutates args
        if(!is_null($resource)) $resource->getColumns();
        foreach($fields as $i => $field){
            if($field->required && !isset($args[$field->name])) return "missing required field";
            if($field->name == "Defining Citation" && $args[$field->name]){
                $citations = explode(",", $args[$field->name][0]);
                $regex = "/(^[ ]?PMID:\s*[0-9]+[ ]?$|^[ ]?DOI:[^ ]+[ ]?$)/";
                foreach($citations as $cite){
                    if(!preg_match($regex, $cite)) return "improperly formatted defining citation";
                }
            }
            if($field->display == "url"){
                if(isset($args[$field->name])){
                    $arg = &$args[$field->name][0];
                    if(substr($arg, 0, 4) != "http"){
                        $arg = "http://" . $arg;
                    }
                }
            }
            if($field->display == "owner-text" && isset($args[$field->name])) {
                $message = "user cannot edit this field";
                if(is_null($resource) || is_null($user)) return $message;
                if($args[$field->name][0] !== $resource->columns[$field->name] && !$resource->isAuthorizedOwner($user->id)) return $message;
            }
            if($field->type == "resource-types" && !$field->validateSingle($args[$field->name][0])) {
                $message = "Invalid resource type(s)";
                return $message;
            }
        }
        return true;
    }

    public function validateSingle($val) {
        /* don't validate for now */
        return true;
        if($this->type == "resource-types") {
            $resource_types = Resource::allowedResourceTypes();
            $resource_types_array = Array();
            foreach($resource_types as $rt) {
                $resource_types_array[$rt["label"]] = true;
            }
            $vals = explode(",", $val);
            foreach($vals as $val) {
                if(!isset($resource_types_array[$val])) return false;
            }
        }
        return true;
    }
}

class Form_Relationship extends Connection {

    public $id;
    public $uid;
    public $cid;
    public $rid;
    public $type;
    public $query;
    public $time;

    public function create($vars) {
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->rid = $vars['rid'];
        $this->type = $vars['type'];
        $this->query = $vars['query'];
        $this->time = time();
    }

    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->rid = $vars['rid'];
        $this->type = $vars['type'];
        $this->query = $vars['query'];
        $this->time = $vars['time'];
    }

    public function insertDB() {
        $this->connect();
        $this->id = $this->insert('community_relationships', 'iiiissi', array(null, $this->uid, $this->cid, $this->rid, $this->type, $this->query, $this->time));
        $this->close();
    }

    public function deleteDB() {
        $this->connect();
        $this->delete('community_relationships', 'i', array($this->id), 'where id=?');
        $this->close();
    }

    public function getByID($id) {
        $this->connect();
        $return = $this->select('community_relationships', array('*'), 'i', array($id), 'where id=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getByRID($cid, $rid) {
        $this->connect();
        $return = $this->select('community_relationships', array('*'), 'ii', array($cid, $rid), 'where cid=? and rid=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getByCommunity($cid, $type) {
        $this->connect();
        $return = $this->select('community_relationships', array('*'), 'is', array($cid, $type), 'where cid=? and type=?');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $relationship = new Form_Relationship();
                $relationship->createFromRow($row);
                $finalArray[] = $relationship;
            }
        }
        return $finalArray;
    }
}

class Resource_Relationships extends Connection {

    public $id;
    public $uid;
    public $reltype_id;
    public $canon_id;
    public $id1;
    public $id2;
    public $timestamp;

    public function create($vars) {
        $this->uid = $vars['uid'];
        $this->reltype_id = $vars['reltype_id'];
        $this->canon_id = $vars['canon_id'];
        $this->id1 = $vars['id1'];
        $this->id2 = $vars['id2'];
        $this->timestamp = time();
    }

    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->reltype_id = $vars['reltype_id'];
        $this->canon_id = $vars['canon_id'];
        $this->id1 = $vars['id1'];
        $this->id2 = $vars['id2'];
        $this->timestamp = $vars['timestamp'];
    }

    public function insertDB() {
        $this->connect();
        $this->id = $this->insert('resouece_relationships', 'iiiissi', array(null, $this->uid, $this->reltype_id, $this->canon_id, $this->id1, $this->id2, $this->timestamp));
        $this->close();
    }

    public function deleteDB() {
        $this->connect();
        $this->delete('resouece_relationships', 'i', array($this->id), 'where id=?');
        $this->close();
    }

    public function getByID($id) {
        $this->connect();
        $return = $this->select('resouece_relationships', array('*'), 'i', array($id), 'where id=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getByRID($cid, $rid) {
        $this->connect();
        $return = $this->select('resouece_relationships', array('*'), 'ii', array($cid, $rid), 'where cid=? and rid=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function getByRelationships($cid, $type) {
        $this->connect();
        $return = $this->select('resouece_relationships', array('*'), 'is', array($cid, $type), 'where cid=? and type=?');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $relationship = new Resouece_Relationships();
                $relationship->createFromRow($row);
                $finalArray[] = $relationship;
            }
        }
        return $finalArray;
    }
}

?>
