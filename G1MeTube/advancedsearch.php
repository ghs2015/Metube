<?php
require_once 'models/AdvancedSearcher.php';
require_once 'function.php';

if(!isset($_SESSION)){
	session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
}

$nullSearch = true;
if(isset($_POST['search'])) {
	if(isset($_POST['words'])&&!empty($_POST['words'])){
		$row['words'] = $_POST['words'];
		$nullSearch = false;
	}
	if(isset($_POST['category'])){
		$row['category'] = $_POST['category'];
		$nullSearch = false;
	}
	if(isset($_POST['extension'])){
		$row['extension'] = $_POST['extension'];
		$nullSearch = false;
	} 
	if(isset($_POST['type'])){
		$row['type'] = $_POST['type'];
		$nullSearch = false;
	}
	if(isset($_POST['author'])&&!empty($_POST['author'])){
		$row['author'] = $_POST['author'];
		$nullSearch = false;
	} 
	if(isset($_POST['dateA'])&&!empty($_POST['dateA'])){
		$row['dateA'] = $_POST['dateA'];
		$nullSearch = false;
	} 
	if(isset($_POST['dateB'])&&!empty($_POST['dateB'])){
		$row['dateB'] = $_POST['dateB'];
		$nullSearch = false;
	}
	if(!$nullSearch){ 
		$searcher = new AdvancedSearcher();
		$searcher->searchAdvanced($row);
		$results = $searcher->getResults();
	}
}

$categories = array('Any','Music','Sports','Gaming','Education','Movies','TV Shows','News');
$extensions = array('Any','jpg','bmp','gif','mp3','mp4','wmv','swf');
$types = array('Any','Image','Audio','Video');
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
		if(!empty($results)):
		
		echo sprintf("<h3>%d results found.</h3>", count($results));
		
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
		} else: ?>
			<?php
			if(isset($_POST['search'])){
				if($nullSearch){
					echo "<h3>Please input data.</h3>";
				} else {
					echo "<h3>No results found.</h3>";
				}
			}
			?>
			<form method="post" action="advancedsearch.php">
			<label for="advancedSearch">Advanced Search:</label><br><br>

			<label for="words">Words:</label>
			<input type="text" class="contentInput" name="words" value="<?php if(isset($_POST['words'])){echo $_POST['words'];}?>"> <br /><br />

			<label for="category">Category:</label>
			<select name="category">
				<?php
				$i=0;
				foreach($categories as $category) {
					echo sprintf('<option value=%d %s>%s</option>', $i, $_POST['category']==$category? 'selected="selected"' : '', $category);
					$i++;
				}?>
			</select><br><br>

			<label for="extension">Extension:</label>
			<select name="extension">
			<?php
			foreach($extensions as $extension) {
				echo sprintf('<option value="%s" %s>%s</option>', $extension, $_POST['extension']==$extension? 'selected="selected"' : '', $extension);
			}?>
			</select><br><br>

			<label for="type">Type:</label>
			<select name="type">
			<?php
			$j=0;
			foreach($types as $type) {
				echo sprintf('<option value=%d %s>%s</option>', $j, $_POST['type']==$type? 'selected="selected"' : '', $type);
				$j++;
			}?>
			</select><br><br>

			<label for="author">Posted by:</label>
			<input type="text" class="contentInput" name="author"> <br /><br />

			<label for="date">Posted between: </label>
			<input type="date" name="dateA" value="<?php if(isset($_POST['dateA'])){echo $_POST['dateA'];}?>"> and 
			<input type="date" name="dateB" value="<?php if(isset($_POST['dateB'])){echo $_POST['dateB'];}?>"><br /><br />

			<input type="submit" class="contentSubmit" name="search" value="Search">
			<!--<input name="reset" type="reset" class="contentSubmit" value="Reset">-->
			</form>

		<?php 
		endif; ?>

		</div>

		<?php require_once 'partials/footer.php' ?>

		</div>
	</body>
</html>
