<?php
require_once "models/Account.php";
require_once "models/Topic.php";
require_once "models/Group.php";
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

if(isset($_GET['groupID'])){
	$group = Group::fromID($_GET['groupID']);
} else {
	if(isset($_SERVER['HTTP_REFERER'])){
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	} else {
		header("Location: index.php");
		exit;
	}
}
if(isset($_POST['newTopic'])){
	$row['User_id'] = $myAccount->getID();
	$row['title'] = trimAndEscape($_POST['topicTitle']);
	$row['comment'] = trimAndEscape($_POST['topicContent']);
	$row['Group_id'] = trimAndEscape($_GET['groupID']);
	if(empty($row['title'])) {
		$msg['titleEmpty'] = true;
	} else if(empty($row['comment'])) {
		$msg['contentEmpty'] = true;
	} else {
		$msg['messageID'] = Topic::create($row);
		$msg['messageSentSuccessfully'] = $msg['messageID'];
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

                <div class="grid_9" style="float:left">
                <h3 class="groupName"><?php echo $group->getName();?></h3>

                <?php
                foreach($group->getTopic() as $topic) {
				?>
			<div class="groupItem">
                                <a href="topic.php?topicID=<?php echo $topic->getID();?>&groupID=<?php echo $group->getID();?>"><h4 class="groupItemTitle"><?php echo $topic->getTitle();?></h4></a>
				<?php if($topic->getUserId()==$myAccount->getID()){ ?>
				<form method="post" action="index.php">
					<input type="hidden" name="groupID" value="<?php echo $group->getID();?>">
					<input type="hidden" name="topicID" value="<?php echo $topic->getID();?>">
					<input type="submit" name="removeTopic" value="remove">
				</form>
				<?php } ?>
                        </div>
                <?php } ?>
		<?php if($group->getUserId()==$myAccount->getID()){ ?>
                        <form method="post" action="group.php">
				<input type="hidden" name="groupID" value="<?php echo $group->getID();?>">
				<input class="button" type="submit" name="deleteGroup" value="delete group">
                        </form>
		<?php } ?>
		<form method="post" action="grouppage.php?groupID=<?php echo $group->getID();?>">
		Topic Title: <input class="topicTitle" size="50" type="text" name="topicTitle" value="<?php if(isset($msg['topicTitle'])){echo $msg['topicTitle'];}?>" placeholder="title"> <br /> <br />
		<textarea rows="8" cols="80" name="topicContent" placeholder="Content..." style="font-size: width:100%"><?php if(isset($msg['topicContent'])){echo $msg['topicContent'];}?></textarea>
		<br />
		<input class="button" type="submit" name="newTopic" value="Post">
		</form>
                </div>

		<div class="grid_2" style="float:right;padding-top:10px;text-align:left">
		<h3>Members</h3>
		<?php
		foreach($group->getAllUsers() as $user){
		?>
			<a href="<?php echo 'channelpage.php?channelID='.$user->getID();?>"><?php echo $user->getUsername();?></a><br>
		<?php }?>
		</div>

        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
