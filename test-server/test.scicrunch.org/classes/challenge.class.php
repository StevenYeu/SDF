<?php

class Challenge extends Connection {

    public $id;
    public $uid;
    public $cid;
    public $component;
    public $action;
    public $isAnonymous;
    public $update_time;
	public $anonymouslabel;
	public $cnt;
	public $icon;
	public $ligand;
	public $error_string;
	public $validation_counter;
	public $validate_pdb_remarks;
	public $challengeset;
	public $stage;
	public $icon1;
	public $start;
	public $end;
	public $checkfound;
	public $guestcompound = array();
	public $email;
	public $proteinligand;	
    public $json_decoded;
    public $color1;
	
    public function createFromRow($vars) {
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->cid = $vars['cid'];
        $this->component = $vars['component'];
        $this->update_time = $vars['update_time'];
        $this->isAnonymous = $vars['isAnonymous'];
        $this->action = $vars['action'];
        $this->anonymouslabel = $this->getAnonymousLabel($this->isAnonymous);
 		$this->cnt = $vars['cnt'];
 		$this->icon = $vars['icon'];
 		$this->icon1 = $vars['icon1'];
 		$this->challengeset = $vars['text1'];
 		$this->stage = $vars['title'];
 		$this->start = $vars['start'];
 		$this->end = $vars['end'];
        $this->email = $vars['email'];
        $this->color1 = $vars['color1'];
        $this->cd_icon = $vars['cd_icon'];

        $this->submission_folder = $vars['submission_folder'];
        $this->json = $vars['json'];
        $this->week = $vars['week'];
        $this->year = $vars['year'];
        $this->add_date = $vars['add_date'];
        $this->box_file_id = $vars['box_file_id'];
        $this->version = $vars['version'];
        $this->source = $vars['source'];

    }

	public function getByID($id) {
        $this->connect();
        $return = $this->select('challenge_data', array('*'), 'i', array($id), 'where id=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }

	public function getIcon1($id) {
 		$this->connect();

	/*	$return = $this->select('select distinct icon1
from community_components cc
inner join component_data cd on cc.component = cd.component
inner join extended_data ed on cd.component = ed.component', array('*'), 'i', array($id), "where ed.id = ?"); 		
*/

$return = $this->select('community_components as cc left join component_data as cd on cc.component=cd.component left join extended_data as ed on cd.component = ed.component', array('icon1'), 'i', array($id), 'where ed.id=?');
        $this->close();

        if (count($return) > 0) {
	        return $return[0]['icon1'];
//            $this->createFromRow($return[0]);
        }
	}
	
    public function getIcon($component) {
 		$this->connect();
		$return = $this->select('component_data', array('icon, component'), 'i', array($component), 'where id=?');

        $this->close();
        if (count($return) > 0) {
//	        return $return[0]['icon'];
            $this->createFromRow($return[0]);
        }
	}
	
	public function getChallengesetByID($component) {
		$this->component = $component;
 		$this->connect();
		$return = $this->select('component_data as comp left join community_components as comm on comm.component = comp.component', array('comp.*', 'comm.icon1', 'comm.text1', 'comm.cid'), 'i', array($component), 'where comp.id=?');
        $this->close();

		if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
	}	

    public function getChallengeByStageID($component) {
        $this->connect();
        $return = $this->select('community_components cc1
            inner join community_components cc2 on cc1.component = cc2.icon3
            inner join component_data cd on cd.component = cc2.component', array('cc1.*','cd.icon as cd_icon'), 'i', array($component), 'where cd.id=?');
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
            return true;
        } else 
            return false;
    }   

	public function insertChallengeDB() {
        $this->connect();

        if (($this->action == 'join') || ($this->action == 'leave')) {
            $return = $this->select('challenge_data', array('action'), 'i', array($this->uid, $this->component), 'where uid=? AND component=? ORDER BY id DESC limit 1');

            // don't do the action if redundant
            if (count($return) && ($return[0]['action'] == $this->action))
                return false;
        }

        $this->id = $this->insert('challenge_data', 'iiiiis', array(null, $this->uid, $this->isAnonymous, $this->component, $this->update_time, $this->action));
        $this->close();
	}

    public function updateChallengeDB() {
        $this->connect();
        $this->update('challenge_data', 'iiiisi', array('uid', 'isAnonymous', 'component', 'update_time', 'action'), array($this->uid, $this->isAnonymous, $this->component, $this->update_time, $this->action, $this->id), 'where id=?');
        $this->close();
    }

	public function checkRegistration($uid, $component) {
        $this->connect();
        $return = $this->select('challenge_data', array('*'), 'ii', array($uid, $component), "where uid=? AND component=? AND action<>'download' ORDER BY update_time desc limit 1");
        $this->close();

        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
/*
		if (count($return)) {
			if ($return[0]['action'] == 'join')
				return "registered_" . $return[0]['update_time'];
		} else
			return "not registered";	
*/			
	}

	public function getAnonymousStatus($uid, $component) {
        $this->connect();
        $return = $this->select('challenge_data', array('anonymous'), 'ii', array($uid, $component), "where uid=? AND component=? AND action='join' ORDER BY update_time desc limit 1");
        $this->close();

		if (count($return)) {
			if ($return[0]['anonymous'] == 0)
				return "Public";
			else
				return "Anonymous";	
		} else
			return "Unknown";	
	}

	public function getAnonymousLabel($anon) {
		if ($anon == 1) {
			return "anonymous";
		} else {
			return "public";
		}	
	}
	
	public function getMyDownloads($uid, $component) {
		 $this->connect();
		 $return = $this->select('challenge_data as chd inner  join extended_data as ed on chd.component = ed.id 
		inner join component_data as cd  on ed.component = cd.id 
		inner join community_components as cc on cd.component = cc.component', array('chd.id', 'chd.component', 'chd.update_time', 'ed.name', 'ed.file', 'cd.title', 'cc.text1'), 'i', array($uid), "where chd.uid = ? AND action='download'");

        $this->close();

        if (count($return) > 0) {
	        return $return;
//            $this->createFromRow($return[0]);
        }

	}
	
	public function getActiveChallenges($cid) {
		$this->connect();
		 $return = $this->select('community_components as comm inner join component_data  as comp on comm.text2 = comp.link',
			 array('comm.text1, comm.component'), 'i', array($cid), "where comm.cid = ? AND comm.icon1 = 'challenge1'
	AND comp.start <= UNIX_TIMESTAMP()
	AND comp.end >= UNIX_TIMESTAMP()");

		$this->close();
		if (count($return) > 0) {
	        return $return;
        }        	
	}

    // time = upcoming, active, completed
    public function getChallengesByTimePeriod($timeperiod, $cid) {
        switch ($timeperiod) {
            case "upcoming":
                $whereparam = ' AND comp.start > UNIX_TIMESTAMP()';
                break;

            case "active":
                $whereparam = ' AND comp.start <= UNIX_TIMESTAMP()
    AND comp.end >= UNIX_TIMESTAMP()';
                break;

            case "completed":
                $whereparam = ' AND comp.end <= UNIX_TIMESTAMP()';
                break;

        }
        $this->connect();
        $return = $this->select('community_components as comm inner join component_data  as comp on comm.text2 = comp.link',
			array('comm.text1, comm.component'), 'i', array($cid), "where comm.cid = ? AND comm.icon1 = 'challenge1'" . $whereparam . " ORDER BY comp.start desc");
    
        $this->close();
        if (count($return) > 0) {
            return $return;
        }           
    }

	public function getChallengesetFromTitle($title) {
        $this->connect();
        $return = $this->select('component_data as cd 
inner join community_components cc on cc.component = cd.component
inner join community_components cc2 on cc.icon3 = cc2.component', array('cd.id'), 's', array($title), "where cc.icon1 = 'challengeset1'
AND cc2.text1 = ?");
        $this->close();

        if (count($return) > 0) {
        return $return;
//            $this->createFromRow($return[0]);
        }
	}
	
	public function getJoined($component) {
        $this->connect();
        $return = $this->select('community_components as comm 
inner join component_data  as comp on comm.text2 = comp.link
inner join challenge_data chal on chal.component = comm.component
inner join users u on u.guid = chal.uid', array('distinct comm.text1, chal.uid, u.firstname, u.lastname, u.email, u.organization'), 'i', array($component), "where comm.component=? and chal.action = 'join'
order by text1, u.lastname");
        $this->close();

        if (count($return) > 0) {
        return $return;
//            $this->createFromRow($return[0]);
        }
	}

	public function getDownloaded($component) {
        $this->connect();
		 $return = $this->select('challenge_data as chd 
		inner join extended_data as ed on chd.component = ed.id 
		inner join component_data as comp  on ed.component = comp.component
		inner join community_components as comm on comp.component = comm.component', array('chd.id'), 'i', array($component), "where comm.icon3=? AND action='download'");
        $this->close();

        if (count($return) > 0) {
        	return $return;
//            $this->createFromRow($return[0]);
        }
	}
	
	public function getSubmissionByChallengeComponent ($component) {
		$this->connect();
		$return = $this->select('challenge_submissions as cs 
		inner join component_data cd on cd.id = cs.component
		inner join community_components cc1 on cd.component = cc1.component
		inner join community_components cc2 on cc1.icon3 = cc2.component', array('distinct cs.uid'), 'i', array($component), "where cc2.component=?");
		$this->close();


        if (count($return) > 0) {
	        return $return;
//            $this->createFromRow($return[0]);
        }

		return $return;
	}

	public function showYourResultsBlock($component) {
		$this->connect();
		$return = $this->select('community_components cc1 inner join component_data cd on cc1.component = cd.component', array('cd.icon'), 'i', array($component), "where cc1.icon3=?");
		$this->close();

		if (count($return) > 0) {
			foreach ($return as $component_stage) {
				//var_dump($component_stage['icon']);
				$obj = json_decode($component_stage['icon']);

				if ((time() > strtotime($obj->open_submissions)) && (time() < strtotime($obj->close_submissions))) {
					return true;
				}
			}
		}

		return false;
	}

    public function showStageLabel($component) {
        $this->connect();
        $return = $this->select('component_data cd1 inner join component_data cd2 on cd1.component = cd2.component', array('cd2.id'), 'i', array($component), "where cd1.id=?");
        $this->close();

        // if more than one stage exists, then show the Stage X label, which is the cd2.title field
        if (count($return) > 1)
            return true;
        else
            return false;
    }

    // check to see if submission URL, like dknet/about/challenge-url/stage_id is real
    public function checkSubmissionURL($vars){
        $this->connect();
        $return = $this->select('communities comm
            inner join community_components comp1 on comp1.cid = comm.id
            inner join community_components comp2 on comp1.component = comp2.icon3
            inner join component_data data on data.component = comp2.component', array('comp1.id'), 'iii', 
            array($vars['portalName'], $vars['title'], $vars['id']), '
            where comm.portalName = ?
                AND comp1.text2 = ?
                AND data.id = ?');
        $this->close();

        if (count($return))
            return true;
        else
            return false;
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

// submission_folder, json, week, add_date, box_file_id
        $this->id = $this->insert('celpp_results', 'issiiisss', array(null, $this->submission_folder, $this->json, $this->week, $this->year, $this->add_date, $this->source, $this->version,  $this->box_file_id));
        $this->close();
    }

    public function getAllCELPPjsonByWeekYear($week, $year) {
        $this->connect();

        $return = $this->select('celpp_results', array('submission_folder, json'), 'ii', array($week, $year), "where week=? AND year=? and source='production'");
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

    // accepts $weeks values of "1", "3", "all"
    // returns the corresponding 'yearweek'
    public function getCELPPYearWeek($weeks) {
        $this->connect();
        
        if ($weeks == "1") {
            $orderby = " ORDER BY yearweek desc LIMIT 1";
        } elseif ($weeks == "all") {
            $orderby = " ORDER BY yearweek asc LIMIT 1";
        } else {
            $orderby = " ORDER BY yearweek desc LIMIT 3";
        }

        $return = $this->select('celpp_weeks ', array('distinct yearweek'), 's', array($weeks), "where (source='production' OR source='box')" . $orderby);
        $this->close();

        if (count($return[0]) > 0) {
            if (($weeks == "1") || ($weeks == "all")) {
                return $return[0]['yearweek'];
            } else {
                // should have 3 array values; return last one;
                return $return[2]['yearweek'];
            }
        }
        return false;        
    }
    

    public function getCELPPjsonByWeeksAgo($yearweek) {
        $this->connect();
        $return = $this->select('celpp_results cr inner join celpp_user_folders cuf on cuf.submission_folder = cr.submission_folder inner join celpp_weeks cw on cr.week = cw.week AND cr.year = cw.year', array('distinct celpp_userhash as submission_folder, cuf.submission_folder as uid_folder, json'), 'i', array($yearweek), "where cw.yearweek >=? AND (cr.source='production' OR cr.source='box') AND json <> '[]'");
        $this->close();

        if (count($return[0]) > 0) {
            return $return;
        }
        return false;        
    }

    public function getCELPPtargetSum($yearweek) {
        $this->connect();
        $return = $this->select('(
select distinct targets, cw.week, cw.year
from celpp_results cr inner join celpp_weeks cw on cr.week = cw.week AND cr.year = cw.year
where cw.yearweek >= ?
) a', array('sum(targets) as summ'), 'i', array($yearweek), "");
        $this->close();

        if (count($return[0]) > 0) {
            return $return;
        }
        return false;        
    }
    public function getBoxAuth() {

        // credentials for "D3R API User" in ucsd-cddi.app.box.com
        // can I store these using D3R api key storage?
        $client_id      = 'nj42q4j300wz8ems7lfmlmts72h2a6xp';
        $client_secret  = '4F1x4WqH5psoWTEBLRyMePfhppe0IpTY';
        $redirect_uri   = 'https://celpp/php/d3r/celpp/redirected.php';

        $box = new Box_API($client_id, $client_secret, $redirect_uri);
//echo "after start getBoxAuth";

        if(!$box->load_token()){
            echo "no token, so i am here";
            if(isset($_GET['code'])){
                $token = $box->get_token($_GET['code'], true);
                var_dump($token);
                echo "That was token";

                if($box->write_token($token, 'file')){
                    $box->load_token();
                }
            } else {
                echo "\n<br />no code, so start here ...";
                $box->get_code();
            }
        }
//var_dump($box);
//exit;
        return $box;
    }   




}
	
function rrmdir($dir) { 
   if (is_dir($dir)) { 
     $objects = scandir($dir); 
     foreach ($objects as $object) { 
       if ($object != "." && $object != "..") { 
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
       } 
     } 
     reset($objects); 
     rmdir($dir); 
   } 
}

function addAndOrForGrammar($andor, $array, $append='') {
	$str = array_pop($array) . $append;
	if ($array) {
		$str = implode("$append, ", $array) . "$append " . $andor . " " . $str;
	}
	
	return $str;	
}

	function build_crystal_proteinligand_array ($crystal, $ligand) {
		$crystal_ligand_array = array();
		foreach ($ligand as $l) {
			foreach ($crystal as $c) {
				array_push($crystal_ligand_array, $c . "-" . $l);
			}
		}
		
		return $crystal_ligand_array;
	}

    /* protocol lines keep getting read until a "checkfor" condition is true */
	function stop_reading_lines($line, $checkfor) {
		foreach($checkfor['required'] as $req) {
			if (preg_match('/^' . $req . '/', $line)) {
				return true;
			}
			
			if (trim(substr($line, 0, 1) == '#'))
				return true;
/*				
			if (trim(substr($line, 0, 1) == ''))
				return true;
*/				
		}

        foreach($checkfor['requiredyn'] as $req) {
            if (preg_match('/^' . $req . '/', $line)) {
                return true;
            }
            
            if (trim(substr($line, 0, 1) == '#'))
                return true;
        }

		return false;
	}

    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    function getToken($length, $alpha=false)
    {
        $token = "";
    //    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet = "abcdefghijkmnopqrstuvwxyz"; // removed l since it looks like 1

        if ($alpha != true)
            $codeAlphabet .= "023456789"; // removed 1 since it looks like l; 0 is ok since no capital O

        $max = strlen($codeAlphabet) - 1;
        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[crypto_rand_secure(0, $max)];
        }
        return $token;
    }

	function checkIfRanksAreNotConsecutive($consecutive) {
        // fixed 11-15-2016 mchiu
        sort($consecutive);

		// $stop value indicates 'nopred' or 'inact' found, which should come at end of rank list ...
	
		// check first $consecutive value
		if (!(is_numeric($consecutive[0])))
			$stop = 0;

		// if first $consecutive value ok, then check the difference be/w adjacent $consecutive records
		if (!(isset($stop))) {
			for ($i=1; $i<sizeof($consecutive); $i++) {
				if (!(is_numeric($consecutive[$i]))) {
					$stop = $i;
					break;
				} else {
					// if the diff is more than one, then a rank is missing
					if ($consecutive[$i] - $consecutive[$i-1] > 1) {
						return "rank missing between ranks: " . addAndOrForGrammar("and", array($consecutive[$i-1], $consecutive[$i]));
					} elseif ($consecutive[$i] == $consecutive[$i-1]) {
						return "duplicate rank: " . $consecutive[$i];
					}	
				}
			}
		}				
			
		if (isset($stop)) {
			// if still here then so far all ranks are consecutive. 
			// now check to see if a numeric rank exists after the text values, which is bad
			for ($i=$stop+1; $i<sizeof($consecutive); $i++) {
				if (is_numeric($consecutive[$i])) {
					return "numeric rank found after line with 'nopred' or 'inact'";
				}
			}
		}		

		return false;
	}


    function checkIfEnergiesAreOrdered($energies) {
        $byenergy = $energies;
        $byrank = $energies;

        // Obtain a list of columns
        foreach ($byenergy as $key => $row) {
            $arank[$key] = $row['rank'];
            $aenergy[$key] = $row['energy'];

        }

        // make copy because original will get changed ...
        $brank = $arank;

        // Sort the data by 'energy' and also by 'rank'
        array_multisort($aenergy, SORT_ASC, $arank, SORT_ASC, $byenergy);
        array_multisort($brank, SORT_ASC, $byrank);

        // if the sorted arrays are different, then need to specify where ...
        if ($byenergy != $byrank) {
            $byby = array();

            $lastenergy = $byrank[0]['energy'] - 1;

            foreach ($byrank as $by) {
                if ($by['energy'] <= $lastenergy)
                    $byby[] = $by['ligand'];

                $lastenergy = $by['energy'];
            }

            return "Energy values do not match rank order. See " . addAndOrForGrammar('and', $byby);
        }


        return false;
    }

    function anonymousPublicPrivate($status) {
        if ($status)
            return 'Private';
        else
            return 'Public';
    }

    function get_weekth($y, $m, $d)
    {
        return intval(date('W',strtotime($y.'-'.$m.'-'.$d)));
    }    
?>
