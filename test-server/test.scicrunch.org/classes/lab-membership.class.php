<?php

class LabMembership extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "lab_memberships";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "uid"       => self::fieldDef("uid", "i", true),
            "labid"     => self::fieldDef("labid", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "level"     => self::fieldDef("level", "i", false),
        );
    }
    protected function _set_level($val) {
        if(!isset(LabMembership::$ALLOWED_LEVELS[$val])) return false;
        if($this->level === 3 && $val !== 3 && !$this->labHasOtherPI()) return false;
        return $val;
    }

    public static $ALLOWED_LEVELS = Array(
        0 => "request",
        1 => "member",
        2 => "manager",
        3 => "PI",
    );
    private $_user;
    private $_lab;

    public static function createNewObj(User $user, Lab $lab, $level = 0) {
        if(!$user->id || !$lab->id) return NULL;
        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "labid" => $lab->id,
            "timestamp" => $timestamp,
            "level" => $level,
        ));

        return $obj;
    }

    static public function deleteObj($obj, User $user = NULL) {
        /* dont delete if there are no other PIs */
        if($obj->level === 3 && !$obj->labHasOtherPI()) return;

        /* delete all lab membership roles */
        $lab_membership_roles = LabMembershipRole::loadArrayBy(Array("lab_membership_id"), Array($obj->id));
        foreach($lab_membership_roles as $lmr) {
            LabMembershipRole::deleteObj($lmr);
        }

        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "uid" => $this->uid,
            "labid" => $this->labid,
            "timestamp" => $this->timestamp,
            "level" => $this->level,
            "username" => $this->user()->getFullName(),
            "email" => $this->user()->email,
        );
    }

    static public function getLevel($user, $lab) {
        $membership = self::loadBy(Array("uid", "labid"), Array($user->id, $lab->id));
        if(is_null($membership)) return false;
        return $membership->level;
    }

    public function user() {
        if(is_null($this->_user)) {
            $this->_user = new User();
            $this->_user->getByID($this->uid);
        }
        return $this->_user;
    }

    public function lab() {
        if(is_null($this->_lab)) {
            $this->_lab = Lab::loadBy(Array("id"), Array($this->labid));
        }
        return $this->_lab;
    }

    public function labHasOtherPI() {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table_name, Array("count(*)"), "ii", Array($this->uid, $this->labid), "where uid != ? and labid = ? and level = 3");
        $cxn->close();
        if($count[0]["count(*)"] > 0) return true;
        return false;
    }

    public function levelName() {
        return self::$ALLOWED_LEVELS[$this->level];
    }

    public function loadByLabAndUser(Lab $lab, User $user) {
        return LabMembership::loadBy(Array("labid", "uid"), Array($lab->id, $user->id));
    }

    public function approve() {
        if($this->level != 0) {
            return;
        }
        $this->level = 1;
        $this->updateDB();
        $subject = "Lab access request approved for " . $this->lab()->name;
        $html_message = Array(
            'Your request to join <a href="' . $this->lab()->community()->fullURL() . '/lab?labid=' . $this->lab()->id . '">' . $this->lab()->name . '</a> has been approved.',
        );
        $text_message = "Your request to join " . $this->lab()->name . " has been approved.  You can go to you lab now by following this link: " . $this->lab()->community()->fullURL() . "/lab?labid=" . $this->lab()->id ;
        \helper\sendEmail($this->user()->email, \helper\buildEmailMessage($html_message, 1, $this->lab()->community()), $text_message, $subject, NULL);
    }

    public function reject() {
        if($this->level != 0) {
            return;
        }

        $subject = "Lab access request not approved for " . $this->lab()->name;
        $text_message = "Your request to join " . $this->lab()->name . " was not approved.";
        $html_message = Array($text_message);
        \helper\sendEmail($this->user()->email, \helper\buildEmailMessage($html_message, 1, $this->lab()->community()), $text_message, $subject, NULL);

        self::deleteObj($this);
    }

    public static function loadLabManagers(Lab $lab) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select(self::$_table_name, Array("*"), "i", Array($lab->id), "where labid=? and level >= 2");
        $cxn->close();

        $managers = Array();
        foreach($rows as $row) {
            $managers[] = new LabMembership($row);
        }
        return $managers;
    }
}
LabMembership::init();

?>
