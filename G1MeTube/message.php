<?php
require_once 'models/Message.php';
require_once 'models/Account.php';
if(!isset($_SESSION)){
    session_start();
}

if(!isset($_SESSION['myAccount'])){
        header("Location: login.php");
        exit;
} else {
        $myAccount = unserialize($_SESSION['myAccount']);
}


//if no conversation specified in GET, send to inbox
if(!isset($_GET['messageID'])) {
	header("Location: inbox.php");
	exit;
}
$message = Message::fromID($_GET['messageID']);
//redirect to inbox if the message is unfound 
if(!$message) {
	header("Location: inbox.php");
	exit;
}

//get other person's account
//other person could be user1 or user2 in Thread
if($message->getSenderID()==$myAccount->getID()) {
	$theirID = $message->getRecipientID();
} else {
	$theirID = $message->getSenderID();
}
$theirAccount = Account::fromID($theirID);


//clear form input if message was just sent
if(isset($msg['messageSentSuccessfully'])) {
	$values['messageContent'] = '';
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
            <h1>Conversation with <?php echo $theirAccount->getUsername();?></h1>
            <?php
                    $senderUsername = $message->getSenderID()==$myAccount->getID()? $myAccount->getUsername() : $theirAccount->getUsername();
            ?>
                <div style="background-color: white; padding-top:10px;" class="grid_12"> <!-- Thread list item -->
                    <strong class="senderUsername"><?php echo $senderUsername;?></strong>
                    <em><?php echo $message->getDate();?></em>
                    <p class="messageContent"><?php echo $message->getMessage();?></p>
                </div>
                <hr class="grid_12" />

            <?php

            if(isset($msg['recipientEmpty'])) {
                echo '<h3 id="recipientEmptyMsg">No recipient specified.</h3>';
            }
            if(isset($msg['recipientDoesNotExist'])) {
                echo '<h3 id="recipientDoesNotExistMst">The specified recipient does not exist.</h3>';
            }
            if(isset($msg['messageToSelf'])) {
                echo '<h3 id="messageToSelfMsg">You cannot send a message to yourself.</h3>';
            }
            if(isset($msg['contentEmpty'])) {
                echo '<h3 id="contentEmptyMsg">The message is empty.</h3>';
            }
            if(isset($msg['contentTooLong'])) {
                echo '<h3 id="contentTooLongMsg">The message is too long. Please shorten.</h3>';
            }
            ?>
            <form method="post" action="<?php echo $thisPage;?>">
                <input type="hidden" name="messageRecipient" value="<?php echo $theirAccount->getUsername();?>">
                <textarea rows="4" name="messageContent" placeholder="Message..." style="font-size: 16px; width:75%"><?php if(isset($values['messageContent'])){echo $values['messageContent'];}?></textarea> <br />
                <input class="contentSubmit" type="submit" name="submitNewMessage" value="Send">
            </form>
            <br />
            <a href="inbox">Back to inbox</a>
            <br /><br />

        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
