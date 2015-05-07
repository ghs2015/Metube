<?php
include_once "models/Account.php";
if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['myAccount'])){
	header("Location: login.php");
}

$myAccount = unserialize($_SESSION['myAccount']);
$subscribedChannels = $myAccount->getSubscribedChannels();
?>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="css/reset.css" />
	<link rel="stylesheet" href="css/text.css" />
	<link rel="stylesheet" href="css/960_12_col.css" />
	<link rel="stylesheet" href="css/mainstyle.css" />
</head>
<body>
	<div id="container" class="container_12">
		<?php require_once 'partials/header.php' ?>
		<div id="content" class="grid_12" style="text-align: center">
			<?php
			//case 1: no subscriptions
			if(count($subscribedChannels) == 0):
			?>
			<h3 id="noSubscriptionsMsg">You have no subscriptions.</h3>

			<?php
			//case 2: user has subscriptions
			else:
				echo '<h2 id="yourSubscriptionsHeader">Your Subscriptions</h2>';
				foreach($subscribedChannels as $channel) {
					?>
					<div class="channel">
					<h3 class="channelName"><a href="channelpage.php?channelID=<?php echo $channel->getOwnerAccount()->getID();?>"><?php echo $channel->getOwnerAccount()->getUsername();?></a></h3>
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
