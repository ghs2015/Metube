<?php
include_once "models/Account.php";
include_once "models/Group.php";
include_once "models/Topic.php";
include_once "function.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else {
	header("Location: login.php");
	exit;
}

if(isset($_GET['topicID'])){
	$topic = Topic::fromID($_GET['topicID']);
} else if(isset($_POST['topicID'])){
        $topic = Topic::fromID($_POST['topicID']);
} else {
        header("Location: group.php");
        exit;
}

if(isset($_POST["createNewComment"])){

	$row['User_id'] = $myAccount->getID();
	$row['comment'] = trimAndEscape($_POST['commentContent']);
	$row['Topic_id'] = $_POST['topicID'];

	if(empty($row['comment'])) {
		$msg['commentEmpty'] = true;
	} else if(strlen($row['comment']) > 5000) {
		$msg['commentTooLong'] = true;
	} else {
		TopicComment::create($row);
		$msg['addTopicCommentSuccessfully'] = true;
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
<link rel="stylesheet" href="css/message.css" />
</head>
<body>
<div id="container" class="container_12">
<?php require_once 'partials/header.php'; ?>
	<div id="content" class="grid_12" style="text-align: center">
			<div class="grid_9" style="float:left">
				<h3 class="topicName"><?php echo $topic->getTitle();?></h3>

				<?php
				foreach($topic->getTopicComments() as $topiccomments) {
				?>
					<div class="message_tag">
					<div class="name"><?php echo Account::fromID($topiccomments->getUserId())->getUsername();?></div>
					<div class="message"><?php echo $topiccomments->getComment();?></div>
					<div class="name"><?php echo $topiccomments->getDate();?></div>
					</div>
				<?php
				} ?>
				<?php
				//create new comment
				if(isset($msg['commentTooLong'])) {
					echo '<h3 id="commentTooLongMsg">Comment is too long.</h3>';
				}
				if(isset($msg['commentEmpty'])) {
					echo '<h3 id="commentEmptyMsg">Comment is empty.</h3>';
				}

				?>
				<br>
				<form method="post" action="topic.php">
					<textarea rows="4" name="commentContent" placeholder="Comment..." style="font-size: 16px; width:75%"><?php if(isset($msg['commentContent'])){echo $msg['commentContent'];}?></textarea>
					<br>
					<input type="hidden" name="topicID" value="<?php echo $topic->getID();?>">
					<input class="button" type="submit" name="createNewComment" value="Comment">
				</form>
			</div>

			<div class="grid_2" style="float:right;padding-top:10px;text-align:left">
			<h3>Members</h3>
			<?php
			$group=Group::fromID($topic->getGroupId());
			foreach($group->getAllUsers() as $user){?>
				<a href="<?php echo 'channelpage.php?channelID='.$user->getID();?>"><?php echo $user->getUsername();?></a><br>
			<?php }?>
			</div>
	</div>
<?php require_once 'partials/footer.php' ?>
</div>
</body>
</html>
