<?php

include('../classes/classes.php');
\helper\scicrunch_session_start();
error_reporting(0);
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if (!isset($_SESSION['user'])) {
	echo "Please reload the page and log in again.\n";
//    header('location:/');
    exit();
}

$action = filter_var($_GET['action'], FILTER_SANITIZE_STRING);
$component = filter_var($_GET['component'], FILTER_SANITIZE_NUMBER_INT);
if (isset($_GET['receipt']))
	$receipt = filter_var($_GET['receipt'], FILTER_SANITIZE_STRING);

if (isset($_GET['protocol_id']))
	$protocol_id = filter_var($_GET['protocol_id'], FILTER_SANITIZE_STRING);

define("CHALLENGE_PATH", getcwd() . "/../upload/challenges/");

switch ($action) {
	case "upload":

		if (!(file_exists(CHALLENGE_PATH)))
			mkdir (CHALLENGE_PATH);

		if (!(file_exists(CHALLENGE_PATH . 'validated')))
			mkdir (CHALLENGE_PATH . 'validated');

		if (!(file_exists(CHALLENGE_PATH . 'unvalidated')))
			mkdir (CHALLENGE_PATH . 'unvalidated');	

		/* 
		Looks like the plupload.js file is called once for each file that is uploaded.
		So, that means this upload.php is also called once for each file.

		Not sure I can do a master alert message once all are uploaded.

		However, I should be able to append some HTML for each file.
		*/
		
		if (empty($_FILES) || $_FILES["file"]["error"]) {
		  die('There was an error with your upload: ' . $_FILES["file"]["error"] );
		}

		if (strpos($_FILES['file']['name'], " ") !== false)
			die('Spaces are not allowed in the filename.');

		// each challenge and dataset will likely have different naming conventions, etc
		$challenge = new Challenge();
		$challenge->getChallengeByStageID($component);

/*
$chset = new Challenge;
$chset->getChallengesetByID($component, $data->component);

			$chset = new Challenge;
			$chset->getChallengesetByID($component);
*/

		$challenge_settings = json_decode($challenge->color1);

		$notification = new Notification();
		
        if ((substr(strtolower($_FILES['file']['name']), 0, 4) !== 'logp') && (substr(strtolower($_POST['name']), -3) == "txt")) {
            $notice_text = 'challenge-protocol-uploaded';
            $notice_content = 'Protocol successfully uploaded: ';
            $proto_or_sub = 'Protocol';
        } else {
            $notice_text = 'challenge-submission-uploaded';
            $notice_content = 'Submission successfully uploaded: ';
            $proto_or_sub = '';
        }
		$notification->create(array(
			'sender' => 0,
			'receiver' => $_SESSION['user']->id,
			'view' => 0,
			'cid' => $challenge->cid,
			'timed'=>0,
			'start'=>time(),
			'end'=>time(),
			'type' => $notice_text,
			'content' => $notice_content . $_FILES['file']['name']
		));
		$notification->insertDB();
		
//		$challengeset_array = array('D3R Grand Challenge 2015'=>'GC2015', 'SAMPL5'=>'SAMPL5', 'Challenge PL-2016-1'=>'PL_2016_1');
//		$challengeset_array = array('dkNet Challenge 2016'=>'dkNetChallenge2016');

		// make sure file directory exists for the files
		if (!(file_exists(CHALLENGE_PATH . 'unvalidated/' . $component))) 
			mkdir(CHALLENGE_PATH . 'unvalidated/' . $component);

		if (!(file_exists(CHALLENGE_PATH . 'validated/' . $component)))
			mkdir(CHALLENGE_PATH . 'validated/' . $component);

		$validation = new Challenge_Submission;
		$validation->challenge_path = CHALLENGE_PATH;

		// get stage specific info
        $chset = new Challenge;
        $chset->getChallengeSetByID($component);
        $stage_settings = json_decode($chset->icon)->predictioncategory;
		// is param1 == 1, then we need a param2?

		$validation->foo = $stage_settings->{$_REQUEST['param2']};

        if ((isset($_REQUEST['param2'])) && ($_REQUEST['param2'] == 'physical-properties')) {
            $validation->foo->file_type = json_decode($chset->icon)->file_type;
			$validation->{'validate' . $challenge_settings->shorty} ($component, $_FILES["file"], $_REQUEST['param2']);
			break;
        }

		if ((isset($_REQUEST['param1'])) && ($_REQUEST['param1'])) {
			// is param2 value valid?
            if ($proto_or_sub !== "Protocol") {
                if (!(isset($stage_settings->{$_REQUEST['param2']}))) {
                    die('You must select a prediction category');
                    exit;
                }
            }


			if (isset($_REQUEST['param3']))
				$validation->foo->anonymous = 1;
			else
				$validation->foo->anonymous = 0;

			$validation->foo->abbr = json_decode($chset->icon)->abbr;
            if ($proto_or_sub == "Protocol") {
                $validation->{'validate' . $challenge_settings->shorty . Protocol} ($component, $_FILES["file"], $_REQUEST['param2'], $_REQUEST['param3']);
            } else {
                $validation->{'validate' . $challenge_settings->shorty} ($component, $_FILES["file"], $_REQUEST['param2']);
            }
		}
		// param2 not required, so don't pass anything.
		else {
			$validation->{'validate' . $challenge_settings->shorty} ($component, $_FILES["file"]);
		}

		//die('{"SO not OK": 0}');
		break;

	case "receiptDelete":
		if (isset($receipt)) {
			$delete = new Challenge_Submission;
			$sub = $delete->getSubmissionFromReceipt($receipt, $_SESSION['user']->id);
			if ($_SESSION['user']->id == $sub['uid']) {
			    // from now on, submissions will have linked protocols, so delete protocols from link table first ...
			    $delete->deleteProtocolsSubmissions($sub['id'], $sub['uid']);

				$delete->logicalDeleteSubmissionFile(CHALLENGE_PATH . "validated/" . $component . "/" . $sub['filename'], CHALLENGE_PATH . "validated/" . $_GET['component'] . "/DELETED-" . $sub['filename']);
				$delete->deleteSubmissionRecord($receipt, $_SESSION['user']->id, $component);

				if (!empty($_SERVER['HTTP_REFERER'])){
					header("Location: ".$_SERVER['HTTP_REFERER']);}
			}
		}
		break;

	case "protocolDelete":
		if (isset($protocol_id)) {
			$delete = new Challenge_Submission;
			
			$sub = $delete->getProtocolFromProtocolID($protocol_id, $_SESSION['user']->id);
			if ($_SESSION['user']->id == $sub['uid']) {
				$delete->logicalDeleteProtocolFile(CHALLENGE_PATH . "validated/" . $component . "/" . $sub['filename'], CHALLENGE_PATH . "validated/" . $_GET['component'] . "/DELETED-" . $sub['filename']);
				$delete->deleteProtocolRecord($protocol_id, $_SESSION['user']->id, $component);

				if (!empty($_SERVER['HTTP_REFERER'])){
					header("Location: ".$_SERVER['HTTP_REFERER']);}
			}
		}
		break;

}
?>
