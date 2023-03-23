<?php


include('../classes/classes.php');
\helper\scicrunch_session_start();

$cid = filter_var($_GET['cid'],FILTER_SANITIZE_NUMBER_INT);

//check if user is trying to leave community they were in, if so nav out of it
if(isset($_GET['main'])){

	$previousPage = Community::fullURLStatic(null) . 'account/communities';
}else{
	$previousPage = $_SERVER['HTTP_REFERER'];
}

if(isset($_SESSION['user']) && isset($_SESSION["user"]->levels[$cid])){
    $_SESSION['user']->levels[$cid] = 0;

    $cxn = new Connection();
    $cxn->connect();
    $cxn->update("community_access", "iii", Array("level"), Array(0, $cid, $_SESSION["user"]->id), "where cid=? and uid=?");
    $cxn->close();
}



header('location:'.$previousPage);

?>
