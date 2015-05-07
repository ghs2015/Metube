<?php
require_once 'models/Account.php';
require_once 'models/Media.php';
header("Cache-Control: no-cache");
header("Pragma: nocache");
if(!isset($_SESSION)){
	session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else { // not logged in, redirect to browse.php
	echo "<a href='login.php'>Login</a>";
	exit;
}

$mediaID = preg_replace("/[^0-9]/","",$_REQUEST['id']);
$rate = preg_replace("/[^0-9]/","",$_REQUEST['stars']);

$media = Media::fromID($mediaID);
if(!$media->isUserRate($myAccount->getID())){// user hasnt rated
	$media->addRate($rate,$myAccount->getID());
}
$current_rating = $media->getRatingScore();

$new_back = array();
for($i=0;$i<5;$i++){
	$j=$i+1;
	if($i<$current_rating-0.5) $class="ratings_stars ratings_vote";
	else $class="ratings_stars";
	$new_back[] .= '<div class="star_'.$j.' '.$class.'"></div>';
}

$new_back[] .= ' <div class="total_votes">'.sprintf("%.1F",$current_rating).'</div>';
$allnewback = join("\n", $new_back);

// ========================

$output = $allnewback;
echo $output;

?>

