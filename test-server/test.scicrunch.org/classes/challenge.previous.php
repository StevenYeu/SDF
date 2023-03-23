<?php

class Challenge_Previous extends Connection {

    public function validateGC2015($component, $file_array) {
        $this->gzipCheck($file_array);

        // check to make sure tar file has basic naming conventions
        $comp_data = new Component_Data;
        $comp_data->getByID($component);
//      $checkagainst = array('Dock', 'Score', 'Free_Energy');
        $checkagainst = explode(",", $comp_data->color);

        if ($this->error_string = $this->tarfileNameCheck($file_array["name"], $checkagainst)) {
            echo str_replace("\n", "<br />\n", $this->error_string);
            exit;
        }
        
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarFile($file_array["tmp_name"], $file_array["name"], $component);
        
        // decompress tmp file and get back path + directory name÷
        $folder_path = $this->decompressTarFile($destination, $file_array["name"]);

        // start validation of tar file based on file "type"
        foreach ($checkagainst as $check) {
            if (preg_match('/' . $check . '/i', $folder_path)) {
                $methodname = 'validateGC2015_' . $check;
                $this->$methodname($folder_path, $component);
                $vars['type'] = $check;

                if (isset($this->error_string)) {
                    echo str_replace("\n", "<br />\n", $this->error_string);
                    
                    // remove working directory
                    rrmdir($folder_path);
                    exit;
                }
            }
        }
        
        // move successfullyl validated file to 'validated' directory
        $receipt_filename = $this->moveValidatedTarFile($destination, $component, $file_array["name"]);

        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = array_shift(explode("-", $receipt_filename));
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $this->insertSubmission($vars);
        
        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
        rrmdir($this->darned_dir);
    }   

    public function validateGC2015_CheckPDBCode($filename, $pdb_code, $pose) {
        // since array is already sorted via scandir, this will give max pose value
        // and it will also help find if more than one pdb use the ligand
        $dock_ligand[$ligand][$dock_code] = $pose;
    
        if (strlen($pdb_code) != 4) {
            $this->error_string .= $filename . " filename must start with a 4 character PDB code\n";
        } elseif (preg_match('/[^a-z0-9]/i', $dock_code)) {
            $this->error_string .= $filename . " filename must start with an alphanumeric PDB code\n";
        }

        $this->dockLigand = array_merge($this->dockLigand, $dock_ligand);
    }
    
    public function validateGC2015_CheckLigand($filename, $component, $ligand, $pose) {
        $json_decoded = json_decode($this->getIcon($component));
        $valid_pdb_array = $json_decoded->PDB_validation; // array of PDB ligands. can only use each once.
        if (in_array(strtolower($ligand), $valid_pdb_array)) {
            // if there is anything after the "pose", then invalidate since that affects the count
            if (($pose < 1) || ($pose > 5)) {
                $this->error_string .= $filename . " has an invalid pose value ($pose)\n";
            } else
                $this->validation_counter->ligand->$ligand->$pose['count']++;

        } else {
            $this->error_string .= $filename . " does not have a valid ligand ($ligand)\n";
        }
    }
    
    public function validateGC2015_CheckLigandOveruse($dock_ligand) {
        // check for ligand over use, ie with more than one pdb code
        foreach (array_keys($dock_ligand) as $ligand) {
            if (sizeof(array_keys($dock_ligand[$ligand], true)) > 1)
                $this->error_string .= $ligand . " cannot be used with more than one PDB code\n";

            // these $remark items need to be scanned for REMARK  energy  #neg value
            foreach ($dock_ligand[$ligand] as $dock=>$pose) {
                if ($pose >= 2)
                    $remark[$dock . "-" . $ligand] = $pose;
            }               
        }
        
        $this->ligandRemarks = $remark;
    }
    
    public function validateGC2015_CheckLineCount ($path, $dockdir, $filename, $min) {
        $txt_file = file_get_contents($path . "/" . $dockdir . "/" . $file);
        $rows = explode("\n", $txt_file);

        if (sizeof($rows) < $min) {
            $this->error_string .= $filename . "line count is too low (" . sizeof($rows) . ")\n";
        }
    }
    
    public function validateGC2015_CheckLineCountandRemarks($path, $dockdir, $min) {
        $dock_ligand_keys = array_keys($this->ligandRemarks);
        $files = array_diff(scandir($path), array('..', '.'));

        foreach ($files as $file) {
            if (substr($file, 0, 2) == '._')
                continue;

            if (substr($file, 0, 9) == '.DS_Store')
                continue;

            if (substr($file, -3) == 'pdb') {
                if (!(filesize($path . "/" . $file))) {
                    $this->error_string .= $file . " - is empty\n";
                    continue;
                }

                $txt_file = file_get_contents($path . "/" . $file);
                $rows = explode("\n", $txt_file);

                if (sizeof($rows) < $min) {
                    $this->error_string .= $file . " - line count is too low (" . sizeof($rows) . ")\n";
                }

                foreach ($dock_ligand_keys as $key) {
                    if (strpos($file, $key) !== false) {
                        foreach ($rows as $row) {
                            if (substr(strtoupper($row), 0, 6) == 'REMARK') {
                                $parts = preg_split('/\s+/', $row);
                                if (((strtolower($parts[1]) == 'energy') || (strtolower($parts[1]) == 'score')) && (isset($parts[2]))) {
                //                  $this->validate_pdb_remarks->$file = 1;
                                    $this->validate_pdb_remarks->$file->energy = $parts[2];
                                }
                            }
                        }   
                        if (!(isset($this->validate_pdb_remarks->$file))) {
                            $this->error_string .= $file . " is missing the energy/score value in the REMARKS\n";
                            $this->validate_pdb_remarks->$file = 0;
                        }   
                    }
                }   
            }
        }
    }
        
    public function validateGC2015_CheckLigand2 () {
        $casted = json_decode(json_encode($this->validation_counter->ligand), true);

        foreach (array_keys($casted) as $key) {
            $the_keys = array_keys($casted[$key]);
            asort($the_keys);

            if ($the_keys[0] != 1) {
                $this->error_string .= $key . " must have a pose value that starts with 1\n";
            }
    
            $j = sizeof($the_keys) - 1;
            while ($j > 0) {
                if (($the_keys[$j] - $the_keys[$j-1]) > 1)
                    $this->error_string .= $key . " has a gap in pose numbering\n";

                $j--;           
            }       
        }
    }

    public function validateGC2015_CheckLigandScores($filename) {
        $base = str_ireplace(".csv", "", $filename);
    
        if (!(preg_match("/LigandScores\-(\d*)/i", $filename, $matches)))
            $this->error_string .= $filename . " - invalid LigandScore filename\n";
        else 
            $this->LigandScores[] = $matches[1];
        
    }

    public function validateGC2015_CheckLigandScoringProtocol($filename) {
        $base = str_ireplace(".txt", "", $filename);

        if (!(preg_match("/LigandScoringProtocol\-(\d*)/i", $filename, $matches)))
            $this->error_string .= $filename . " - invalid LigandScoringProtocol filename\n";
        else
            $this->LigandScoringProtocol[] = $matches[1];

    }

    public function validateGC2015_CheckLigandScoreIndex() {
        foreach ($this->LigandScores as $index) {
            if (!(in_array($index, $this->LigandScoringProtocol)))
                $this->error_string .= "LigandScore-" . $index . ".csv does not have a matching LigandScoringProtocol file\n";
        }       

        foreach ($this->LigandScoringProtocol as $index) {
            if (!(in_array($index, $this->LigandScores)))
                $this->error_string .= "LigandScoringProtocol-" . $index . ".csv does not have a matching LigandScores file\n";
        }       
    }
    
    public function validateGC2015_Dock ($folder_path, $component) {
        $this->dockLigand = array();
        $this->LigandScores = array();
        $this->LigandScoringProtocol = array();
        $ppp = 0; //posepredictionprotocol

        $dockdir = array_diff(scandir($folder_path), array('..', '.'));
        foreach ($dockdir as $filename) {
            if (substr($filename, 0, 2) == '._')
                continue;

            if (substr($file, 0, 9) == '.DS_Store')
                continue;
            
            if (substr(strtolower($filename), -4) == '.pdb') {
                $base = str_ireplace(".pdb", "", $filename);
                $dockname_split_array = explode("-", $base);
                $pdb_code = $dockname_split_array[0];
                $ligand = $dockname_split_array[1];
                $pose = $dockname_split_array[2];

                $this->validateGC2015_CheckPDBCode(strtolower($filename), $pdb_code, $pose);
                $this->validateGC2015_CheckLigand(strtolower($filename), $component, $ligand, $pose);
//              $this->validateGC2015_CheckLineCount($folder_path, $dockdir, $filename, 200) {

                if (isset($dockname_split_array[3])) {
                    $this->error_string .= $filename . " file name cannot have characters after pose value\n";
                }
            } elseif (strtolower($filename) == strtolower('PosePredictionProtocol.txt')) {
                $ppp++;
            } elseif (substr(strtolower($filename), -4) == '.csv') {
                $this->validateGC2015_CheckLigandScores(strtolower($filename));
            } elseif (substr(strtolower($filename), -4) == '.txt') {
                $this->validateGC2015_CheckLigandScoringProtocol(strtolower($filename));
            } else {
                $this->error_string .= $filename . " - unrecognized file type\n";
            }       
        }

        $this->validateGC2015_CheckLigandOveruse($this->dockLigand);
        $this->validateGC2015_CheckLineCountandRemarks($folder_path, $dockdir, 200);
//      $this->validateGC2015_CheckLigand2();
        $this->validateGC2015_CheckLigandScoreIndex();
        
        if (!($ppp))
            $this->error_string .= "No PosePredictionProtocol.txt file found\n";
    }

    public function validateGC2015_Score ($folder_path, $component) {
        $this->LigandScores = array();
        $this->LigandScoringProtocol = array();

        $scoredir = array_diff(scandir($folder_path), array('..', '.'));
        foreach ($scoredir as $filename) {
            if (substr($filename, 0, 2) == '._')
                continue;

            if (substr(strtolower($filename), -4) == '.csv') {
                $this->validateGC2015_CheckLigandScores(strtolower($filename));
            } elseif (substr(strtolower($filename), -4) == '.txt') {
                $this->validateGC2015_CheckLigandScoringProtocol(strtolower($filename));
            } else {
                $this->error_string .= $filename . " - unrecognized file type\n";
            }       
        }
    }   

    public function validateGC2015_CheckFreeEnergiesSet($filename) {
        $base = str_ireplace(".csv", "", $filename);

        if (!(preg_match("/FreeEnergiesSet(\d*)/i", $filename, $matches))) {
            $this->error_string .= $filename . " - invalid FreeEnergiesSet filename\n";
        } else {
            $this->FreeEnergySets[] = $matches[1];
        }
    }
    
    public function validateGC2015_Free_Energy ($folder_path, $component) {
        $this->FreeEnergySets = array();
        $fep = 0; //FreeEnergyProtocol

        $freedir = array_diff(scandir($folder_path), array('..', '.'));
        foreach ($freedir as $filename) {
            if (substr($filename, 0, 2) == '._')
                continue;
        
            if (strtolower($filename) == strtolower('FreeEnergyProtocol.txt')) {
                $fep++;
            } elseif (substr(strtolower($filename), -4) == '.csv') {
                $this->validateGC2015_CheckFreeEnergiesSet(strtolower($filename));
            } else {
                $this->error_string .= $filename . " - unrecognized file type\n";
            }       
        }

        if (!($fep))
            $this->error_string .= "No FreeEnergyProtocol.txt file found\n";
        /*
        if (sizeof($this->FreeEnergySets) !== array_pop(sort($this->FreeEnergySets))) {
            $this->error_string .= "Invalid numbering for FreeEnergySet files\n";
            $this->error_string .= sizeof($this->FreeEnergySets) . "\n";
            $this->error_string .= print_r(sort($this->FreeEnergySets), true) . "\n";
        }
        */  
    }

public function validatePL_2016_1($component, $file_array) {
        $this->gzipCheck($file_array);

        // check to make sure tar file has basic naming conventions
        $comp_data = new Component_Data;
        $comp_data->getByID($component);
        $checkagainst = explode(",", $comp_data->color);

        if ($this->error_string = $this->tarfileNameCheck($file_array["name"], $checkagainst)) {
            echo str_replace("\n", "<br />\n", $this->error_string);
            exit;
        }
        
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarFile($file_array["tmp_name"], $file_array["name"], $component);
        
        if ($results = $this->tarFileTZFCheck($destination)) {
            echo str_replace("\n", "<br />\n", $file_array["name"] . " - " . $results);
            exit;
        }   

        // decompress tmp file and get back path + directory name÷
        $folder_path = $this->decompressTarFile($destination, $file_array["name"]);

        // start validation of tar file based on file "type"
        foreach ($checkagainst as $check) {
            if (preg_match('/' . $check . '/i', $folder_path)) {
                $methodname = 'validatePL_2016_1_' . $check;
                $this->$methodname($folder_path, $component);
                $vars['type'] = $check;

                if (isset($this->error_string)) {
                    echo str_replace("\n", "<br />\n", $this->error_string);
                    
                    // remove working directory
                    rrmdir($folder_path);
                    exit;
                }
            }
        }
        
        // move successfullyl validated file to 'validated' directory
        $receipt_filename = $this->moveValidatedTarFile($destination, $component, $file_array["name"]);

        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = array_shift(explode("-", $receipt_filename));
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $this->insertSubmission($vars);
        
        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
        rrmdir($this->darned_dir);
    }   

    
                
            
    public function validatePL_2016_1_Dock ($folder_path, $component) {
        $this->dockLigand = array();

        $this->ProteinScores = array();
        $this->ProteinScoringProtocol = array();
        $ppp = 0; //posepredictionprotocol
        $pdb = 0; //pdb
        $mol = 0; //pdb
        $ui = 0; // UserInfo.txt

        $proteinscore = 0; //proteinscore
        $proteinscoringprotocol = 0; //proteinscoringprotocol

        $this->json_decoded = json_decode($this->getIcon($component));
        
        $valid_proteins = $this->json_decoded->dock_validation->proteins; // array of protein. 
        $valid_ligands = $this->json_decoded->dock_validation->ligands; // array of ligands. 
        $valid_proteinligand_array = build_crystal_proteinligand_array($valid_proteins, $valid_ligands);

        $dockdir = array_diff(scandir($folder_path), array('..', '.'));
        if (!(sizeof($dockdir)))
            $this->error_string .= "Submission folder cannot be empty\n";
        else {  
            foreach ($dockdir as $filename) {
                if (substr($filename, 0, 2) == '._')
                    continue;

                if (substr($file, 0, 9) == '.DS_Store')
                    continue;
            
                // PosePredictionProtocol is easier to locate find since it doesn't have a "-#" in the name
                if (strtolower($filename) == strtolower('PosePredictionProtocol.txt')) {
                    $ppp++;
                } elseif (strtolower($filename) == strtolower('UserInfo.txt')) {
                    if ($this->validateUserInfoText($folder_path, $filename)) {
                        $ui++;
                    }
                } elseif (substr(strtolower($filename), -4) == '.pdb') {
                    $base = str_ireplace(".pdb", "", $filename);
                    if ($this->validatePL_2016_1_CheckPDBMolFilename($filename, $base, $valid_proteinligand_array, "pdb")) {
                        $pdb = 1;
                    }       
                } elseif (substr(strtolower($filename), -4) == '.mol') {
                    $base = str_ireplace(".mol", "", $filename);
                    if ($this->validatePL_2016_1_CheckPDBMolFilename($filename, $base, $valid_proteinligand_array, "mol")) {
                        $mol = 1;
                    }       

                } elseif (substr(strtolower($filename), -4) == '.csv') {
                    if ($this->validateScoresFilename($filename, 10, array("Protein")))
                        $ps = 1;
                } elseif (substr(strtolower($filename), -4) == '.txt') {
                    if ($this->validateProtocolFilename($filename, 10, array("ProteinScoring")))
                        $psp = 1;
                } else {
                    $this->error_string .= $filename . " - unrecognized file type\n";
                }
            
            }

            if (!($ppp))
                $this->error_string .= "No PosePredictionProtocol.txt file found\n";

            if (!($ui))
                $this->error_string .= "No 'UserInfo.txt' file found\n";

            // if error found, go ahead and report it now and stop.
            if ($this->error_string) {
                echo str_replace("\n", "<br />\n", $this->error_string);
                exit;
            }



    /*
        for Dock files, these are optional, so removing them ...
            if (!($ps))
                $this->error_string .= "No ProteinScore file found\n";

            if (!($psp))
                $this->error_string .= "No ProteinScoringProtocol file found\n";
    */
            if (!($pdb))
                $this->error_string .= "No PDB file(s) found\n";

            // ALL $valid_proteinligand_array records must have PDB/MOL files.
            if ($diff1 = array_diff($valid_proteinligand_array, array_keys($this->proteinligand)))
                $this->error_string .= "PDB and MOL file(s) missing for " . addAndOrForGrammar("and", $diff1) . "\n";

            // check to make sure there are as many ProteinScore files as ProteinScoringProtocol files
            $this->checkScoresVsProtocolPredictions("Protein");

            if ($this->error_string) {
                echo str_replace("\n", "<br />\n", $this->error_string);
                exit;
            }

            $this->validatePL_2016_1_CheckProteinOveruse($this->proteinligand);
            $this->validatePL_2016_1_CheckFileContents($folder_path, $dockdir, 200, "dock");
    //      $this->validatePL_2016_1_CheckLineCountandRemarks($folder_path, $dockdir, 200);
    //      echo "exit removed";
    //      exit;
            $this->validatePL_2016_1_CheckDockPoses();
        }
    }

    public function validatePL_2016_1_CheckPDBMolFilename($filename, $base, $valid_proteinligand_array, $pdbmol) {
        $maxpose = 5;
        $found = 0;
        $error_here = 0;

        if (strpos($base, "_") !== false)
            $this->error_string .= $filename . " should not have any underscores.\n";
        else {
            preg_match('/(.*)-(\d*)/', $base, $matches);
        
            if (!(sizeof($matches))) {
                $error_here = 1;
                $this->error_string .= $filename . " has an invalid filename.\n";
            } elseif ($matches[2] == '') {
                $error_here = 1;
                $this->error_string .= $filename . " has an invalid pose number.\n";
            } elseif (($matches[2] < 1) || ($matches[2] > $maxpose)) {
                $error_here = 1;
                $this->error_string .= $filename . " has an invalid pose number ({$matches[2]}).\n";
            } else {
                foreach ($valid_proteinligand_array as $proteinligand) {
                    if (strtolower($proteinligand) == strtolower($matches[1])) {
                        /* doubtful situation, but might be possible ... what if user had xyz-1.pdb and xyz-1.PDB?
                            need to give an error */
                        if (isset($this->proteinligand[$proteinligand][$pdbmol][$matches[2]])) {
                            $error_here = 1;
                            $this->error_string .= $proteinligand . " with pose " . $matches[2] . " - duplicate found.\n";
                        } else {
                            $this->proteinligand[$proteinligand][$pdbmol][$matches[2]] = $matches[2];
                            $this->validation_counter->ligand->$proteinligand->$pdbmol->$matches[2]['count']++;
                            
//                          print_r($this->validation_counter->ligand);
                            $found = 1;
                        }   
                        break;
                    }
                }

                if (!($found)) {                
                    $error_here = 1;
                    $this->error_string .= $filename . " has an invalid filename.\n";
                }   
            }
        }
        
        if ($error_here)
            return false;
        else
            return true;
    }

//  public function validatePL_2016_1_CheckLineCountandRemarks($path, $dockdir, $min) {
    public function validatePL_2016_1_CheckFileContents($path, $dir, $min, $dockscore) {
        foreach ($dir as $file) {
            if (substr($file, 0, 2) == '._')
                continue;

            if (substr($file, 0, 9) == '.DS_Store')
                continue;

            // only dock files have pdb
            if (($dockscore == 'dock') && (substr($file, -3) == 'pdb')) {
                if (!(filesize($path . "/" . $file))) {
                    $this->error_string .= $file . " - is empty\n";
                    continue;
                }
                $this->validatePL_2016_1_CheckPDBLineCount($path, $file, $min);

            // only dock files have mol
            } elseif (($dockscore == 'dock') && (substr($file, -3) == 'mol')) {
                if (!(filesize($path . "/" . $file))) {
                    $this->error_string .= $file . " - is empty\n";
                    continue;
                }
                $this->validatePL_2016_1_CheckMOLRemarks($path, $file);
    
            } elseif (substr($file, -3) == 'csv') {
                $this->validateScoresLines($path, $file, $dockscore);
            } elseif (substr($file, -3) == 'txt') {
                /* both of the following just go to extractProtocolText, 
                    so maybe it's ok to leave these, but update extractProtocolText with a filetype parameter
                    This will also be needed to give the file name in error messages
                */  
                if (stripos($file, "PosePrediction") !== false) {
                    $checkfor = array('type'=>'PosePrediction', 'required'=>array('Name:', 'Software:', 'System Preparation Parameters:', 'System Preparation Method:', 'Pose Prediction Parameters:', 'Pose Prediction Method:'));
                    $this->extractProtocolText($path, $file, $checkfor);
                } elseif (preg_match('/(\w*Scoring)Protocol/i', $file, $matches)) {
//              } elseif (stripos($file, "ProteinScoring") !== false) {
//                  $checkfor = array('type'=>'ProteinScoring', 'required'=>array('Name:', 'Software:', 'Parameters:', 'Method:'));
                    $checkfor = array('type'=>$matches[1], 'required'=>array('Name:', 'Software:', 'Parameters:', 'Method:'));
                    $this->extractProtocolText($path, $file, $checkfor);
                } else {
                    // UserInfo.txt will come thru here. That's OK since it was validated when found.
                    if ($file !== 'UserInfo.txt') {
                        //var_dump($file);
                        exit;
                    }
                }
            }
        }
    }

    public function validatePL_2016_1_CheckPDBLineCount($path, $file, $min) {
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);

        if (sizeof($rows) < $min) {
            $this->error_string .= $file . " - line count is too low (" . sizeof($rows) . "/$min)\n";
        }
    }
        
    public function validatePL_2016_1_CheckMOLRemarks($path, $file) {
        $proteinligand_keys = array_keys($this->ligandRemarks);
        
        $txt_file = file_get_contents($path . "/" . $file);
        $rows = explode("\n", $txt_file);

        // using the keys to figure out the protein rather than parsing the file name to get the protein
        foreach ($proteinligand_keys as $key) {
            if (strpos($file, $key) !== false) {
                if (substr(strtoupper($rows[0]), 0, 6) == 'REMARK') {
                    $parts = preg_split('/\s+/', trim($rows[0]));
                    if ((strtolower($parts[1]) == 'energy') || (strtolower($parts[1]) == 'score')) {
                        if (isset($parts[2])) {
                            if (is_numeric($parts[2]))
                                $this->validate_mol_remarks->$file->energy = $parts[2];
                            else    
                                $this->error_string .= $file . " - Energy/score value must be numeric\n";
                        } else {
                            $this->error_string .= $file . " is missing the energy/score value in the REMARK line\n";
                            $this->validate_mol_remarks->$file = 0;
                        }
                    } else {
                        $this->error_string .= $file . " - REMARK line must have the phrase 'energy' or 'score'\n";
                    }   
                } else {
                    $this->error_string .= $file . " - The first line must have a REMARK with energy/score data\n";
                }
            }
            continue;
        }   
    
    }

/*
    public function validatePL_2016_1_CheckPosePredictionProtocolLines($path, $file, $checkfor) {
        $this->extractProtocolText($path, $file, $checkfor);
    }
                
    public function validatePL_2016_1_CheckScoringProtocolLines($path, $file, $checkfor) {
        $this->extractProtocolText($path, $file, $checkfor);
    }
*/              
/*
    public function validatePL_2016_1_CheckProteinScorePrediction() {
        foreach ($this->ProteinScores as $prediction) {
            if (!(in_array($prediction, $this->ProteinScoringProtocol)))
                $this->error_string .= "ProteinScores-" . $prediction . ".txt does not have a matching ProteinScoringProtocol file\n";
        }

        foreach ($this->ProteinScoringProtocol as $prediction) {
            if (!(in_array($prediction, $this->ProteinScores)))
                $this->error_string .= "ProteinScoringProtocol-" . $prediction . ".csv does not have a matching ProteinScores file\n";
        }
    }
*/
    public function validatePL_2016_1_CheckProteinOveruse($proteinligand) {
        // check for ligand over use, ie with more than one pdb code
        foreach (array_keys($proteinligand) as $ligand) {
            // these $remark items need to be scanned for REMARK  energy  #neg value
            foreach ($proteinligand[$ligand] as $dock=>$pose) {
                if ($pose >= 2)
                    $remark[$ligand] = $pose;
            }               
        }
        
        $this->ligandRemarks = $remark;
    }

    public function validatePL_2016_1_CheckDockPoses () {
        $casted = json_decode(json_encode($this->validation_counter->ligand), true);

        foreach (array_keys($casted) as $proteinligand) {
            foreach (array("mol", "pdb") as $pdbmol) {
                $the_keys = array_keys($casted[$proteinligand][$pdbmol]);
                $pose_array[$pdbmol] = $the_keys;

                asort($the_keys);

                // make sure pose 1 exists
                if ($the_keys[0] != 1) {
                    $this->error_string .= $proteinligand . "-" . $the_keys[0] . ". " . $pdbmol . " must have a pose value that starts with 1\n";
                }
    
                $missing = array();
                $j = sizeof($the_keys) - 1;
                while ($j > 0) {
                    if (($the_keys[$j] - $the_keys[$j-1]) > 1) {
                        for ($m=$the_keys[$j-1]+1; $m<$the_keys[$j]; $m++) {
                            $missing[] = $m;
                        }
                        $this->error_string .= $proteinligand . " is missing " . $pdbmol . " files. Missing pose(s): " . addAndOrForGrammar("and", $missing) . "\n";
                    }

                    $j--;           
                }       
            }
            
            if ($adiff = array_diff($pose_array['pdb'], $pose_array['mol']))
                $this->error_string .= "There must be a MOL file for each PDB file. Missing MOL file with pose(s): " . addAndOrForGrammar("and", $adiff) . "\n";

            if ($adiff = array_diff($pose_array['mol'], $pose_array['pdb']))
                $this->error_string .= "There must be a MOL file for each PDB file. Missing PDB file with pose(s): " . addAndOrForGrammar("and", $adiff) . "\n";

        }
    }

    public function validatePL_2016_1_Score ($folder_path, $component) {
    /*
    17-ohp (311) protein only
    25-d3 (310) protein and ligand both
    */
        $ui = 0;
        
        $this->json_decoded = json_decode($this->getIcon($component));
        $required_scores_array = $this->json_decoded->score_validation->requiredscores; // Protein and/or Ligand 
        $required_protocols_array = $this->json_decoded->score_validation->requiredprotocols; // ProteinScoring and/or LigandScoring

        foreach ($required_scores_array as $proteinligand) {
            $this->{$proteinligand . "Scores"} = array();
            $this->{$proteinligand . "ScoringProtocol"} = array();
        }
        $this->PosePredictionProtocol = array();

        $scoredir = array_diff(scandir($folder_path), array('..', '.'));
        
        if (!(sizeof($scoredir)))
            $this->error_string .= "Submission folder cannot be empty\n";
        else {  
            foreach ($scoredir as $filename) {
                $found = 0;
                if (substr($filename, 0, 2) == '._')
                    continue;

                if (substr($file, 0, 9) == '.DS_Store')
                    continue;

                if (strtolower($filename) == strtolower('UserInfo.txt')) {
                    if ($this->validateUserInfoText($folder_path, $filename)) {
                        $found = 1;
                        $ui++;
                    }

                // Loop thru allowed "Scores"           
//              foreach ($required_scores_array as $score) {
                    // if the file starts with $score, then continue checking
                } elseif (substr(strtolower($filename), -4) == '.csv') {
                    if ($this->validateScoresFilename($filename, 10, $required_scores_array)) {
                        $found = 1;
                    }
                } elseif (substr(strtolower($filename), -4) == '.txt') {
                    // copy $required_scores_array so we can add PosePrediction to the test array
                    if ($this->validateProtocolFilename($filename, 10, array_merge($required_protocols_array, array("PosePrediction")))) {
                        $found = 1;
                    }   
                } else {
                    $this->error_string .= $filename . " - unrecognized file type\n";
                }   

/* take this out for now. So far, the error messages are sufficient ...
                if (!($found)) {
                    $this->error_string .= $filename . " - " . addAndOrForGrammar("or", $required_scores_array) . " not found in filename.\n";
                }    
*/              
            }
        }

        foreach (array_diff($this->PosePredictionProtocol, $this->{$required_scores_array[0] . "Scores"}) as $adiff) {
            $this->error_string .= "PosePredictionProtocol-" . $adiff . ".txt doesn't have a corresponding " . $required_scores_array[0] . "Scores file\n";
        }

        if ($this->error_string) {
            echo str_replace("\n", "<br />\n", $this->error_string);
            exit;
        }

        $pred_check = array();
        foreach ($required_scores_array as $score) {
            $this->{"validatePL_2016_1_Check" . $score . "ScorePrediction"};

            // check to make sure there are as many ProteinScore files as ProteinScoringProtocol files
            $this->checkScoresVsProtocolPredictions($score);

            if (!(sizeof($this->{$score . "Scores"}))) {
                $this->error_string .= "No valid '" . $score . "Scores' file found\n";
            } else {
                $pred_check[] = $score;
            }       

            if (!(sizeof($this->{$score . "ScoringProtocol"})))
                $this->error_string .= "No valid '" . $score . "ScoringProtocol' file found\n";
        }
        
        // if more than one $required_scores_array elements, then Protein and Ligand both exist, so make sure prediction (-n) match
        if (sizeof($required_scores_array) > 1) {
            if ($this->ProteinScores !== $this->LigandScores){
                $this->error_string .= "Prediction value mismatch between LigandScores and ProteinScores file(s)\n";
            }

            if ($this->ProteinScoringProtocol !==$this->LigandScoringProtocol) {
                $this->error_string .= "Prediction value mismatch between LigandScoringProtocol and ProteinScoringProtocol file(s)\n";
            }
        }

        $this->validatePL_2016_1_CheckFileContents($folder_path, $scoredir, 200, "score");
        $this->validatePL_2016_1_CheckScoresPredictions($pred_check);
    }
    
/*
    public function validatePL_2016_1_CheckLigandScorePrediction() {
        foreach ($this->LigandScores as $prediction) {
            if (!(in_array($prediction, $this->LigandScoringProtocol)))
                $this->error_string .= "LigandScore-" . $prediction . ".csv does not have a matching LigandScoringProtocol file\n";
        }       

        foreach ($this->LigandScoringProtocol as $prediction) {
            if (!(in_array($prediction, $this->LigandScores)))
                $this->error_string .= "LigandScoringProtocol-" . $prediction . ".csv does not have a matching LigandScores file\n";
        }       
    }
*/
    public function validatePL_2016_1_CheckScoresPredictions ($scores) {
        foreach ($scores as $score) {
            $the_scores = $this->{$score . "Scores"};

            // make sure pose 1 exists
            // using != since 1 comes from a string ...
            if ($the_scores[0] != 1) {
                $this->error_string .= $score . "Scores-" . $the_scores[0] . ".csv must have a prediction value that starts with 1\n";
            }

            $j = sizeof($the_scores) - 1;
            while ($j > 0) {
                if (($the_scores[$j] - $the_scores[$j-1]) > 1) {
                    $bad_pose_array = array($the_scores[$j], $the_scores[$j-1]);
                    $this->error_string .= $score . "Scores files have a gap in prediction numbering. (" . addAndOrForGrammar("and", $bad_pose_array) . ")\n";
                }

                $j--;           
            }       
        }
    }

    public function validatePL_2016_1_CheckLigandOveruse($dock_ligand) {
        // check for ligand over use, ie with more than one pdb code
        foreach (array_keys($dock_ligand) as $ligand) {
            if (sizeof(array_keys($dock_ligand[$ligand], true)) > 1)
                $this->error_string .= $ligand . " cannot be used with more than one PDB code\n";

            // these $remark items need to be scanned for REMARK  energy  #neg value
            foreach ($dock_ligand[$ligand] as $dock=>$pose) {
                if ($pose >= 2)
                    $remark[$dock . "-" . $ligand] = $pose;
            }               
        }
        
        $this->ligandRemarks = $remark;
    }



    public function validateSAMPL5($component, $file_array) {
        // check to make sure TXT file has basic naming conventions
        // for SAMPL5, since not dealing with tar/gzip files, let's validate first on file name before moving the file to validated directory.
        $comp_data = new Component_Data;
        $comp_data->getByID($component);
//      $checkagainst = array('OAH', 'OAMe', 'CBClip');
//      $checkagainst = array('DC');
        $checkagainst = explode(",", $comp_data->color);

        $this->TXTNameCheck($file_array["name"], $checkagainst);
        
        // move tmp file to 'unvalidated' directory
        $destination = $this->moveTempTarFile($file_array["tmp_name"], $file_array["name"], $component);


        // lucky us ... DC or DCStandard are easy to match vs HG ones
        if (substr($this->checkfound, 0, 2) == 'DC') {
            $this->validateSAMPL5_DC_HG($destination, $component, 'DC');
        }   
        else
            $this->validateSAMPL5_DC_HG($destination, $component, $this->checkfound);

        if (stripos($this->checkfound, "standard"))
            $vars['type'] = 'Standard';
        else
            $vars['type'] = 'Regular';

        if (isset($this->error_string)) {
            echo str_replace("\n", "<br />\n", $this->error_string);
            exit;
        }   
        
        // move successfullyl validated file to 'validated' directory
        $receipt_filename = $this->moveValidatedTarFile($destination, $component, $file_array["name"]);

        // log upload to the database
        $vars['uid'] = $_SESSION['user']->id;
        $vars['receipt_id'] = array_shift(explode("-", $receipt_filename));
        $vars['filename'] = $receipt_filename;
        $vars['component'] = $component;
        $vars['update_time'] = time();
        $this->insertSubmission($vars);
        
        $this->sendReceiptEmail($vars, $_SESSION['user']->email);
    }   

    public function TXTNameCheck($filename, $checkagainst) {
        if (strtolower(substr($filename, -4)) !== ".txt") {
            $error_string .= $filename . " must be named with a .txt file extension\n";
            echo $error_string;
            exit;           
        }       
        else 
            $filename = str_ireplace(".txt", "", $filename);

        foreach ($checkagainst as $check) {
            $found = 0;
                            
            // OAH-anything-1.txt
            // look for OAH, OAMe, CBClip, DC, ... whatever in filename
            $file_split = explode("-", $filename);
            if ($file_split[0] == $check)
                $found++;

            if (!(is_int((int) $file_split[2])))
                $error_string .= $filename . $file_split[2] . " is missing integer value.\n";
                
            if ($found > 1)
                $error_string .= $filename . " cannot have " . addAndOrForGrammar("or", $checkagainst) . " more than once in filename";

            if ($found) {
                $this->checkfound = $check;
                return 1;
            }   

            if (isset($error_string)) {
                echo $error_string;
                exit;
            }   
        }
        $error_string .= $filename . " does not have " . addAndOrForGrammar("or", $checkagainst) . " in the filename";
        echo $error_string;
        exit;
    }

    public function validateSAMPL5_DC_HG ($destination, $component, $dchg) {
        $txt_file = file_get_contents($destination);
        $rows = explode("\n", $txt_file);

        $startscanning = 'begin';
        $atleastone = 0;
        $checkfor = array('Predictions:', 'Software:', 'Method:', 'Name');

        $json_decoded = json_decode($this->getIcon($component));
        $valid_guest_array = $json_decoded->SAMPL5_validation; // array of DC Guests

        if ($dchg == 'DC') {
            $req_count = 4;
        } else {
            $req_count = 7;
        }

        $rowcount = 0;
        
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
            if (!($found)) 
                $store[$startscanning][] = $rows[$i];
        }
        
        // loop thru the found checkfor values  
        foreach (array_keys($store) as $key) {
            $atleastone = 0;

            switch ($key) {
                case 'Predictions:':
                    foreach ($store[$key] as $row) {
                        $row = trim($row);
                        $first_char = substr($row, 0, 1);
                        if (!( ($first_char == '#') || ($first_char == ''))) {
                            $row_split = explode(",", $row);

                            // must have 4 or 6 values
                            if (sizeof($row_split) !== $req_count) {
                                $this->error_string .= $row . " does not have the proper number of elements (" . $req_count . ")\n";
                            }

                            // first value must be a valid guest if HG, Compound if DC. $valid_guest_array has the validation data
                            if (!($this->checkSAMPLGuestCompound($row_split[0], $valid_guest_array, $dchg))) {
                                if ($dchg == 'DC')
                                    $this->error_string .= $row_split[0] . " is not a valid compound name.\n";
                                else
                                    $this->error_string .= $row_split[0] . " is not a valid guest name.\n";
                            }

                            // check required values
                            if ($dchg == 'DC') {
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
                        $this->error_string .= "There must be a 'Name' value<br />\n";
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
            } // switch
        } // foreach

        if (sizeof($checkfor)) {
            $this->error_string .= $filename . " is missing the following keywords: " . addAndOrForGrammar("and", $checkfor) . "\n";
        }

        $meth_count = 0;
        foreach ($method as $method_line) {
            $meth_count += sizeof(explode(" ", $method_line));
        }
        
        if ($meth_count < 50)
            $this->error_string .= "Method should be at least 50 words in length.\n";

        if (($dchg !== 'DC') && ($this->checkfound !== 'HGStandard')){
            $valid_diff = array_diff((array) $valid_guest_array->{$this->checkfound}, $this->guestcompound[$this->checkfound]);

            if (sizeof($valid_diff))
                $this->error_string .= "Guest component(s) missing from predictions: " . addAndOrForGrammar('and', $valid_diff) . "\n";
        } elseif ($this->checkfound !== 'DCStandard') {
            $size0 = sizeof($this->guestcompound['batch0']);
            $size1 = sizeof($this->guestcompound['batch1']);
            $size2 = sizeof($this->guestcompound['batch2']);
            
            if (($size0) && ($size0 < sizeof((array) $valid_guest_array->batch0)))
                $this->error_string .= "All batch 0 compounds must be included in the predictions.\n";
            
            if ($size1) {
                if (!($size0))
                    $this->error_string .= "All batch 0 compounds must be included in the predictions.\n";
                if ($size1 !== sizeof((array) $valid_guest_array->batch1))
                    $this->error_string .= "All batch 1 compounds must be included in the predictions since you have included a batch 1 compound.\n";
            }
                
            if ($size2) {
                if (!($size0))
                    $this->error_string .= "All batch 0 compounds must be included in the predictions.\n";
                if (!($size1))
                    $this->error_string .= "All batch 1 compounds must be included in the predictions since you have included a batch 2 compound.\n";
            
                if ($size2 !== sizeof((array) $valid_guest_array->batch2))
                $this->error_string .= "All batch 2 compounds must be included in the predictions since you have included a batch 2 compound.\n";
            }
        }

        return;
    }

    public function checkSAMPLGuestCompound($guest, $valid_guest_array, $dchg) {
        foreach ($valid_guest_array as $key=>$value) {
            if ($dchg !== 'DC')  {
                if ($key !== $dchg) {
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
}