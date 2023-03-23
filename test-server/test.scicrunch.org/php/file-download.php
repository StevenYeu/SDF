<?php

include '../classes/classes.php';

DEFINE("D3R_NONWEB_FOLDER", "/var/www/d3r-files/evaluation-results/");
DEFINE("DOI_NONWEB_FOLDER", $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/");

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

function readfile_chunked($filename, $retbytes = true) {
    $chunksize = 1 * (1024 * 1024); // how many bytes per chunk
    $buffer = '';
    $cnt = 0;
    // $handle = fopen($filename, 'rb');
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }
    while (!feof($handle)) {
        $buffer = fread($handle, $chunksize);
        echo $buffer;
        ob_flush();
        flush();
        if ($retbytes) {
            $cnt += strlen($buffer);
        }
    }
    $status = fclose($handle);
    if ($retbytes && $status) {
        return $cnt; // return num. bytes delivered like readfile() does.
    }
    return $status;

}

function create_zip($files = array(), $destination = '', $overwrite = false) {
    //if the zip file already exists and overwrite is false, return false
    if (file_exists($destination) && !$overwrite) {
        return false;
    }
    //create the archive
    $zip = new ZipArchive();
    echo 'before';
    if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
        return false;
    }
    echo 'hi';
    //add the files
    foreach ($files as $file) {
        $zip->addFile($file, $file);
    }
    //debug
    //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

    //close the zip -- done!
    $zip->close();

    //check to make sure the file exists
    return file_exists($destination);
}

function getExtension($str) {
    $parts = explode(".",$str);
    return $parts[count($parts) - 1];
}

$type = filter_var($_GET['type'], FILTER_SANITIZE_STRING);
$file_type = filter_var($_GET['file-type'], FILTER_SANITIZE_STRING);
$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$receipt = filter_var($_GET['receipt'], FILTER_SANITIZE_STRING);
$component = filter_var($_GET['component'], FILTER_SANITIZE_NUMBER_INT);

// get datasetid from DOI
if (isset($_GET['doi'])) {
    $splitme = explode("_", $_GET['doi']);
    $datasetid = $splitme[1];

    $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
    $cid = $dataset->lab()->cid;

    session_start();
}

if ($type == 'extended') {
    $data = new Extended_Data();
    $data->getByID($id);
    $file = '../upload/extended-data/' . $data->file;
    $fileName = $data->name . "." . getExtension($file);

	// only track downloads for D3R (cid = 73)
	if ($data->cid == 73) {
		\helper\scicrunch_session_start();
		$challenge = new Challenge;

		// actually, only log those for D3R challenge set data
		if($challenge->getIcon1($id) == 'challengeset1') {
			if (!(isset($_SESSION['user']->id))):
	?>
				<script type='text/javascript'>
					alert('Please log in again.');
					if (document.referrer == "") {
						window.location.href = window.location.protocol + "//" + window.location.hostname;
					} else {
						history.back()
					}
				</script>
	<?php
			exit;
			endif;

			$vars['uid'] = $_SESSION['user']->id;
			$vars['component'] = $id;
			$vars['update_time'] = time();
			$vars['action'] = "download";
			$vars['isAnonymous'] = 0;

			$challenge->createFromRow($vars);
            $challenge->insertChallengeDB();

/* following code is to add user to challenge and mail chimp.
   removed for now in favor of requiring them to join challenge before d/l data
            $community = new Community();
            $community->getByID($data->cid);

            $challenge = new Challenge();
            $challenge->getChallengeByStageID($data->data);

            // Add user to challenge
            $vars['update_time'] = time();
            $vars['action'] = 'join';
            $vars['component'] = $challenge->component;
            $vars['community'] = $community->id;

            $challenge_decoded = json_decode($challenge->color1);

            $challenge->createFromRow($vars);
            $challenge->insertChallengeDB();

            // Add user to challenge's mailchimp list
            $httpCode = \helper\mailchimpPut($community->mailchimp_api_key, $challenge_decoded->mailchimp_list_id, "subscribed", $_SESSION['user']->email, $_SESSION['user']->firstname, $_SESSION['user']->lastname);

            // 200 indicates successful action
            if ($httpCode == 200) {
                $notification = new Notification();
                $notification->create(array(
                    'sender' => 0,
                    'receiver' => $_SESSION['user']->id,
                    'view' => 0,
                    'cid' => $community->id,
                    'timed'=>0,
                    'start'=>time(),
                    'end'=>time(),
                    'type' => 'join-community-mailchimp',
                    'content' => 'Successfully added to the challenge mailing list: ' . $challenge->text1
                ));
                $notification->insertDB();
                $_SESSION['user']->last_check = time();
            }
*/
		}
	}

} elseif ($type == 'zip') {
    $holder = new Extended_Data();
    $files = $holder->getByData($id, true);
    foreach ($files[$file_type] as $file) {
        $fileArray[] = '../upload/extended-data/' . $file->file;
    }
    print_r($fileArray);
    $fileName = rand(0, 10000000) . '.zip';
    $return = create_zip($fileArray, '../upload/zips/' . $name,true);
    $file = '../upload/zips/' . $name;
} elseif ($type == 'datasubmission') {
    \helper\scicrunch_session_start();
    $data = new Challenge_Submission();
    $sub = $data->getSubmissionFromReceipt($receipt);

    // only send file if owner
    if ($sub['uid'] == $_SESSION['user']->id) {
        $file = '../upload/challenges/validated/' . $sub['component'] . "/" . $sub['filename'];
        $fileName = $sub['filename'];
    }
}  elseif ($type == 'mychallengeresults') {
    \helper\scicrunch_session_start();

    if (in_array($component, array(417, 443))) {
        $fileName = $_SESSION['user']->id . "-" . $component . "-" . $file_type . ".tgz";
        $file = D3R_NONWEB_FOLDER . $component . "/" . $fileName;
        $fileName = "GC2-" . $component . "-" . $file_type . ".tgz";
    } elseif (in_array($component, array(279, 280, 281, 294))) {
        $fileName = $_SESSION['user']->id . "-" . $component . ".tgz";
        $file = D3R_NONWEB_FOLDER . $component . "/" . $fileName;

    } elseif (in_array($component, array(965, 966, 967, 968, 1009, 969, 970, 971, 972))) {
        $data = new Challenge_Submission();
        $sub = $data->getSubmissionFromReceipt($receipt);

        if ($sub) {
            if ($sub['uid'] == $_SESSION['user']->id) {
                $sub_data = array("965"=>"p38a", "966"=>"VEGFR2", "967"=>"TIE2", "968"=>"CatS_stage1","1009"=>"CatS_stage2", "969"=>"JAK2_SC2", "970"=>"JAK2_SC3", "971"=>"ABL1", "972"=>'CatS_stage1B');

                if (($sub['type'] == 'scoreligand') || ($sub['type'] == 'scorestructure'))
                    $fileName = $sub_data[$component] . "_" . $receipt . "_" . str_replace("score", "", $sub['type']) . "_based_scoring.csv";
                elseif (($sub['type'] == 'freeenergy1') || ($sub['type'] == 'freeenergy2')) {
                    if ($sub_data[$component] == 'TIE2')
                        $fileName = $sub_data[$component] . "_" . $receipt . "_" . str_replace("freee", "free_e", $sub['type']) . ".csv";
                    else
                        $fileName = $sub_data[$component] . "_" . $receipt . "_free_energy.csv";
                }
                else {
                    if ($component == 968)
                        $stage = "1A";
                    else
                        $stage = "1B";

                    $fileName = "CatS_" . $stage . "_Pose_" . $receipt . "_LigandEval.csv";
                }

                $file = D3R_NONWEB_FOLDER . $component . "/" . $fileName;

            } else
                echo "You're not the owner of that file ...";
        }
    }
} elseif ($type == 'usersubmissions') {
    \helper\scicrunch_session_start();

    if (in_array($component, array(417, 443))) {
        $data = new Challenge_Submission();
        $sub = $data->getSubmissionFromReceipt($receipt);

        // in case someone gets sneaky and tries to download by changing URL line, check for flag and ownership
        if ($sub['isAnonymous']) {
            // only send file if owner
            if ((!(isset($_SESSION['user']->id))) || ($sub['uid'] != $_SESSION['user']->id)) {
            ?>
                <script type='text/javascript'>
					alert('If you are the owner of this file, please log in first, and then try again.');
					if (document.referrer == "") {
						window.location.href = window.location.protocol + "//" + window.location.hostname;
					} else {
						history.back()
					}
				</script>
			<?php
    			exit;
    		}

        // ok to offer download now
        } else {
            $file = '../upload/challenges/validated/' . $sub['component'] . "/" . $sub['filename'];
            $fileName = $sub['filename'];
        }
    }
} elseif ($type == 'doi') {
    if (!(isset($_GET['doi'])))
        echo "DOI is missing.";

    $dataset_split = explode("_", $_GET['doi']);

    $file = DOI_NONWEB_FOLDER . "public/dataset_".  $dataset_split[1] . "/v1/" . $_GET['doi'] . ".zip";
    $fileName = $_GET['doi'] . ".zip";

    // add to logs
    ScicrunchLogs::createNewObj($cid, $_SESSION['user']->id, $datasetid, 'dataset', 'dataset download', $_SERVER['REQUEST_URI']);

} elseif ($type == 'dict') {
    if (!(isset($_GET['doi'])))
        echo "DOI is missing.";

    $dataset_split = explode("_", $_GET['doi']);

    $file = DOI_NONWEB_FOLDER . "public/dataset_".  $dataset_split[1] . "/v1/" . $_GET['doi'] . "_dict.csv";
    $fileName = $_GET['doi'] . "_dict.csv";

    // add to logs
    ScicrunchLogs::createNewObj($cid, $_SESSION['user']->id, $datasetid, 'dataset', 'dictionary download', $_SERVER['REQUEST_URI']);

} elseif ($type == 'associated') {
    if (!(isset($_GET['type'])))
        die ("File type is missing.");
        
    if (!(isset($_GET['filename'])))
        die ("File name type is missing.");

    if (preg_match("/(\w*)_(\d*)_\d*/", $_GET['filename'], $match)) {
        if (($match[1] != 'dictionary') && ($match[1] != 'methodology'))
            die("Associated file type not allowed.");

        $file = DOI_NONWEB_FOLDER . "dataset_" . $match[2] . "/" . $_GET['filename'];
        $fileName = $_GET['filename'];

        $dataset = Dataset::loadBy(Array("id"), Array($match[2]));
        $cid = $dataset->lab()->cid;

        session_start();    

        // add to logs
        ScicrunchLogs::createNewObj($cid, $_SESSION['user']->id, $match[2], 'dataset', $match[1] . ' download', $_SERVER['REQUEST_URI']);    

    } else 
        die("Invalid filename");

} elseif ($type == 'curator_csv') {
    if (!(isset($_GET['type'])))
        die ("File type is missing.");
        
    if (!(isset($_GET['filename'])))
        die ("File name type is missing.");

    if (preg_match("/dataset_(\d*)/", $_GET['filename'], $match)) {
        $file = DOI_NONWEB_FOLDER . "dataset_" . $match[1] . "/" . $_GET['filename'];
        $fileName = $_GET['filename'];

    } else 
        die("Invalid filename");
}
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . $fileName);
header('Pragma: no-cache');
ob_clean();
flush();
readfile_chunked($file);
// You need to exit after that or at least make sure that anything other is not echoed out:
?>
