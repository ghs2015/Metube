<?php
require_once 'models/Media.php';
require_once 'models/Playlist.php';
require_once 'models/MediaComment.php';
require_once 'models/Playlist.php';
require_once 'models/Account.php';
require_once 'function.php';
if(!isset($_SESSION)){
    session_start();
}

if(!isset($_GET['mediaID'])) {
	//redirect to homepage if there is no mediaID
	header("Location: index.php");
	exit;
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
        $myAccount = unserialize($_SESSION['myAccount']);
}

$media = Media::fromID($_GET['mediaID']);

if(!isset($_POST)||empty($_POST)){// not post request, increase view count
	$media->increaseViewCount();
}

//if comment was just posted, clear form value
if(isset($msg['commentedSuccessfully'])) {
	$values['newCommentContent'] = '';
}
if(isset($_POST['download'])) {
	Downloader::download($media);
}
if(isset($_POST['favorite'])) {
	$myAccount->favorite($media->getID());
	header("Location: media.php?mediaID=".$media->getID());
	exit;
}
if(isset($_POST['unfavorite'])) {
	$myAccount->unfavorite($media->getID());
	header("Location: media.php?mediaID=".$media->getID());
	exit;
}
if(isset($_POST['addToPlaylist']) && // add media to playlist request
		isset($myAccount)) { // user logged in
	$mediaID = $_GET['mediaID'];
	$playlistID = $_POST['playlistID'];

	$userID = $myAccount->getID();
	if(!is_numeric($mediaID) || intval($mediaID) < 0) {
		$msg['playlistDoesNotExist'] = true;
	} else if(!($playlist = Playlist::fromID(intval($playlistID))) ||
			$playlist->getUserID() != $userID) {
		$msg['playlistDoesNotExist'] = true;
	} else if($playlist->containsMedia($mediaID)) {
		$msg['mediaAlreadyInPlaylist'] = true;
	}
	$playlist->addMedia($mediaID);
	$msg['addedMediaToPlaylist'] = true;
}
if(isset($_POST['submitNewComment']) && // comment post request
		isset($myAccount)) { // user logged in
	$row['comment'] = trimAndEscape($_POST['newCommentContent']);
	$row['User_id'] = $myAccount->getID();
	if(empty($row['comment'])) {
		$msg['commentEmpty'] = true;
	} else if(strlen($row['comment']) > 5000) {
		$msg['commentTooLong'] = true;
	} else {
		$media->addComment($row);
		$msg['commentedSuccessfully'] = true;
	}
}
if(isset($_POST['deleteComment']) && // comment post request
		isset($myAccount)) { // user logged in
	$row['id'] = $_POST['commentID'];
	$media->deleteComment($row);
}
if(isset($_POST['deleteMedia']) && // comment post request
		isset($myAccount)) { // user logged in
	$media->delete();
	header("Location: index.php");
	exit;
}
if($media and isset($myAccount)) {
	$isFavorite = $myAccount->isFavorite($media->getID());

	//if in playlist
	if(isset($_GET['playlistID']) && ($playlist = Playlist::fromID($_GET['playlistID'])) &&
		$playlist->getUserID() == $myAccount->getID() && $playlist->containsMedia($media->getID())) {
		$playlistMedia = $playlist->getMedia();
	}
}
$allowDownload = false;
$allowRating = false;
$allowDiscussion = false;
$allowViewing = false;

?>
<html>
	<head>
		<link rel="stylesheet" href="css/reset.css" />
		<link rel="stylesheet" href="css/text.css" />
		<link rel="stylesheet" href="css/960_12_col.css" />
		<link rel="stylesheet" href="css/mainstyle.css" />
		<link rel="stylesheet" href="css/rating.css" />
		<link rel="stylesheet" href="css/media.css" />
		<link rel="stylesheet" href="css/comment.css" />
		<script src="jquery/jquery-1.11.2.min.js" type="text/javascript"></script>
		<script type="text/javascript" src="lib/rating.js"></script>
	</head>
<body>
	<div id="container" class="container_12">
		<?php require_once 'partials/header.php' ?>
		<div id="content" class="grid_12" style="text-align: center">
			<?php
			//case 1: media not found
			if(!$media):
			?>
				<h3>The media could not be found.</h3>

			<?php
			//case 2: media found
			elseif(($media->getShare()==2) || // shared with everybody
				(isset($myAccount) &&( $myAccount->isWatchable($media->getID()))) // logged in, is friend and not in view blocklist
				):
				$allowViewing = true;
				if($media->getDownloadType()==2 || // downloadable by everybody
					isset($myAccount) && $myAccount->isDownloadable($media->getID()) // logged in, watchable and not in download blocklist
					){
					$allowDownload = true;
				}
				$allowDiscussion = $media->getDiscussion();
				$allowRating = $media->getRating();
				$name = $media->getFileName();
				$extension = $media->getExtension();
				$viewCount = $media->getView();
				$type = $media->getType();
				?>
				<h3 id="mediaTitle"><?php echo $media->getTitle();?></h3>
				<!--media display-->
				<?php
				if($type == 0) {
				?>
					<img src="testStream.php?name=<?php echo $name?>&type=0&extension=<?php echo $extension?>" />
				<?php
				} else if($type == 1) {
				?>
					<audio controls preload="auto">
						<source src="mediaFile.php?name=<?php echo $name?>&type=1&extension=<?php echo $extension?>" type="audio/<?php echo $extension?>">
						Your browser does not support the audio element.
					</audio> 
				<?php
				} else if($type == 2) {
				?>


					<video id="movie" width="640" height="320" preload controls>
						<source src="testStream.php?name=<?php echo $name?>&type=2&extension=webm" type="video/webm; codecs=vp8,vorbis" />
						<source src="testStream.php?name=<?php echo $name?>&type=2&extension=ogv" type="video/ogg; codecs=theora,vorbis" />
						<source src="testStream.php?name=<?php echo $name?>&type=2&extension=mp4" />
						<object width="640" height="320" type="application/x-shockwave-flash"
						data="flowplayer-3.2.1.swf">
						<param name="movie" value="flowplayer-3.2.1.swf" />
						<param name="allowfullscreen" value="true" />
						</object>
					</video>
					<script>
						var v = document.getElementById("movie");
							v.onclick = function() {
						if (v.paused) {
							v.play();
						} else {
							v.pause();
						}
						};
					</script>
				<?php
				}
				?>

				<?php if(isset($myAccount)) { ?>
				<br>
					<div id="rating_<?php echo $media->getID();?>" class="ratings">
						<?php 
						for($i=0;$i<5;$i++){
							$j=$i+1;
							if($i<$media->getRatingScore()-0.5) $class="ratings_stars ratings_vote";
							else $class="ratings_stars";
							echo '<div class="star_'.$j.' '.$class.'"></div>';
						}
						?>
						<div class="total_votes"><?php echo sprintf("%.1F",$media->getRatingScore());?></div>
					</div>
				<?php }?>

				<?php echo $viewCount.' views';?>
				<br>
				<!--download link-->
				<?php if($allowDownload){ ?>
					<form method="post" action="download.php">
						<input type="hidden" name="name" value="<?php echo $name;?>">
						<input type="hidden" name="extension" value="<?php echo $extension;?>">
						<input type="submit" name="download" value="download">
					</form>
				<?php } ?>

				<?php
				//add media to playlist if logged in
				if(isset($myAccount)) {
					$playlists = $myAccount->getPlaylists();
					if(isset($msg['playlistDoesNotExist'])) {
						echo '<h3 id="playlistDoesNotExistMsg">The selected playlist does not exist.</h3>';
					}
					if(isset($msg['mediaAlreadyInPlaylist'])) {
						echo '<h3 id="mediaAlreadyInPlaylistMsg">The media is already in this playlist.</h3>';
					}
					if(count($playlists) > 0) {
					?>
					Add to: 
					<form method="post" action="media.php?mediaID=<?php echo $media->getID();?>">
						<select name="playlistID">
							<?php
							foreach($playlists as $playlist) {
								//add checkmark to playlists media is a member of (&#x2713; is checkmark)
								//automatically select whichever element is passed from processor object
								echo sprintf('<option value="%d" %s>%s %s</option>',
									$playlist->getID(),
									$playlist->getID()==0? 'selected="selected"':'',
									$playlist->getName(),
									$playlist->containsMedia($_GET['mediaID'])? '&#x2713;':'');
							}
							?>
						</select>
						<input type="submit" name="addToPlaylist" value="add to playlist">
					</form>
					<?php
					}
					?>
					<!--favorites button-->
					<form method="post" action="media.php?mediaID=<?php echo $media->getID();?>">
						<?php
						if($isFavorite) {
							echo '<input type="submit" name="unfavorite" value="unfavorite">';
						} else {
							echo '<input type="submit" name="favorite" value="favorite">';
						}
						?>
					</form>

					<?php if($myAccount->getID()==$media->getUploadUserID()){?>
						<form method="post" action="media.php?mediaID=<?php echo $media->getID();?>">
							<input type="submit" name="deleteMedia" value="delete media">
						</form>
					<?php } ?>
					<?php
				}?>
			<br><br>
			<div class="alpha grid_4" style="float:right">
			<h3>Recommendation:</h3>
			<?php
			$results = $media->getRecommendation();
			foreach($results as $m) {
				$account = Account::fromID($m->getUploadUserID());
				if($m->getType()==0){
					$pic = '<img src="images/image.png" alt="Image" style="width:100;height:100%">';
				} else if($m->getType()==1){
					$pic = '<img src="images/audio.png" alt="Audio" style="width:100;height:100%">';
				} else {
					$pic = '<img src="images/video.png" alt="Video" style="width:100;height:100%">';
				}
				?>
					<div class="media_tag">
					<div class="picture">
					<?php echo $pic;?>
					</div>
					<div class="title"><a href="media.php?mediaID=<?php echo $m->getID();?>"><?php echo $m->getTitle();?></a></div>
					<div class="author">Upload by: <a href="<?php echo 'account.php?accountID='.$account->getID();?>"><?php echo $account->getUsername();?></a></div>
					<div class="view"><?php echo $m->getView();?> Views | Rating: <?php echo $m->getRatingScore();?></div>
					<div class="rating"><?php echo $m->getDate();?></div>
					<div class="description"><?php echo $m->getDescription();?></div>
					</div>
					<?php
			}
			?>
			</div>
			<div class="grid_7 omega" style="float:left">
				<?php
				//if in playlist
				if(isset($playlistMedia)) {
					echo sprintf('<h4>Playlist: %s</h4>', $playlist->getName());
					foreach($playlistMedia as $playlistMediaItem) {
						//highlight the current media item
						echo sprintf('<a href="media.php?mediaID=%d&playlistID=%d">',
							$playlistMediaItem->getID(), $playlist->getID());
						if($playlistMediaItem->getID() == $media->getID()) {
							echo sprintf('<h5 style="color:red">%s</h5>', $playlistMediaItem->getTitle());
						} else {
							echo sprintf('<h5>%s</h5>', $playlistMediaItem->getTitle());
						}
						echo '</a>';
					}
				}
				
				//comments
				$comments = $media->getComments();
				echo "<h3>Comments</h3>";
				foreach($comments as $comment) {
					$commenterAccount = Account::fromID($comment->getUserId());
					?>
						<div class="message_tag">
						<div class="name"><a href="channelpage.php?channelID=<?php echo $commenterAccount->getID();?>"><?php echo $commenterAccount->getUsername();?></a></div>
						<div class="message"><?php echo $comment->getComment();?></div>
						<div class="name"><?php echo $comment->getDate();?>
							<?php if($commenterAccount->getID()==$myAccount->getID()){?>
								<form style="display:inline" method="post" action="media.php?mediaID=<?php echo $media->getID();?>">
									<input type="hidden" name="commentID" value="<?php echo $comment->getID();?>">
									<input type="submit" name="deleteComment" value="delete">
								</form>
							<?}?>
						</div>
						</div>
					<?php }?>
				<br><br>
					<?php
					if(isset($msg['commentEmpty'])) {
						echo '<h3 id="commentEmptyMsg">Your comment is empty.</h3>';
					}
					if(isset($msg['commentTooLong'])) {
						echo '<h3 id="commentTooLongMsg">Your comment is too long.</h3>';
					}
					//only show comment form if user is logged in
					if(isset($myAccount)) {
						?>
					<form method="post" action="media.php?mediaID=<?php echo $media->getID();?>">
						<textarea rows="3" style="font-size:16px; width:40%" name="newCommentContent"><?php if(isset($values['newCommentContent'])){echo $values['newCommentContent'];}?></textarea>
						<input type="submit" name="submitNewComment" value="comment">
					</form>
				<?php
				}
				?>
			</div>	
			<?php endif; ?>
		</div>
		<?php require_once 'partials/footer.php' ?>
	</div>
</body>
</html>
