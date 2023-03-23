<?php
class Challenge_Submission extends Connection {
    public function createFromRow($vars) {
        $this->uid = $vars['uid'];
        $this->email = $vars['email'];
        $this->filename = $vars['filename'];
        $this->protocol_id = $vars['protocol_id'];
    }
    public function startFileValidation($path, $file, $component) {
        $folder_path = $this->decompressFile($path, $file);
        
        if (in_array($component, array(279, 280)))
            $this->validateGC2015 ($component, $folder_path);
        unlink($folder_path);
        exit;
        
    }
    // check to see if file actually is gzip.
    public function gzipCheck($file_array) {
        $command = 'file ' . $file_array["tmp_name"];
        exec($command, $retval);
        // /private/var/tmp/php2UcjJS: POSIX tar archive 0
        // /private/var/tmp/phpI4aAuH: gzip compressed data, from Unix, last modified: Tue Dec 15 06:15:45 2015 0
        if (strpos($retval[0], "POSIX tar")) {
            $this->error_string = $file_array["name"] . " is not a gzip compressed file. Please gzip the file using 'tar -czf'.\n";
            echo str_replace("\n", "<br />\n", $this->error_string);
            exit;
        }   
    }
    // Excel validation can only be done by checking extension
    public function excelCheck($file_array) {
        $file_parts = pathinfo($file_array['name']);
        if (!(($file_parts['extension'] == 'xls') || ($file_parts['extension'] == 'xlsx'))) {
            $this->error_string = $file_array["name"] . " is not an Excel file.";
            echo str_replace("\n", "<br />\n", $this->error_string);
            exit;
        }
    }
    public function sendReceiptEmail($vars, $email) {
        $ch = new Challenge;
        $ch->getChallengesetByID($vars['component']);
        $comm = new Community;
        $comm->getByID($ch->cid);
        $subject = $ch->challengeset . " - " . $ch->stage . " Submission Received - " . $vars['receipt_id'];
        $noquestion_url = array_shift(explode("?", $_SERVER['HTTP_REFERER']));
$html_message = "<p style='padding-bottom: 10px;'>Thank you for your <strong>" . $ch->challengeset . " - " . $ch->stage . "</strong> submission. It has been assigned a unique name " . $vars['filename'] . " with a receipt ID: <strong>" . $vars['receipt_id'] . "</strong>. Please include this ID in any correspondence about your submission.</p>
<p style='padding-bottom: 10px;'>You can manage your " . $ch->challengeset . " - " . $ch->stage . " submissions at <a href='" . $noquestion_url . "'>" . $noquestion_url . "</a>.</p>
<p>Sincerely,<br />
D3R
</p>";
$text_message = "Thank you for your " . $ch->challengeset . " - " . $ch->stage . " submission. It has been assigned a unique " . $vars['filename'] . " with a receipt ID: " . $vars['receipt_id'] . ". Please include this ID in any correspondence about your submission.
You can manage your " . $ch->challengeset . " - " . $ch->stage . " submissions at " . $noquestion_url . ".
Sincerely,
D3R";
        $to = $email;
        \helper\sendEmail($to, \helper\buildEmailMessage(array($html_message, '&nbsp;'), $alt=0, $comm), $text_message, $subject, 'Drug Design Data <drugdesigndata@gmail.com>');
    }
public function sendDeleteEmail($vars, $email) {
        $ch = new Challenge;
        $ch->getChallengesetByID($vars['component']);
        $comm = new Community;
        $comm->getByID($ch->cid);
        $subject = $ch->challengeset . " - " . $ch->stage . " Submission Deleted - " . $vars['receipt_id'];
        $noquestion_url = array_shift(explode("?", $_SERVER['HTTP_REFERER']));
        $html_message = "<p style='padding-bottom: 10px;'>Your <strong>" . $ch->challengeset . " - " . $ch->stage . "</strong> submission (" . $vars['receipt_id'] . ") has been deleted</p>
<p style='padding-bottom: 10px;'>You can manage your " . $ch->challengeset . " - " . $ch->stage . " submissions at <a href='" . $noquestion_url . "'>" . $noquestion_url . "</a>.</p>
<p>Sincerely,<br />
D3R
</p>";
$text_message = "Your " . $ch->challengeset . " - " . $ch->stage . " submission (" . $vars['receipt_id'] . ") has been deleted. 
You can manage your " . $ch->challengeset . " - " . $ch->stage . " submissions at " . $noquestion_url . ".
Sincerely,
D3R";
        $to = $email;
        \helper\sendEmail($to, \helper\buildEmailMessage(array($html_message, '&nbsp;'), $alt=0, $comm), $text_message, $subject, 'Drug Design Data <drugdesigndata@gmail.com>');
        $notification = new Notification();
        $notification->create(array(
            'sender' => 0,
            'receiver' => $_SESSION['user']->id,
            'view' => 0,
            'cid' => $ch->cid,
            'timed'=>0,
            'start'=>time(),
            'end'=>time(),
            'type' => 'challenge-submission-deleted',
            'content' => 'Submission successfully deleted : ' . $vars['receipt_id']
        ));
        $notification->insertDB();      
    }
        
    public function tarfileNameCheck($filename, $checkagainst) {
        if ($this->dupFilenameCheck($_SESSION['user']->id, $filename, $this->component))
            return $filename . " has already been submitted. Please upload with a new name.\n";
        $found = 0;
        foreach ($checkagainst as $check) {
            // look for dock, score, free_energy, or whatever in filename
            if (stripos($filename, $check) !== false)
                $found++;
        }
    
        if ($found > 1)
            $error_string .= $filename . " cannot have " . addAndOrForGrammar("or", $checkagainst) . " more than once in filename";
        if (!($found))
            $error_string .= $filename . " must have " . addAndOrForGrammar("or", $checkagainst) . " in the filename";
        if (isset($error_string))
            return $error_string;
        else
            return;         
            
    }
    
    // run 'tar -tzf' to see if top level directory is there AND no Mac files
    public function tarFileTZFCheck($destination) {
        exec('tar -tzf ' . $destination, $output);
        if (substr($output[0], -1) !== "/") {
            return "Missing required top level directory";
        } else {
            foreach ($output as $file) {
                if (strpos($file, "._") !== false) {
                    return "'._' Mac files found. Please use the '--disable-copyfile' tar option to remove them.";
                } elseif ($file == '.DS_Store') {
                    return "'.DS_Store' Mac files found. Please use the '--exclude=.DS_Store' tar option to remove it.";
                }
            }
        }
        return false;
    }   
    public function dupFilenameCheck($uid, $filename, $component) {
        $this->connect();
        $return = $this->select('challenge_submissions', array('filename'), 'sii', array("%-" . $component . "-" . $filename, $uid, $component), "where filename LIKE ? AND uid=? AND component=?");
        $this->close();
        if (count($return) > 0) {
            return $return;
        }
    }
    
    public function moveTempTarFile ($tmpfile, $filename, $component) {
        $unvalidated_filename = $_SESSION['user']->id . "-" . $component . "-" . time() . "-" . $filename; // basic rename 
        $destination = $this->challenge_path . "unvalidated/" . $component . "/" . $unvalidated_filename;
        move_uploaded_file($tmpfile, $destination);
        return $destination;
    }
    public function moveTempTarRadioFile ($tmpfile, $filename, $component, $radio) {
        $unvalidated_filename = $_SESSION['user']->id . "-" . $component . "-" . $radio . "-" . time() . "-" . $filename; // basic rename 
        $destination = $this->challenge_path . "unvalidated/" . $component . "/" . $unvalidated_filename;
        move_uploaded_file($tmpfile, $destination);
        return $destination;
    }
    public function moveTempExcelFile ($tmpfile, $filename, $component) {
        $unvalidated_filename = $_SESSION['user']->id . "-" . $component . "-" . time() . "-" . $filename; // basic rename 
        $destination = $this->challenge_path . "unvalidated/" . $component . "/" . $unvalidated_filename;
        move_uploaded_file($tmpfile, $destination);
        return $destination;
    }
    public function moveValidatedTarFile ($unvalidated, $component, $filename) {
        $receipt = getToken(5);
        $receipt_filename = $receipt . "-" . $component . "-" . $filename; // name with receipt instead of userid
        $receipt_destination = $this->challenge_path . "validated/" . $component . "/" . $receipt_filename;
        if (!copy($unvalidated, $receipt_destination))
            $this->error_string = $filename . " - there was an error moving the file";
        else {
            unlink($unvalidated);
            return $receipt_filename;
        }   
    }
    public function moveValidatedTarRadioFile ($unvalidated, $component, $filename, $radio) {
        $receipt = getToken(5);
        $receipt_filename = $component . "-" . $radio . "-" . $receipt . "-" . $filename; // name with receipt instead of userid
        
        $receipt_destination = $this->challenge_path . "validated/" . $component . "/" . $receipt_filename;
        if (!copy($unvalidated, $receipt_destination))
            $this->error_string = $filename . " - there was an error moving the file";
        else {
            unlink($unvalidated);
            return $receipt_filename;
        }   
    }    

    // newer version that can decompress files > 20M
    public function decompressTarFile2($filee, $filename) {
        // Raising this value may increase performance
        $buffer_size = 4096; // read 4kb at a time

        // handle both .tgz and .tar.gz
        if (substr($filename, -4) == '.tgz')
            $out_file_name = str_replace('.tgz', '.tar', $filee); 
        elseif (substr($filename, -7) == '.tar.gz')
            $out_file_name = str_replace('.tar.gz', '.tar', $filee); 

        // Open our files (in binary mode)
        $file = gzopen($filee, 'rb');
        $out_file = fopen($out_file_name, 'wb'); 

        // Keep repeating until the end of the input file
        while (!gzeof($file)) {
            // Read buffer-size bytes
            // Both fwrite and gzread and binary-safe
            fwrite($out_file, gzread($file, $buffer_size));
        }

        // Files are done, close files
        fclose($out_file);
        gzclose($file);

//        echo "made it past extraction => " . $out_file_name . "\n";

        $phar = new PharData($out_file_name); ///something.tar
        $phar->extractTo(str_replace(".tar", "", $out_file_name));

        $tmp_path = str_replace(".tar", "", $out_file_name);
        unlink($out_file_name);

        if (is_dir($tmp_path)) {
            if ($dh = opendir($tmp_path)) {
                // count how many dirs found. Should only be one ...
                $dir_found = 0;

                while (($file = readdir($dh)) !== false) {
                    // skip past . and .. in directory listing
                    if (($file == ".") || ($file == ".."))
                        continue;

                    if (filetype($tmp_path . "/" . $file) == "dir") {
                        $good_dir = $file;
                        $dir_found++;
                    } elseif (filetype($tmp_path . "/" . $file) == "file") {
                        $this->error_message = "file found when only directories allowed!";
                        echo $this->error_message; //$filename . " - could not untar file\n";
                        unlink($tmp_path);
                        exit;
                    }
            
                }
                closedir($dh);
    
                if ($dir_found > 1) {
                    $this->error_message = "more than one directory found!";
                    echo $this->error_message;
                    unlink($tmp_path);
                    exit;
                }
            }
        }

        unlink($tmp_path);
        return $folder_path = $tmp_path . "/" . $good_dir;
    }


    public function decompressTarFile($file, $filename) {
        try {
            // decompress from gz
            $p = new PharData($file);
            $p->decompress();
    //      unlink($file);
        } catch (Exception $e) {
           $this->error_message = $filename . " - could not ungzip file\n";
            echo str_replace("\n", "<br />\n", $this->error_message);
           exit;
        }
        try {
            // unarchive from the tar
            if (substr($file, -3) == '.gz')
                $tarname = str_replace(".gz", "", $file);
            elseif (substr($file, -4) == '.tgz')    {
                $tarname = str_replace(".tgz", "", $file) . ".tar";
            }
            $phar = new PharData($tarname); ///something.tar
            $phar->extractTo(str_replace(".tar", "", $tarname));
            unlink($tarname);
        } catch (Exception $e) {
           $this->error_message = $filename . " - could not untar file\n";
            echo str_replace("\n", "<br />\n", $this->error_message);
           exit;
        }
        
        $tardir = array_diff(scandir(str_replace(".tar", "", $tarname)), array('..', '.'));
        // [0] = .; [1] = ..; [2] is the wanted directory
        $this->darned_dir = str_replace(".tar", "", $tarname);
        if (is_dir(str_replace(".tar", "", $tarname) . "/" . $tardir[2])) {
            // if they tar'd up a directory, send back tar dir name
            return $folder_path = str_replace(".tar", "", $tarname) . "/" . $tardir[2];
        } else {
            // if they just tar'd up files, then just send back the dir name
            return $folder_path = str_replace(".tar", "", $tarname);
        }
    }
    
    public function insertSubmission ($vars) {
        $this->connect();
        $this->id = $this->insert('challenge_submissions', 'iissisis', 
        array(null, $vars['uid'], $vars['receipt_id'], $vars['filename'], $vars['component'], $vars['type'], $vars['isAnonymous'], $vars['update_time']));
        $this->close();
    }
    public function insertProtocol ($vars) {
        $this->connect();
        $this->id = $this->insert('challenge_protocols', 'iissiis', 
        array(null, $vars['challenge_submission_id'], $vars['filename'], $vars['protocol_id'], $vars['uid'], $vars['component'], $vars['add_date']));
        $this->close();
    }
    public function insertProtocolSubmission ($vars) {
        $this->connect();
        $this->id = $this->insert('challenge_protocols_submissions', 'iiiii', 
        array(null, $vars['component'], $vars['protocol_id'], $vars['submission_id'],  $vars['uid']));
        $this->close();
    }

    public function deleteProtocolsSubmissions ($submission_id,$uid) {
        $this->connect();
        $this->id = $this->delete('challenge_protocols_submissions', 'ii', 
        array($submission_id,  $uid), 'where submission_id = ? AND uid = ?');
        $this->close();
    }

    public function getSubmissionByUser ($cid, $component, $uid) {
        $this->connect();
        $return = $this->select('challenge_submissions', array('*'), 'ii', array($uid, $component), "where uid=? AND component=?");
        $this->close();
        if (count($return) > 0) {
            return $return;
//            $this->createFromRow($return[0]);
        }
        return $return;
    }
    public function getProtocolsFromSubmissionID($submission_id) {
        $this->connect();
        $return = $this->select('challenge_protocols cp inner join challenge_protocols_submissions cps on cp.id = cps.protocol_id', array('cp.protocol_id', 'cp.component', 'cp.filename'), 'i', array($submission_id), "where submission_id = ?"); 
        $this->close();
        if (count($return) > 0) {
            return $return;
//            $this->createFromRow($return[0]);
        }
        return $return;
    }
/*
    public function getProtocolsSubmissionsByUser($cid, $component, $uid) {
        $this->connect();
        $return = $this->select('challenge_submissions cs', array('cs.*', 
        "(select cp.protocol_id from challenge_protocols_submissions cps inner join challenge_protocols cp on cps.protocol_id = cp.id where cps.submission_id = cs.id AND cp.protocol_id LIKE '%-pp') as pp ", 
        "(select cp.protocol_id from challenge_protocols_submissions cps inner join challenge_protocols cp on cps.protocol_id = cp.id where cps.submission_id = cs.id AND cp.protocol_id LIKE '%-fe') as fe ",
        "(select cp.protocol_id from challenge_protocols_submissions cps inner join challenge_protocols cp on cps.protocol_id = cp.id where cps.submission_id = cs.id AND cp.protocol_id LIKE '%-ls') as ls"), 'ii', array($uid, $component), "where uid=? AND component=?");
        $this->close();
        if (count($return) > 0) {
            return $return;
//            $this->createFromRow($return[0]);
        }
        return $return;
    }
*/
    public function getSubmissionFromReceipt ($receipt) {
        $this->connect();
        $return = $this->select('challenge_submissions', array('id', 'uid', 'filename', 'component', 'type', 'isAnonymous'), 's', array($receipt), "where receipt_id=?");
        $this->close();
        if (count($return) > 0) {
            return $return[0];
//            $this->createFromRow($return[0]);
        }
        return $return;
    }
    public function deleteSubmissionFile($filename) {
        unlink(realpath($filename));
    }
    
    public function logicalDeleteSubmissionFile($filename, $newname) {
        rename(realpath($filename), $newname);
    }
    
    public function deleteSubmissionRecord($receipt, $uid, $component) {
        $this->connect();
        $this->delete('challenge_submissions', 'si', array($receipt, $uid), 'where receipt_id=? AND uid = ?');
        $this->close();
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = $receipt;
        $vars['component'] = $component;
        $this->sendDeleteEmail($vars, $_SESSION['user']->email);
    }
    
    public function getProtocolFromProtocolID ($protocol_id) {
        $this->connect();
        $return = $this->select('challenge_protocols', array('uid', 'filename', 'component'), 's', array($protocol_id), "where protocol_id=?");
        $this->close();
        if (count($return) > 0) {
            return $return[0];
//            $this->createFromRow($return[0]);
        }
        return $return;
    }
    public function deleteProtocolFile($filename) {
        unlink(realpath($filename));
    }
    
    public function logicalDeleteProtocolFile($filename, $newname) {
        rename(realpath($filename), $newname);
    }
    
    public function deleteProtocolRecord($protocol_id, $uid, $component) {
        $this->connect();
        $this->delete('challenge_protocols', 'si', array($protocol_id, $uid), 'where protocol_id=? AND uid = ?');
        $this->close();
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = $receipt;
        $vars['component'] = $component;
  //      $this->sendDeleteEmail($vars, $_SESSION['user']->email);
    }
    
    
    
    public function countSubmissionsPerStage($component, $uid) {
        $this->connect();
        $return = $this->select('challenge_submissions', array('count(*) as cnt'), 'ii', array($component, $uid), 'where component=? AND uid=?');
        $this->close();
        if (count($return) > 0) {
            return $return[0]['cnt'];
        }
    }
    public function GetUserInfoFromReceipt($receipt) {
        $this->connect();
        $return = $this->select('challenge_submissions as cs inner join users u on u.guid = cs.uid', array('email', 'uid'), 's', array($receipt), 'where receipt_id=?');
        
        $this->close();
        if (count($return) > 0) {
            $this->createFromRow($return[0]);
        }
    }
    
    // changed to no longer use $component ...
    public function GetProtocolsByUID($uid) {
        $this->connect();
        $return = $this->select('challenge_protocols cp', array('id', 'uid', 'filename', 'protocol_id', 'add_date', 'component', '(select count(*) from challenge_protocols_submissions where protocol_id = cp.id) as submissions'), 'i', array($uid), "where uid = ? ORDER BY add_date asc");
        
        $this->close();
        if (count($return) > 0) {
            return $return;
        }
   
    }
    
    public function validateScoresFilename($filename, $max, $required_scores_array) {
        $found = 0;
        foreach ($required_scores_array as $score) {
            $target = $score . "Scores";
            if (preg_match('/^' . $target . '-(.*)\.csv/i', $filename, $matches)) {
                if ((is_int((int) $matches[1])) && ($matches[1] > 0) && ($matches[1] <= $max)) {    
                    $this->{$score . "Scores"}[] = $matches[1];
                    return true;
                } else {
                    $this->error_string .= $filename . " - Invalid or missing prediction value\n";
                    return false;
                }
            }
        }
        $this->error_string .= $filename . " - Filename must begin with " . addAndOrForGrammar("or", $required_scores_array, "Scores-") . "\n";
    }
    
    public function validateProtocolFilename($filename, $max, $required_protocols_array) {
        $found = 0;
        foreach ($required_protocols_array as $protocol) {
            $target = $protocol . "Protocol";
            if (preg_match('/^' . $target . '-(.*)\.txt/i', $filename, $matches)) {
                if ((is_int((int) $matches[1])) && ($matches[1] > 0) && ($matches[1] <= $max)) {    
                    $this->{$protocol . "Protocol"}[] = $matches[1];
                    return true;
                } else {
                    $this->error_string .= $filename . " - Invalid or missing prediction value\n";
                    return false;
                }
            }
        }
        $this->error_string .= $filename . " - Filename must begin with " . addAndOrForGrammar("or", $required_protocols_array, "Protocol-") . "\n";
    }
    public function checkScoresVsProtocolPredictions($proteinligand) {
        foreach ($this->{$proteinligand . "Scores"} as $prediction) {
            if (!(in_array($prediction, $this->{$proteinligand . "ScoringProtocol"})))
                $this->error_string .= $proteinligand . "Score-" . $prediction . ".csv does not have a matching " . $proteinligand . "ScoringProtocol file\n";
        }       
        foreach ($this->{$proteinligand . "ScoringProtocol"} as $prediction) {
            if (!(in_array($prediction, $this->{$proteinligand . "Scores"})))
                $this->error_string .= $proteinligand . "ScoringProtocol-" . $prediction . ".csv does not have a matching " . $proteinligand . "Scores file\n";
        }       
    }
    public function validateScoresLines($path, $file, $dockscore) {
        $proteinligand = preg_replace('/Scores-\d*\.csv/i', '', $file);
        $found = 0;
        // if valid protein found, we need to start 'watching' it, thus $watch[$protein1][$rank]
        $watch = array();
//      $valid_crystal_structure_array = $this->json_decoded->validation_crystal_structure; 
        $valid_crystal_structure_array = $this->json_decoded->{$dockscore . "_validation"}->{strtolower($proteinligand . "s")};
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);
        foreach ($rows as $row) {
            if (sizeof($valid_crystal_structure_array) < sizeof($watch)) {
                $this->error_string .= $file . " - The number of lines in the file exceeds the number of proteins\n";
                break;
            }
            if ((substr($row, 0, 1) == '#') || ($row == ''))
                continue;
            else {
                if (!($found)) {
                    if (preg_match('/^Type:\s?(\w*)/i', trim($row), $matches)) {
                        if (strlen(str_replace($matches[0], '', trim($row)))) {
                            $this->error_string .= $file . " - Invalid text '" . trim(str_replace($matches[0], '', trim($row))) . "' found after 'energy' or 'score'\n";
                        } else {    
                            if (!((strtolower($matches[1]) == 'energy') || (strtolower($matches[1]) == 'score')))
                                $this->error_string .= $file . " - 'Type' value is invalid. Must be 'energy' or 'score'\n";
                        }
                        $found = 1; // even if there are errors, should still report as found to suppress the 'no type line' found error
                    }
                } else {
                    $row_split = explode(",", $row);
                    if (sizeof($row_split) !== 3)
                        $this->error_string .= $file . " - Line: $row - should have 3 values\n";
                    else {  
                        $protein1 = $row_split[0];
                        $rank = trim($row_split[1]);
                        $energy_score = trim($row_split[2]);
                    
                //      var_dump($watch);
                //      var_dump(array_diff($valid_crystal_structure_array, array_keys($watch)));
                        
                        // continue if protein1 hasn't been found yet, ie not on the watch list
                        if (in_array($protein1, array_diff($valid_crystal_structure_array, array_keys($watch)))) {
                            // if it's in the array, go ahead and set the key since it was included. 
                            //will still give error if other data in line is bad
                            $watch[$protein1] = '';
                            if (strlen($rank)) {
                                // 'inact' and 'nopred' are the only allowed non numeric values
                                // *** Note: not checking the last value if inact/nopred. that's how example file looks ...
                                if ( (strtolower($rank) == 'inact') || (strtolower($rank) == 'nopred')) {
                                    // I think I should still watch this since the rank's need to be unique
                                    $watch[$protein1][$rank] = $rank;
                                    $found = $protein1;
//                                  continue;
                                } elseif ((is_integer($rank + 0)) && ($rank > 0)) { // need the +0 since $rank is a string.
                                    if (strlen($energy_score)) {
                                        if ( (strtolower($energy_score) == 'inact') || (strtolower($energy_score) == 'nopred')) {
                                            // I think I should still watch this since the rank's need to be unique
                                            $watch[$protein1][$rank] = $rank;
                                            $found = $protein1;
//                                          continue;
                                        } elseif (is_numeric($energy_score)) {
                                            $watch[$protein1][$rank] = $rank;
                                            $found = $protein1;
                                        } else {
                                            $this->error_string .= $file . " - Line: $row - energy/score must be numeric (" . $energy_score . ")\n";
                                        }                                       
                                        
                                    } else {
                                        $this->error_string .= $file . " - Line: $row - energy/score is missing\n";
                                    }
                                } else {
                                    $this->error_string .= $file . " - Line: $row - rank '" . $rank . "' must be a positive integer\n";
                                }
                            } else {
                                $this->error_string .= $file . " - Line: $row - rank is missing\n";
                            }
                        } else {
                            if (in_array($protein1, array_keys($watch)))
                                $this->error_string .= $file . " - Line: $row - duplicate " . strtolower($proteinligand) . " ($protein1)\n";
                            else    
                                $this->error_string .= $file . " - Line: $row - invalid " . strtolower($proteinligand) . " ($protein1)\n";
                        }
                    }
                }
            }
        }
        if (!($found)) {
            $this->error_string .= $file . " does not have a line that starts with 'Type:'\n";
        }
        
        if ($prot_diff = array_diff($valid_crystal_structure_array, array_keys($watch))) {
            $this->error_string .= $file . " - All proteins must be included. Missing: " . addAndOrForGrammar("and", $prot_diff) . "\n";
        }   
//      var_dump($watch);
//      var_dump(array_diff($valid_crystal_structure_array, array_keys($watch)));
        // Do I need to check the ranks here?
        // I would think that all the integer ranks would come first, and 'inact/nopred' would come after
        // the protein needs to be unique, so $watch[$protein1] doesn't seem right.
        $consecutive = array();
        foreach ($watch as $proteinligand) {
            foreach ($proteinligand as $key=>$value) {
                $consecutive[] = $value;
            }   
        }
        if ($return_consecutive = checkIfRanksAreNotConsecutive($consecutive)) {
            $this->error_string .= $file . " - " . $return_consecutive . "\n";
        }   
    }
    public function extractProtocolText($path, $file, $checkfor) {
    // $checkfor['type'] will be things like 'PosePrediction', 'ProteinScoring', 'LigandScoring' ...
// System Preparation Method:
// Pose Prediction Method:
// Method:
        $file_id = $path . "/" . $file;
        // This will be different than SAMPL since some of them have data on the same line ... vs having data after the label 
        $temp_checkfor = $checkfor['required'];
        $temp_checkforyn = $checkfor['requiredyn'];
        $method = 0;
        $method_text = array();
        $file_contents = file_get_contents($path . "/" . $file);
        $line_array = explode("\n", $file_contents);
        for ($l=0; $l<sizeof($line_array); $l++) {
            if ((substr($line_array[$l], 0, 1) == '#') || (trim($line_array[$l]) == ''))
                continue;
            if ($method) {
                // if method found, read the rest of the lines until end
                for ($r=$l; $r<sizeof($line_array); $r++) {
//                    print_r($r . " - " . $line_array[$r]);
                    //$method_text .= $line_array[$r];
                    
                    // only keep reading until some stop flag is reached.
                    if (stop_reading_lines($line_array[$r], $checkfor)) {
                        $l = $r-1;
                        $method = 0;
                        break;
                    } else {
                        $method_text[$method][] = $line_array[$r];
                        $l = $r;
                    }   
                }
                $this->save_protocol[$file_id][$method] = $method_text[$method];
                $method = 0;
                
                //break; // break out of main loop .. should end looping of lines
//              $this->sql_save_protocol_data($file_id, 'Method:', $method_text);
            
            } else {
                $valid_checkfor = 0;
                $valid_checkforyn = 0;
                // check Yes/No fields for proper value
                //print_r($l . " - " . $line_array[$l]);
                for ($i=0; $i<sizeof($checkfor['requiredyn']); $i++) {
                    // field has ? mark, so need to escape it!!!
                    $pattern = '/^' . str_replace("?", "\?", $checkfor['requiredyn'][$i]) . '\s*(.*)/i';
                    if (preg_match($pattern, $line_array[$l], $matches)) {
                        $value = trim($matches[1]);
                        if (!(in_array(strtolower($value), array('y', 'n', 'yes', 'no'))))
                            $this->error_string .= $file . " - Line: '" . $line_array[$l] . " - <span style='color:red'>'" . $checkfor['requiredyn'][$i] . "' field must be Yes/No.</span>\n";
                        else {
                            $valid_checkforyn = 1;
                            $this->save_protocol[$file_id][$checkfor['requiredyn'][$i]][] = $value;
                        }
                        // set the $valid_checkfor so that it doesn't go thru the plain required checks.
                        $valid_checkfor = 1;
                        unset($temp_checkforyn[$i]);
                    }
                }
                if ($valid_checkfor)
                    continue;
                for ($i=0; $i<sizeof($checkfor['required']); $i++) {
                    $pattern = '/^' . $checkfor['required'][$i] . '\s*(.*)/i';
                    if (preg_match($pattern, $line_array[$l], $matches)) {
                        $value = $matches[1];
                        // handle 'Name' differently since only one line allowed.
                        if ($checkfor['required'][$i] == 'Name:') {
                            if (strlen($value)) {
                                if (!(in_array('Name:', $temp_checkfor))) {
                                    $this->error_string .= $file . " - Line: '" . $line_array[$l] . " - 'Name:' field already exists\n";
                                } else {
                                    $valid_checkfor = 1;
                                    unset($temp_checkfor[$i]);
                                    $this->save_protocol[$file_id][$checkfor['required'][$i]][] = $value;
                                }
                            } else {
                                $valid_checkfor = 1;
                                $this->error_string .= $file . " - Line: '" . $line_array[$l] . "' - blank field found\n";
                            }
                        } 
                        // 'Method' handled differently since need to grab paragraph text ...
                        // Since paragraph text expected, need to give leeway if text starts on next line ...
                        elseif (   (($checkfor['type'] == 'PosePrediction') && (($checkfor['required'][$i] == 'System Preparation Method:') || ($checkfor['required'][$i] == 'Pose Prediction Method:'))) || ( (($checkfor['type'] == 'ProteinScoring') || ($checkfor['type'] == 'LigandScoring') || ($checkfor['type'] == 'FreeEnergy')) && ($checkfor['required'][$i] == 'Method:'))) {
//                      } elseif ($checkfor['required'][$i] == 'Method:') {
                            if (in_array($checkfor['required'][$i], array_keys($method_text))) {
                                $this->error_string .= $file . " - Line: '" . $line_array[$l] . " - '" . $checkfor['required'][$i] . " already exists\n";
                                $valid_checkfor = 1;                                
                            } else {
                                $valid_checkfor = 1;
                                unset($temp_checkfor[$i]);
                                $method = $checkfor['required'][$i];
                                $method_text[$method][] = $value;
                            }
                        } elseif (strlen($value)) {
                            $valid_checkfor = 1;
                            unset($temp_checkfor[$i]);
                            $this->save_protocol[$file_id][$checkfor['required'][$i]][] = $value;
                        } else {
                            $valid_checkfor = 1;
                            $this->error_string .= $file . " - Line: '" . $line_array[$l] . "' - blank field found\n";
                        }
                        break;
                    }
                }
                
                if (!($valid_checkfor)) {
                    $this->error_string .= $file . " - Line: '" . $line_array[$l] . "' - invalid field found\n";
                }   
            }
        }
        // can be problematic for the very last method field if it's blank since it's supposed to continue reading lines ...
        foreach (array_keys($method_text) as $expected) {
            $cnt = 0;
            foreach ($method_text[$expected] as $line) {
                $cnt += strlen(trim($line));
            }
            if ($cnt == 0) {
                $this->error_string .= $file . " - " . $expected . "' - blank field found\n";
                $temp_checkfor[] = $expected;
            }
        }
        
        // allowing both 'Parameters:' and 'Parameter:' required a little extra work ...
        // $p will count the number of missing `Parameter(s)` fields
        $p = 0;
        if (count($temp_checkfor)) {
            foreach ($temp_checkfor as $key=>$field) {
                if (($field == 'Parameters:') || ($field == 'Parameter:')) {
                    $p++;
                    unset($temp_checkfor[$key]);
                }
            }
            // if neither version of Parameter(s) is present, then give appropriate error
            if ($p == 2) {
                if (($this->foo->index == 2) || ($this->foo->index == 3))
                    $pvalue = 'Parameters:';
                elseif (($this->foo->index == 4) || ($this->foo->index == 5))
                    $pvalue = 'Parameter:';
                $this->error_string .= $file . " is missing the `$pvalue` field\n";
            }
            // if just one "Parameter(s) is present, that means they're OK."
            if (!(empty($temp_checkfor)))
                $this->error_string .= $file . " is missing some field(s): " . addAndOrForGrammar("and", $temp_checkfor) . "\n";
        }
        if (count($temp_checkforyn))
             $this->error_string .= $file . " is missing some field(s): " . addAndOrForGrammar("and", $temp_checkforyn) . "\n";   
             
        displayErrorString($this->error_string);
    }
    public function validateUserInfoText($path, $file) {
        $error = 0;
        $file_id = $path . "/" . $file;
        $checkfor = $temp_checkfor = array('Submitter Last Name:', 'Submitter First Name:', 'Submitter email:', 'Submitter Organization:', 'Research group or PI Name:', 'Research group or PI Email:');
    
        $file_contents = file_get_contents($path . "/" . $file);
        $line_array = explode("\n", $file_contents);
        for ($l=0; $l<sizeof($line_array); $l++) {
            if (!(strlen(trim($line_array[$l])))) {
                continue;
            }
            $valid_checkfor = 0;
            for ($i=0; $i<sizeof($checkfor); $i++) {
                if (preg_match('/^' . $checkfor[$i] . '\S*(.*)/i', $line_array[$l], $matches)) {
                    if (strlen(trim($matches[1]))) {
                        if (!(in_array($checkfor[$i], $temp_checkfor))) {
                            $this->error_string .= $file . " - Line: '" . $line_array[$l] . "' - '" . $checkfor[$i] . "' field already exists\n";
                        } else {
                            unset($temp_checkfor[$i]);
                            $this->save_user_info[$checkfor[$i]][] = $matches[1];
                            $valid_checkfor = 1;
                        }
                    }
                }   
            }
            if (!($valid_checkfor)) {
                $this->error_string .= "Line: '" . $line_array[$l] . "' - Invalid UserInfo.txt line\n";
                $error = 1;
            }
        }
        
        if (sizeof($temp_checkfor)) {
            $this->error_string .= $file . " is missing some data: " . addAndOrForGrammar("and", $temp_checkfor) . ". Use NA if appropriate.\n";
            $error = 1;
        }
        
        if ($error) {
            return false;
        }
        return true;
    }
    
    public function sql_save_protocol_data ($file_id, $check, $value) {
        $this->save_protocol[$file_id][$check][$value] = $value;
    }
    
    public function validatePDBID($filename, $pdb) {
        //var_dump($pdb);
        if (strlen($pdb) !== 4) {
            $this->error_string .= $filename . " filename must start with a 4 character PDB code\n";
        } elseif (preg_match('/[^a-z0-9]/i', $pdb)) {
            $this->error_string .= $filename . " filename must start with an alphanumeric PDB code\n";
        }
    }
    public function validateLigand($filename, $ligand, $valid_ligand_array) {
        if (!(in_array($ligand, $valid_ligand_array)))
            $this->error_string .= $filename . " has an invalid ligand ($ligand).\n";
    }
    public function validatePoseRank($filename, $poserank, $poserank_max) {
        // pose = 0
        if (($poserank_max > 0) && ($poserank == 0))
            $this->error_string .= $filename . " has an invalid pose rank value (0)\n";
        // pose too high
        if ($poserank > $poserank_max)
            $this->error_string .= $filename . " has an invalid pose rank value ($poserank)\n";
    }
    public function validateLineCount($folder_path, $filename, $min) {
        $txt_file = file_get_contents($folder_path . "/" . $filename);
        $rows = explode("\n", $txt_file);
        if (sizeof($rows) < $min) {
            $this->error_string .= $filename . " - line count is too low (" . sizeof($rows) . " lines/$min line minimum)\n";
        }
    }
    public function validateMOLRemarks($folder_path, $filename, $pose) {
        $txt_file = file_get_contents($folder_path . "/" . $filename);
        $rows = explode("\n", $txt_file);
        if (substr(strtoupper($rows[0]), 0, 6) == 'REMARK') {
            $parts = preg_split('/\s+/', trim($rows[0]));
            if ((strtolower($parts[1]) == 'energy') || (strtolower($parts[1]) == 'score')) {
                if (isset($parts[2])) {
                    if (is_numeric($parts[2]))
                        $this->validate_mol_remarks->$filename->energy = $parts[2];
                    else    
                        $this->error_string .= $filename . " - Energy/score value must be numeric\n";
                } else {
                    $this->error_string .= $filename . " is missing the energy/score value in the REMARK line\n";
                    $this->validate_mol_remarks->$filename = 0;
                }
            } else {
                $this->error_string .= $filename . " - REMARK line must have the phrase 'energy' or 'score'\n";
            }   
        } else {
            if ($pose > 1)
                $this->error_string .= $filename . " - The first line must have a REMARK with energy/score data\n";
        }
        if (empty($rows[4]))
            $this->error_string .= $filename . " - Row 5 cannot be blank. Please make sure the file is not empty.\n";
        elseif (!(stripos($rows[3], 'V2000')))
            $this->error_string .= $filename . " - not V2000 compliant (missing V2000 on line 4). \n";
    }    
    public function validatePDBMolMatch($pdbmol_array) {
       if ($adiff = array_diff($pdbmol_array['.pdb'], $pdbmol_array['.mol']))
            $this->error_string .= "There must be a MOL file for each PDB file. Missing: " . addAndOrForGrammar("and", $adiff, '.mol') . "\n";
        if ($adiff = array_diff($pdbmol_array['.mol'], $pdbmol_array['.pdb']))
            $this->error_string .= "There must be a MOL file for each PDB file. Missing: " . addAndOrForGrammar("and", $adiff, '.pdb') . "\n";
    }
    public function validatePDBPoseDuplicate($files, $poserank_max) {
        $tmp_array = array();
        // some types will not have pose values, so dup check will be different
        if ($poserank_max > 0) { 
            // if rank is a single digit, then chop off -X
            if ($poserank_max < 10) {
                foreach ($files as $file) {
                    $pdb_ligand = substr($file, 0, strlen($file) - 2);
                    if (in_array($pdb_ligand, $tmp_array))
                        $this->error_string = $pdb_ligand . " can not have more than one pose\n";
                    else
                        $tmp_array[] = $pdb_ligand;
                }
            }
        }
    }
    public function validatePDBMolFilename($filename, $valid_ligand_array) {
        $pdbmol = strtolower(substr($filename, -4));
        if (($pdbmol !== '.mol') || ($pdbmol !== '.pdb')) {
            $this->error_string .= $filename . " has an invalid filename.\n";
            return false;
        }
        $base = str_ireplace($pdbmol, "", $filename);
        $maxpose = 5;
        $found = 0;
        $error_here = 0;
        // expecting (PDB)-(ligandID)-(poserank)
        preg_match('/(.*)-(.*)-(\d*)/', $base, $matches);
    
        if (!(sizeof($matches))) {
            $error_here = 1;
            $this->error_string .= $filename . " has an invalid filename.\n";
        } elseif ($matches[3] == '') {
            $error_here = 1;
            $this->error_string .= $filename . " has an invalid pose number.\n";
        } elseif (($matches[3] < 1) || ($matches[3] > $maxpose)) {
            $error_here = 1;
            $this->error_string .= $filename . " has an invalid pose number ({$matches[3]}).\n";
        } else {
            foreach ($valid_ligand_array as $ligand) {
                if ('FXR_' . $ligand == strtoupper($matches[2])) {
                    /* doubtful situation, but might be possible ... what if user had xyz-1.pdb and xyz-1.PDB?
                        need to give an error */
                    if (isset($this->ligand[$ligand][$pdbmol][$matches[3]])) {
                        $error_here = 1;
                        $this->error_string .= $pl[$i] . " with pose " . $matches[3] . " - duplicate found.\n";
                    } else {
                        $this->ligand[$ligand][$pdbmol][$matches[3]] = $matches[3];
//                                $this->validation_counter->ligand->$pl[$i]->$pdbmol->$matches[3]['count']++;
//                          print_r($this->validation_counter->ligand);
                        $found = 1;
                    }   
                    break;
                }
            }
            if (!($found)) {                
                $error_here = 1;
                $this->error_string .= $filename . " " . $matches[1] . " " . $matches[2] . " has an invalid filename.\n";
            }
        }
        if ($error_here)
            return false;
        else
            return true;
    }
/* let's try a new way to do this with GC2 */
    public function validateGC2($component, $file_array, $radio) {
        $this->listofligands = array();
        $this->predictioncategory = $radio;
        $this->gzipCheck($file_array);
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarRadioFile($file_array["tmp_name"], $file_array["name"], $component, $this->foo->index);
        // decompress tmp file and get back path + directory name÷
        $folder_path = $this->decompressTarFile($destination, $file_array["name"]);
        $this->folder_path = $folder_path;
        $challenge = new Challenge();
        $challenge->getChallengeByStageID($component);
        $challenge_settings = json_decode($challenge->color1);
        // build array of allowed files;
        foreach (array('csv', 'txt') as $ext) {
            $req = $ext . '_required';
            $req_array[$ext] = (array)$this->foo->{$req};
            $this->{$ext} = array();
        }
        /* loop thru files in $folder_path for basic filename check */
        $this->validateGC2BasicFilenameCheck($folder_path, $req_array);
        displayErrorString($this->error_string);
        // do more indepth checking ...
        $this->buildValidLigandArray();
        // check txt, csv, pdb, mol
        $this->validateGC2FindMissingFiles($this->files);
        // check pdb, mol
        // hmm, some things compare all files together, some check things individually ...
        if (isset($this->foo->pdbmol_required)) {
            $this->validateGC2PDBMolFilenames($this->files, $this->foo->maxpose);
// Take this out . It is certainly ok to have different pdb with ligand.
/*            
            $this->validateLigandPDB($this->LigandPDB);
            
            if ($this->foo->maxpose)
                $this->validatePDBLigandGaps($this->PDB_Ligand);
*/
        }
        displayErrorString($this->error_string);
    
        foreach ($this->files->csv as $csv) {
            if (strtolower($csv) == 'ligandscores.csv') {
                $this->validateGC2Scores($folder_path, $csv, $this->valid_ligand_array);
            } elseif (strtolower($csv) == 'freeenergies.csv') {
                $this->validateGC2FreeEnergies($folder_path, $csv, $this->valid_ligand_array);
            }
        }  
        displayErrorString($this->error_string);
        // protocol files require parsing blocks of text, but each should be similar ...
        foreach ($this->files->txt as $txt) {
            if (strtolower($txt) == 'posepredictionprotocol.txt') {
                $checkfor = array('type'=>'PosePrediction', 'required'=>array('Name:', 'Software:', 'System Preparation Parameters:', 'System Preparation Method:', 'Pose Prediction Parameters:', 'Pose Prediction Method:'));
                $this->extractProtocolText($folder_path, $txt, $checkfor);
            } elseif (strtolower($txt) == 'ligandscoringprotocol.txt') {
                $checkfor = array('type'=>'LigandScoring', 'required'=>array('Name:', 'Software:', 'Parameters:', 'Parameter:', 'Method:'));
                $this->extractProtocolText($folder_path, $txt, $checkfor);
            } elseif (strtolower($txt) == 'freeenergyprotocol.txt') {
                $checkfor = array('type'=>'FreeEnergy', 'required'=>array('Name:', 'Software:', 'Parameters:', 'Parameter:', 'Method:'));
                $this->extractProtocolText($folder_path, $txt, $checkfor);
            } elseif (strtolower($txt) == 'userinfo.txt') {
                $this->validateUserInfoText($folder_path, $txt);
            }
        }  
        displayErrorString($this->error_string);
        // run validation, move to validated directory if ok
        $receipt_filename = $this->moveValidatedTarRadioFile($destination, $component, $file_array["name"], $this->foo->index);
        $rec_split = explode("-", $receipt_filename);
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = $rec_split[2];
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $vars['type'] = $radio;
        $this->insertSubmission($vars);
        
        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
        rrmdir($this->darned_dir);
    }
    public function validateGC2BasicFilenameCheck($folder_path, $req_array) {
        /* start with a loop across directory to validate file names only, build list as we go */
        $dir = array_diff(scandir($folder_path), array('..', '.'));
        if (!(sizeof($dir)))
            $this->error_string .= "Submission folder cannot be empty\n";
        else {  
            foreach ($dir as $filename) {
                if (substr($filename, 0, 2) == '._')
                    continue;
                if (substr($filename, 0, 9) == '.DS_Store')
                    continue;
                if (strtolower($filename) == 'suppinfo')
                    continue;
            
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                if (($extension == 'csv') || ($extension == 'txt')) {
                    // sizeof = 0 means that extension is not , and a file is found, 
                    if (!(sizeof($req_array[$extension])))
                        $this->error_string .= $filename . " is not allowed\n";
                    elseif (!(in_array( $filename, $req_array[$extension] ))) {
                        $this->error_string .= $filename . " is not an allowed " . strtoupper($extension) . " file\n";
                    } 
                    // don't bother saving filenames if there already is an error
                    elseif (!(strlen($this->errorstring))) {
                        if (isset($this->files->{$extension}[$filename]))
                            $this->error_string .= "Duplicate file found ($filename)\n";
                        else
                            $this->files->{$extension}[] = $filename;
                    }
                    
                } elseif (($extension == 'pdb') || ($extension == 'mol')) {
                    if (!(isset($this->foo->pdbmol_required)))
                        $this->error_string .= strtoupper($extension) . " files are not allowed ($filename)\n";
                    else {
                        if (preg_match($this->foo->pdbmol_required, str_replace("." . $extension, "", $filename), $matches)) {
                            if (isset($this->files->{$extension}[$filename]))
                                $this->error_string .= "Duplicate file found ($filename)\n";
                            else {
                                $this->files->{$extension}[$filename] = $matches;
                            }
                        } else {
                            $this->error_string .= $filename . " has an invalid filename\n";
                        }
                    }
                }
                else
                    $this->error_string .= "Invalid filename or folder ($filename)\n";
            }
        }
    }
    public function buildValidLigandArray($abbr) {
    // if there are ligands to check, need to build $this->valid_ligand_array
    // valid_ligand_range uses x..y, whereas valid_ligand_specific has exact list
        if (isset($this->foo->valid_ligand_range)) {
            list($ligand_min, $ligand_max) = explode(",", $this->foo->valid_ligand_range);
            // populate the $valid_ligand_array of valid ligands
            for ($i=$ligand_min; $i<=$ligand_max; $i++) {
                $this->valid_ligand_array[] = $abbr . '_' . (int) $i;
            }
        } elseif (isset($this->foo->valid_ligand_specific)) {
            $this->valid_ligand_array = array_map(function($ligand, $abbr) { return strtoupper($this->foo->abbr) . "_" . $ligand; }, explode(",", $this->foo->valid_ligand_specific));
        }
    }
    public function buildValidProteinLigandArray() {
        foreach ($this->valid_ligand_array as $ligand) {
            foreach ($this->foo->valid_protein_specific as $protein) {
                $this->valid_proteinligand_array[] = $protein . "-" . $ligand;
            }
        }
    }
    public function validateGC2FindMissingFiles($files) {
        // check for missing files in the _required list
        foreach (array('txt', 'csv') as $extension) {
            $req = $extension . "_required";
            if (isset($this->foo->{$req})) {
                // if no files of that extension submitted, show error
                if (!($files->$extension))
                    $this->error_string .= "Missing " . strtoupper($extension) . " file(s): " . addAndOrForGrammar("and", $this->foo->$req) . "\n";
                // if extension submitted, compare against required list
                else {
                    $diff1 = array_diff($this->foo->{$req}, $files->$extension);
                    if (sizeof($diff1))
                        $this->error_string .= "Missing " . strtoupper($extension) . " file(s): " . addAndOrForGrammar("and", $diff1) . "\n";
                }
            }
        }
        if (isset($this->foo->pdbmol_required)) {
            // check and see if required PDB/MOL files are present
            if (!($files->pdb))
                $this->error_string .= "PDB files are required\n";
            if (!($files->mol))
                $this->error_string .= "MOL files are required\n";
            // check to see if each pdb has a mol and vice-versa
            if (!(strlen($this->error_string))) {
                $mol_keys = array_keys($this->files->mol);
                foreach ($this->files->pdb as $pdb) {
                    $newmol = $pdb[0] .= ".mol";
                    if (!(in_array($newmol, $mol_keys))) {
                        $this->error_string .= "Each PDB file must have a matching MOL file. ($newmol not found)\n";
                    }
                    
                }
                $pdb_keys = array_keys($this->files->pdb);
                foreach ($this->files->mol as $mol) {
                    $newpdb = $mol[0] .= ".pdb";
                    if (!(in_array($newpdb, $pdb_keys))) {
                    $this->error_string .= "Each MOL file must have a matching PDB file. ($newpdb not found)\n";
                    }
                    
                }
            }
        }
    }
    public function validateGC2PDBMolFilenames($files, $maxpose) {
        $min = 200;
        foreach ($files->pdb as $matches) {
            // validate PDBID
            $this->validatePDBID($matches[0] . '.pdb', $matches[1]);
            // validate LigandID
            $this->validateLigandID($matches[2], $this->valid_ligand_array);
            // validate PoseRank
            if ($maxpose) {
                $this->validatePoseRank($matches[0] . '.pdb', $matches[3], $maxpose);
            }
            $this->LigandPDB[$matches[2]][$matches[1]][$matches[3]] = 1;
            $this->PDB_Ligand[$matches[1] . '-' . $matches[2]][$matches[3]] = 1;
            $this->validateMOLRemarks($this->folder_path, $matches[0] . '.mol', $matches[3]);
            $this->validateLineCount($this->folder_path, $matches[0] . '.pdb', $min);
        }
/*
        // need to figure out how to show warning and allow upload to continue ...
        $ligand_diff = (array_diff($this->valid_ligand_array, $this->listofligands));
        if (count($ligand_diff)) {
            $this->warning_string .= 'Missing ligand(s): ' . addAndOrForGrammar('and', $ligand_diff) . "\n";
//            echo "Warning: " . $this->warning_string . "<br />\n";
        }
*/        
    }
    public function validateLigandID($ligandid, $valid_ligand_array) {
        if (!(in_array(strtoupper($ligandid), $valid_ligand_array)))
            $this->error_string .= "LigandID is not valid ($ligandid)\n";
        else {
            $this->listofligands[] = strtoupper($ligandid);
        }
    }
    public function validateProteinLigandID($proteinligandid, $valid_proteinligand_array) {
        if (!(in_array(strtoupper($proteinligandid), $valid_proteinligand_array)))
            $this->error_string .= "Protein Ligand pair is not valid ($proteinligandid)\n";
        else {
            $this->listofproteinligands[] = strtoupper($proteinligandid);
        }
    }
    public function validateLigandPDB($ligandpdb) {
        foreach (array_keys($ligandpdb) as $ligand) {
            $array5 = array();
            if (sizeof(array_keys($ligandpdb[$ligand])) > 1) {
                foreach (array_keys($ligandpdb[$ligand]) as $pdb) {
                    foreach (array_keys($ligandpdb[$ligand][$pdb]) as $digitt) {
                        if (in_array($digitt, $array5)) {
                            $this->error_string .= "More than one 'Pose $digitt' found for $ligand\n";
                        } else 
                            $array5[] = $digitt;
                    }
                }
            }
//                $this->error_string .= $ligand . " cannot be used with more than one PDBID (" . addAndOrForGrammar("and", array_keys($ligandpdb[$ligand])) . ")\n";
        }
    }
    // check to see if pose(s) start with 1, no gaps be/w poses
    public function validatePDBLigandGaps($pdb_ligand) {
        foreach (array_keys($pdb_ligand) as $pair) {
            $keyset = array_keys($pdb_ligand[$pair]);
            sort($keyset);
            for ($i=0; $i<sizeof($keyset); $i++) {
                if ($i == 0) {
                    if ($keyset[$i] !== 1)
                        $this->error_string .= $pair . "-" . $keyset[$i] . ".pdb - First pose rank must be 1\n";
                } else {
                    if (($keyset[$i] - $last_index) > 1) {
                        $this->error_string .= $pair . " - Pose rank values must be consecutive. Gap found between ranks " . addAndOrForGrammar("and", array($keyset[$i-1], $keyset[$i])) . "\n";
                    }
                }
                $last_index = $keyset[$i];
            }
        }
    }
    public function validateGC2Scores($path, $file, $valid_ligand_array) {
        $typefound = 0;
        // if valid protein found, we need to start 'watching' it, thus $watch[$protein1][$rank]
        $watch = array();
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);
        foreach ($rows as $row) {
            if (sizeof($valid_ligand_array) < sizeof($watch)) {
                $this->error_string .= $file . " - The number of lines in the file exceeds the number of ligands\n";
                break;
            }
            if ((substr($row, 0, 1) == '#') || ($row == ''))
                continue;
            else {
                if (!($typefound)) {
                    if (preg_match('/^Type:\s?(\w*)[\s,]*/i', trim($row), $matches)) {
                        if (strlen(str_replace($matches[0], '', trim($row)))) {
                            $this->error_string .= $file . " - Invalid text '" . trim(str_replace($matches[0], '', trim($row))) . "' found after 'energy' or 'score'\n";
                        } else {    
                            if (!((strtolower($matches[1]) == 'energy') || (strtolower($matches[1]) == 'score')))
                                $this->error_string .= $file . " - 'Type' value is invalid. Must be 'energy' or 'score'\n";
                        }
                        $typefound = 1; // even if there are errors, should still report as found to suppress the 'no type line' found error
                    }
                } else {
                    $row_split = explode(",", $row);
                    if (sizeof($row_split) !== 3)
                        $this->error_string .= $file . " - Line: $row - should have 3 values\n";
                    else {  
                        $ligand = $row_split[0];
                        $rank = trim($row_split[1]);
                        $energy_score = trim($row_split[2]);
                    
                        if (in_array($ligand, array_diff($valid_ligand_array, array_keys($watch)))) {
                            // if it's in the array, go ahead and set the key since it was included. 
                            //will still give error if other data in line is bad
                            $watch[$ligand] = '';
                            if (strlen($rank)) {
                                // 'inact' and 'nopred' are the only allowed non numeric values
                                // *** Note: not checking the last value if inact/nopred. that's how example file looks ...
                                if ( (strtolower($rank) == 'inact') || (strtolower($rank) == 'nopred')) {
                                    // I think I should still watch this since the rank's need to be unique
                                    $watch[$ligand][$rank] = $rank;
                                    $typefound = $ligand;
//                                  continue;
                                } elseif ((is_integer($rank + 0)) && ($rank > 0)) { // need the +0 since $rank is a string.
                                    if (strlen($energy_score)) {
                                        if ( (strtolower($energy_score) == 'inact') || (strtolower($energy_score) == 'nopred')) {
                                            // I think I should still watch this since the rank's need to be unique
                                            $watch[$ligand][$rank] = $rank;
                                            $typefound = $ligand;
//                                          continue;
                                        } elseif (is_numeric($energy_score)) {
                                            $watch[$ligand][$rank] = $rank;
                                            $typefound = $ligand;
                                        } else {
                                            $this->error_string .= $file . " - Line: $row - energy/score must be numeric (" . $energy_score . ")\n";
                                        }                                       
                                        
                                    } else {
                                        $this->error_string .= $file . " - Line: $row - energy/score is missing\n";
                                    }
                                } else {
                                    $this->error_string .= $file . " - Line: $row - rank '" . $rank . "' must be a positive integer\n";
                                }
                            } else {
                                $this->error_string .= $file . " - Line: $row - rank is missing\n";
                            }
                        } else {
                            if (in_array($ligand, array_keys($watch)))
                                $this->error_string .= $file . " - Line: $row - duplicate " . strtolower($proteinligand) . " ($ligand)\n";
                            else    
                                $this->error_string .= $file . " - Line: $row - invalid ligand ($ligand)\n";
                        }
                    }
                }
            }
        }
        if (!($typefound)) {
            $this->error_string .= $file . " does not have a line that starts with 'Type:'\n";
        }
        
        if ($prot_diff = array_diff($valid_ligand_array, array_keys($watch))) {
            $this->error_string .= $file . " - All ligands must be included. Missing: " . addAndOrForGrammar("and", $prot_diff) . "\n";
        }   
//      var_dump($watch);
//      var_dump(array_diff($valid_crystal_structure_array, array_keys($watch)));
        // Do I need to check the ranks here?
        // I would think that all the integer ranks would come first, and 'inact/nopred' would come after
        // the protein needs to be unique, so $watch[$protein1] doesn't seem right.
        $consecutive = array();
        foreach ($watch as $proteinligand) {
            foreach ($proteinligand as $key=>$value) {
                $consecutive[] = $value;
            }   
        }
        if ($return_consecutive = checkIfRanksAreNotConsecutive($consecutive)) {
            $this->error_string .= $file . " - " . $return_consecutive . "\n";
        }   
    }
    public function validateGC2FreeEnergies($path, $file, $valid_ligand_array) {
        // if valid protein found, we need to start 'watching' it, thus $watch[$protein1][$rank]
        $watch = array();
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);
        foreach ($rows as $row) {
            if (sizeof($valid_ligand_array) < sizeof($watch)) {
                $this->error_string .= $file . " - The number of lines in the file exceeds the number of ligands\n";
                break;
            }
            if ((substr($row, 0, 1) == '#') || ($row == ''))
                continue;
            elseif (strpos($row, "Type") !== false)
                $this->error_string .= $file . " - 'Type' is not allowed, expecting ligand\n";
            else {
                $row_split = explode(",", $row);
                if ((sizeof($row_split) !== 3) && (trim($row_split[1]) !== 'nopred'))
                    $this->error_string .= $file . " - Line: $row - should have 3 values\n";
                else {  
                    $ligand = $row_split[0];
                    $binding = trim($row_split[1]);
                    $uncertainty = trim($row_split[2]);
                    if (in_array($ligand, array_diff($valid_ligand_array, array_keys($watch)))) {
                        // if it's in the array, go ahead and set the key since it was included. 
                        //will still give error if other data in line is bad
                        $watch[$ligand] = '';
                        /* 2017-01-17 - new code to allow for 'nopred' */
                        // if 'nopred' found in $binding or $uncertainly, allow it
                        if ((strtolower($binding) == 'nopred') || (strtolower($uncertainty) == 'nopred')){
                            $watch[$ligand] = $binding;
                            $typefound = $ligand;
                        } elseif (is_numeric($binding)) {
                            if (is_numeric($uncertainty)) {
                                $watch[$ligand] = $binding;
                                $typefound = $ligand;
                            } else 
                                $this->error_string .= $file . " - Line: $row - Uncertainty must be numeric (" . $uncertainty . ")\n";
                        } else
                                $this->error_string .= $file . " - Line: $row - Binding Free Energy must be numeric (" . $binding . ")\n";
                    } else {
                        if (in_array($ligand, array_keys($watch)))
                            $this->error_string .= $file . " - Line: $row - duplicate " . strtolower($proteinligand) . " ($ligand)\n";
                        else    
                            $this->error_string .= $file . " - Line: $row - invalid ligand ($ligand)\n";
                    }
                }
            }
        }
        if ($prot_diff = array_diff($valid_ligand_array, array_keys($watch))) {
            $this->error_string .= $file . " - All ligands must be included. Missing: " . addAndOrForGrammar("and", $prot_diff) . "\n";
        }
    }
/**** GC3 Validation ****/
/* let's try a new way to do this with GC3 */
    public function validateGC3($component, $file_array, $radio) {
        $this->listofligands = array();
        $this->listofproteinligands = array();
        $this->predictioncategory = $radio;
        $this->gzipCheck($file_array);
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarRadioFile($file_array["tmp_name"], $file_array["name"], $component, $this->foo->index);
        // decompress tmp file and get back path + directory name÷
        $folder_path = $this->decompressTarFile($destination, $file_array["name"]);
        $this->folder_path = $folder_path;
        $challenge = new Challenge();
        $challenge->getChallengeByStageID($component);
        $challenge_settings = json_decode($challenge->color1);
        if ($this->foo->pdb_ligand_map) {
            $this->foo->pdb_ligand_map_array = (array)$this->foo->pdb_ligand_map;
        }
        // build array of allowed files;
        foreach (array('csv', 'txt') as $ext) {
            $req = $ext . '_required';
            $req_array[$ext] = (array)$this->foo->{$req};
            $this->{$ext} = array();
        }
        /* loop thru files in $folder_path for basic filename check */
        $this->validateGC3BasicFilenameCheck($folder_path, $req_array);
        displayErrorString($this->error_string);
        // do more indepth checking ...
        $this->buildValidLigandArray(strtoupper($this->foo->abbr));
        // if need to do protein-ligand check (like ABL1 subchallenge)
        if ($this->foo->valid_protein_specific)
            $this->buildValidProteinLigandArray();
        displayErrorString($this->error_string);
        // check txt, csv, pdb, mol
        $this->validateGC3FindMissingFiles($this->files);
        // check pdb, mol
        // hmm, some things compare all files together, some check things individually ...
        if (isset($this->foo->pdbmol_required)) {
            $this->validateGC3PDBMolFilenames($this->files, $this->foo->maxpose);
//$this->validateLigandPDB($this->LigandPDB);
            // ABCD-CatS_1.pdb and WXYZ-CatS_1.pdb not allowed with same ligand
/*            
            if ($radio != 'pose')
                $this->validateLigandPDB($this->LigandPDB);
            
            if ($this->foo->maxpose)
                $this->validatePDBLigandGaps($this->PDB_Ligand);
*/
        }
        displayErrorString($this->error_string);
        foreach ($this->files->csv as $csv) {
            if (strtolower($csv) == 'ligandscores.csv') {
                if (isset($this->valid_proteinligand_array))
                    $this->validateGC3Scores($folder_path, $csv, $this->valid_proteinligand_array);
                else
                    $this->validateGC3Scores($folder_path, $csv, $this->valid_ligand_array);
            } elseif (strtolower($csv) == 'freeenergies.csv') {
                $this->validateGC3FreeEnergies($folder_path, $csv, $this->valid_ligand_array);
            }
        }  
        displayErrorString($this->error_string);
        // protocol files require parsing blocks of text, but each should be similar ...
        foreach ($this->files->txt as $txt) {
            if (strtolower($txt) == 'posepredictionprotocol.txt') {
                $checkfor = array('type'=>'PosePrediction', 'required'=>array('Name:', 'Software:', 'System Preparation Parameters:', 'System Preparation Method:', 'Pose Prediction Parameters:', 'Pose Prediction Method:'), 'requiredyn'=>array('Answer 1:', 'Answer 2:'));
                $this->extractProtocolText($folder_path, $txt, $checkfor);
            } elseif (strtolower($txt) == 'ligandscoringprotocol.txt') {
                $checkfor = array('type'=>'LigandScoring', 'required'=>array('Name:', 'Software:', 'Parameters:', 'Parameter:', 'Method:'), 'requiredyn'=>array('Answer 1:','Answer 2:'));
                $this->extractProtocolText($folder_path, $txt, $checkfor);
            } elseif (strtolower($txt) == 'freeenergyprotocol.txt') {
                $checkfor = array('type'=>'FreeEnergy', 'required'=>array('Name:', 'Software:', 'Protein Forcefield:', 'Ligand Forcefield:', 'Water Model:', 'Parameters:', 'Parameter:', 'Method:'), 'requiredyn'=>array('Answer 1:','Answer 2:'));
                $this->extractProtocolText($folder_path, $txt, $checkfor);
            } elseif (strtolower($txt) == 'userinfo.txt') {
                $this->validateUserInfoText($folder_path, $txt);
            }
        }  
        displayErrorString($this->error_string);
        // run validation, move to validated directory if ok
        $receipt_filename = $this->moveValidatedTarRadioFile($destination, $component, $file_array["name"], $this->foo->index);
        $rec_split = explode("-", $receipt_filename);
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = $rec_split[2];
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $vars['type'] = $radio;
        $vars['isAnonymous'] = $this->foo->anonymous;
        $this->insertSubmission($vars);
        $vars['challenge_submission_id'] = $this->id;
        switch ($radio) {
            case 'pose':
                $vars['filename'] = 'PosePredictionProtocol.txt';
                $vars['protocol_id'] = $vars['receipt_id'] . '-pp';
                $this->insertProtocol($vars);
                break;
            case 'scoreligand':
                $vars['filename'] = 'LigandScoringProtocol.txt';
                $vars['protocol_id'] = $vars['receipt_id'] . '-ls';
                $this->insertProtocol($vars);
                break;
            case 'scorestructure':
                $vars['filename'] = 'PosePredictionProtocol.txt';
                $vars['protocol_id'] = $vars['receipt_id'] . '-pp';
                $this->insertProtocol($vars);
                $vars['filename'] = 'LigandScoringProtocol.txt';
                $vars['protocol_id'] = $vars['receipt_id'] . '-ls';
                $this->insertProtocol($vars);
                break;
            case 'freeenergy1':
                $vars['filename'] = 'PosePredictionProtocol.txt';
                $vars['protocol_id'] = $vars['receipt_id'] . '-pp';
                $this->insertProtocol($vars);
                $vars['filename'] = 'FreeEnergyProtocol.txt';
                $vars['protocol_id'] = $vars['receipt_id'] . '-fe';
                $this->insertProtocol($vars);
                break;
        }
        
//        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
        rrmdir($this->darned_dir);
    }
    public function validateGC3BasicFilenameCheck($folder_path, $req_array) {
        /* start with a loop across directory to validate file names only, build list as we go */
        $dir = array_diff(scandir($folder_path), array('..', '.'));
        if (!(sizeof($dir)))
            $this->error_string .= "Submission folder cannot be empty\n";
        else {  
            foreach ($dir as $filename) {
                if (substr($filename, 0, 2) == '._')
                    continue;
                if (substr($filename, 0, 9) == '.DS_Store')
                    continue;
                if (strtolower($filename) == 'suppinfo')
                    continue;
            
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                if (($extension == 'csv') || ($extension == 'txt')) {
                    // sizeof = 0 means that extension is not , and a file is found, 
                    if (!(sizeof($req_array[$extension])))
                        $this->error_string .= $filename . " is not allowed\n";
                    elseif (!(in_array( $filename, $req_array[$extension] ))) {
                        $this->error_string .= $filename . " is not an allowed " . strtoupper($extension) . " file\n";
                    } 
                    // don't bother saving filenames if there already is an error
                    elseif (!(strlen($this->errorstring))) {
                        if (isset($this->files->{$extension}[$filename]))
                            $this->error_string .= "Duplicate file found ($filename)\n";
                        else
                            $this->files->{$extension}[] = $filename;
                    }
                    
                } elseif (($extension == 'pdb') || ($extension == 'mol')) {
                    if (!(isset($this->foo->pdbmol_required)))
                        $this->error_string .= strtoupper($extension) . " files are not allowed ($filename)\n";
                    else {
                        if (preg_match($this->foo->pdbmol_required, str_replace("." . $extension, "", $filename), $matches)) {
                            if (isset($this->files->{$extension}[$filename]))
                                $this->error_string .= "Duplicate file found ($filename)\n";
                            else {
                                $this->files->{$extension}[$filename] = $matches;
                            }
                        } else {
                            $this->error_string .= $filename . " has an invalid filename\n";
                        }
                    }
                }
                else
                    $this->error_string .= "Invalid filename or folder ($filename)\n";
            }
        }
    }
    public function validateGC3FindMissingFiles($files) {
        // check for missing files in the _required list
        foreach (array('txt', 'csv') as $extension) {
            $req = $extension . "_required";
            if (isset($this->foo->{$req})) {
                // if no files of that extension submitted, show error
                if (!($files->$extension))
                    $this->error_string .= "Missing " . strtoupper($extension) . " file(s): " . addAndOrForGrammar("and", $this->foo->$req) . "\n";
                // if extension submitted, compare against required list
                else {
                    $diff1 = array_diff($this->foo->{$req}, $files->$extension);
                    if (sizeof($diff1))
                        $this->error_string .= "Missing " . strtoupper($extension) . " file(s): " . addAndOrForGrammar("and", $diff1) . "\n";
                }
            }
        }
        if (isset($this->foo->pdbmol_required)) {
            // check and see if required PDB/MOL files are present
            if (!($files->pdb))
                $this->error_string .= "PDB files are required\n";
            if (!($files->mol))
                $this->error_string .= "MOL files are required\n";
            // check to see if each pdb has a mol and vice-versa
            if (!(strlen($this->error_string))) {
                $mol_keys = array_keys($this->files->mol);
                foreach ($this->files->pdb as $pdb) {
                    $newmol = $pdb[0] .= ".mol";
                    if (!(in_array($newmol, $mol_keys))) {
                        $this->error_string .= "Each PDB file must have a matching MOL file. ($newmol not found)\n";
                    }
                    
                }
                $pdb_keys = array_keys($this->files->pdb);
                foreach ($this->files->mol as $mol) {
                    $newpdb = $mol[0] .= ".pdb";
                    if (!(in_array($newpdb, $pdb_keys))) {
                    $this->error_string .= "Each MOL file must have a matching PDB file. ($newpdb not found)\n";
                    }
                    
                }
            }
        }
    }
    public function validateGC3PDBMolFilenames($files, $maxpose) {
        $min = 200;
        foreach ($files->pdb as $matches) {
            // validate PDBID
            $this->validatePDBID($matches[0] . '.pdb', $matches[1]);
            // if $this->valid_proteinligand_array is set, then check protein-ligand and not ligand
            if (isset($this->valid_proteinligand_array))
                $this->validateProteinLigandID($matches[2], $this->valid_proteinligand_array);
            else {
                // validate LigandID
                $this->validateLigandID($matches[2], $this->valid_ligand_array);
            }
            if (!$this->error_string ) {
                // GC3 Stage 1B has self docking, so must use pdbid ligandid pairing
                if ($this->foo->pdb_ligand_map_array) {
                    if (strtolower($this->foo->pdb_ligand_map_array[strtolower($matches[2])]) != strtolower($matches[1])) {
                        $this->error_string .= $matches[2] . " must use <i>" . $this->foo->pdb_ligand_map_array[strtolower($matches[2])] . "</i> for the PDBID. $matches[1] was found.\n";
                    }
                }
            }
            // validate PoseRank
            if ($maxpose) {
                $this->validatePoseRank($matches[0] . '.pdb', $matches[3], $maxpose);
            }
            $this->LigandPDB[$matches[2]][$matches[1]][$matches[3]] = 1;
            $this->PDB_Ligand[$matches[1] . '-' . $matches[2]][$matches[3]] = 1;
            $this->validateMOLRemarks($this->folder_path, $matches[0] . '.mol', $matches[3]);
            $this->validateLineCount($this->folder_path, $matches[0] . '.pdb', $min);
        }
/*
        // need to figure out how to show warning and allow upload to continue ...
        $ligand_diff = (array_diff($this->valid_ligand_array, $this->listofligands));
        if (count($ligand_diff)) {
            $this->warning_string .= 'Missing ligand(s): ' . addAndOrForGrammar('and', $ligand_diff) . "\n";
//            echo "Warning: " . $this->warning_string . "<br />\n";
        }
*/        
    }
    public function validateGC3Scores($path, $file, $valid_ligand_array) {
        $typefound = 0;
        // if valid protein found, we need to start 'watching' it, thus $watch[$protein1][$rank]
        // also need to check to make sure energy is ordered, so added $watchenergy 2017-09-21
        $watch = array();
        $watchenergy = array();
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);
        foreach ($rows as $row) {
            if (sizeof($valid_ligand_array) < sizeof($watch)) {
                $this->error_string .= $file . " - The number of lines in the file exceeds the number of ligands\n";
                break;
            }
            if ((substr($row, 0, 1) == '#') || ($row == ''))
                continue;
            else {
                if (!($typefound)) {
                    if (preg_match('/^Type:\s?(\w*)[\s,]*/i', trim($row), $matches)) {
                        if (strlen(str_replace($matches[0], '', trim($row)))) {
                            $this->error_string .= $file . " - Invalid text '" . trim(str_replace($matches[0], '', trim($row))) . "' found after 'energy' or 'score'\n";
                        } else {    
                            if (!((strtolower($matches[1]) == 'energy') || (strtolower($matches[1]) == 'score')))
                                $this->error_string .= $file . " - 'Type' value is invalid. Must be 'energy' or 'score'\n";
                        }
                        $typefound = 1; // even if there are errors, should still report as found to suppress the 'no type line' found error
                    }
                } else {
                    $row_split = explode(",", $row);
                    if (sizeof($row_split) !== 3)
                        $this->error_string .= $file . " - Line: $row - should have 3 values\n";
                    else {  
                        $ligand = strtoupper($row_split[0]);
                        $rank = trim($row_split[1]);
                        $energy_score = trim($row_split[2]);
                    
                        if (in_array($ligand, array_diff($valid_ligand_array, array_keys($watch)))) {
                            // if it's in the array, go ahead and set the key since it was included. 
                            //will still give error if other data in line is bad
                            $watch[$ligand] = '';
                            if (strlen($rank)) {
                                // 'inact' and 'nopred' are the only allowed non numeric values
                                // *** Note: not checking the last value if inact/nopred. that's how example file looks ...
                                if ( (strtolower($rank) == 'inact') || (strtolower($rank) == 'nopred')) {
                                    // I think I should still watch this since the rank's need to be unique
                                    $watch[$ligand][$rank] = $energy_score;
                                    $typefound = $ligand;
//                                  continue;
                                } elseif ((is_integer($rank + 0)) && ($rank > 0)) { // need the +0 since $rank is a string.
                                    if (strlen($energy_score)) {
                                        if ( (strtolower($energy_score) == 'inact') || (strtolower($energy_score) == 'nopred')) {
                                            // I think I should still watch this since the rank's need to be unique
                                            $watch[$ligand][$rank] = $energy_score;
                                            $typefound = $ligand;
//                                          continue;
                                        } elseif (is_numeric($energy_score)) {
                                            $watch[$ligand][$rank] = $energy_score;
                                            $typefound = $ligand;
                                        } else {
                                            $this->error_string .= $file . " - Line: $row - energy/score must be numeric (" . $energy_score . ")\n";
                                        }                                       
                                        
                                    } else {
                                        $this->error_string .= $file . " - Line: $row - energy/score is missing\n";
                                    }
                                } else {
                                    $this->error_string .= $file . " - Line: $row - rank '" . $rank . "' must be a positive integer\n";
                                }
                            } else {
                                $this->error_string .= $file . " - Line: $row - rank is missing\n";
                            }
                        } else {
                            if (in_array($ligand, array_keys($watch)))
                                $this->error_string .= $file . " - Line: $row - duplicate " . strtolower($proteinligand) . " ($ligand)\n";
                            else    
                                $this->error_string .= $file . " - Line: $row - invalid ligand ($ligand)\n";
                        }
                    }
                }
            }
        }
        if (!($typefound)) {
            $this->error_string .= $file . " does not have a line that starts with 'Type:'\n";
        } elseif ($prot_diff = array_diff($valid_ligand_array, array_keys($watch))) {
            $this->error_string .= $file . " - All ligands must be included. Missing: " . addAndOrForGrammar("and", $prot_diff) . "\n";
        }   
//      var_dump($watch);
//      var_dump(array_diff($valid_crystal_structure_array, array_keys($watch)));
        // Do I need to check the ranks here?
        // I would think that all the integer ranks would come first, and 'inact/nopred' would come after
        // the protein needs to be unique, so $watch[$protein1] doesn't seem right.
        $consecutive = array();
        $prediction = array();
        foreach ($watch as $proteinligand=>$rankenergy) {
            foreach ($rankenergy as $key=>$value) {
                if (($key == 'inact') || ($key == 'nopred'))
                    continue;
                else {
                    $consecutive[] = $key;
                    $prediction[] = array('ligand'=>$proteinligand, 'rank'=>$key, 'energy'=>($value + 0));
                }
            }   
        }
        // did not enforce any ordering, so could be sorted by ligand or rank. Let's check the ranks ...
        if ($return_consecutive = checkIfRanksAreNotConsecutive($consecutive)) {
            $this->error_string .= $file . " - " . $return_consecutive . "\n";
        }   
        // if here, then ranks were OK. Let's check that the energies are also ordered properly.
        if ($matches[1] == 'energy') {
            if ($return_prediction = checkIfEnergiesAreOrdered($prediction)) {
                $this->error_string .= $file . " - " . $return_prediction . "\n";
            }   
        }
    }
    public function validateGC3FreeEnergies($path, $file, $valid_ligand_array) {
        // if valid protein found, we need to start 'watching' it, thus $watch[$protein1][$rank]
        $watch = array();
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);
        foreach ($rows as $row) {
            if (sizeof($valid_ligand_array) < sizeof($watch)) {
                $this->error_string .= $file . " - The number of lines in the file exceeds the number of ligands\n";
                break;
            }
            if ((substr($row, 0, 1) == '#') || ($row == ''))
                continue;
            elseif (strpos($row, "Type") !== false)
                $this->error_string .= $file . " - 'Type' is not allowed, expecting ligand\n";
            else {
                $row_split = explode(",", $row);
                if ((sizeof($row_split) !== 3) && (trim($row_split[1]) !== 'nopred'))
                    $this->error_string .= $file . " - Line: $row - should have 3 values\n";
                else {  
                    $ligand = strtoupper($row_split[0]);
                    $binding = trim($row_split[1]);
                    $uncertainty = trim($row_split[2]);
                    if (in_array($ligand, array_diff($valid_ligand_array, array_keys($watch)))) {
                        // if it's in the array, go ahead and set the key since it was included. 
                        //will still give error if other data in line is bad
                        $watch[$ligand] = '';
                        /* 2017-01-17 - new code to allow for 'nopred' */
                        // if 'nopred' found in $binding or $uncertainly, allow it
                        if ((strtolower($binding) == 'nopred') || (strtolower($uncertainty) == 'nopred')){
                            $watch[$ligand] = $binding;
                            $typefound = $ligand;
                        } elseif (is_numeric($binding)) {
                            if (is_numeric($uncertainty)) {
                                $watch[$ligand] = $binding;
                                $typefound = $ligand;
                            } else 
                                $this->error_string .= $file . " - Line: $row - Uncertainty must be numeric (" . $uncertainty . ")\n";
                        } else
                                $this->error_string .= $file . " - Line: $row - Binding Free Energy must be numeric (" . $binding . ")\n";
                    } else {
                        if (in_array($ligand, array_keys($watch)))
                            $this->error_string .= $file . " - Line: $row - duplicate " . strtolower($proteinligand) . " ($ligand)\n";
                        else    
                            $this->error_string .= $file . " - Line: $row - invalid ligand ($ligand)\n";
                    }
                }
            }
        }
        if ($prot_diff = array_diff($valid_ligand_array, array_keys($watch))) {
            $this->error_string .= $file . " - All ligands must be included. Missing: " . addAndOrForGrammar("and", $prot_diff) . "\n";
        }
    }
    public function validatedkNetNursa2016($component, $file_array) {
        // check Excel file name, not much more can be validated ...
        $this->excelCheck($file_array);
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempExcelFile($file_array["tmp_name"], $file_array["name"], $component);
        // run validation, move to validated directory if ok
        $receipt_filename = $this->moveValidatedTarFile($destination, $component, $file_array["name"]);
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = array_shift(explode("-", $receipt_filename));
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $vars['type'] = 'Excel';
        $this->insertSubmission($vars);
        
        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
        rrmdir($this->darned_dir);
    }
    public function validateSAMPL6($component, $file_array, $radio) {

        // check to make sure TXT file has basic naming conventions
        // for SAMPL5, since not dealing with tar/gzip files, let's validate first on file name before moving the file to validated directory.
        $comp_data = new Component_Data;
        $comp_data->getByID($component);
        $checkagainst = explode(",", $comp_data->color);
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarFile($file_array["tmp_name"], $file_array["name"], $component);
        $chset = new Challenge;
        $chset->getChallengeSetByID($component);
        $stage_settings = json_decode($chset->icon);
        // check file name for TXT , CSV, other stuff
        $this->CSVTXTNameCheck($file_array["name"], $checkagainst, $this->foo->file_type);
        displayErrorString($this->error);
        // pp = physical properties; hg = host guest
/*    var_dump($destination);
    var_dump($component);
    var_dump($radio);
    var_dump($this->checkfound);
    die('{"stop ... next is checkcontents": 0}');
*/    
        $this->checkSAMPL6contents($destination, $component, $radio, $this->checkfound);
        displayErrorString($this->error);
        
        // move successfullyl validated file to 'validated' directory
        $receipt_filename = $this->moveValidatedTarFile($destination, $component, $file_array["name"]);
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = array_shift(explode("-", $receipt_filename));
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $vars['type'] = $radio;
        $vars['isAnonymous'] = $this->foo->anonymous;
        $this->insertSubmission($vars);
        
//        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
    }   
/**** GC4 Validation ****/
/* This will be different since protocol and tgz are submitted/validated separately */
    public function validateGC4Protocol($component, $file_array, $radio, $nickname) {
        $this->predictioncategory = $radio;
        switch ($radio) {
            case 'pose':
                $checkfor = array('type'=>'PosePrediction', 'required'=>array('Name:', 'Software:', 'System Preparation Parameters:', 'System Preparation Method:', 'Pose Prediction Parameters:', 'Pose Prediction Method:'), 'requiredyn'=>array('Answer 1:', 'Answer 2:'));
                $twoletter = "-pp";
                break;
                
            case 'scoreligand':
                $checkfor = array('type'=>'LigandScoring', 'required'=>array('Name:', 'Software:', 'Parameters:', 'Parameter:', 'Method:'), 'requiredyn'=>array('Answer 1:', 'Answer 2:'));
                $twoletter = "-ls";
                break;
                
            case 'freeenergy1':
                $checkfor = array('type'=>'FreeEnergy', 'required'=>array('Name:', 'Software:', 'Protein Forcefield:', 'Ligand Forcefield:', 'Water Model:', 'Parameters:', 'Parameter:', 'Method:'), 'requiredyn'=>array('Answer 1:', 'Answer 2:'));
                $twoletter = "-fe";
                break;
        }
        // if error with filename, show error
        displayErrorString($this->error);
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarRadioFile($file_array["tmp_name"], $file_array["name"], $component, $this->foo->index);
        $basename = basename($destination);        
        $this->extractProtocolText(str_replace("/" . $basename, "", $destination), $basename, $checkfor);
        displayErrorString($this->error);
        // run validation, move to validated directory if ok
        $receipt_filename = $this->moveValidatedTarRadioFile($destination, $component, $file_array["name"], $this->foo->index);
        $rec_split = explode("-", $receipt_filename);
        $vars['challenge_submission_id'] = null;
        $vars['filename'] = $receipt_filename;
        $vars['protocol_id'] = $rec_split[2] . $twoletter;
        $vars['component'] = $component;
        $vars['uid'] = $_SESSION['user']->id;
        $vars['add_date'] = time();
        $this->insertProtocol($vars);
    }
    public function validateGC4($component, $file_array, $radio) {
        $this->listofligands = array();
        $this->listofproteinligands = array();
        $this->predictioncategory = $radio;
        $this->gzipCheck($file_array);
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarRadioFile($file_array["tmp_name"], $file_array["name"], $component, $this->foo->index);
        // decompress tmp file and get back path + directory name÷

        // if upload size > 20M, use newer decompressTarFile2 function
        if ($file_array["size"] > 16*1024*1024)
            $folder_path = $this->decompressTarFile2($destination, $file_array["name"]);
        else
            $folder_path = $this->decompressTarFile($destination, $file_array["name"]);

        $this->folder_path = $folder_path;
        $challenge = new Challenge();
        $challenge->getChallengeByStageID($component);
        $challenge_settings = json_decode($challenge->color1);
        if ($this->foo->pdb_ligand_map) {
            $this->foo->pdb_ligand_map_array = (array)$this->foo->pdb_ligand_map;
        }
        // build array of allowed files;
        foreach (array('csv', 'txt') as $ext) {
            $req = $ext . '_required';
            $req_array[$ext] = (array)$this->foo->{$req};
            $this->{$ext} = array();
        }
        /* loop thru files in $folder_path for basic filename check */
        $this->validateGC3BasicFilenameCheck($folder_path, $req_array);
        displayErrorString($this->error_string);
        // do more indepth checking ...
        $this->buildValidLigandArray(strtoupper($this->foo->abbr));
        // if need to do protein-ligand check (like ABL1 subchallenge)
        if ($this->foo->valid_protein_specific)
            $this->buildValidProteinLigandArray();
        displayErrorString($this->error_string);
        // check txt, csv, pdb, mol
        $this->validateGC3FindMissingFiles($this->files);
        // check pdb, mol
        // hmm, some things compare all files together, some check things individually ...
        if (isset($this->foo->pdbmol_required)) {
            $this->validateGC3PDBMolFilenames($this->files, $this->foo->maxpose);
//$this->validateLigandPDB($this->LigandPDB);
            // ABCD-CatS_1.pdb and WXYZ-CatS_1.pdb not allowed with same ligand
/*            
            if ($radio != 'pose')
                $this->validateLigandPDB($this->LigandPDB);
            
            if ($this->foo->maxpose)
                $this->validatePDBLigandGaps($this->PDB_Ligand);
*/
        }
        displayErrorString($this->error_string);
        // protocol files require parsing blocks of text, but each should be similar ...
        if ($this->files->txt == 'userinfo.txt') {
            $this->validateUserInfoText($folder_path, $txt);
        }
        displayErrorString($this->error_string);
        
        foreach ($this->files->csv as $csv) {
            if (strtolower($csv) == 'ligandscores.csv') {
                if (isset($this->valid_proteinligand_array))
                    $this->validateGC3Scores($folder_path, $csv, $this->valid_proteinligand_array);
                else
                    $this->validateGC3Scores($folder_path, $csv, $this->valid_ligand_array);
            } elseif (strtolower($csv) == 'freeenergies.csv') {
                $this->validateGC3FreeEnergies($folder_path, $csv, $this->valid_ligand_array);
            }
        }  
        displayErrorString($this->error_string);
        // run validation, move to validated directory if ok
        $receipt_filename = $this->moveValidatedTarRadioFile($destination, $component, $file_array["name"], $this->foo->index);
        $rec_split = explode("-", $receipt_filename);
        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = $rec_split[2];
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $vars['type'] = $radio;
        $vars['isAnonymous'] = $this->foo->anonymous;
        $this->insertSubmission($vars);
        $vars['challenge_submission_id'] = $this->id;
        $vars['submission_id'] = $vars['challenge_submission_id'];
        // insert the protocol submission keys
        switch ($radio) {
            case 'pose':
                $vars['protocol_id'] = $_REQUEST['pp'];
                $this->insertProtocolSubmission($vars);
                break;
            case 'scoreligand':
                $vars['protocol_id'] = $_REQUEST['ls'];
                $this->insertProtocolSubmission($vars);
                break;
            case 'scorestructure':
                $vars['protocol_id'] = $_REQUEST['pp'];
                $this->insertProtocolSubmission($vars);
                $vars['protocol_id'] = $_REQUEST['ls'];
                $this->insertProtocolSubmission($vars);
                break;
            case 'freeenergy1':
                $vars['protocol_id'] = $_REQUEST['pp'];
                $this->insertProtocolSubmission($vars);
                $vars['protocol_id'] = $_REQUEST['fe'];
                $this->insertProtocolSubmission($vars);
                break;
            case 'freeenergy1-fe':
                $vars['protocol_id'] = $_REQUEST['pp'];
                $this->insertProtocolSubmission($vars);
                $vars['protocol_id'] = $_REQUEST['fe'];
                $this->insertProtocolSubmission($vars);
                break;
            case 'freeenergy1-sb':
                $vars['protocol_id'] = $_REQUEST['pp'];
                $this->insertProtocolSubmission($vars);
                $vars['protocol_id'] = $_REQUEST['ls'];
                $this->insertProtocolSubmission($vars);
                break;
            case 'freeenergy1-lb':
                $vars['protocol_id'] = $_REQUEST['ls'];
                $this->insertProtocolSubmission($vars);
                break;
        }
        
//        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
        rrmdir($this->darned_dir);
    }
    public function CSVTXTNameCheck($filename, $checkagainst, $filetype) {
        if (strtolower(substr($filename, -3)) !== $filetype)
            displayErrorString("File extension invalid. Only csv allowed.");

        $original = $filename;
        $pattern = "/\.txt|\.csv/i";
        $matches = preg_split($pattern, $filename);
        // check for .csv.csv or .txt.txt
        if (sizeof($matches) > 2) {
            $this->error .= $filename . " has a bad file extension\n";
            displayErrorString($this->error);
        } elseif (sizeof($matches) == 1) {
            $this->error .= $filename . " has a bad file extension. Expecting " . addAndOrForGrammar("or", array(".txt", ".csv")) . "\n";
            displayErrorString($this->error);
        } else 
            //$filename = substr($matches[0], 0 , -1);
            $filename = $matches[0];
            $file_split = explode("-", $filename);

// not sure why "-" validation was limited to "typeI". limitation removed for SAMPL6 logP
//            if (substr($checkagainst[0], 0, 5) == 'typeI') {
                if (sizeof($file_split) > 3) {
                    $error_string .= $original . " cannot have a '-' in the name part of the filename.\n";
                }
                if (strpos($file_split[1], '.') !== false) {
                    $error_string .= $original . " cannot have a '.' in the name part of the filename.\n";
                }
                displayErrorString($this->error);
//            }
        foreach ($checkagainst as $check) {
            $found = 0;
                  
// **** For now, just look for a single dash and $checkagainst. If integer needed, handle elsewhere 
/*
don't need this for SAMPL6
            if (sizeof($file_split) > 3) {
                $error_string .= $original . " has too many dashes ('-') in the filename\n";
                break;
            }
*/
            $pattern = "/^.*-(\d*)$/";
            if (!(preg_match($pattern, $filename))) {
                $error_string .= $original . " is missing integer value in filename.\n";
                break;
            }
            if (strtolower($file_split[0]) == strtolower($check)) {
                $found++;
            }

            if ($found > 1)
                $error_string .= $original . " cannot have " . addAndOrForGrammar("or", $checkagainst) . " more than once in filename";

            if (isset($error_string)) {
                displayErrorString($error_string);
                exit;
            }   

            if ($found) {
                $this->checkfound = $check;
                return true;
            }   
        }


        if (isset($error_string)) {
            displayErrorString($error_string);
            exit;
        }   
        $error_string .= $original . " does not have " . addAndOrForGrammar("or", $checkagainst) . " in the filename";
        displayErrorString($error_string);
        exit;
    }
        
        public function checkSAMPL6contents ($destination, $component, $radio, $checkagainst) {
        $txt_file = file_get_contents($destination);
        $rows = preg_split("/\\r\\n|\\r|\\n/", $txt_file);
//        $rows = explode("\n", $txt_file);
        $startscanning = 'begin';
        $atleastone = 0;
        $checkfor = array('Predictions:', 'Software:', 'Method:', 'Name:');
        // SAMPLing has "Cost"
        if (($checkagainst == 'absolute') || ($checkagainst == 'relative'))
            $checkfor[] = 'Cost:';
        elseif ($checkagainst == 'logP')
            $checkfor[] = 'Category:';

/*
$challenge = new Challenge();
        $challenge->getChallengeByStageID($component);
        $challenge_settings = json_decode($challenge->color1);
*/
// get stage specific info
        $chset = new Challenge;
        $chset->getChallengeSetByID($component);
        $stage_settings = json_decode($chset->icon);        
        $valid_guest_array = $stage_settings->predictioncategory->$radio->validation_details;
         // array of DC Guests
        // $valid_guest_array = $stage_settings->SAMPL6_validation; // array of DC Guests
        // pKa Excel/CSV fix #1 - remove commas and then check for equality
        if ($radio == 'physical-properties') {
            // logP doesn't care about the first line having Type ... probably because challenge is not concurrent with other types ...
            if ($checkagainst !== 'logP') {
                if (trim(str_replace("," , "", $rows[0])) != '# Submission ' . str_replace('type', 'Type ', $checkagainst))
                    $this->error .= "First line must be: '# Submission " . str_replace('type', 'Type ', $checkagainst) . "'\n";
            }
        }
        $rowcount = 0;
        $checkfor_original = $checkfor;

        for ($i=0; $i<sizeof($rows); $i++) {
            if (!(is_utf8($rows[$i]))) {
                $this->error_string .= "Line: '" . $rows[$i] . "' has a non utf-8 character.\n";
                displayErrorString($this->error_string);
                exit;
            }
        }

        // this method steps thru line by line and can advance to lines while in loop
        for ($i=0; $i<sizeof($rows); $i++) {
            // if checkfor found, keep reading in lines until a new checkfor is found ...
            $found = 0;
            if (sizeof($checkfor)) {
                for ($j=0; $j<sizeof($checkfor); $j++) {
                    $pos = stripos($rows[$i], $checkfor[$j]);
                    if (($pos !== false) && ($pos < 1)) {
                        $startscanning = $checkfor[$j];
                        array_splice($checkfor, $j, 1);
                        $found = 1;
                        break ;
                    }
                }
            }
            if (!($found)) {
                $store[$startscanning][] = trim($rows[$i]);
            }
        }
        // Show missing keyword error early in the process
        if (sizeof($checkfor)) {
            $this->error_string .= "File is missing the following keywords: " . addAndOrForGrammar("and", $checkfor) . ". Please make sure your file includes a " . addAndOrForGrammar("and", $checkfor) . " section.\n";
            displayErrorString($this->error_string);
            exit;
        }
        
        // loop thru the found checkfor values  
        foreach (array_keys($store) as $key) {
            // each section should only appear once ...
            foreach ($store[$key] as $key2) {
                if (trim($key2) == trim($key)) {
                    $this->error_string .= "Section '" . $key . "' can only be used once\n";
                    displayErrorString($this->error_string);
                    exit;
                }
            }
            $atleastone = 0;
            switch ($key) {
                case 'Predictions:':
                    if ($radio == "physical-properties")
                        $this->checkSAMPLPhyPropPredictions($checkagainst, $store[$key], $valid_guest_array);
                    elseif (($checkagainst == 'absolute') || ($checkagainst == 'relative'))
                        $this->checkSAMPLSAMPlingPredictions($checkagainst, $store[$key], $valid_guest_array);
                    else {
                        foreach ($store[$key] as $row) {
                            $row = trim($row);
                            $first_char = substr($row, 0, 1);
                            if (!( ($first_char == '#') || ($first_char == ''))) {
                                $row_split = explode(",", $row);
                                if ($checkagainst == 'DC') {
                                    $req_count = 4;
                                } else {
                                    $req_count = 7;
                                }
                                // must have 4 or 6 values
                                if (sizeof($row_split) != $req_count) {
                                    $this->error_string .= $row . " does not have the proper number of elements (" . $req_count . ")\n";
                                }
                                // first value must be a valid guest if HG, Compound if DC. $valid_guest_array has the validation data
                                if (!($this->checkSAMPLGuestCompound($row_split[0], $valid_guest_array, $checkagainst))) {
                                
                                    if ($checkagainst == 'DC')
                                        $this->error_string .= $row_split[0] . " is not a valid compound name.\n";
                                    else
                                        $this->error_string .= $row_split[0] . " is not a valid guest name.\n";
                                }
                                // check required values
                                if ($checkagainst == 'DC') {
                                    for ($i=1; $i<$req_count; $i++) {
                                        if (!(is_numeric(trim($row_split[$i]))))
                                            $this->error_string .= $row . " has an invalid numeric value (" . $row_split[$i] . ")\n";
                                    }
                                } else {
                                
                                    if (($row_split[1] == '') || (!(is_numeric(trim($row_split[1]))))) {
                                        $this->error_string .= $row . " - first value must be a number.\n";
                                    }
                                    if ((strlen(trim($row_split[5])) || (strlen(trim($row_split[6])) ))  && (strlen(trim($row_split[4])) == 0))
                                        $this->error_string .= $row . " - 5th and 6th value cannot be present since 4th value does not exists.\n";
                                        
                                }
                                
                                $atleastone++;
                            }
                        } // foreach
                        
                        if ($atleastone == 0)
                            $this->error_string .= "There must be at least one valid 'Predictions' value<br />\n";
                    }
                    break;
                case 'Name':
                    foreach ($store[$key] as $row) {
                        $row = trim($row);
                        $first_char = substr($row, 0, 1);
                        if (!( ($first_char == '#') || ($first_char == ''))) {
                            $atleastone++;
                        }
                    }
                    if ($atleastone == 0)
                        $this->error_string . "There must be a 'Name' value<br />\n";
                    elseif ($atleastone > 1)
                        $this->error_string .= "There can only be one 'Name' value<br />\n";
                    break;
                case 'Software:':                                   
                    foreach ($store[$key] as $row) {
                    $row = trim($row);
                        $first_char = substr($row, 0, 1);
                        if (!( ($first_char == '#') || ($first_char == ''))) {
                            $atleastone++;
                        }
                    }
                    if ($atleastone == 0)
                        $this->error_string .= "There must be a 'Software' value<br />\n";
                    break;
                    
                case 'Method:':     
                    foreach ($store[$key] as $row) {
                        $row = trim($row);
                        $first_char = substr($row, 0, 1);
                        if (!( ($first_char == '#') || ($first_char == ''))) {
                            $method[] = $row;
                        }
                    }
                    break;
                // SAMPLing added "Cost"
                case 'Cost:':
                    $this->checkSAMPLCost($store[$key], $valid_guest_array, $checkagainst);
                    if (sizeof($this->outliers->cost) && sizeof($this->outliers->prediction))
                        if (array_diff($this->outliers->cost, $this->outliers->prediction) || array_diff($this->outliers->prediction, $this->outliers->cost))
                            $this->error_string .= "Extra data in cost does not match extra data in predictions\n";
                    displayErrorString($this->error_string);
                    break;

                case 'Category:':
                    foreach ($store[$key] as $row) {
                        $row = trim($row);
                        $first_char = substr($row, 0, 1);
                        if (!( ($first_char == '#') || ($first_char == ''))) {
                            $atleastone++;
                            
                            if (!(in_array($row, array("Physical", "Empirical", "Mixed", "Other")))) {
                                $this->error_string .= 'Category value must be: "Physical", "Empirical", "Mixed", "Other"' . "<br />\n";
                                displayErrorString($this->error_string);
                                break;
                            }

                        }
                    }

                    if ($atleastone == 0)
                        $this->error_string .= "No 'Category' value found.<br />\n";
                    elseif ($atleastone > 1)
                        $this->error_string .= "There can only be one 'Category' value<br />\n";
                    break;
                
            } // switch
        } // foreach
        
        $meth_count = 0;
        $meth_lines = 0;
        foreach ($method as $method_line) {
            if (substr($method_line, 0, 1) !== '#')
                $meth_lines++;

            $meth_count += sizeof(explode(" ", $method_line));
        }

        if ($checkagainst == "logP") {
            if ($meth_lines < 1)
                $this->error_string .= "Method should be at one line.\n";
        } elseif ($meth_count < 50)
            $this->error_string .= "Method should be at least 50 words in length.\n";

        displayErrorString($this->error_string);
        return;
    }
    public function checkSAMPLCost($cost, $valid_guest_array, $checkagainst) {
        $found = array();
        $this->outliers->cost = array();
        foreach ($cost as $line) {
            if (substr($line, 0, 1) == '#')
                continue;
            $line_split = explode(',', $line);
            if ($line_split[1] < 0)
                $this->error_string .= $line_split[0] . " must have a positive 'total number of energy evaluations' \n";
            if (!(is_int($line_split[1] + 0)))
                $this->error_string .= $line_split[0] . " must have an integer value for 'total number of energy evaluations' (" . $line_split[1] . ")\n";
            if ($line_split[2] < 0)
                $this->error_string .= $line_split[0] . " must have a positive numeric value for 'total CPU time (in seconds)' \n";
            if (strlen(trim($line_split[3]))) {
                if ($line_split[3] < 0)
                    $this->error_string .= $line_split[0] . " must have a positive numeric value for 'total CPU time (in seconds)' \n";
            }
            if (!(in_array($line_split[0], $valid_guest_array->$checkagainst->valid_compounds))) {
                // outliers ok but must have the minimum required. so, track outliers!
                $this->outliers->cost[] = $line_split[0];
            } elseif (in_array($line_split[0], $found))
                $this->error .= $line_split[0] . " has already been used.\n";
            else
                $found[] = $line_split[0];
        }
        if (sizeof($found) == 0)
            $this->error_string .= "No valid Cost lines found.<br />\n";
        elseif (array_diff($valid_guest_array->$checkagainst->valid_compounds, $found))
            $this->error_string .= "Cost is missing for " . addAndOrForGrammar("and", array_diff($valid_guest_array->$checkagainst->valid_compounds, $found)) . "\n";
    }
    public function checkSAMPLSAMPlingPredictions($checkagainst, $predictions, $valid_guest_array) {
        $found = array();
        $this->outliers->prediction = array();
        $cnt = 0;
        foreach ($predictions as $row) {
            $row = trim($row);
            if ((substr($row, 0, 1) == '#') || (substr($row, 0, 1) == ''))
                continue;
            $row_split = str_getcsv($row);
            $cnt++;
            // check the column count
            if ($valid_guest_array->$checkagainst->column_exact == sizeof($row_split)) {
                for ($i=1; $i<$valid_guest_array->$checkagainst->column_exact; $i++) {
                    // odd row_split value is required; even is optional. check if exists
                    if ($i % 2)
                        if (strlen(trim($row_split[$i])))
                            $this->checkSAMPL2digitfloat($row_split[$i], $cnt);
                    else
                        $this->checkSAMPL2digitfloat($row_split[$i], $cnt);        
                }
            }
            else
                $this->error .= 'Row ' . $cnt . " has a bad column count. (" . sizeof($row_split) . ")\n";
            // check to see if compound OK                    
            if (!(in_array($row_split[0], $valid_guest_array->$checkagainst->valid_compounds))) {
                // outliers ok but must have the minimum required. so, track outliers!
                $this->outliers->prediction[] = $row_split[0];
            } elseif (in_array($row_split[0], $found))
                $this->error .= $row_split[0] . " has already been used.\n";
            else
                $found[] = $row_split[0];
        }
        if (!isset($found))
            $this->error .= "Must have at least one valid prediction\n";
        
        elseif (array_diff($valid_guest_array->$checkagainst->valid_compounds, $found))
            $this->error_string .= "Prediction is missing for " . addAndOrForGrammar("and", array_diff($valid_guest_array->$checkagainst->valid_compounds, $found)) . "\n";
    }
    public function checkSAMPLPhyPropPredictions($type, $predictions, $valid_guest_array) {
    
/*
    var_dump($type);
    var_dump($predictions);
    var_dump($valid_guest_array);
*/    
//    die('no more!');
        $simpler_array = $valid_guest_array->$type->valid_molecules->SM;
        $temp_array = $simpler_array;

        switch (strtolower($type)) {
            case "typei":
                $cnt = 0;
                foreach ($predictions as $row) {
                    $row = trim($row);
                    // pKa Excel/CSV fix #2a - skip line if starts with "#
                    if ((substr($row, 0, 1) == '#') || (substr($row, 0, 1) == '') || (substr($row, 0, 2) == '"#'))
                        continue;
                    // pKa Excel/CSV fix #3a - skip line if all commas
                    if (str_replace(",", "", $row) == '')
                        continue;
                    $row_split = str_getcsv($row);
                    $cnt++;
                    // check the column count
                    if (isset($valid_guest_array->$type->column_max))
                        if (sizeof($row_split) > (int) $valid_guest_array->$type->column_max)
                            $this->error .= 'Row ' . $cnt . " has too many columns\n";
                    elseif (isset($valid_guest_array->$type->column_exact))
                        if (sizeof($row_split) !== (int) $valid_guest_array->$type->column_exact)
                            $this->error .= 'Row ' . $cnt . ' can only have ' . $valid_guest_array->$type->column_exact . " columns\n";
                    elseif (sizeof($row_split) < 2)
                        $this->error .= 'Row ' . $cnt . " must have at least 2 columns\n";
                    if (!preg_match($valid_guest_array->$type->sm_pattern, $row_split[0], $matches))
                        $this->error .= 'Row ' . $cnt . " has a bad column 1.\n";
                    // check for valid molecule
                    if ($matches[1] > (int) $valid_guest_array->$type->molecule_max)
                        $this->error .= 'Row ' . $cnt . " column 1 has a bad molecule id.\n";
                    if (!preg_match($valid_guest_array->$type->sm_pattern, $row_split[1], $matches))
                        $this->error .= 'Row ' . $cnt . " has a bad column 2.\n";
                    if ($matches[1] > (int) $valid_guest_array->$type->molecule_max)
                        $this->error .= 'Row ' . $cnt . " column 2 has a bad molecule id.\n";
                    // check for pair existence
                    if (in_array($row_split[0] . ', ' . $row_split[1], $type1_found))
                        $this->error .= $row_split[0] . ', ' . $row_split[1] . " combination already used\n";
                    else {
                        // insert pair in order and reverse
                        $type1_found[] = $row_split[0] . ', ' . $row_split[1];
                        $type1_found[] = $row_split[1] . ', ' . $row_split[0];
                    }
                    // check for valid numbers
                    if ($row_split[2]) {
                        if (stripos($row_split[2], "e"))
                            $this->error .= 'Row ' . $cnt . " - " . $row_split[2] . " has incorrect number format.  It must be a float with 2 decimals.\n";
                        else
                            $this->checkSAMPL2digitfloat($row_split[2], $cnt);
                    }
                    if ($row_split[3]) {
                        if (stripos($row_split[3], "e"))
                            $this->error .= 'Row ' . $cnt . " - " . $row_split[3] . " has incorrect number format.  It must be a float with 2 decimals.\n";
                        else
                            $this->checkSAMPL2digitfloat($row_split[3], $cnt);
                    }
                }
                if (!isset($type1_found))
                    $this->error .= "Must have at least one valid prediction\n";
                break;
            case "typeii":
                $cnt = 0;
                foreach ($predictions as $row) {
                    $row = trim($row);
                    // pKa Excel/CSV fix #2b - skip line if starts with "#
                    if ((substr($row, 0, 1) == '#') || (substr($row, 0, 1) == '') || (substr($row, 0, 2) == '"#'))
                        continue;
                    // pKa Excel/CSV fix #3b - skip line if all commas
                    if (str_replace(",", "", $row) == '')
                        continue;
                    $row_split = str_getcsv($row);
                    $cnt++;
                    // check the column count
                    if (isset($valid_guest_array->$type->column_exact))
                        if (sizeof($row_split) !== (int) $valid_guest_array->$type->column_exact)
                            $this->error .= 'Row ' . $cnt . " must have 102 columns\n";
                    if (!preg_match($valid_guest_array->$type->sm_pattern, $row_split[0], $matches))
                        $this->error .= 'Row ' . $cnt . " has an invalid microstate ID.\n";
                    // check for valid molecule
                    if ($matches[1] > (int) $valid_guest_array->$type->molecule_max)
                        $this->error .= 'Row ' . $cnt . " column 1 has a bad molecule id.\n";
                    // check for pair existence
                    if (in_array($row_split[0], $type2_found))
                        $this->error .= $row_split[0] . " already used\n";
                    else {
                        // insert pair in order and reverse
                        $type2_found[] = $row_split[0];
                    }
                    // check for valid numbers or 'infinity'
                    for ($i=1; $i<102; $i++) {
                        $valid_row = 1;
                        $row_split[$i] = trim($row_split[$i]);
                        // check for '-infinity' or ''
                        if (($row_split[$i] == '-infinity') || ($row_split[$i] == ''))
                            $valid_row = 1;
                        else {
                            if (!(stripos($row_split[$i], "e"))) {
                                $this->error .= 'Row ' . $cnt . ": " . $row_split[$i] . " must be in scientific notation. Alternatively, if predicted value is ln(0) or -inf, it should be reported using `-infinity` string.\n";
                                $valid_row = 0;
                            }
                            // check for 2 decimals in scientific notation
                            elseif (sprintf('%1.2E', $row_split[$i]) == $row_split[$i]) {
                                if (preg_match("/\.\d\d/", $row_split[$i], $matches))
                                    $valid_row = 1;
                                else {
                                    $this->error .= 'Row ' . $cnt . ": " . $row_split[$i] . " must have precision of 3\n";
                                    $valid_row = 0;
                                } 
                            } else {
                                $this->error .= 'Row ' . $cnt . ": " . $row_split[$i] . " must have precision of 3\n";
                                $valid_row = 0;
                            }
                        }
                        if (!$valid_row)
                            $this->error .= 'Row ' . $cnt . ": bad column $i\n";
                    }
                }
                if (!isset($type2_found))
                    $this->error .= "Must have at least one valid prediction\n";
                break;
                
            case "typeiii":
                $cnt = 0;
                foreach ($predictions as $row) {
                    $row = trim($row);
                    // pKa Excel/CSV fix #2c - skip line if starts with "#
                    if ((substr($row, 0, 1) == '#') || (substr($row, 0, 1) == '') || (substr($row, 0, 2) == '"#'))
                        continue;
                    // pKa Excel/CSV fix #3c - skip line if all commas
                    if (str_replace(",", "", $row) == '')
                        continue;
                    $row_split = str_getcsv($row);
                    $cnt++;
                    // check the column count
                    if (isset($valid_guest_array->$type->column_max))
                        if (sizeof($row_split) > (int) $valid_guest_array->$type->column_max) {
                            $this->error .= 'Row ' . $cnt . " can only have " . $valid_guest_array->$type->column_max . " columns maximum\n";
                            continue;
                        }
                    if (!preg_match($valid_guest_array->$type->sm_pattern, $row_split[0], $matches)) {
                        $this->error .= 'Row ' . $cnt . " has an invalid molecule ID.\n";
                        continue;
                    }
                    // check for valid molecule
                    if ($matches[1] > (int) $valid_guest_array->$type->molecule_max) {
                        $this->error .= 'Row ' . $cnt . " column 1 has a bad molecule id.\n";
                        continue;
                    }
                    // add to $type3_found so later can check for molecule existence
                    $type3_found[] = $row_split[0];
                    // check for valid numbers
                    if ($row_split[1])
                        $this->checkSAMPL2digitfloat($row_split[1], $cnt);
                    if ($row_split[2])
                        $this->checkSAMPL2digitfloat($row_split[2], $cnt);
                    if (!(($row_split[2] >= 0) && ($row_split[2] <= 14)))
                        $this->error .= $row_split[2] . " must be between 0 and 14\n";
                    if (!(($row_split[3] >= 0) && ($row_split[3] <= 14)))
                        $this->error .= $row_split[3] . " must be between 0 and 14\n";
                }
                if (!isset($type3_found))
                    $this->error .= "Must have at least one valid prediction\n";
                break;

            case "logp":
                $cnt = 0;
                $found_array = array();

                foreach ($predictions as $row) {
                    $row = trim($row);
                    // pKa Excel/CSV fix #2c - skip line if starts with "#
                    if ((substr($row, 0, 1) == '#') || (substr($row, 0, 1) == '') || (substr($row, 0, 2) == '"#'))
                        continue;
                    // pKa Excel/CSV fix #3c - skip line if all commas
                    if (str_replace(",", "", $row) == '')
                        continue;
                    $row_split = str_getcsv($row);
                    $cnt++;
                    // check the column count
                    if (isset($valid_guest_array->$type->column_max))
                        if (sizeof($row_split) > (int) $valid_guest_array->$type->column_max) {
                            $this->error .= 'Row ' . $cnt . " can only have " . $valid_guest_array->$type->column_max . " columns maximum\n";
                            continue;
                        }

                    // check to see if valid molecule
                    if (!(in_array($row_split[0], $simpler_array))) {
                        $this->error .= 'Row ' . $cnt . " has an invalid molecule ID.\n";
                    } else {
                       if (($key = array_search($row_split[0], $temp_array)) !== false)
                            unset($temp_array[$key]);
                    }

                    if ((trim($row_split[0]) == '') || (trim($row_split[1]) == '') || (trim($row_split[2]) == '') || (trim($row_split[3]) == '')) {
                        $this->error .= 'Row ' . $cnt . " of Predictions section has an invalid value. No field can be empty.\n";
                        continue;
                    }

                    if (in_array($row_split[0], $found_array))
                        $this->error .= 'Row ' . $cnt . " has a duplicate molecule ID: " . $row_split[0] . ".\n";
                    else
                        $found_array[] = $row_split[0];

                    // add to $logP_found_found so later can check for molecule existence
                    $logP_found = $row_split[0];
                    // check for valid numbers
                    if ($row_split[1])
                        $this->checkSAMPL2digitfloat($row_split[1], $cnt);
                    if (!(is_numeric($row_split[2])))
                        $this->error .= "'" . $row_split[2] . "' must be numeric\n";
                    if (!(is_numeric($row_split[3])))
                        $this->error .= "'" . $row_split[3] . "' must be numeric\n";

                }

                if (!isset($logP_found))
                    $this->error .= "Must have at least one valid prediction\n";

                if (sizeof($temp_array))
                    $this->error .= "You must include all molecules. Missing: " . implode(", ", $temp_array) . "\n";

                break;
                            
        }
        displayErrorString($this->error);
    }
    public function checkSAMPLGuestCompound($guest, $valid_guest_array, $dchg) {
// $guest = "CB8-G0";
// $dchg = 'cb8';
//  [CB8] => Array ( [0] => CB8-G0 
//                    [1] => CB8-G1
//print_r($valid_guest_array);
//echo $dchg . " is hostguest<br />\n";
        foreach ($valid_guest_array as $key=>$value) {
        /*
        the first key is CB8
the first key is OAMe
the first key is CBClip
the first key is HGStandard
*/
            if ($dchg !== 'DC')  {
                if (strtolower($key) !== strtolower($dchg)) {
                    continue;
                }
            }   
            foreach ($value as $batch) {
                if (strtolower($guest) == strtolower($batch)) {
                    $found = true;
                    $this->guestcompound[$key][] = $guest;
                    break 2;
                }
            }
        }
        if (!(isset($found)))
            return false;
        else
            return true;
    }
    public function checkSAMPL2digitfloat($x, $rownum) {
        $original = $x;
        if (!is_float($x + 0))
            $this->error .= "Row " . $rownum . " - " . $x . " must be a floating number\n";
        // if in scientific notation convert to float before doing decimal check ...
        if (stripos($x, "e"))
            $x = (float) $x;
        // check for 2 decimals
        if (!preg_match("/\.\d\d$/", $x))
            $this->error .= "Row " . $rownum . " - " . $original . " must have 2 decimals\n";
             
    }
}
function getDashElement($string, $position) {
    $tmp = explode("-", $string);
    return $tmp[$position];
}
function displayErrorString($error_string) {
    if (strlen($error_string)) {
        echo "<strong><span style='font-size: 1.2em; color:red'>File Submission Failed</span></strong><br />";
        echo str_replace("\n", "<br />\n", $error_string);
        exit;
    }
}
function is_utf8($str) {
    $subst = '';
    $re = "/[^°ÅâàäöôıîïçşğüùûéèêëôαßΔδελμ]/";
    $restul= preg_replace($re, $subst, $str);
    if (strlen($restul)) {
        return 0;   
    }

    return 1;
//    return (bool) mb_check_encoding($str, 'UTF-8');
}