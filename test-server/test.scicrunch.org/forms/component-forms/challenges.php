<?php

	include '../../classes/classes.php';
	\helper\scicrunch_session_start();
	\helper\mailchimpPut();

$cid = filter_var($_POST['community'], FILTER_SANITIZE_NUMBER_INT);
$component = filter_var($_POST['component'], FILTER_SANITIZE_NUMBER_INT);

if (($_POST['action'] == 'join') || ($_POST['action'] == 'leave')) {

	$challenge = new Challenge;
	$vars['uid'] = $_SESSION['user']->id;
	$vars['update_time'] = time();
	$vars['action'] = $_POST['action'];
	$vars['component'] = $component;
	$vars['community'] = $cid;

	$challenge->createFromRow($vars);
 	$challenge->insertChallengeDB();

 	/* add/remove from mailchimp 'challenge specific' mailing list */
 	$do_mailchimp = 0;

 	// get community info. will need portalName and mailchimp_api_key
 	$community = new Community();
 	$community->getByID($cid);

 	if (isset($_POST['mailchimp'])) {
	 	if (!is_null($community->mailchimp_api_key)) {
			$comp = new Component;
			$comp->getByType($cid, $component);
			$comp_parameters = json_decode($comp->color1, true);

	 		if (!empty($comp_parameters['mailchimp_list_id']))
		 		$do_mailchimp = 1;
		} 		
 	}

	if ($do_mailchimp) {
		if ($vars['action'] == 'join') {
			$mc_status = 'subscribed';
		    $chimp_msg = "Successfully added to the challenge mailing list.";
		} else {
			$mc_status = 'unsubscribed';
		    $chimp_msg = "Successfully removed from the challenge mailing list.";
		}

        $httpCode = \helper\mailchimpPut($community->mailchimp_api_key, $comp_parameters['mailchimp_list_id'], $mc_status, $_SESSION['user']->email, $_SESSION['user']->firstname, $_SESSION['user']->lastname);

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
                'type' => $vars['action'] . '-challenge-mailchimp',
                'content' => $chimp_msg . ': ' . $comp->text1
            ));
            $notification->insertDB();
            $_SESSION['user']->last_check = time();    


		} else {
			// change $chimp_msg if not 200
			if ($vars['action'] == 'join')
		    	$chimp_msg = "We were not able to add you to the challenge mailing list at this time.";
		    else
		    	$chimp_msg = "We were not able to remove you from the challenge mailing list at this time.";
		}

	}
	
	if ($vars['action'] == 'join') 	
		echo "Your registration data has been received.\n";
	elseif ($vars['action'] == 'leave') {
		echo "You have left the Challenge.\n ";
	}

	if ($do_mailchimp)
		echo $chimp_msg;
} 
?>
