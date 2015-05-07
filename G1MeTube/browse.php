<?php
include_once "function.php";
include_once "models/Browser.php";
if(!isset($_SESSION)){
    session_start();
}

$category = 0;
$page = 1;
$sortby = 0;

if(isset($_GET['category'])) {
	$category = $_GET['category'];
}
if(isset($_GET['page'])) {
	$page = $_GET['page'];
}
if(isset($_GET['sortby'])) {
        $sortby = $_GET['sortby'];
}
//get browse results from Browser
$browser = new Browser($category, $page, $sortby);
$results = $browser->getResults();
$numResults = $browser->getTotalNumResults();
$numPages = $browser->getNumPages();
$page = $browser->getPage();

//get categories and add "Any" category
$categories = array('Any','Music','Sports','Gaming','Education','Movies','TV Shows','News');
$sorttypes = array('Time','Views','Rating');

//turn a page index into the url of that page
function urlWithPage($index) {
	$params = $_GET;
	$params['page'] = $index;
	return sprintf("browse.php?%s", http_build_query($params));
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

        	<fieldset class="alpha grid_2" style="float:right; padding: 5px; margin-top:10px">
			<form method="get" action="browse.php">
				<label for="category">Organize by:</label><br>
				<select name="category"> 
					<?php
					$i = 0;
					foreach($categories as $category) {
						echo sprintf('<option class="contentOption" value=%d %s>%s</option>',
							$i,
							$_GET['category']==$i? 'selected="selected"' : '',
							$category);
						$i++;
					}
					?>
				</select><br /><br />
				<label for="sortby">Sort by:</label><br>
				<select name="sortby"> 
					<?php
					$j = 0;
					foreach($sorttypes as $sorttype) {
						echo sprintf('<option class="contentOption" value=%d %s>%s</option>',
							$j,
							$_GET['sortby']==$j? 'selected="selected"' : '',
							$sorttype);
						$j++;
					}
					?>
				</select><br /><br />
				<input type="submit" class="contentSubmit" style="width:5em" value="submit">
			</form>
		</fieldset>	
		<div class="grid_9 omega" style="float:left">
		<?php
			//case 1: No results
			if($numResults == 0):
			echo '<h3 id="noResultsMsg">No results found</h3>';

			//case 2: Results
			else:
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

			echo "<br>";
			
			if($page > 1) {
				echo sprintf('<a href="%s">previous</a>', urlWithPage($page-1));
			}
			echo "&nbsp;";
			if($page < $numPages) {
				echo sprintf('<a href="%s">next</a>', urlWithPage($page+1));
			}

			echo "<br><br>";
		?>
		</div>

        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
