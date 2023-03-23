<?php

class Lab extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "labs";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "cid"                   => self::fieldDef("cid", "i", true),
            "uid"                   => self::fieldDef("uid", "i", true),
            "name"                  => self::fieldDef("name", "s", false),
            "private_description"   => self::fieldDef("private_description", "s", false),
            "public_description"    => self::fieldDef("public_description", "s", false),
            "timestamp"             => self::fieldDef("timestamp", "i", true),
            "curated"               => self::fieldDef("curated", "s", false, Array("allowed_values" => self::$CURATED_STATUSES)),
            "broadcast_message"     => self::fieldDef("broadcast_message", "s", false),
        );
    }
    protected function _set_name($val) { return self::setNotEmpty($val); }
    protected function _set_private_description($val) { return self::setNotEmpty($val); }
    protected function _set_public_description($val) { return self::setNotEmpty($val); }
    protected function _set_broadcast_message($val) {
        if(is_null($val)) {
            return "";
        }
        return $val;
    }

    const CURATED_STATUS_PENDING = "pending";
    const CURATED_STATUS_REJECTED = "rejected";
    const CURATED_STATUS_APPROVED = "approved";
    static protected $CURATED_STATUSES = Array(self::CURATED_STATUS_PENDING, self::CURATED_STATUS_REJECTED, self::CURATED_STATUS_APPROVED);

    private $_community;
    private $_templates;
    private $_datasets;

    static public function createNewObj(Community $community, User $user, $name, $private_description, $public_description) {
        if(is_null($community->id) || !$user->id || !$name || !$private_description || !$public_description) return NULL;

        $timestamp = time();
        $curated = self::CURATED_STATUS_PENDING;

        $obj = self::insertObj(Array(
            "id" => NULL,
            "cid" => $community->id,
            "uid" => $user->id,
            "name" => $name,
            "private_description" => $private_description,
            "public_description" => $public_description,
            "timestamp" => $timestamp,
            "curated" => $curated,
            "broadcast_message" => "",
        ));

        return $obj;
    }

    static public function deleteObj($obj) {
        // no delete
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "cid" => $this->cid,
            "uid" => $this->uid,
            "name" => $this->name,
            "public_description" => $this->public_description,
            "private_description" => $this->private_description,
            "broadcast_message" => $this->broadcast_message,
        );
    }

    public function approveLab($status) {
        $user = new User();
        $user->getByID($this->uid);
        if($status === self::CURATED_STATUS_APPROVED) {
            $this->curated = $status;
            LabMembership::createNewObj($user, $this, 3);
            $this->updateDB();

            /* send email */
            $subject = $this->name . " approved in " . $this->community()->portalName . " community";
            $html_message = Array(
                'Your lab has been approved in the ' . $this->community()->portalName . ' community.',
                '<a href="' . $this->community()->fullURL() . '/lab?labid=' . $this->id . '">Go to your lab now</a>'
            );
            $text_message = 'Your lab has been approved in the ' . $this->community()->portalName . ' community.  Follow this link ' . $this->community()->fullURL() . '/lab?labid=' . $this->id . ' to go to your lab now.';
            \helper\sendEmail($user->email, \helper\buildEmailMessage($html_message, 1, $this->community()), $text_message, $subject, NULL);
        } elseif($status === self::CURATED_STATUS_REJECTED) {
            $this->curated = $status;
            $this->updateDB();

            /* send email */
            $subject = $this->name . " not approved in " . $this->community()->portalName . " community";
            $html_message = Array(
                'Your lab has been rejected in the ' . $this->community()->portalName . ' community.',
            );
            $text_message = 'Your lab has been rejected in the ' . $this->community()->portalName . ' community.';
            \helper\sendEmail($user->email, \helper\buildEmailMessage($html_message, 1, $this->community()), $text_message, $subject, NULL);
        }
    }

    public function isMember(User $user) {
        $lm = LabMembership::loadBy(Array("uid", "labid"), Array($user->id, $this->id));
        if(is_null($lm)) return false;
        if($lm->level < 1) return false;
        return true;
    }

    public function isModerator(User $user) {
        $lm = LabMembership::loadBy(Array("uid", "labid"), Array($user->id, $this->id));
        if(is_null($lm)) return false;
        if($lm->level < 2) return false;
        return true;
    }

    public function hasMembers() {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("lab_memberships", Array("count(*)"), "i", Array($this->id), "where labid=? and level > 0");
        $cxn->close();
        return $count[0]["count(*)"] > 0;
    }

    public function community() {
        if(is_null($this->_community)) {
            $this->_community = new Community();
            $this->_community->getByID($this->cid);
        }
        return $this->_community;
    }

    public function uniqueDatasetName($name) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(
            "datasets d inner join dataset_fields_templates t on d.dataset_fields_template_id = t.id",
            Array("count(*)"),
            "is",
            Array($this->id, $name),
            "where d.active = 1 and t.labid = ? and d.name = ?"
        );
        $cxn->close();
        if($count[0]["count(*)"] == 0) {
            return true;
        }
        return false;
    }

    public function templates() {
        if(is_null($this->_templates)) {
            $this->_templates = DatasetFieldTemplate::loadArrayBy(Array("labid", "active"), Array($this->id, 1));
        }
        return $this->_templates;
    }

    public function datasets() {
        if(is_null($this->_datasets)) {
            $this->_datasets = Array();
            $templates = $this->templates();
            foreach($templates as $template) {
                $this->_datasets = array_merge($this->_datasets, Dataset::loadArrayBy(Array("dataset_fields_template_id"), Array($template->id)));
            }
        }
        return $this->_datasets;
    }

    public static function getUserMainLab(User $user = NULL, $community_id) {
        if(is_null($user)) return NULL;
        /*$community_id = 97;*/
        $cxn = new Connection();
        $cxn->connect();
        /*$lab_memberships = $cxn->select("lab_memberships", Array("labid"), "i", Array($user->id), "where uid = ? and level > 0 order by level desc limit 1");*/
        $lab_memberships = $cxn->select("lab_memberships m inner join labs l on m.labid = l.id", Array("m.labid"), "ii", Array($user->id,$community_id), "where m.uid = ? and l.cid = ? and level > 0 order by level desc limit 1");
        $cxn->close();
        if(empty($lab_memberships)) return NULL;
        $lab = Lab::loadBy(Array("id"), Array($lab_memberships[0]["labid"]));
        return $lab;
    }

    public function managerEmails() {
        $lab_managers = LabMembership::loadLabManagers($this);
        $emails = Array();
        foreach($lab_managers as $lm) {
            $emails[] = $lm->user()->email;
        }
        return $emails;
    }

    public static function getCommunityCount(Community $community) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table_name, Array("count(*)"), "i", Array($community->id), "where cid=?");
        $cxn->close();
        return $count[0]["count(*)"];
    }

    public function numberOfMembers() {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("lab_memberships", Array("count(*)"), "i", Array($this->id), "where labid=? and level > 0");
        $cxn->close();
        return $count[0]["count(*)"];
    }

    public function numberOfDatasets() {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("(labs l inner join dataset_fields_templates dft on l.id = dft.labid) inner join datasets d on dft.id = d.dataset_fields_template_id", Array("count(*)"), "i", Array($this->id), "where labid = ? and d.active = 1");
        $cxn->close();
        return $count[0]["count(*)"];
    }
}
Lab::init();

?>
