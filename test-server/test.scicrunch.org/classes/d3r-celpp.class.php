<?php

class D3RCelpp extends Connection {

    public $id;
    public $uid;
    public $cid;
    public $week;
    public $year;
    public $json;
    public $add_date;
    public $submission_folder;
    public $box_file_id;
    public $box_folder;
    public $targets;
    public $source;
    public $version;
    public $version_schrodinger;
    public $targets_user;
	
    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->week = $vars['week'];
        $this->year = $vars['year'];
        $this->json = $vars['json'];
        $this->submission_folder = $vars['submission_folder'];
        $this->add_date = $vars['add_date'];
        $this->box_file_id = $vars['box_file_id'];
        $this->box_folder = $vars['box_folder'];
        $this->targets = $vars['targets'];
        $this->source = $vars['source'];
        $this->version = $vars['version'];
        $this->version_schrodinger = $vars['version_schrodinger'];
        $this->targets_user = $vars['targets_user'];
    }

    public function insertCELPPregistration() {
        $this->connect();

        if (($this->action == 'join') || ($this->action == 'leave')) {
            $return = $this->select('celpp_users', array('action'), 'i', array($this->uid, $this->component), 'where uid=? AND component=? ORDER BY id DESC limit 1');

            // don't do the action if redundant
            if (count($return) && ($return[0]['action'] == $this->action))
                return false;
        }

// uid, uid_alpha, box_id, email, celpp_id, join_date
        $this->id = $this->insert('challenge_data', 'iiiiis', array(null, $this->uid, $this->isAnonymous, $this->component, $this->update_time, $this->action));
        $this->close();
    }


    public function insertCELPPjson() {
        $this->connect();
        $this->id = $this->insert('celpp_results', 'issiiissiisi', array(null, $this->submission_folder, $this->json, $this->week, $this->year, $this->add_date, $this->source, $this->version, $this->box_file_id, null, $this->version_schrodinger, $this->targets_user));
        $this->close();
    }

    public function insertCELPPweek() {
        $this->connect();
        $padweek = sprintf('%02d', $this->week);
        $yearweek = $this->year . $padweek;

        $this->id = $this->insert('celpp_weeks', 'iiiissi', array(null, $this->week, $this->year, $this->targets, $this->source, $this->box_folder, $yearweek));
        $this->close();
    }

    public function getAllCELPPjsonByWeekYear($week, $year) {
        $this->connect();

        $return = $this->select('celpp_results', array('submission_folder, json'), 'ii', array($week, $year), 'where week=? AND year=?');
        $this->close();

        if (count($return) > 0) {
            return $return;
        }

        return false;        
    }	

    public function getCELPPjsonByID($id) {
        $this->connect();

        $return = $this->select('celpp_results', array('json'), 'i', array($id), 'where box_file_id=?');
        $this->close();
        if (count($return[0]) > 0) {
            return $return[0]['json'];
        }

        return false;        
    }

    public function getCELPPjsonByWeeksAgo($weeks) {
        $this->connect();

        $return = $this->select('celpp_results', array('submission_folder, json'), 'i', array($weeks), 'where week >=?');
        $this->close();

        if (count($return[0]) > 0) {
            return $return;
        }
        return false;        
    }

    public function getCELPPjsonByWeekYear($submission_folder, $week, $year) {
        $this->connect();

        $return = $this->select('celpp_results', array('json'), 'sii', array($submission_folder, $week, $year), 'where submission_folder = ? AND week = ? and year = ?');
        $this->close();

        if (count($return[0]) > 0) {
            return $return;
        }
        return false;        
    }

     public function getCELPPResultsGTEYear($year) {
        $this->connect();

        $return = $this->select('celpp_results cr', array('cr.submission folder', 'cr.json', 'cr.week', 'cr.year'), 'i', array($year), "where year >= ?"); 
        $this->close();
        if (count($return) > 0) {
            return $return;
//            $this->createFromRow($return[0]);
        }
        return $return;
    }    
}
