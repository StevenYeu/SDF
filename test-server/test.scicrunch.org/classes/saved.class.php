<?php

class Saved extends Connection {

    public $id;
    public $uid;
    public $name;
    public $cid;
    public $category;
    public $subcategory;
    public $nif;
    public $query;
    public $display;
    public $params;
    public $weekly;
    public $time;

    public function create($vars){
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->name = $vars['name'];
        $this->category = $vars['category'];
        $this->subcategory = $vars['subcategory'];
        $this->nif = $vars['nif'];
        $this->query = $vars['query'];
        $this->display = $vars['display'];
        $this->params = $vars['params'];
        $this->weekly = $vars['weekly'];
        $this->time = time();
    }

    public function createFromRow($vars){
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->name = $vars['name'];
        $this->cid = $vars['cid'];
        $this->category = $vars['category'];
        $this->subcategory = $vars['subcategory'];
        $this->nif = $vars['nif'];
        $this->query = $vars['query'];
        $this->display = $vars['display'];
        $this->params = $vars['params'];
        $this->weekly = $vars['weekly'];
        $this->time = $vars['time'];
    }

    public function insertDB(){
        $this->connect();
        $this->id = $this->insert('saved_searches','iisissssssii',array(null,$this->uid,$this->name,$this->cid,$this->category,$this->subcategory,$this->nif,$this->query,$this->display,$this->params,$this->weekly,$this->time));
        $this->close();
    }

    public function returnURL($sub_id=NULL, $current_community=NULL){
        $community = new Community();
        $community->getByID($this->cid);
        if(!is_null($current_community) && $current_community->redirect_url && $this->cid == $current_community->id) $url = $current_community->redirect_url . "/" . $community->portalName . "/" . $this->category;  // if in community with its own hostname
        else if($current_community->type == "interlex") $url = PROTOCOL . '://' . FQDN . '/'.$current_community->portalName.'/'.$current_community->type;
        else $url = PROTOCOL . '://' . FQDN . '/'.$community->portalName.'/'.$this->category;
        if($this->subcategory){
            $url .= '/'.$this->subcategory;
        }
        if($this->nif){
            $url .= '/source/'.$this->nif;
        }
        $url .= '/search?q='.$this->query.$this->params;
        if(!is_null($sub_id)) $url .= "&notif=".$sub_id;

        // remove pages
        $url = preg_replace("/page=\d+(&)?/", "", $url);
        if($url[strlen($url) - 1] == "&") $url = substr($url, 0, strlen($url) - 1);

        return $url;
    }

    public function getByID($id){
        $this->connect();
        $return = $this->select('saved_searches',array('*'),'i',array($id),'where id=?');
        $this->close();

        if(count($return)>0){
            $this->createFromRow($return[0]);
        }
    }

    public function updateName($name){
        $this->connect();
        $this->update('saved_searches','si',array('name'),array($name,$this->id),'where id=?');
        $this->close();
    }

    public function checkExist($vars){
        $this->connect();
        $return = $this->select('saved_searches',array('id','name'),'iisssss',array($vars['uid'],$vars['cid'],$vars['category'],$vars['subcategory'],$vars['nif'],$vars['query'],$vars['params']),'where uid=? and cid=? and category=? and subcategory=? and nif=? and query=? and params=?');
        $this->close();

        if(count($return)>0){
            $saved = new Saved();
            $saved->createFromRow($return[0]);
            return $saved;
        } else {
            return false;
        }
    }

    public function getUserSearches($uid){
        $this->connect();
        $return = $this->select('saved_searches',array('*'),'i',array($uid),'where uid=?');
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $saved = new Saved();
                $saved->createFromRow($row);
                $finalArray[] = $saved;
            }
        }

        return $finalArray;
    }

    public function deleteDB(){
        $this->connect();
        $this->delete('saved_searches','i',array($this->id),'where id=?');
        $this->close();
    }

    public function nifServicesType() {
        if($this->category === "literature") return "saved-search-literature";
        if($this->category === "data") {
            if(!($this->nif)) return "saved-search-summary";
        }
        return "saved-search-data";
    }

    public function searchVars() {
        $search_vars = Array();
        $community = new Community();
        $community->getByID($this->cid);
        $community->getCategories();
        $search_vars["portalName"] = $community->portalName;
        $search_vars["category"] = $this->category;
        if($this->subcategory) $search_vars["subcategory"] = $this->subcategory;
        $search_vars["q"] = $this->query ? \helper\decodeUTF8($this->query) : "*";
        $search_vars["page"] = 1;
        $search_vars["community"] = $community;
        if($this->nif) $search_vars["nif"] = $this->nif;
        $search_vars = array_merge($search_vars, self::getParams($this->params));
        return $search_vars;
    }

    private static function getParams($raw_params){
        $params = Array();
        $raw_params_array = explode("&", $raw_params);
        foreach($raw_params_array as $rpa){
            if($rpa === "") continue;
            $rpa = explode("=", $rpa);
            $key = \helper\decodeUTF8($rpa[0]);
            $val = \helper\decodeUTF8($rpa[1]);
            if(\helper\endsWith($key, "[]")){
                $key = substr($key, 0, strlen($key) - 2);
                if(!isset($params[$key])) $params[$key] = Array();
                $params[$key][] = $val;
            }else{
                $params[$key] = $val;
            }
        }
        return $params;
    }

    public static function getUserSavedCount(User $user) {
        if(!$user->id) return 0;
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("saved_searches", Array("count(*)"), "i", Array($user->id), "where uid=?");
        $cxn->close();
        return $count[0]["count(*)"];
    }

}

?>
