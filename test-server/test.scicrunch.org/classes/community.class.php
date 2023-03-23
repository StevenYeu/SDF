<?php

/*
 * Community Class
 *   Class that handles everything pertaining to Communities within SciCrunch. Communities are User created entities
 *   the create webpages for a user defined community
 *
 * @internal DBTable: communities
 * @vars     DBColumns: id,uid,name,description,portalName,url,shortName,logo,private,access,resourceView,dataView,
 *     literatureView,time (iissssssiiiiii)
 */

class Community extends Connection
{

    public $id;
    public $uid;
    public $name;
    public $description;
    public $portalName;
    public $address;
    public $url;
    public $shortName;
    public $logo;
    public $private;
    public $access;
    public $resourceView;
    public $dataView;
    public $literatureView;
    public $time;
    public $about_home_view;
    public $about_sources_view;
    public $ga_code;
    public $redirect_url;
    public $altPortalName;
    public $rid;
    public $verified;
    public $front_page_visible;
    public $search_name_comm_resources;
    public $search_name_more_resources;
    public $search_name_literature;

    private $_datasetRequiredFields;
    public $components;

    public $categoryLabels;
    public $urlTree;
    public $categoryTree;
    public $savedSqls;
    public $wiki;
    public $resourceType;
    public $relationships;
    public $meta;
    public $views;
    public static $banned = array(
        "'94.19.%'",
        "'199.58%'",
        "'127.0%'",
        "'27.159%'",
        "'144.76%'",
        "'46.165%'",
        "'178.137%'",
        "'86.7.%'",
        "'204.15%'",
        "'66.249%'",
        "'5.10%'",
        "'63.147%'",
        "'208.107%'",
        "'74.112%'",
        "'173.208%'",
        "'108.59%'",
        "'83.149%'",
        "'171.65%'",
        "'202.244%'",
        "'24.239%'");
    static public $invalidCommunities = Array('faq','account','browse','create','error','information','versions','news', 'page', 'resolver', 'api', 'js', 'templates', 'php', 'forms', 'css');

    public $dbTypes = 'iisssssssiiiiiiissisiiisssss';

    public function create($vars)
    {
        $this->uid = $vars['uid'];
        $this->name = $vars['name'];
        $this->shortName = $vars['shortName'];
        $this->description = $vars['description'];
        $this->address = $vars['address'];
        $this->portalName = $vars['portalName'];
        $this->url = $vars['url'];
        $this->private = $vars['private'];
        $this->logo = $vars['logo'];
        $this->resourceView = $vars['resourceView'];
        $this->dataView = $vars['dataView'];
        $this->literatureView = $vars['literatureView'];
        $this->about_home_view = $vars['about_home_view'];
        $this->about_sources_view = $vars['about_sources_view'];
        $this->ga_code = $vars['ga_code'];
        $this->redirect_url = $vars['redirect_url'];
        $this->access = $vars['access'];
        $this->altPortalName = $vars["altPortalName"];
        $this->rid = $vars["rid"];
        $this->verified = $vars["verified"];
        $this->front_page_visible = $vars["front_page_visible"];
        $this->search_name_comm_resources = $vars["search_name_comm_resources"];
        $this->search_name_more_resources = $vars["search_name_more_resources"];
        $this->search_name_literature = $vars["search_name_literature"];
        $this->mailchimp_api_key = $vars["mailchimp_api_key"];
        $this->mailchimp_default_list = $vars["mailchimp_default_list"];
    }

    /*
     * public edit
     *   Function to edit the current community object to later update the DB table with
     *
     * @param  array[] vars key:value pair where keys relate to the columns in the table
     * @return void
     */

    public function edit($vars)
    {
        $this->name = $vars['name'];
        $this->shortName = $vars['shortName'];
        $this->description = $vars['description'];
        $this->address = $vars['address'];
        $this->url = $vars['url'];
        $this->private = $vars['private'];
        $this->logo = $vars['logo'];
        $this->resourceView = $vars['resourceView'];
        $this->dataView = $vars['dataView'];
        $this->literatureView = $vars['literatureView'];
        $this->about_home_view = $vars['about_home_view'];
        $this->about_sources_view = $vars['about_sources_view'];
        $this->ga_code = $vars['ga_code'];
        $this->redirect_url = $vars['redirect_url'];
        $this->access = $vars['access'];
        $this->front_page_visible = $vars["front_page_visible"];
        $this->search_name_comm_resources = $vars["search_name_comm_resources"];
        $this->search_name_more_resources = $vars["search_name_more_resources"];
        $this->search_name_literature = $vars["search_name_literature"];
        $this->mailchimp_api_key = $vars["mailchimp_api_key"];
        $this->mailchimp_default_list = $vars["mailchimp_default_list"];
    }

    public function createFromRow($row)
    {
        $this->id = $row['id'];
        $this->uid = $row['uid'];
        $this->name = $row['name'];
        $this->shortName = $row['shortName'];
        $this->description = $row['description'];
        $this->address = $row['address'];
        $this->portalName = $row['portalName'];
        $this->url = $row['url'];
        $this->private = $row['private'];
        $this->access = $row['access'];
        $this->logo = $row['logo'];
        $this->resourceView = $row['resourceView'];
        $this->dataView = $row['dataView'];
        $this->literatureView = $row['literatureView'];
        $this->about_home_view = $row['about_home_view'];
        $this->about_sources_view = $row['about_sources_view'];
        $this->ga_code = $row['ga_code'];
        $this->redirect_url = $row['redirect_url'];
        $this->time = $row['time'];
        $this->altPortalName = $row["altPortalName"];
        $this->rid = $row["rid"];
        $this->verified = $row["verified"];
        $this->front_page_visible = $row["front_page_visible"];
        $this->search_name_comm_resources = $row["search_name_comm_resources"];
        $this->search_name_more_resources = $row["search_name_more_resources"];
        $this->search_name_literature = $row["search_name_literature"];
        $this->mailchimp_api_key = $row['mailchimp_api_key'];
        $this->mailchimp_default_list = $row['mailchimp_default_list'];
    }

    public function insertDB()
    {
        $this->connect();
        $this->id = $this->insert('communities', $this->dbTypes, array($this->id, $this->uid, $this->name, $this->description, $this->address, $this->portalName, $this->url, $this->shortName, $this->logo, $this->private, $this->access, $this->resourceView, $this->dataView, $this->literatureView, $this->about_home_view, $this->about_sources_view, $this->ga_code, $this->redirect_url, time(), $this->altPortalName, $this->rid, $this->verified, $this->front_page_visible, $this->search_name_comm_resources, $this->search_name_more_resources, $this->search_name_literature, $this->mailchimp_api_key, $this->mailchimp_default_list));
        $this->close();
    }

    public function updateDB()
    {
        $this->connect();
        $this->update('communities', 'sssssisiiiiissisiiisssssi',
            array('name', 'shortName', 'description', 'address', 'url', 'private', 'logo', 'resourceView', 'dataView', 'literatureView', 'about_home_view', 'about_sources_view', 'ga_code', 'redirect_url', 'access', 'altPortalName', "rid", "verified", "front_page_visible", "search_name_comm_resources", "search_name_more_resources", "search_name_literature", "mailchimp_api_key", "mailchimp_default_list"),
            array($this->name, $this->shortName, $this->description, $this->address, $this->url, $this->private, $this->logo, $this->resourceView, $this->dataView, $this->literatureView, $this->about_home_view, $this->about_sources_view, $this->ga_code, $this->redirect_url, $this->access, $this->altPortalName, $this->rid, $this->verified, $this->front_page_visible, $this->search_name_comm_resources, $this->search_name_more_resources, $this->search_name_literature, $this->mailchimp_api_key, $this->mailchimp_default_list, $this->id),
            'where id=?');
        $this->close();
    }

    /*
     * public join
     *   Handles the joining of a user to this community
     *
     * @internal DBTable: community_access
     * @param int    id    the ID of the user
     * @param String name  the name of the User
     * @param int    level the level at which to insert the user as
     *
     * @return void
     */

    public function join($id, $name, $level)
    {
        $this->connect();
        $return = $this->select('community_access', array('*'), 'ii', array($id, $this->id), 'where uid=? and cid=? and level > 0');
        if (count($return) == 0)
            $this->insert('community_access', 'iisiii', array(null, $id, $name, $this->id, $level, time()));
        $this->close();
    }

    /*
     * public updateUser
     *   function to handle updating a user's level in this community
     *
     * @internal DBTable: community_access
     * @param int uid   the user ID to update
     * @param int level the level to update the user to
     *
     * @return void
     */

    public function updateUser($uid, $level)
    {
        $this->connect();
        $return = $this->select('community_access', array('id'), 'ii', array($uid, $this->id), 'where uid=? and cid=?');
        if (count($return) > 0) {
            $id = $return[0]['id'];
            $this->update('community_access', 'ii', array('level'), array($level, $id), 'where id=?');
        }
        $this->close();
    }

    /*
     * public removeUser
     *   function to remove the user from the community
     *
     * @internal DBTable: community_access
     * @param int uid the user ID of the user to delete from the community
     *
     * @return void
     */

    public function removeUser($uid)
    {
        $this->connect();
        $this->delete('community_access', 'ii', array($uid, $this->id), 'where uid=? and cid=?');
        $this->close();
    }

    public function getCommCount()
    {
        $this->connect();
        $return = $this->select('communities', array('count(id)'), null, array(), '');
        $count = $return[0]['count(id)'];
        $this->close();

        return $count;
    }

    public function getUserCount()
    {
        $this->connect();
        $return = $this->select('community_access', array('count(id)'), 'i', array($this->id), 'where cid=?');
        $count = $return[0]['count(id)'];
        $this->close();

        return $count;
    }

    public function getUsers()
    {
        $this->connect();
        $return = $this->select('community_access', array('*'), 'i', array($this->id), 'where cid=?');
        $this->close();

        return $return;
    }

    public function getUser($uid){
        $this->connect();
        $return = $this->select('community_access', array('*'), 'ii', array($this->id, $uid), 'where cid=? and uid=?');
        $this->close();

        return $return;
    }

    public function getByID($id)
    {
        $this->connect();
        $return = $this->select('communities', array('*'), 'i', array($id), 'where id=?');
        if (count($return) > 0) {
            $this->createFromRow($return[0]);
            $meta = $this->select('community_metadata', array('*'), 'i', array($this->id), 'where cid=? and active=1');
            if (count($meta) > 0) {
                foreach ($meta as $row) {
                    $this->meta[(string)$row['name']] = $row['value'];
                }
            }
            $this->close();
            return true;
        } else {
            $this->close();
            return false;
        }
    }

    public function getByPortalName($name)
    {
        $this->connect();
        $return = $this->select('communities', array('*'), 's', array($name), 'where portalName=?');
        if (count($return) > 0) {
            $this->createFromRow($return[0]);
            $meta = $this->select('community_metadata', array('*'), 'i', array($this->id), 'where cid=? and active=1');
            if (count($meta) > 0) {
                foreach ($meta as $row) {
                    $this->meta[(string)$row['name']] = $row['value'];
                }
            }
            $this->close();
            return true;
        } else {
            $this->close();
            return false;
        }
    }

    public function getCategories()
    {
        $this->urlTree = array();
        $categories = Category::getCategories($this->id);
        if ($categories) {
            foreach ($categories as $category) {
                if ($category->source) {
                    if ($category->subcategory) {
                        $this->urlTree[$category->category]['subcategories'][$category->subcategory]['urls'][] = Connection::environment() . '/v1/federation/data/' . $category->source . '.xml?orMultiFacets=true' . $category->filter . $category->facet;
                        $this->urlTree[$category->category]['subcategories'][$category->subcategory]['nif'][] = $category->source;
                        $this->urlTree[$category->category]['subcategories'][$category->subcategory]['objects'][] = $category;
                        $this->views[$category->source] = true;
                    } else {
                        $this->urlTree[$category->category]['urls'][] = Connection::environment() . '/v1/federation/data/' . $category->source . '.xml?orMultiFacets=true' . $category->filter . $category->facet;
                        $this->urlTree[$category->category]['nif'][] = $category->source;
                        $this->urlTree[$category->category]['objects'][] = $category;
                        $this->views[$category->source] = true;
                    }
                }
            }
        }
    }

    public function getAllCategories()
    {
        $categories = Category::getCategories($this->id);
        if ($categories) {
            foreach ($categories as $category) {
                if ($category->subcategory) {
                    $this->urlTree[$category->category]['subcategories'][$category->subcategory]['urls'][] = Connection::environment() . '/v1/federation/data/' . $category->source . '.xml?orMultiFacets=true' . $category->filter . $category->facet;
                    $this->urlTree[$category->category]['subcategories'][$category->subcategory]['nif'][] = $category->source;
                    $this->urlTree[$category->category]['subcategories'][$category->subcategory]['objects'][] = $category;
                    $this->views[$category->source] = true;
                } else {
                    $this->urlTree[$category->category]['urls'][] = Connection::environment() . '/v1/federation/data/' . $category->source . '.xml?orMultiFacets=true' . $category->filter . $category->facet;
                    $this->urlTree[$category->category]['nif'][] = $category->source;
                    $this->urlTree[$category->category]['objects'][] = $category;
                    $this->views[$category->source] = true;
                }
            }
        }
    }

    public function searchCommunities($cids, $query, $offset, $limit, $verified_only = false)
    {
        $cis = Array();
        if ($cids) {
            foreach ($cids as $cid) {
                $cis[] = 'id=' . $cid;
            }
            $where = ' or ' . implode(' or ', $cis);
        } else $where = '';
        $case = "IF(comm.name LIKE ?,  20, IF(name LIKE ?, 10, 0)) +
                          IF(comm.description LIKE ?, 5,  0) +
                          IF(comm.portalName   LIKE ?, 15,  0)  AS weight";
        $verified_string = $verified_only ? " and verified=1" : "";
        $this->connect();
        $return = $this->select('communities as comm', array('SQL_CALC_FOUND_ROWS *', $case), 'sssssss', array($query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%', '%' . $query . '%'), "where (comm.name LIKE ? OR
                             comm.description LIKE ? OR
                             comm.portalName  LIKE ?) and (private=0" . $where . ")" . $verified_string .
                             " order by verified desc, weight desc limit $offset,$limit");
        $finalArray['count'] = $this->getTotal();
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $community = new Community();
                $community->createFromRow($row);
                $finalArray['results'][] = $community;
            }
        }

        return $finalArray;
    }



// (192 - 64) / 16 = 8
// 8 ^ 3 = 512 colors

    public function communityColor()
    {
        define(COL_MIN_AVG, 64);
        define(COL_MAX_AVG, 192);
        define(COL_STEP, 16);
        $range = COL_MAX_AVG - COL_MIN_AVG;
        $factor = $range / 256;
        $offset = COL_MIN_AVG;

        $base_hash = substr(md5($this->portalName), 0, 6);
        $b_R = hexdec(substr($base_hash, 0, 2));
        $b_G = hexdec(substr($base_hash, 2, 2));
        $b_B = hexdec(substr($base_hash, 4, 2));

        $f_R = floor((floor($b_R * $factor) + $offset) / COL_STEP) * COL_STEP;
        $f_G = floor((floor($b_G * $factor) + $offset) / COL_STEP) * COL_STEP;
        $f_B = floor((floor($b_B * $factor) + $offset) / COL_STEP) * COL_STEP;

        return sprintf('#%02x%02x%02x', $f_R, $f_G, $f_B);
    }

    public function isVisible($user){
        // boolean if user can see the community id
        if($this->private == 0) return true;
        if(is_null($user)) return false;
        if($user->levels[$this->id] > 0) return true;
        return false;
    }

    public function isPageVisible($user = NULL, $page_type = NULL, $page_title = NULL) {
        // check is user can see page in community
        if(!$this->private || (!is_null($user) && isset($user->levels[$this->id]) && $user->levels[$this->id] > 0))
            return true;
        if($page_type == "join")
            return true;
        if($page_type == "about" && ($page_title == "join-request-confirm" || $page_title == "join-request-response" || $page_title == "join-request-response-expired"))
            return true;
        if($page_type === "about" && $this->id === 97) { // hard code odc-sci pages as public
            return true;
        }
        if($page_type === "about" && $this->id === 501) { // hard code odc-tbi pages as public
            return true;
        }

        if($page_type === "data" && (($this->id === 97) || ($this->id === 501))) { // hard code odc-tbi pages as public
            return true;
        }

        if(($page_type == "home" || $page_type == "community-labs") && $this->front_page_visible == 1) {
            return true;
        }

        /* check from database for community access */
        if (!is_null($user)) {
            $cxn = new Connection();
            $cxn->connect();
            $count = $cxn->select("community_access", Array("count(*)"), "ii", Array($user->id, $this->id), "where uid=? and cid=? and level > 0");
            $cxn->close();
            if ($count[0]["count(*)"] > 0)
                return true;
        }

        return false;
    }

    public function shouldHttpHostRedirect($http_host){
        if(is_null($this->redirect_url) || $this->redirect_url === "") return false;
        $http_host = strtolower($http_host);
        $redirect_url = strtolower($this->redirect_url);
        if(strpos($http_host, parse_url($redirect_url, PHP_URL_HOST)) === false) return true;
        return false;
    }

    public function httpHostRedirectURL($uri){
        return $this->redirect_url . $uri;
    }

    static public function getAllCommunities($limit = MAXINT, $offset = 0){
        $cxn = new Connection();
        $cxn->connect();
        $comm_ids = $cxn->select("communities", Array("id"), "ii", Array($offset, $limit), "limit ?, ?");
        $cxn->close();

        $comms = Array();
        foreach($comm_ids as $cid){
            $comm = new Community();
            $comm->getByID($cid["id"]);
            if(!is_null($comm->id)) $comms[] = $comm;
        }

        return $comms;
    }

    static public function totalCount(){
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("communities", Array("count(*)"), "", Array(), "");
        $cxn->close();
        return $count[0]["count(*)"];
    }

    static public function uniquePortalName($portal_name) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("communities", Array("count(*)"), "ss", Array($portal_name, $portal_name), "where portalName=? or altPortalName=?");
        $cxn->close();
        if($count[0]["count(*)"] > 0) return false;
        return true;
    }

    // does not check if unique, just valid
    static public function validPortalName($portal_name) {
        if(in_array($portal_name, self::$invalidCommunities)) return false;
        if(preg_match('/[^0-9a-zA-Z\-]/', $portal_name)) return false;
        return true;
    }

    public function isArchived() {
        return strpos($this->portalName, "-archived") !== false;
    }

    public function archive() {
        if($this->isArchived()) return;
        $this->portalName = $this->portalName . "-archived" . (string) time();
        $this->connect();
        $this->update("communities", "si", Array("portalName"), Array($this->portalName, $this->id), "where id=?");
        $this->close();
    }

    public function deArchive() {
        if(!$this->isArchived()) return;
        $position = strpos($this->portalName, "-archived");
        $portal_name =  substr($this->portalName, 0, $position);
        if(self::uniquePortalName($portal_name)) {
            $this->portalName = $portal_name;
            $this->connect();
            $this->update("communities", "si", Array("portalName"), Array($this->portalName, $this->id), "where id=?");
            $this->close();
        }
    }

    public function addSubmittedBy($resource, $user, $api_key) {
        // get community resource
        if(!$this->rid) return;
        $comm_resource = new Resource();
        $comm_resource->getByID($this->rid);
        if(!$comm_resource->id) return;

        // get user, if no user just pass empty one
        if(is_null($user) && is_null($api_key) && !$user) $user = new User();

        // add relationship
        require_once __DIR__ . "/../api-classes/add_delete_resource_relationship.php";
        addDeleteResourceRelationship($user, $api_key, "add", $resource->rid, $comm_resource->rid, $resource->rid, "res", "submitted");
    }

    public function fullURL() {
        if($this->redirect_url) return $this->redirect_url;
        if($this->portalName) {
            if ($this->portalName == "dknet") {
              $redirect = "https://dknet.org";
              return $redirect;
            } else {
              $portal_name = $this->portalName;
            }
        } else {
            $portal_name = "scicrunch";
        }
        return PROTOCOL . "://" . FQDN . "/" . $portal_name;
    }

    public static function fullURLStatic($community) {
        if($community) {
            return $community->fullURL();
        } else {
            $community = new Community();
            $community->getByID(0);
            return $community->fullURL();
        }
    }

    public function headerComponentFilePath() {
        $components = $this->components;
        if($this->id == 0) {
            return $GLOBALS["DOCUMENT_ROOT"] . "/ssi/header.php";
        } elseif(count($components["header"]) > 0) {
            $component = $components["header"][0];
            switch($component->component) {
                case 0: return $GLOBALS["DOCUMENT_ROOT"] . "/components/header/header-normal.php";
                case 1: return $GLOBALS["DOCUMENT_ROOT"] . "/components/header/header-boxed.php";
                case 2: return $GLOBALS["DOCUMENT_ROOT"] . "/components/header/header-float.php";
                case 3: return $GLOBALS["DOCUMENT_ROOT"] . "/components/header/header-flat.php";
                case 4: return $GLOBALS["DOCUMENT_ROOT"] . "/components/header/header-float-no-logo.php";
            }
        }
        return $GLOBALS["DOCUMENT_ROOT"] . "/components/header/header-normal.php";
    }

    public function footerComponentFilePath() {
        $components = $this->components;
        if(count($components["footer"]) > 0) {
            $component = $components["footer"][0];
            switch($component->component) {
                case 92: return $GLOBALS["DOCUMENT_ROOT"] . "/components/footer/footer-normal.php";
                case 91: return $GLOBALS["DOCUMENT_ROOT"] . "/components/footer/footer-light.php";
                case 90: return $GLOBALS["DOCUMENT_ROOT"] . "/components/footer/footer-dark.php";
            }
        }
        return $GLOBALS["DOCUMENT_ROOT"] . "/components/footer/footer-normal.php";
    }

    public static function trustedCID($cid) {
        /* scicrunch community */
        if($cid === 0) return true;

        /* null or other falsey value */
        if(!$cid) return false;

        $cxn = new Connection();
        $cxn->connect();
        $verified = $cxn->select("communities", Array("verified"), "i", Array($cid), "where id=?");
        $cxn->close();
        if(empty($verified)) return false;
        if($verified[0]["verified"] === 1) return true;
        return false;
    }

    public static function getPortalName($community) {
        if(!$community->portalName) return "scicrunch";
        return $community->portalName;
    }

    public function isTrusted() {
        if($this->cid === 0) return true;
        if(!$this->cid) return false;
        if($this->verified === 1) return true;
        return false;
    }

    static public function getByIDStatic($id) {
        $comm = new Community();
        $comm->getByID($id);
        if(!$comm->id && $comm->id !== 0) return NULL;
        return $comm;
    }

    public function datasetRequiredFields() {
        if(is_null($this->_datasetRequiredFields)) {
            $this->_datasetRequiredFields = CommunityDatasetRequiredField::loadArrayBy(Array("cid"), Array($this->id));
        }
        return $this->_datasetRequiredFields;
    }

    public static function getSearchNameCommResources($comm) {
        if($comm && $comm->search_name_comm_resources) return $comm->search_name_comm_resources;
        return "Community Resources";
    }

    public static function getSearchNameMoreResources($comm) {
        if($comm && $comm->search_name_more_resources) return $comm->search_name_more_resources;
        return "More Resources";
    }

    public static function getSearchNameLiterature($comm) {
        if($comm && $comm->search_name_literature) return $comm->search_name_literature;
        return "Literature";
    }

    public function isMember(User $user = NULL) {
        if(is_null($user)) return false;
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("community_access", Array("count(*)"), "ii", Array($user->id, $this->id), "where uid = ? and cid = ? and level > 0");
        $cxn->close();
        return $count[0]["count(*)"] > 0;
    }

    public function getModeratorEmails() {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select("community_access ca inner join users u on ca.uid = u.guid", Array("email"), "i", Array($this->id), "where ca.level >= 2 and u.verified = 1 and ca.cid=?");
        $cxn->close();

        $emails = Array();
        foreach($rows as $row) {
            $emails[] = $row["email"];
        }
        return $emails;
    }

    public function getUsedViewIDs() {
        return array_keys($this->views);
    }

    public function labEnabled() {
        if($this->portalName == "odc-sci") {
            return true;
        }
        if($this->portalName == "odc-tbi") {
            return true;
        }
        if($this->portalName == "vagm") {
            return true;
        }
        return false;
    }

    public function rinStyle() {
        if($this->portalName == "dknet") {
            return true;
        }
        if($this->portalName == "dknetbeta") {
            return true;
        }
        return false;
    }
}

class Category extends Connection
{

    public $id;
    public $uid;
    public $cid;
    public $x, $y, $z;
    public $category;
    public $subcategory;
    public $source;
    public $filter;
    public $facet;
    public $active;
    public $time;

    public $dbTypes = 'iiiiiisssssii';

    public function create($vars)
    {
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->x = $vars['x'];
        $this->y = $vars['y'];
        $this->z = $vars['z'];
        $this->category = $vars['category'];
        $this->subcategory = $vars['subcategory'];
        $this->source = $vars['source'];
        $this->filter = $vars['filter'];
        $this->facet = $vars['facet'];
        $this->active = 1;
        $this->time = time();
    }

    public function createFromRow($vars)
    {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->x = $vars['x'];
        $this->y = $vars['y'];
        $this->z = $vars['z'];
        $this->category = $vars['category'];
        $this->subcategory = $vars['subcategory'];
        $this->source = $vars['source'];
        $this->filter = $vars['filter'];
        $this->facet = $vars['facet'];
        $this->active = $vars['active'];
        $this->time = $vars['time'];
    }

    public function insertDB()
    {
        $this->connect();
        $this->id = $this->insert('community_structure', $this->dbTypes, array(null, $this->uid, $this->cid, $this->x, $this->y, $this->z, $this->category, $this->subcategory, $this->source, $this->filter, $this->facet, $this->active, $this->time));
        $this->close();
    }

    public function updateDB()
    {
        $this->connect();
        $this->update('community_structure', 'sssi',
            array('source', 'filter', 'facet'),
            array($this->source, $this->filter, $this->facet, $this->id),
            'where id=?');
        $this->close();
    }

    public function getCategories($cid)
    {
        $this->connect();
        $return = $this->select('community_structure', array('*'), 'i', array($cid), 'where cid=? and active=1 order by x asc, y asc, z asc, id asc');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $category = new Category();
                $category->createFromRow($row);
                $finalArray[] = $category;
            }
        }

        return $finalArray;
    }

    public function getUsed()
    {
        $this->connect();
        $return = $this->select('community_structure', array('cid', 'source'), null, array(), 'where active=1');
        $this->close();

        if (count($return) > 0) {
            foreach ($return as $row) {
                $category = new Category();
                $category->createFromRow($row);
                $finalArray[] = $category;
            }
        }

        return $finalArray;
    }

    public function getByID($id)
    {
        $this->connect();
        $return = $this->select('community_structure', array('*'), 'i', array($id), 'where id=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

    public function deleteType($type)
    {
        $this->connect();
        $rand = -rand(10, 100);
        if ($type == 'category') {
            $this->update('community_structure', 'iii', array('x'), array($rand, $this->x, $this->cid), 'where x=? and cid=?');
            $this->close();
            $this->shiftAll('x', -1, false);
            $this->connect();
            $this->delete('community_structure', 'ii', array($rand, $this->cid), 'where x=? and cid=?');
        } elseif ($type == 'subcategory') {
            $this->update('community_structure', 'iiii', array('y'), array($rand, $this->x, $this->y, $this->cid), 'where x=? and y=? and cid=?');
            $this->close();
            $this->shiftAll('y', -1, false);
            $this->connect();
            $this->delete('community_structure', 'iii', array($this->x, $rand, $this->cid), 'where x=? and y=? and cid=?');
        } elseif ($type == 'source') {
            $null = is_null($this->x) && is_null($this->y) && is_null($this->z);
            if($null) {
                $this->update('community_structure', 'ii', array('z'), array($rand, $this->cid), 'where x is NULL and y is NULL and z is NULL and cid=?');
            } else {
                $this->update('community_structure', 'iiiii', array('z'), array($rand, $this->x, $this->y, $this->z, $this->cid), 'where x=? and y=? and z=? and cid=?');
            }
            $this->close();
            $this->shiftAll('z', -1, false);
            $this->connect();
            if($null) {
                $this->delete('community_structure', 'ii', array($rand, $this->cid), 'where x is NULL and y is NULL and z=? and cid=?');
            } else {
                $this->delete('community_structure', 'iiii', array($this->x, $this->y, $rand, $this->cid), 'where x=? and y=? and z=? and cid=?');
            }
        }
        $this->close();
    }

    public function getPanelHeader($sub, $x, $y, $total, $name, $cid, $category, $subcategory)
    {
        if (!$sub)
            $html = '<div class="panel panel-dark"><div class="panel-heading" style="border-bottom: 0">';
        else
            $html = '<div class="panel panel-grey"><div class="panel-heading" style="border-bottom: 0">';

        $html .= '<h3 class="panel-title clickable" style="display: inline-block;cursor:pointer"><i class="clickable-icon fa fa-plus"></i> ' . $name . '
                                </h3>';
        $html .= '<label class="pull-right">';
        $html .= '<div class="btn-group" style="margin-top:-4px">';
        $html .= '<button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">Action<i class="fa fa-angle-down"></i></button>';
        $html .= '<ul class="dropdown-menu" role="menu">';

        if ($x > 0 && $sub == null)
            $html .= '<li><a href="/forms/community-forms/category-shift.php?x=' . $x . '&cid=' . $cid . '&direction=up"><i class="fa fa-angle-up"></i> Shift Up</a></li>';
        elseif ($y > 0)
            $html .= '<li><a href="/forms/community-forms/category-shift.php?x=' . $x . '&y=' . $y . '&cid=' . $cid . '&direction=up"><i class="fa fa-angle-up"></i> Shift Up</a></li>';

        if (!$sub && $x < $total - 1)
            $html .= '<li><a href="/forms/community-forms/category-shift.php?x=' . $x . '&cid=' . $cid . '&direction=down"><i class="fa fa-angle-down"></i> Shift Down</a></li>';
        elseif ($y < $total - 1)
            $html .= '<li><a href="/forms/community-forms/category-shift.php?x=' . $x . '&y=' . $y . '&cid=' . $cid . '&direction=down"><i class="fa fa-angle-down"></i> Shift Down</a></li>';

        if (!$sub)
            $html .= '<li><a href="javascript:void(0)" class="category-name-btn" x="' . $x . '" cid="' . $cid . '" control="category-name" category="' . $category . '"><i class="fa fa-pencil"></i> Change Name</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)" class="category-name-btn" x="' . $x . '" y="' . $y . '" cid="' . $cid . '" control="subcategory-name" category="' . $category . '" subcategory="' . $subcategory . '"><i class="fa fa-pencil"></i> Change Name</a></li>';

        if (!$sub)
            $html .= '<li><a href="javascript:void(0)" class="category-load-btn" x="' . $x . '" cid="' . $cid . '" control="add-sub" category="' . $category . '"><i class="fa fa-plus"></i> Add Subcategory</a></li>';

        if (!$sub)
            $html .= '<li><a href="javascript:void(0)" class="category-load-btn" x="' . $x . '" cid="' . $cid . '" control="add-source" category="' . $category . '" subcategory="' . $subcategory . '"><i class="fa fa-database"></i> Add Source</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)" class="category-load-btn" x="' . $x . '" y="' . $y . '" cid="' . $cid . '" control="add-source" category="' . $category . '" subcategory="' . $subcategory . '"><i class="fa fa-database"></i> Add Source</a></li>';

        if (!$sub)
            $html .= '<li><a href="/forms/community-forms/category-delete.php?type=category&x=' . $x . '&cid=' . $cid . '"><i class="fa fa-times"></i> Remove</a></li>';
        else
            $html .= '<li><a href="/forms/community-forms/category-delete.php?type=subcategory&x=' . $x . '&y=' . $y . '&cid=' . $cid . '"><i class="fa fa-times"></i> Remove</a></li>';

        $html .= '</ul></div>';
        $html .= '</label></div><div class="panel-body" style="display:none">';

        return $html;
    }

    public function shiftAll($type, $num, $inc)
    {
        $this->connect();
        if ($type == 'z')
            $this->increment('community_structure', 'iiiii', array($type), array($num, $this->x, $this->y, $this->z, $this->cid), 'where x=? and y=? and z>? and cid=?');
        elseif ($type == 'y')
            $this->increment('community_structure', 'iiii', array($type), array($num, $this->x, $this->y, $this->cid), 'where x=? and y>? and cid=?');
        elseif ($type == 'x')
            $this->increment('community_structure', 'iii', array($type), array($num, $this->x, $this->cid), 'where x>? and cid=?');
        $this->close();
        if ($inc)
            $this->$type++;
    }

    public function swap($x, $y, $z, $direction, $cid)
    {
        $this->connect();
        $rand = -rand(10, 100);
        if (isset($z) && $z > -2) {
            if ($direction == 'up') {
                $this->update('community_structure', 'iiiii', array('z'), array($rand, $x, $y, $z, $cid), 'where x=? and y=? and z=? and cid=?');
                $this->update('community_structure', 'iiiii', array('z'), array($z, $x, $y, $z - 1, $cid), 'where x=? and y=? and z=? and cid=?');
                $this->update('community_structure', 'iiiii', array('z'), array($z - 1, $x, $y, $rand, $cid), 'where x=? and y=? and z=? and cid=?');
            } else {
                $this->update('community_structure', 'iiiii', array('z'), array($rand, $x, $y, $z, $cid), 'where x=? and y=? and z=? and cid=?');
                $this->update('community_structure', 'iiiii', array('z'), array($z, $x, $y, $z + 1, $cid), 'where x=? and y=? and z=? and cid=?');
                $this->update('community_structure', 'iiiii', array('z'), array($z + 1, $x, $y, $rand, $cid), 'where x=? and y=? and z=? and cid=?');
            }
            $this->close();
            return 'source';
        } elseif (isset($y) && $y > -2) {
            if ($direction == 'up') {
                $this->update('community_structure', 'iiii', array('y'), array($rand, $x, $y, $cid), 'where x=? and y=? and cid=?');
                $this->update('community_structure', 'iiii', array('y'), array($y, $x, $y - 1, $cid), 'where x=? and y=? and cid=?');
                $this->update('community_structure', 'iiii', array('y'), array($y - 1, $x, $rand, $cid), 'where x=? and y=? and cid=?');
            } else {
                $this->update('community_structure', 'iiii', array('y'), array($rand, $x, $y, $cid), 'where x=? and y=? and cid=?');
                $this->update('community_structure', 'iiii', array('y'), array($y, $x, $y + 1, $cid), 'where x=? and y=? and cid=?');
                $this->update('community_structure', 'iiii', array('y'), array($y + 1, $x, $rand, $cid), 'where x=? and y=? and cid=?');
            }
            $this->close();
            return 'subcategory';
        } elseif (isset($x) && $x > -2) {
            if ($direction == 'up') {
                $this->update('community_structure', 'iii', array('x'), array($rand, $x, $cid), 'where x=? and cid=?');
                $this->update('community_structure', 'iii', array('x'), array($x, $x - 1, $cid), 'where x=? and cid=?');
                $this->update('community_structure', 'iii', array('x'), array($x - 1, $rand, $cid), 'where x=? and cid=?');
            } else {
                $this->update('community_structure', 'iii', array('x'), array($rand, $x, $cid), 'where x=? and cid=?');
                $this->update('community_structure', 'iii', array('x'), array($x, $x + 1, $cid), 'where x=? and cid=?');
                $this->update('community_structure', 'iii', array('x'), array($x + 1, $rand, $cid), 'where x=? and cid=?');
            }
            $this->close();
            return 'category';
        }
    }

    public function updateName($cid, $type, $pastC, $pastS, $category, $subcategory)
    {
        $this->connect();
        if ($type == 'category-name') {
            $this->update('community_structure', 'ssi', array('category'), array($category, $pastC, $cid), 'where category=? and cid=?');
        } elseif ($type == 'subcategory-name') {
            $this->update('community_structure', 'sssi', array('subcategory'), array($subcategory, $pastC, $pastS, $cid), 'where category=? and subcategory=? and cid=?');
        }
    }

    public function checkName($name, $parent, $cid)
    {
        $this->connect();
        if ($parent)
            $return = $this->select('community_structure', array('id'), 'ssi', array($parent, $name, $cid), 'where category=? and subcategory=? and cid=?');
        else
            $return = $this->select('community_structure', array('id'), 'si', array($name, $cid), 'where category=? and cid=?');
        $this->close();

        if (count($return) > 0)
            return true;
        else
            return false;
    }
}
