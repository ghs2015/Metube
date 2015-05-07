<?php
require_once 'models/Inbox.php';
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else {
	header("Location: login.php");
	exit;

}

$messages = $myAccount->getMessages();
?>

<html>
<head>
    <title></title>
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/text.css" />
    <link rel="stylesheet" href="css/960_12_col.css" />
    <link rel="stylesheet" href="css/mainstyle.css" />
    <link rel="stylesheet" href="css/message.css" />
</head>
<body>
    <!-- container is a wrapper for all main sections, and defines bounds of
    any content on the screen -->
    <div id="container" class="container_12">

    	<!-- IMPORT HEADER -->
    	<?php require_once 'partials/header.php' ?>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align: center">
            <h1>Messages</h1>
            
            <?php
                foreach($messages as $message) {
		$sender = Account::fromID($message->getSenderID());
		$receiver = Account::fromID($message->getRecipientID());
		?>
			<div class="message_tag">
			<div class="name">From <a href="channelpage.php?channelID=<?php echo $sender->getID();?>"><?php echo $sender->getUsername();?></a> 
					to <a href="channelpage.php?channelID=<?php echo $receiver->getID();?>"><?php echo $receiver->getUsername();?></a></div>
			<div class="message"><?php echo $message->getMessage();?></div>
			<div class="name"><?php echo $message->getDate();?></div>
			</div>
		<?php } ?>
		<div class="message_tag" style="text-align:center;border-style:none;">
		<br><a href="newMessage.php">Send new message</a>
		</div>
		</div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
