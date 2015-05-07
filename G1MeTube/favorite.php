<?php
require_once 'models/Media.php';
require_once 'function.php';
session_start();

if(isset($_SESSION['myAccount'])){ // logged in, get user info
        $myAccount = unserialize($_SESSION['myAccount']);
} else { // not logged in, redirect to browse.php
        header("Location: login.php");
}

//remove requested media from favorites 
if(isset($_POST['unfavorite'])) {
	$myAccount->unfavorite($_POST['mediaID']);
}

$favorites = $myAccount->getFavorites();
?>
<html>
<head>
<link rel="stylesheet" href="css/reset.css" />
<link rel="stylesheet" href="css/text.css" />
<link rel="stylesheet" href="css/960_12_col.css" />
<link rel="stylesheet" href="css/mainstyle.css" />
<link rel="stylesheet" href="css/media.css" />
</head>
<body>
<div id="container" class="container_12">
<?php require_once 'partials/header.php' ?>
<div id="content" class="grid_12" style="text-align: center">
<?php
//case 1: no favorites
if(count($favorites) == 0):
?>
<h3 id="noFavoritesMsg">You have no favorites.</h3>

<?php
//case 2: user has favorites
else:
echo '<h2>Your Favorites</h2>';
foreach($favorites as $media) {
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
                                <form method="post" action="favorite.php" style="float:right">
                                <input type="hidden" name="mediaID" value="<?php echo $media->getID();?>">
                                <input type="image" name="unfavorite" src="images/delete-button.png"  width="48" height="24">
                                </form>
                        </div>
                        </div>


		<?php
		}
		endif;
		?>
</div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>

	</body>
</html>
