<?php
require_once "models/Account.php";
require_once "models/Channel.php";
require_once "function.php";
if(!isset($_SESSION)){
	session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else {
	//redirect to homepage if not logged in
	header("Location: index.php");
	exit;
}

if(isset($_GET['channelID'])){
	$channel = Channel::fromID($_GET['channelID']);
} else {
	if(isset($_SERVER['HTTP_REFERER'])){
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	} else {
		header("Location: index.php");
		exit;

	}
}
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
    <!-- container is a wrapper for all main sections, and defines bounds of
    any content on the screen -->
    <div id="container" class="container_12">

    	<!-- IMPORT HEADER -->
    	<?php require_once 'partials/header.php' ?>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align: center">

                <div class="channel">
                <h3 class="channelName"><?php echo $channel->getOwnerAccount()->getUsername();?></h3>

                <?php
                foreach($channel->getMedia() as $media) {
				?>
						<div class="channelItem">
                                <a href="media.php?mediaID=<?php echo $media->getID();?>&channelID=<?php echo $channel->getOwnerAccount()->getID();?>"><h4 class="channelItemTitle"><?php echo $media->getTitle();?></h4></a>
                                <form method="post" action="index.php">
                                <input type="hidden" name="channelID" value="<?php echo $channel->getID();?>">
                                <input type="hidden" name="mediaID" value="<?php echo $media->getID();?>">
                                <input type="submit" name="removeMedia" value="remove">
                                </form>
                                </div>
                <?php
                } ?>
                        <form method="post" action="channels.php">
                        <input type="hidden" name="channelID" value="<?php echo $channel->getID();?>">
                        <input type="submit" name="deleteChannel" value="delete channel">
                        </form>
                </div>


        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
