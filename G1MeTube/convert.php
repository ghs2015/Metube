<?php
include_once "function.php";

// if this page was not called by AJAX, die
if (!$_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') die('Invalid request');

// get variable sent from client-side page
$filename = isset($_POST['filename']) ? strip_tags($_POST['filename']) :null;
$filetype = isset($_POST['filetype']) ? strip_tags($_POST['filetype']) :null;
$fileExt = isset($_POST['fileExt']) ? strip_tags($_POST['fileExt']) :null;
$id = isset($_POST['id']) ? strip_tags($_POST['id']) :null;

//run some queries, printing some kind of result
if($filetype==2){// convert video
	if(!convertVideo($filename,$fileExt)){
		throw new Exception('Convert video error!');
	}
}

echo '<h3>Here is a <a href="media.php?mediaID='.$id.'">link to your media</a></h3>';
// echo results
?>
