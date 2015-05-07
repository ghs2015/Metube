<?php
require_once 'models/Searcher.php';

if(!isset($_SESSION)){
	session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
}

if(isset($_GET['searchQuery'])) {
	$searcher = new Searcher();
	$searcher->search($_GET['searchQuery']);
	$searchQuery = $searcher->getSearchString();
	$results = $searcher->getResults();
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
		<div id="container" class="container_12">

		<?php require_once 'partials/header.php' ?>

		<div id="content" class="grid_12" style="text-align: center">

		<?php
		if(isset($searchQuery)):
		
		echo sprintf("<h3>%d results found for %s</h3>", count($results), $searchQuery);
		
		foreach($results as $media) {
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
			<div class="rating">Rating: <?php echo $media->getRatingScore();?></div>
			<div class="description"><?php echo $media->getDescription();?></div>
			</div>

		<?php
		}
		
		endif;
		?>

		</div>

		<?php require_once 'partials/footer.php' ?>

		</div>
	</body>
</html>
