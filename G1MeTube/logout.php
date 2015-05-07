<?php
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){
	unset($_SESSION['myAccount']);
}
header('Location: browse.php'); 
?>
