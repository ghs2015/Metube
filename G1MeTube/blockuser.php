<?php
include_once "models/Account.php";
if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['myAccount'])){
	header("Location: login.php");
	exit;
} else {
	$myAccount = unserialize($_SESSION['myAccount']);
}

if(isset($_POST['block'])){
	$myAccount->addBlock($_POST['accountID']);
} else if (isset($_POST['unblock'])){
	$myAccount->deleteBlock($_POST['accountID']);
}

if(isset($_SERVER['HTTP_REFERER'])){
	header("Location: ".basename($_SERVER['HTTP_REFERER']));
	exit;
} else {
	header("Location: browse.php");
}

?>

