<?php

class CommunityAccessRequest extends DBObject {
    static protected $_table = "community_access_requests";
    static protected $_table_fields = Array("id", "uid", "cid", "status", "owner_access_string", "timestamp", "message");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiissis";

    const STATUS_PENDING = "pending";
    const STATUS_REJECTED = "rejected";
    const STATUS_APPROVED = "approved";
    const STATUS_UNDER_REVIEW = "under_review";
    static $allowed_statuses = Array(self::STATUS_PENDING, self::STATUS_REJECTED, self::STATUS_APPROVED, self::STATUS_UNDER_REVIEW);
    static $max_time = 604800;  // number of seconds in one week

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid() { return $this->uid; }
        public function _set_uid($val) { if(is_null($this->uid)) $this->uid = $val; }
    private $cid;
        public function _get_cid() { return $this->cid; }
        public function _set_cid($val) { if(is_null($this->cid)) $this->cid = $val; }
    private $status;
        public function _get_status() { return $this->status; }
        public function _set_status($val) { if(in_array($val, self::$allowed_statuses)) $this->status = $val; }
    private $owner_access_string;
        public function _get_owner_access_string() { return $this->owner_access_string; }
        public function _set_owner_access_string($val) { if(is_null($this->owner_access_string)) $this->owner_access_string = $val; }
    private $timestamp;
        public function _get_timestamp() { return $this->timestamp; }
        public function _set_timestamp($val) { if(is_null($this->timestamp)) $this->timestamp = $val; }
    private $message;
        public function _get_message() { return $this->message; }
        public function _set_message($val) { if(is_null($this->message)) $this->message = $val; }

    static public function createNewObj($user, $community, $message) {
        /* make sure user is  not already in community */
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("community_access", Array("count(*)"), "ii", Array($user->id, $community->id), "where uid=? and cid=? and level > 0");
        $cxn->close();
        if($count[0]["count(*)"] > 0) return NULL;

        /* make sure a pending request does not already exist */
        $existing_request = self::loadBy(Array("uid", "cid", "status"), Array($user->id, $community->id, self::STATUS_PENDING));
        if(!is_null($existing_request)) return NULL;

        $status = self::STATUS_PENDING;
        $timestamp = time();
        $owner_access_string = APIKey::getRandomKeyString(12);

        $obj = new CommunityAccessRequest(Array(
            "id" => NULL,
            "uid" => $user->id,
            "cid" => $community->id,
            "status" => $status,
            "owner_access_string" => $owner_access_string,
            "timestamp" => $timestamp,
            "message" => $message,
        ));
        self::insertObj($obj);

        if($obj->id) $obj->emailCommunityOwner($user, $community);

        return $obj;
    }

    static public function deleteObj($obj) {
        return;
    }

    static public function verifyString($owner_string, $status) {
        $return_val = Array("message" => NULL);

        /* load by string */
        $access_request = self::loadBy(Array("owner_access_string"), Array($owner_string), true, false, true);
        if(is_null($access_request)) {
            $community = new Community();
            $community->getByID(0);
            $return_val["community"] = $community;
            $return_val["message"] = "bad string";
            return $return_val;
        }

        /* request found, load the community */
        $community = new Community();
        $community->getByID($access_request->cid);
        $return_val["community"] = $community;

        /* make sure access request is still pending */
        if($access_request->status !== self::STATUS_PENDING) {
            $return_val["message"] = "already responded";
            return $return_val;
        }

        /* make sure string is searched within allotted time */
        $timenow = time();
        if($timenow - $access_request->timestamp > self::$max_time) {
            $return_val["message"] = "expired string";
            return $return_val;
        }

        if($status == self::STATUS_APPROVED) {
            if($access_request->approve()) return $return_val;
        } elseif ($status == self::STATUS_REJECTED) {
            if($access_request->reject()) return $return_val;
        }

        $return_val["message"] = "something went wrong";
        return $return_val;
    }

    public function approve() {
        if(($this->status !== self::STATUS_PENDING) && ($this->status !== self::STATUS_UNDER_REVIEW)) return false; // status already changed, return

        $user = new User();
        $user->getByID($this->uid);
        if(!$user->id) return false;

        $community = new Community();
        $community->getByID($this->cid);
        if(!$community->id) return false;

        $this->status = self::STATUS_APPROVED;
        $this->updateDB();

        if(isset($user->levels[$community->id]) && $user->levels[$community->id] > 0) return false;
        $community->join($user->id, $user->getFullName(), 1);

        $this->sendApprovedEmail($user, $community);

        return true;
    }

    public function reject() {
        if($this->status !== self::STATUS_PENDING) return false; // status already changed, return

        $this->status = self::STATUS_REJECTED;
        $this->updateDB();

        $user = new User();
        $user->getByID($this->uid);
        if(!$user->id) return;

        $community = new Community();
        $community->getByID($this->cid);
        if(!$community->id) return false;

        $this->sendRejectedEmail($user, $community);

        return true;
    }

    public function under_review() {
        if($this->status !== self::STATUS_PENDING) return false; // status already changed, return

        $this->status = self::STATUS_UNDER_REVIEW;
        $this->updateDB();

        $user = new User();
        $user->getByID($this->uid);
        if(!$user->id) return;

        $community = new Community();
        $community->getByID($this->cid);
        if(!$community->id) return false;

        $this->sendUnderReviewEmail($user, $community); // send email to community owners to let others know it's under review

        return true;
    }

    private function sendApprovedEmail($user, $community) {
        $message = Array('Your request to join the community <a href="' . $community->fullURL() . '">' . $community->name . '</a> has been approved by the community owner.');
        $text_message = 'Your request to join the community ' . $community->name . ' (' . $community->fullURL() . ') has been approved by the community owner.';
        $subject = 'Approved request to join ' . $community->name;
        \helper\sendEmail($user->email, \helper\buildEmailMessage($message, 1, $community), $text_message, $subject, NULL);
    }

    private function sendRejectedEmail($user, $community) {
        $message = Array('Your request to join the community ' . $community->name . ' has been rejected.  If you believe you were rejected in error, try resubmitting your request at <a href="' . $community->fullURL() . '">' . $community->fullURL() . '</a>.');
        $text_message = 'Your request to join the community ' . $community->name . ' has been rejected.  If you believe you were rejected in error, try resubmitting your request a ' . $community->fullURL() . ' .';
        $subject = 'Request to join '. $community->name;
        \helper\sendEmail($user->email, \helper\buildEmailMessage($message, 1, $community), $text_message, $subject, NULL);
    }

    private function emailCommunityOwner($user, $community) {
        /* get community admin emails */
        $cxn = new Connection();
        $cxn->connect();
        $emails = $cxn->select("community_access ca inner join users u on ca.uid = u.guid", Array("email"), "i", Array($community->id), "where ca.level >= 2 and u.verified = 1 and ca.cid=?");
        $cxn->close();

        $approve_url = PROTOCOL . '://' . FQDN . '/forms/community-forms/user-request-response.php?idstring=' . $this->owner_access_string . '&status=approved';
        $reject_url = PROTOCOL . '://' . FQDN . '/forms/community-forms/user-request-response.php?idstring=' . $this->owner_access_string . '&status=rejected';
        
        $userblock = "<b>User</b>: " . $user->getFullName() . "<br />\n";
        $userblock.= "<b>Email</b>: " . $user->email . "\n";
        if (!empty($user->organization))
            $userblock.= "<br /><b>Organization</b>: " . $user->organization . "\n";
        if (!empty($user->orcid_id))
            $userblock.= "<br /><b>ORCID</b>: <a href='https://orcid.org/" . $user->orcid_id . "'>" . $user->orcid_id . "</a>\n";
        if (!empty($user->website))
            $userblock.= "<br /><b>Lab website</b>: " . $user->website . "\n";

        $message = Array($userblock);
        $message[] = "<h2>Community Join Request</h2>\n";
        $message[] = '<p><b>' . $user->getFullName() . '</b> has sent a request to join your private community <a href="' . $community->fullURL() . '">' . $community->name . '</a>.</p>' . "\n" . '<p>Please click a link below:<br /><a href="' . $approve_url . '"><img src="https://scicrunch.org/assets/img/icons/icons8-ok-48.png" height="24px"> Approve request</a><br /><a href="' . $reject_url . '"><img src="https://scicrunch.org/assets/img/icons/icons8-cancel-48.png" height="24px"> Reject request</a></p><p>These links will expire after seven days, but users can still be added through the community management dashboard.</p>';

        $userblock = "User: " . $user->getFullName() . "\n";
        $userblock.= "Email: " . $user->email . "\n";
        if (!empty($user->organization))
            $userblock.= "Organization: " . $user->organization . "\n";
        if (!empty($user->orcid_id))
            $userblock.= "ORCID: https://orcid.org/" . $user->orcid_id . "\n";
        if (!empty($user->website))
            $userblock.= "Lab website: " . $user->website . "\n";
        $userblock .= "\nCommunity Join Request\n\n";

        $text_message = $userblock . $user->getFullName() . ' has sent a request to join your private community ' . $community->name . ".\n\nTo approve, go to this link: " . $approve_url . "\nTo reject, go to this link: " . $reject_url . "\n\nLinks expire after seven days, but users can still be added through the community management dashboard.\n";
        if($this->message) {
            $message[] = "The user left the following message:";
            $message[] = $this->message;
            $text_message .= "  The user left the following message: " . $this->message;
        }
        $subject = 'Request to join ' . $community->name;

        foreach($emails as $email) {
            \helper\sendEmail($email["email"], \helper\buildEmailMessage($message, 1, $community), $text_message, $subject, NULL);
        }
    }

    private function sendUnderReviewEmail($user, $community) {
        // get community admin emails 
        $cxn = new Connection();
        $cxn->connect();
        $emails = $cxn->select("community_access ca inner join users u on ca.uid = u.guid", Array("email"), "i", Array($community->id), "where ca.level >= 2 and u.verified = 1 and ca.cid=?");
        $cxn->close();

        $userblock = "<b>User</b>: " . $user->getFullName() . "<br />\n";
        $userblock.= "<b>Email</b>: " . $user->email . "\n";
        if (!empty($user->organization))
            $userblock.= "<br /><b>Organization</b>: " . $user->organization . "\n";
        if (!empty($user->orcid_id))
            $userblock.= "<br /><b>ORCID</b>: <a href='https://orcid.org/" . $user->orcid_id . "'>" . $user->orcid_id . "</a>\n";
        if (!empty($user->website))
            $userblock.= "<br /><b>Lab website</b>: " . $user->website . "\n";

        $message = Array($userblock);
        $message[] = "<h2>Community Join Request - Under Review</h2>\n";
        $message[] = '<p>Note: A community owner has started reviewing the community join request submitted by <b>' . $user->getFullName() . '</b></p>';

        $userblock = "User: " . $user->getFullName() . "\n";
        $userblock.= "Email: " . $user->email . "\n";
        if (!empty($user->organization))
            $userblock.= "Organization: " . $user->organization . "\n";
        if (!empty($user->orcid_id))
            $userblock.= "ORCID: https://orcid.org/" . $user->orcid_id . "\n";
        if (!empty($user->website))
            $userblock.= "Lab website: " . $user->website . "\n";
        $userblock .= "\nCommunity Join Request\n\n";

        $text_message = $userblock . 'Note: A community owner has started reviewing the community join request submitted by ' . $user->getFullName() . '.';
        if($this->message) {
            $message[] = "The user left the following message:";
            $message[] = $this->message;
            $text_message .= "  The user left the following message: " . $this->message;
        }
        $subject = 'Request to join ' . $community->name . " is under review";

        foreach($emails as $email) {
            \helper\sendEmail($email["email"], \helper\buildEmailMessage($message, 1, $community), $text_message, $subject, NULL);
        }
    }

    static public function getPendingCount($community) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table, Array("count(*)"), "is", Array($community->id, self::STATUS_PENDING), "where cid = ? and status = ?");
        $cxn->close();
        return $count[0]["count(*)"];
    }
}

?>
