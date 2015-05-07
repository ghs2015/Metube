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

if(isset($_POST['addFriend'])){
	if($myAccount->isBlockedBy($_POST['accountID'])){
		header("Location: accounts.php");
		exit;
	}
	$myAccount->addFriend($_POST['accountID']);
	if(isset($_SERVER['HTTP_REFERER'])){
		if(basename($_SERVER['HTTP_REFERER'])=='accounts.php'){
			header("Location: accounts.php");
			exit;
		}
	}
} else if (isset($_POST['removeFriend'])){
	$myAccount->deleteFriend($_POST['accountID']);
	if(isset($_SERVER['HTTP_REFERER'])){
		if(basename($_SERVER['HTTP_REFERER'])=='accounts.php'){
			header("Location: accounts.php");
			exit;
		}
	}
}


$friends = $myAccount->getFriends();
?>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="css/reset.css" />
	<link rel="stylesheet" href="css/text.css" />
	<link rel="stylesheet" href="css/960_12_col.css" />
	<link rel="stylesheet" href="css/mainstyle.css" />
	<link rel="stylesheet" href="css/friend.css" />
</head>
<body>
	<div id="container" class="container_12">
		<?php require_once 'partials/header.php' ?>
		<div id="content" class="grid_12" style="text-align: center">
			<?php
			//case 1: no friends
			if(count($friends) == 0):
			?>
			<h3 id="noFriendMsg">You have no friends.</h3>

			<?php
			//case 2: user has friends
			else:
			echo '<h2 id="friendsHeader">Your Friends</h2>';
			foreach($friends as $friend) {
				if($friend->getSex()==0){
					$pic = '<img src="images/male.png" alt="Image" style="width:100;height:100%">';
				} else {
					$pic = '<img src="images/female.png" alt="Image" style="width:100;height:100%">';
				}
				?>

					<div class="friend_tag">
						<div class="picture">
						<?php echo $pic;?>
						</div>
						<div class="username"><a href="channelpage.php?channelID=<?php echo $friend->getID();?>"><?php echo $friend->getUsername();?></a></div>
						<div class="name">Full Name: <?php echo $friend->getFname();?> <?php echo $friend->getLname();?></div>
						<div class="birthday">Birthday: <?php echo $friend->getDob();?></div>
						<div class="email">Email: <?php echo $friend->getEmail();?></div>
						<div class="remove">
							<form method="post" action="friend.php" style="float:left">
								<input type="hidden" name="accountID" value="<?php echo $friend->getID();?>">
								<input type="image" name="removeFriend" src="images/delete-button.png"  width="48" height="24">
							</form>
						</div>
					</div>
				<?php } ?>
			<?php
			endif;
			?>
		</div>
		<?php require_once 'partials/footer.php' ?>
	</div>
</body>
</html>
