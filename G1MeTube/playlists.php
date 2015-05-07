<?php
include_once "models/Account.php";
include_once "models/Playlist.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else {
	header("Location: login.php");
	exit;
}

if(isset($_POST['deletePlaylist'])){
	$playlistID = $_POST['playlistID'];
	if(!is_numeric($playlistID) || intval($playlistID) < 0) {
		$msg['playlistDoesNotExist'] = true;
	} else if(!($deletePlaylist = Playlist::fromID(intval($playlistID))) ||
			$deletePlaylist->getUserID() != $myAccount->getID()) {
		$msg['playlistDoesNotExist'] = true;
	}
	$deletePlaylist->delete();
	$msg['playlistDeleted'] = true;
} else if(isset($_POST["createNewPlaylist"])){

	$row['User_id'] = $myAccount->getID();
	$row['name'] = $_POST['newPlaylistName'];

	if(empty($row['name'])) {
		$msg['playlistNameEmpty'] = true;
	} else if(strlen($row['name']) > 5000) {
		$msg['playlistNameTooLong'] = true;
	} else {
		Playlist::create($row);
		$msg['addPlaylistSuccessfully'] = true;
	}

}

$playlists = $myAccount->getPlaylists();

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
//case 1: no playlists
if(count($playlists) == 0){
?>
	<h3 id="noPlaylistsMsg">You have no playlists.</h3>

	<?php
}
//case 2: user has playlists
else {
	echo '<h2>Your Playlists</h2>';

	//show existing playlists
	foreach($playlists as $playlist) {
		?>
		<div class="playlist">
		<a href="playlistpage.php?playlistID=<?php echo $playlist->getID();?>"><?php echo $playlist->getName();?></a>
			<form method="post" action="playlists.php" style="display:inline">
				<input type="hidden" name="playlistID" value="<?php echo $playlist->getID();?>">
				<input class="button" type="submit" name="deletePlaylist" value="delete playlist">
			</form>
		</div>
	<?php
	}
}
?>

<br>

<?php


//create new playlist
if(isset($msg['playlistNameEmpty'])) {
	echo '<h3 id="playlistNameEmptyMsg">Playlist name is empty.</h3>';
}
if(isset($msg['playlistNameTooLong'])) {
	echo '<h3 id="playlistNameTooLongMsg">Playlist name is too long.</h3>';
}
if(isset($msg['addPlaylistSuccessfully'])) {
	echo '<h3 id="addPlaylistSuccessfully">Playlist '.$row['name'].' is created.</h3>';
}
?>
<form method="post" action="playlists.php">
New Playlist: <input type="text" class="contentInput" name="newPlaylistName"><br><br>
<input type="submit" name="createNewPlaylist" class="contentSubmit" value="create playlist">
</form>
</div>
<?php require_once 'partials/footer.php' ?>
</div>
</body>
</html>
