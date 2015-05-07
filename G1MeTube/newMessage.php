<?php
require_once "models/Account.php";
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

if(isset($_POST['submitNewMessage'])){
	$row['User_id1'] = $myAccount->getID();
	$row['message'] = trimAndEscape($_POST['messageContent']);
	$recipient = trimAndEscape($_POST['messageRecipient']);
	if(!($row['User_id2'] = Account::getIDfromUsername($recipient))) {
		$msg['recipientDoesNotExist'] = true;
	} else if(empty($row['User_id2'])) {
		$msg['recipientEmpty'] = true;
	} else if($row['User_id1'] == $row['User_id2']) {
		$msg['messageToSelf'] = true;
	} else if(empty($row['message'])) {
		$msg['contentEmpty'] = true;
	} else if(strlen($row['message']) > 65535) {
		$msg['contentTooLong'] = true;
	} else {
		$msg['messageID'] = $myAccount->sendMessage($row);
		$msg['messageSentSuccessfully'] = $msg['messageID'];
	}
}

//redirect to new message on message send
if(isset($msg['messageSentSuccessfully'])) {
	header(sprintf("Location: inbox.php", $msg['messageID']));
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
            <h1>New Message</h1>
            <?php
            if(isset($msg['recipientEmpty'])) {
                echo '<h3 id="recipientEmptyMsg">No recipient specified.</h3>';
            }
            if(isset($msg['recipientDoesNotExist'])) {
                echo '<h3 id="recipientDoesNotExistMsg">The specified recipient does not exist.</h3>';
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
            <form method="post" action="newMessage.php">
                To: <input class="contentInput" type="text" name="messageRecipient" value="<?php if(isset($msg['messageRecipient'])){echo $msg['messageRecipient'];}?>" placeholder="username"> <br /> <br />
                <textarea rows="4" name="messageContent" placeholder="Message..." style="font-size: 16px; width:75%"><?php if(isset($msg['messageContent'])){echo $msg['messageContent'];}?></textarea>
                <br /><br />
                <input class="contentSubmit" type="submit" name="submitNewMessage" value="Send">
            </form>
            <br />
            <a href="inbox.php">Back to inbox</a>
            <br />
        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
