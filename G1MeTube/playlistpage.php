<?php
require_once "models/Account.php";
require_once "models/Playlist.php";
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

if(isset($_GET['playlistID'])){
	$playlist = Playlist::fromID($_GET['playlistID']);
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
    <link rel="stylesheet" href="css/media.css" />
</head>
<body>
    <!-- container is a wrapper for all main sections, and defines bounds of
    any content on the screen -->
    <div id="container" class="container_12">

    	<!-- IMPORT HEADER -->
    	<?php require_once 'partials/header.php' ?>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align: center">

                <div class="playlist">
                <p style="font-size:24px;color:#FBB13D;font-weight:bold;"><?php echo $playlist->getName();?></p>
		<form method="post" action="playlists.php" style="display:inline;">
		<input type="hidden" name="playlistID" value="<?php echo $playlist->getID();?>">
		<input type="image" name="deletePlaylist" src="images/delete-all-button.png" alt="Delete list" width="60" height="24">
		</form><br><br>

                <?php
                foreach($playlist->getMedia() as $media) {
				$account = Account::fromID($media->getUploadUserID());
                                if($media->getType()==0){
                                        $pic = '<img src="images/image.png" alt="Image" style="width:100;height:100%">';
                                } else if($media->getType()==1){
                                        $pic = '<img src="images/audio.png" alt="Audio" style="width:100;height:100%">';
                                } else {
                                        $pic = '<img src="images/video.png" alt="Video" style="width:100;height:100%">';
                                }
                ?>

			<div class="media_tag">
			<div class="picture">
			<?php echo $pic;?>
			</div>
			<div class="title"><a href="media.php?mediaID=<?php echo $media->getID();?>"><?php echo $media->getTitle();?></a></div>
			<div class="author">Upload by: <a href="<?php echo 'account.php?accountID='.$account->getID();?>"><?php echo $account->getUsername();?></a></div>
			<div class="view"><?php echo $media->getView();?> Views | <?php echo $media->getDate();?></div>
			<div class="description"><?php echo $media->getDescription();?></div>
			<div class="rating">Rating: <?php echo $media->getRatingScore();?>	
				<form method="post" action="index.php" style="float:right">
				<input type="hidden" name="playlistID" value="<?php echo $playlist->getID();?>">
				<input type="hidden" name="mediaID" value="<?php echo $media->getID();?>">
				<input type="image" name="removeMedia" src="images/delete-button.png" alt="Remove media from list" width="48" height="24">
				</form>
			</div>
			</div>
                <?php
                } ?>
                </div>


        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
