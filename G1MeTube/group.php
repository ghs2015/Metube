<?php
include_once "models/Account.php";
include_once "models/Group.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else {
	header("Location: login.php");
	exit;
}

if(isset($_POST['deleteGroup'])){
	$groupID = $_POST['groupID'];
	if(!is_numeric($groupID) || intval($groupID) < 0) {
		$msg['groupDoesNotExist'] = true;
	} else if(!($deleteGroup = Group::fromID(intval($groupID))) ||
			$deleteGroup->getUserID() != $myAccount->getID()) {
		$msg['groupDoesNotExist'] = true;
	}
	$deleteGroup->delete();
	$msg['groupDeleted'] = true;
} else if(isset($_POST["createNewGroup"])){
	$row['User_id'] = $myAccount->getID();
	$row['name'] = $_POST['newGroupName'];
	if(empty($row['name'])) {
		$msg['groupNameEmpty'] = true;
	} else if(strlen($row['name']) > 5000) {
		$msg['groupNameTooLong'] = true;
	} else {
		$groupID = Group::create($row);
		$group = Group::fromID($groupID);
		$group->addUser($group->getUserId());
		$msg['addGroupSuccessfully'] = true;
	}
}else if(isset($_POST['leaveGroup'])){
	$groupID = $_POST['groupID'];
	if(!is_numeric($groupID) || intval($groupID) < 0) {
		$msg['groupDoesNotExist'] = true;
	} else if(!($leaveGroup = Group::fromID(intval($groupID)))) {
		$msg['groupDoesNotExist'] = true;
	} else if(!$leaveGroup->contain($myAccount->getID())) {
		$msg['groupDoesNotContainUser'] = true;
	} else {
		$leaveGroup->deleteUser($myAccount->getID());
		$msg['groupLeaved'] = true;
	}
} else if(isset($_POST["joinGroup"])){
	$row['User_id'] = $myAccount->getID();
	$groupID = $_POST['groupID'];
	if(!($joinGroup = Group::fromID(intval($groupID)))) {
		$msg['groupDoesNotExist'] = true;
	} else if($joinGroup->contain($myAccount->getID())) {
		$msg['groupContainUser'] = true;
	} else {
		$joinGroup->addUser($myAccount->getID());
		$msg['groupJoined'] = true;
	}
}


//$groups = $myAccount->getGroups();
$groups = Group::getAllGroups();
$mygroups = Group::getUserGroups($myAccount->getID());
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
<?php require_once 'partials/header.php'; ?>
<div id="content" class="grid_12" style="text-align: center">
<?php
//case 1: no groups
if(count($mygroups) == 0){
?>
	<h3 id="noGroupsMsg">You have no groups.</h3>

	<?php
}
//case 2: user has groups
else {
	echo '<h2>Your Groups</h2>';

	//show existing groups
	foreach($mygroups as $mygroup) {
		?>
		<div class="group">
		<a href="grouppage.php?groupID=<?php echo $mygroup->getID();?>"><?php echo $mygroup->getName();?></a>
			<form method="post" action="group.php" style="display:inline">
				<input type="hidden" name="groupID" value="<?php echo $mygroup->getID();?>">
				<input class="button" type="submit" name="leaveGroup" value="leave group">
			</form>
			<?php if($mygroup->getUserId()==$myAccount->getID()){ ?>
				<form method="post" action="group.php" style="display:inline">
					<input type="hidden" name="groupID" value="<?php echo $mygroup->getID();?>">
					<input class="button" type="submit" name="deleteGroup" value="delete group">
				</form>
			<?php } ?>
		</div>
	<?php
	}
}
?>

<div class="group"><hr></div>


<?php
//case 1: no groups
if(count($groups) == 0){
?>
	<h3 id="noGroupsMsg">No groups.</h3>

	<?php
}
//case 2: user has groups
else {
	echo '<h2>All Groups</h2>';

	//show existing groups
	foreach($groups as $group) {
		?>
		<div class="group">
		<a href="grouppage.php?groupID=<?php echo $group->getID();?>"><?php echo $group->getName();?></a>
			<?php if($group->getUserId()==$myAccount->getID()){ ?>
				<form method="post" action="group.php" style="display:inline">
					<input type="hidden" name="groupID" value="<?php echo $group->getID();?>">
					<input class="button" type="submit" name="deleteGroup" value="delete group">
				</form>
			<?php } 
			      if($group->contain($myAccount->getID())) { ?>
				<form method="post" action="group.php" style="display:inline">
					<input type="hidden" name="groupID" value="<?php echo $group->getID();?>">
					<input class="button" type="submit" name="leaveGroup" value="leave group">
				</form>
			<?php } else { ?>
				<form method="post" action="group.php" style="display:inline">
					<input type="hidden" name="groupID" value="<?php echo $group->getID();?>">
					<input class="button" type="submit" name="joinGroup" value="join group">
				</form>
			<?php }?>
		</div>
	<?php
	}
}
?>

<div class="group"><hr></div>

<?php


//create new group
if(isset($msg['groupNameEmpty'])) {
	echo '<h3 id="groupNameEmptyMsg">Group name is empty.</h3>';
}
if(isset($msg['groupNameTooLong'])) {
	echo '<h3 id="groupNameTooLongMsg">Group name is too long.</h3>';
}
if(isset($msg['addGroupSuccessfully'])) {
	echo '<h3 id="addGroupSuccessfully">Group '.$row['name'].' is created.</h3>';
}
?>
<div class="group">
<form method="post" action="group.php">
New Group: <input type="text" name="newGroupName">
<input type="submit" class="button" name="createNewGroup" value="create group">
</form>
</div>
</div>
<?php require_once 'partials/footer.php' ?>
</div>
</body>
</html>
