<?php

class MailcountLog extends DBObject {
    static protected $_table = "mailcount_log";
    static protected $_table_fields = Array("id", "datetime", "mailcount");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iii";

    static public $totalAllowedEmails = 15000;

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $datetime;
        public function _get_datetime() { return $this->datetime; }
        public function _set_datetime($val) { if(is_null($this->datetime)) $this->datetime = $val; }
    private $mailcount;
        public function _get_mailcount() { return $this->mailcount; }
        public function _set_mailcount($val) { $this->mailcount = $val; }

    static public function createNewObj($date) {
        $obj = new MailcountLog(Array(
            "id" => NULL,
            "datetime" => $date,
            "mailcount" => 1,
        ));

        MailcountLog::insertObj($obj);
        return $obj;
    }

    static private function datetimeFmt($time=NULL) {
        if(is_null($time)) $time = time();
        return (int) date("Ymd", $time);
    }

    static public function addOne() {
        $today = self::datetimeFmt(time());
        $today_log = self::loadBy(Array("datetime"), Array($today));
        if(is_null($today_log)) {
            $today_log = self::createNewObj($today);
        } else {
            $today_log->mailcount += 1;
            $today_log->updateDB();
        }
        return $today_log;
    }

    static public function checkNumberEmails() {
        $monthago = self::datetimeFmt(strtotime('-31 days'));
        $cxn = new Connection();
        $cxn->connect();
        $mailcounts = $cxn->select(self::$_table, Array("mailcount"), "i", Array($monthago), "where datetime >= ?");
        $cxn->close();
        $total = 0;
        foreach($mailcounts as $mailcount) $total += $mailcount["mailcount"];
        if($total > self::$totalAllowedEmails) return false;
        return true;
    }

    static public function deleteObj($obj) {
        return;
    }
}

?>
