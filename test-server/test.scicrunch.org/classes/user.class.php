<?php

class User extends Connection {

    public $id;
    private $password;
    public $email;
    public $firstname;
    public $middleInitial;
    public $lastname;
    public $role;
    public $organization;
    public $verified;
    public $verify_string;
    public $orcid_id;
    public $subscribe_email;

    public $levels;
    public $mod;
    public $preferences;
    public $stats;
    public $keys;
    public $salt;
    public $created;
    public $banned;
    public $maxLevel = 0;
    public $rids;
    public $collections;
    public $items;

    public $onlineUsers;
    public $MAGIC = 215;

    public $last_check;

    public function create($vars) {
        $this->firstname = $vars['firstname'];
        $this->lastname = $vars['lastname'];
        $this->email = $vars['email'];
        $this->organization = $vars['organization'];
        $this->orcid_id = $vars['orcid_id'];
        $this->subscribe_email = 1;

        $this->salt = str_replace('.', '', uniqid(mt_rand(), true));
        $this->password = $vars['password'];
        $this->role = 0;
        $this->banned = 0;
        $this->created = time();
        $this->verified = 0;

        $this->connect();
        do{
            $this->verify_string = str_replace('.', '', uniqid(mt_rand(), true));
        } while(count($this->select('users', array('verify_string'), 's', array($this->verify_string), 'where verify_string=?')) > 0);	// make sure verify string is unique
        $this->close();
    }

    // Handle user creation with data from CILogon
    public function create_with_cilogon($vars) {
        $this->firstname = $vars['firstname'];
        $this->lastname = $vars['lastname'];
        $this->email = $vars['email'];
        $this->organization = NULL;
        $this->orcid_id = NULL;
        $this->subscribe_email = 1;

        $this->salt = NULL;
        $this->password = NULL;
        $this->role = 0;
        $this->banned = 0;
        $this->created = time();
        $this->verified = 1;

    }

    public function createFromRow($row) {
        if (!$row || $row['guid'] == null || $row['guid'] == '') {
            $this->id = false;
        } else
            $this->id = $row['guid'];

        $this->email = $row['email'];
        $this->firstname = $row['firstName'];
        $this->lastname = $row['lastName'];
        $this->organization = $row['organization'];
        $this->orcid_id = $row["orcid_id"];
        $this->subscribe_email = $row["subscribe_email"];
        $this->middleInitial = $row['middleInitial'];
        $this->role = $row['level'];
        $this->banned = $row['banned'];
        $this->created = $row['created'];
        $this->verified = $row['verified'];
        $this->verify_string = $row['verify_string'];
    }

    // Get all the labs the user has access to.
    public function getLabs($uid){
        $this->connect();
        // (table, return_variables, input_types_string, input_array, groupby_where_limit_string)
        $return = $this->select('lab_memberships inner join labs on labs.id = lab_memberships.labid', array('labs.cid', 'labs.id', 'labs.name'), 's', array($uid), 'where lab_memberships.uid=?');
        // returns a list dictionaries; aka records
        return $return;
    }

    public function getByName($name){
        $this->connect();
        $return = $this->select('users', array('guid'), 'ss', array($name[0],$name[1]), 'where firstName=? and lastName=? order by id asc limit 1');
        if (count($return) == 1) {
            $this->createFromRow($return[0]);
        }
        $this->close();
    }

    public function getByEmail($email){
        $this->connect();
        $return = $this->select('users', array('guid,email,firstName,lastName'), 's', array($email), 'where email=? order by id asc limit 1');
        if (count($return) == 1) {
            $this->createFromRow($return[0]);
        }
        $this->close();
    }

    public function getByID($id) {
        $this->connect();
        $return = $this->select('users', array('*'), 'i', array($id), 'where guid=?');
        if (count($return) == 1) {
            $this->createFromRow($return[0]);
            $access = $this->select('community_access', array('*'), 'i', array($this->id), 'where uid=?');
            $resources = $this->select('community_log', array('*'), 'i', array($this->id), 'where uid=?');
            if (count($access) > 0) {
                foreach ($access as $row) {
                    $this->levels[$row['cid']] = $row['level'];
                }
            }
            if (count($resources) > 0) {
                foreach ($resources as $row) {
                    $this->preferences[$row['cmid']][$row['euid']][$row['action']] = true;
                }
            }
        }
        $this->close();
    }

    public function getUserCount(){
        $this->connect();
        $return = $this->select('users', array('count(id)'), null, array(), 'where email like "%@%"');
        $count = $return[0]['count(id)'];
        $this->close();

        return $count;
    }

    public function deleteDB(){
        $this->connect();
        $this->delete('users','i',array($this->id),'where guid=?');
        $this->delete('community_access','i',array($this->id),'where uid=?');
        $this->close();
    }

    public function getUsers($offset,$limit){
        $this->connect();
        $return = $this->select('users', array('*'), null, array(), 'where email like "%@%" order by id desc limit '.$offset.','.$limit);
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $user = new User();
                $user->createFromRow($row);
                $finalArray[] = $user;
            }
        }
        return $finalArray;
    }

    public function getUsersQuery($query,$offset,$limit, $vrf, $unvrf, $usr, $curtr, $mod, $admin, $bnnd, $nobnnd, $cid){
        $this->connect();
        $verifyQuery = ($vrf && $unvrf) ? "" : " and users.verified=".(($vrf)?"1":"0");
        $bannedQuery = ($bnnd && $nobnnd) ? "" : " and users.banned=".(($bnnd)?"1":"0");
        $levelQuery = "";
        if (!($usr && $curtr && $mod && $admin)) {
            $options = array();
            if ($usr)
                array_push($options, "0");
            if ($curtr)
                array_push($options, "1");
            if ($mod)
                array_push($options, "2");
            if ($admin)
                array_push($options, "3");
            $levelQuery = " and users.level in (" . implode (",", $options) . ")";
        }

        $req_vars = array('%'.$query.'%','%'.$query.'%','%'.$query.'%','%'.$query.'%');
        $where_stmt = 'where ((users.firstName like ? or users.lastName like ? or users.email like ? or concat(users.firstName," ",users.lastName) like ?)'.$verifyQuery.$bannedQuery.$levelQuery.') and users.email like "%@%"';
        if($cid) {
            $table = "users inner join community_access on users.guid = community_access.uid";
            $res_cols = Array("SQL_CALC_FOUND_ROWS users.*");
            $req_types = "ssssi";
            $req_vars[] = $cid;
            $where_stmt .= " and community_access.cid = ? and community_access.level > 0";
        } else {
            $table = "users";
            $res_cols = Array('SQL_CALC_FOUND_ROWS *');
            $req_types = "ssss";
        }
        $where_stmt .= ' order by id desc limit '.$offset.','.$limit;

        $return = $this->select($table, $res_cols, $req_types, $req_vars, $where_stmt);
        $finalArray['count'] = $this->getTotal();
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $user = new User();
                $user->createFromRow($row);
                $finalArray['results'][] = $user;
            }
        }
        return $finalArray;
    }

    public function updateOnline() {
        $this->connect();
        $time = strtotime(date("Y-m-d H:i:s"));
        $end = $time + 160;
        $online = $this->select('online_users', array('*'), 'i', array($_SESSION['user']->id), 'where uid=?');
        if (count($online) == 1) {
            if ($online[0]['end'] < $time) {
                $this->update('online_users', 'iiii', array('start', 'last', 'end'), array($time, $time, $end, $online[0]['id']), 'where id=?');
            } else {
                $this->update('online_users', 'iii', array('last', 'end'), array($time, $end, $online[0]['id']), 'where id=?');
            }
        } else {
            $this->insert('online_users', 'iiiii', array(null, $_SESSION['user']->id, $time, $end, $time));
        }
        $this->close();
        return $end - 10;
    }

    public function updateLocation($cid,$url){
        //echo str_replace('/','|',$url);
        $this->connect();
        $this->update('online_users','isi',array('cid','page'),array($cid,$url,$this->id),'where uid=?');
        $this->close();
    }

    public function goOffline() {
        $this->connect();
        $time = strtotime(date("Y-m-d H:i:s"));
        $this->update('online_users', 'iii', array('last_time', 'end'), array($time, $time, $_SESSION['user']->id), 'where uid=?');
        $this->close();
    }

    public function getOnlineUsers(){
        $time = time();
        $this->connect();
        $return = $this->select('online_users',array('uid'),'ii',array($time,$time),'where last<=? and end>?');
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $user = new User();
                $user->getByID($row['uid']);
                $finalArray[$user->id] = $user;
            }
        }
        return $finalArray;
    }

    public function login($email, $password) {
        $cxn = new Connection();
        $cxn->connect();
        $row = $cxn->select("users", Array("*"), "ss", Array($password, $email), "where convert(password, char(1024)) = convert(md5(concat(?, salt)), char(1024)) and email = ?");
        $cxn->close();
        $this->createFromRow($row[0]);

        if($this->id) {
            $this->loginProcess(true);
            $this->updateORCIDData();
        }
    }

    public function login_with_cilogon($cilogon_id) {
        $cxn = new Connection();
        $cxn->connect();
        $row = $cxn->select("users", Array("*"), "s", Array($cilogon_id), "JOIN users_cilogn_mapping ON users.guid = users_cilogn_mapping.user_id where users_cilogn_mapping.cilogon_id = ?");
        $cxn->close();
        $this->createFromRow($row[0]);

        if($this->id) {
            $this->loginProcess(true);
            $this->updateORCIDData();
        }
    }

    public function loginProcess($initialLogin) {
        if ($this->id) {
            $this->connect();
            $access = $this->select('community_access', array('*'), 'i', array($this->id), 'where uid=?');
            $collect0 = $this->select('collected', array('count(*)'), 'i', array($this->id), 'where uid=? and collection=0');
            $collections = $this->select('collections', array('*'), 'i', array($this->id), 'where uid=? order by id desc');
            if($initialLogin && $this->role>0){
                $questions = $this->select('component_data',array('count(*)'),null,array(),'where component=104 and description is null');
                if((int)$questions[0]['count(*)']>0){
                    $notification = new Notification();
                    $notification->create(array(
                        'sender' => 0,
                        'receiver' => $this->id,
                        'view' => 0,
                        'cid' => 0,
                        'timed'=>0,
                        'start'=>time(),
                        'end'=>time(),
                        'type' => 'pending-questions',
                        'content' => 'There are '.$questions[0]['count(*)'].' questions unanswered.'
                    ));
                    $notification->insertDB();
                    $this->last_check = time();
                }
            }
            if (count($access) > 0) {
                foreach ($access as $row) {
                    $this->levels[$row['cid']] = $row['level'];
                }
            }
            $default = new Collection();
            $default->createFromRow(array('id' => 0, 'name' => 'Default Collection', 'count' => $collect0[0]['count(*)'], 'time' => 1400569200));
            $this->collections[0] = $default;

            if (count($collections) > 0) {
                foreach ($collections as $row) {
                    $collection = new Collection();
                    $collection->createFromRow($row);
                    $this->collections[$collection->id] = $collection;
                }
            }

            if ($initialLogin) {
                $this->log('logged in');
            }
            $this->close();
        }
    }

    public function findUser($term, $limit=MAXINT){
        $this->connect();
        $return = $this->select('users',array('*'),'sssi',array('%'.$term.'%','%'.$term.'%','%'.$term.'%', $limit),'where firstName like ? or lastName like ? or email like ? and email like "%@%" limit ?');
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $user = new User();
                $user->createFromRow($row);
                $finalArray[] = $user;
            }
        }
        return $finalArray;
    }

    public function checkPassword($password){
        $this->connect();
        $salt = $this->select('users',array('salt'),'i',array($this->id),'where guid=?');
        $return = $this->select('users',array('guid'),'is',array($this->id,$password),'where guid=? and password=md5(concat(?,\''.$salt[0]['salt'].'\'))');
        $this->close();

        if(count($return)>0){
            if($return[0]['guid']==$this->id)
                return true;
        }
        return false;
    }

    public function updateField($field,$value){
        if ($field == 'id' || $field == 'guid' || $field == 'created') {
            return false;
        }

        $this->connect();
        if($field=='password'){
            $salt = $this->select('users',array('salt'),'i',array($this->id),'where guid=?');
            $this->updateSalt('users','si',array('password'),array($value,$this->id),$salt[0]['salt'],'where guid=?');
        } elseif($field=='banned'||$field=='level'||$field=='subscribe_email') {
            $this->update('users','ii',array($field),array($value,$this->id),'where guid=?');
        } else {
            $this->update('users','si',array($field),array($value,$this->id),'where guid=?');
        }
        $this->close();
        $this->log('update field: '.$field);
    }

    public function insertIntoDB() {
        $this->connect();

        $this->created = strtotime(date("Y-m-d H:i:s"));
        $stmt = $this->mysqli->prepare("INSERT INTO users VALUES (?, ?, ?, md5(concat(?, '$this->salt')), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        //printf("Errormessage: %s\n", $mysqli->error);
        //print_r($this);
        $stmt->bind_param('iisssiissssiissi', $a = null, $this->id, $this->email, $this->password, $this->salt, $this->banned, $this->role, $this->firstname, $this->middleInitial, $this->lastname, $this->organization, $this->created, $this->verified, $this->verify_string, $this->orcid_id, $this->subscribe_email);
//printf("Errormessage: %s\n", $mysqli->error);
        /* execute prepared statement */

        $return = $stmt->execute();
        $id = $stmt->insert_id;
        $this->id = $id + $this->MAGIC;

        $not_unique = true;
        $loop_counter = 0;
        while($not_unique){
            $guids = $this->select('users', array('*'), 'i', array($this->id), 'where guid=?');
            if(count($guids) == 0) $not_unique = false;
            else $this->id += 1;
            $loop_counter += 1;
            if($loop_counter > 100000) exit;	// prevent infinite loops
        }

        $this->update('users','ii',array('guid'),array($this->id,$id),'where id=?');

        $this->close();
        $this->log('registered');
        return $return;
    }

    // Created by Stevem
    // Adds CI mapping with user creation
    // Should only run if user has never visited site before
    public function insertIntoDBCI($cilogon_id) {
        $this->connect();

        $this->created = strtotime(date("Y-m-d H:i:s"));
        $stmt = $this->mysqli->prepare("INSERT INTO users VALUES (?, ?, ?, md5(concat(?, '$this->salt')), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iisssiissssiissi', $a = null, $this->id, $this->email, $this->password, $this->salt, $this->banned, $this->role, $this->firstname, $this->middleInitial, $this->lastname, $this->organization, $this->created, $this->verified, $this->verify_string, $this->orcid_id, $this->subscribe_email);

        $return = $stmt->execute();
        $id = $stmt->insert_id;
        $this->id = $id + $this->MAGIC;
        $not_unique = true;
        $loop_counter = 0;
        while($not_unique){
            $guids = $this->select('users', array('*'), 'i', array($this->id), 'where guid=?');
            if(count($guids) == 0) $not_unique = false;
            else $this->id += 1;
            $loop_counter += 1;
            if($loop_counter > 100000) exit;	// prevent infinite loops
        }

        $this->update('users','ii',array('guid'),array($this->id,$id),'where id=?');
        $this->insert('users_cilogn_mapping', 'iis', array(null, $this->id, $cilogon_id));
        $this->close();
        $this->log('registered');
        return $this->id;
    }


    public function log($action){
        $this->connect();
        $this->insert('user_log','iisssi',array(null,$this->id,$this->getFullName(),'SciCrunch',$action,time()));
        $this->close();
    }

    public function getFullName() {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function resetPassword() {

        $password = APIKey::getRandomKeyString(16);

        $this->updateField('password', $password);

        $to = $this->email;
        $subject = 'SciCrunch Password Reset';

        $message = Array(
            "Hello,<br/>",
            "We've received a request to reset your SciCrunch password.  Your password has been set to: <b>" . $password . "</b> .<br/>",
            "If you did not send this request, please contact us at <a href=\"mailto:support@scicrunch.org\">support@scicrunch.org</a>"
        );
        $text_message = "Hello, we've received a request to reset your SciCrunch password.  Your password has been set to: ". $password . " .  If you did not send this request, please contact us at support@scicrunch.org.";
        \helper\sendEmail($to, \helper\buildEmailMessage($message), $text_message, $subject);

        $this->log('reset pw');

        if ($sent)
            return true;
        else
            return false;
    }

    public function getMyResources() {
        $this->connect();
        $finalArray = array();
        $return = $this->select('resources',array('SQL_CALC_FOUND_ROWS *'),'i',array($this->id),'where uid=?');
        $finalArray['count'] = $this->getTotal();
        $this->close();

        if(count($return)>0){
            foreach($return as $row){
                $resource = new Resource();
                $resource->createFromRow($row);
                $resource->getColumns();
                $finalArray['data'][] = $resource;
            }
        }

        return $finalArray;

    }

    public function get_gravatar($s = 300, $d = 'identicon', $r = 'g', $img = false, $atts = array() ) {
        $url = '//www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $this->email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }


    public function verifyUser($ver_string, $login_time = 7200){
        $this->connect();

        $this->update('users', 'is', array('verified'), array(1, $ver_string), 'where verify_string=?');

        $user_row = $this->select("users", Array("*"), "s", Array($ver_string), "where verify_string = ?");
        if(count($user_row) === 1 && $user_row[0]["created"] + $login_time > time()) {
            $this->createFromRow($user_row[0]);
            $this->loginProcess(true);
        }
    }

    public function sendVerifyEmail($referer){
        if($this->verified != 0) return;
        $level_keys = array_keys($this->levels);
        if(count($level_keys) == 1 && $level_keys[0] != 0){
            $comm = new Community();
            $comm->getByID($level_keys[0]);
            //$comm_message = 'SciCrunch and the community <a href="http://' . $_SERVER['HTTP_POST'] . '/' . $comm->portalName . '">' . $comm->name . '</a>';
            $comm_message = $comm->name;
            $comm_subject = $comm->shortName . " email verification";
            $alt = 1;
            $community_name = $comm->name;
        }else{
            $comm_message = "SciCrunch";
            $alt = 0;
            $comm_subject = "Scicrunch email verification";
            $comm = NULL;
            $community_name = "SciCrunch";
        }
        if(is_null($comm)) $hostname = PROTOCOL . "://" . FQDN;
        else $hostname = $comm->fullURL();
        $verification_address = $hostname . '/verification/' . $this->verify_string;
        if($referer != "") $verification_address .= "?referer=" . $referer;
        $message = Array('Thank you for registering with ' . $comm_message . '.  Please click <a href="' . $verification_address . '">here</a> to verify your email.');
        $text_message = "Thank you for registering with " . $comm_message . ".  Please go to this address to verify your email: " . $verification_address;
        \helper\sendEmail($this->email, \helper\buildEmailMessage($message, $alt, $comm), $text_message, $comm_subject, NULL, $community_name);
    }

    public function emailExists($email){
        $this->connect();
        $return = $this->select('users', array('*'), 's', array($email), 'where email=?');
        $this->close();
        if(count($return) == 0) return false;
        return true;
    }

    public static function idExists($id){
        $cxn = new Connection();
        $cxn->connect();
        $users = $cxn->select("users", Array("*"), "i", Array($id), "where guid=? limit 2");
        $cxn->close();

        return count($users) === 1;
    }

    public function getPrimaryCommunity() {
        // function returns the community the user uses the most based on saved searches

        $cxn = new Connection();
        $cxn->connect();
        $saved_searches = $cxn->select("saved_searches", Array("cid"), "i", Array($this->id), "where uid=?");
        $cxn->close();

        if(empty($saved_searches)) {
            $cid = 0;
        } else {
            $comm_ids = Array();
            foreach($saved_searches as $sse) {
                $thiscid = $sse["cid"];
                if(isset($comm_ids[$thiscid])) $comm_ids[$thiscid] += 1;
                else $comm_ids[$thiscid] = 1;
            }
            arsort($comm_ids);
            $keys = array_keys($comm_ids);
            $cid = $keys[0];
        }
        $community = new Community();
        $community->getByID($cid);
        return $community;
    }

    public function mainCommunity() {
        $cxn = new Connection();
        $cxn->connect();
        $access = $cxn->select("community_access", Array("cid"), "i", Array($this->id), "where uid = ? and level > 0 order by date limit 1");
        $cxn->close();

        if(count($access) > 0) {
            $community = new Community();
            $community->getByID($access[0]["cid"]);
            if($community->id || $access[0]["cid"] === 0) return $community;
        }
        $community = new Community();
        $community->getByID(0);
        return $community;
    }

    public function visibleCommunities() {
        $cxn = new Connection();
        $cxn->connect();
        $comms = $cxn->select("communities", Array("*"), "", Array(), "");
        $cxn->close();

        $visible_communities = Array();
        foreach($comms as $comm) {
            $commid = $comm["id"];
            if($comm["private"] == 0 || (isset($this->levels[$commid]) && $this->levels[$commid] > 0)) {
                $visible_communities[$commid] = true;
            }
        }

        return $visible_communities;
    }

    public function newEmailUnsubscribeKey() {
        $expire = strtotime("+30 day");
        $key = ActionKey::createNewObj("email-unsubscribe", $this->id, $expire, NULL);
        return $key;
    }

    public function unsubscribeAllEmail() {
        $this->subscribe_email = 0;
        $this->updateField("subscribe_email", 0);
    }

    public function updateORCIDData() {
        require_once __DIR__ . "/../api-classes/update_subscription.php";
        if(!$this->orcid_id) return;

        /* get the oauth access token and make the request */
        $access_token = \helper\getOrcidOauthAccessToken();
        $works_request = \helper\sendGetRequest(
            "https://pub.orcid.org/v3.0/" . $this->orcid_id . "/works",
            Array(),
            Array(
                "Content-Type: application/orcid+json",
                "Authorization: Bearer " . $access_token
            )
        );
        $works = json_decode($works_request, true);

        $employments_request = \helper\sendGetRequest(
            "https://pub.orcid.org/v3.0/" . $this->orcid_id . "/employments",
            Array(),
            Array(
                "Content-Type: application/orcid+json",
                "Authorization: Bearer " . $access_token
            )
        );
        $employments = json_decode($employments_request, true);

        /* if no orcid works, stop
        if(
            !isset($employments["affiliation-group"]) || empty($employments["affiliation-group"])
        ) die("nothing to process");
        */

        /* get orcid pmids */
        $orcid_works = $works["group"];
        foreach($orcid_works as $ow) {
            foreach($ow["external-ids"] as $identifier) { // each one of these is a separate work
                $found = 0;
                foreach ($identifier as $work) {
                    // pmid/doi should not be in the same external-id as rrid, so OK to be in same foreach loop
                    $works_type = $work["external-id-type"];
                    $title = $ow["work-summary"][0]["title"]["title"]["value"];
                    $save_title = $title;

                    if ((strtolower($work["external-id-type"]) == "pmid") || (strtolower($work["external-id-type"]) == "doi")) {
                       // $build_json[$works_type] = $literature[$title][$works_type];
                        $build_json[$works_type] = $work["external-id-value"];
                        $found = 'works';
                    } elseif ($work["external-id-type"] == "rrid") {
                        $build_json["rrid"] = $work["external-id-value"];
                        $found = 'rrid';
                        break;
                    }
                }
                // finished processing a single work. let's build a json string and add it to $today array
                if ($found) {
                    ksort($build_json);
                    $build_json["name"] = $save_title;
                    $apidata[$found][] = json_encode($build_json, JSON_UNESCAPED_SLASHES);
                    unset($build_json);
                }
            }
        }


        $orcid_employments = $employments["affiliation-group"];
        foreach($orcid_employments as $oe) {
            $found = 0;
            foreach($oe["summaries"] as $summary) {
                foreach ($summary as $summ) {
                    // end-date month/date aren't required, so messy to determine if still employed
                    // just assume that first record without end date is good! ;)
                    if (is_null($summ["end-date"])) {
                        $build_json["organization"] = $summ["organization"]["name"];
                        $build_json["organization-identifier"] = $summ["organization"]["disambiguated-organization"]["disambiguated-organization-identifier"];
                        $build_json["organization-source"] = $summ["organization"]["disambiguated-organization"]["disambiguation-source"];
                        $found++;
                        break 3;
                    }
                }
            }
        }
        // only want to store one org, so if first is valid, keep it ...
        if ($found) {
            $apidata["organization"][] = json_encode($build_json, JSON_UNESCAPED_SLASHES);
            unset($build_json);
        }

        function compareAndInsert($user, $ued_name, $apidata) {
            $ued_abbr = str_replace("orcid-", "", $ued_name);
            $existing_data = UsersExtraData::loadArrayBy(Array("uid", "name"), Array($user->id, $ued_name));

            // examine $existing_data and apidata together. If equal, then data hasn't changed, so leave alone
            $exists_index = 0; // makes sense to unset by index
            $apidata_index=0;

            foreach ($existing_data as $exists) {
                $exists_value_array = $exists->value;
                foreach ($apidata[$ued_abbr] as $apidata_record) {
                    $apidata_record_array  = json_decode($apidata_record, true);
                    // works data matches, so leave alone, which means remove from today array and existing_data array
                    if ($apidata_record_array == $exists_value_array) {
                        unset($apidata[$ued_abbr][$apidata_index]);
                        unset($existing_data[$exists_index]);
                        $apidata_index++;
                        break;
                    }
                }
                // anything left means there's a difference
                $exists_index++;
            }

            // check apidata
            $apidata_index = 0;
            foreach ($apidata[$ued_abbr] as $apidata_record) {
                $apidata_record_array  = json_decode($apidata_record, true);
                $found = 0;
                foreach (array_keys($apidata_record_array) as $key) {
                    if ($key == 'name')
                        continue;
                    $exists_index = 0;

                    foreach ($existing_data as $exists) {
                        $exists_value_array = $exists->value;
                        // if $key is in $exists json string, then we need to update
                        echo json_encode($exists->value);
                        echo $apidata_record_array[$key];

                        if (strpos(json_encode($exists->value), $apidata_record_array[$key])) {
                            $update_me[$exists->id] = $apidata_record;
                            unset($apidata[$ued_abbr][$apidata_index]); //
                            unset($existing_data[$exists_index]);
                            $exists_index++;
                            $found++;
                            break 2;
                        }
                    }
                }
                if (!$found) {
                // didn't match, so must be new
                $add_me[] = $apidata_record;
                $apidata_index++;
                }
            }

            if (isset($add_me)) {
    //            print_r($add_me);
                foreach ($add_me as $json) {
                    $json_1 = json_decode($json, true);
                    UsersExtraData::createNewObj($user, $ued_name, $json_1);
                }
            }

            if (isset($update_me)) {
    //            print_r($update_me);
                foreach (array_keys($update_me) as $key) {
                    $existing_data = UsersExtraData::loadArrayBy(Array("uid", "id"), Array($user->id, $key));
                    UsersExtraData::deleteObj($existing_data[0]);
                    UsersExtraData::createNewObj($user, $ued_name, json_decode($update_me[$key], true));

    //                UsersExtraData::updateUED($key, $update_me[$key]);
                }
            }
        }

        compareAndInsert($this, "orcid-works", $apidata);
        compareAndInsert($this, "orcid-rrid", $apidata);
        compareAndInsert($this, "orcid-organization", $apidata);
    }
}

class UserDBO extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "users";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "guid"              => self::fieldDef("guid", "i", true),
            "email"             => self::fieldDef("email", "s", false),
            "password"          => self::fieldDef("password", "s", false),
            "salt"              => self::fieldDef("salt", "s", false),
            "banned"            => self::fieldDef("banned", "i", false),
            "level"             => self::fieldDef("level", "i", false),
            "firstName"         => self::fieldDef("firstName", "s", false),
            "middleInitial"     => self::fieldDef("middleInitial", "s", false),
            "lastName"          => self::fieldDef("lastName", "s", false),
            "organization"      => self::fieldDef("organization", "s", false),
            "created"           => self::fieldDef("created", "i", true),
            "verified"          => self::fieldDef("verified", "i", false),
            "verify_string"     => self::fieldDef("verify_string", "s", true),
            "orcid_id"          => self::fieldDef("orcid_id", "s", false),
            "subscribe_email"   => self::fieldDef("subscribe_email", "i", false),
        );
    }
    protected function _display_password($val) { return "*****"; }
    protected function _display_salt($val) { return "*****"; }
    protected function _display_created($val) { return self::displayTime($val); }

    public static function createNewObj() {

    }

    public static function deleteObj($obj) {

    }

    public function arrayForm() {

    }
}
UserDBO::init();

?>
