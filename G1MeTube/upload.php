<?php
require_once 'models/Media.php';
require_once 'function.php';
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else { // not logged in, redirect to browse.php
	header("Location: browse.php");
}


if(isset($_POST['upload'])){

	$uploadOk = 1;

	$row['title'] = trimAndEscape($_POST['title']);
	if(empty($row['title'])) {
		$msg['emptyTitle'] = '<h3 id="titleEmptyMsg">Title is required.</h3><br/>';
		$uploadOk = 0;
	} else if(strlen($row['title']) > 100) {
		$msg['titleTooLong'] = '<h3 id="titleTooLongMsg">Title is too long.</h3><br/>';
		$uploadOk = 0;
	}

	$row['description'] = trimAndEscape($_POST['description']);
	if(empty($row['description'])) {
		$msg['emptyDescription'] = '<h3 id="descriptionEmptyMsg">Description is required.</h3><br/>';
		$uploadOk = 0;
	} else if(strlen($row['description']) > 500) {
		$msg['descriptionTooLong'] = '<h3 id="descriptionTooLongMsg">Description is too long.</h3><br/>';
		$uploadOk = 0;
	}

	$row['keywords'] = trimAndEscape($_POST['keywords']);
	//replace each group of whitespace with a single space
	$row['keywords'] = preg_replace('!\s+!', ' ', $row['keywords']);
	$row['keywords'] = array_filter(explode(';', $row['keywords']));
	foreach($row['keywords'] as $key=>$keyword) {
		//trim whitespace
		$row['keywords'][$key] = trim($keyword);
		if(strlen($keyword) > 35) {
			$msg['keywordTooLong'] = '<h3 id="keywordTooLongMsg">At least one keyword is too long.</h3><br/>';
			$uploadOk = 0;
		}
	}

	/*
	   No need to trimAndEscape category because value should be selected from a dropdown.
	   If user were to manipulate dropdown values, he could do no harm because the value
	   is never displayed to front end and getCategories() check would catch the error.

	   If we did use html_special_chars on the $_POST['category'], then some chars
	   would be escaped, making them mismatch corresponding string in database.
	 */
	$row['category'] = $_POST['category'];

	if(empty($_FILES['fileToUpload']['name'])) {
		$msg['noFileSelected'] = '<h3 id="noFileSelectedMsg">No file selected.</h3><br/>';
		$uploadOk = 0;
	} else if($_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
		//if fileTooLarge, send message to user. Otherwise throw exception.
		if($_FILES['fileToUpload']['error'] == UPLOAD_ERR_FORM_SIZE) {
			$msg['fileTooLarge'] = '<h3>The file is too large.</h3><br/>';
			$uploadOk = 0;
		} else {
			//				throw new ServerFailedException($message);
		}
	}

	if($uploadOk==1){
		$target_dir = 'uploads/';
		$target_file = $target_dir . basename($_FILES['fileToUpload']['name']);
		$row['extension'] = pathinfo($target_file,PATHINFO_EXTENSION);
		$row['type'] = getMediaType($_FILES['fileToUpload']['type']);
		$row['share_type'] = $_POST['share_type'];
		$row['download_type'] = $_POST['download_type'];
		$row['allow_discussion'] = isset($_POST['allow_discussion']) ? 1:0;
		$row['allow_rating'] = isset($_POST['allow_rating']) ? 1:0;
		$row['filename'] = preg_replace('/\\.[^.\\s]{3,4}$/', '', basename($_FILES['fileToUpload']['name']));
		$row['path'] = $target_file;
		$row['User_id'] = $myAccount->getID();

		$media = Media::create($row);

		if(file_exists($row['path']) ||
				!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $row['path'])) {
			//clean up sql entries and throw exception
			$msg['fileExist'] = '<h3>File already exists.</h3><br/>';
		} else {
			chmod($row['path'], 0644);
			$msg['uploadSuccess'] = '<h3 id="uploadSuccess">Upload success!</h3><br/>';
		}
	}

//	if($row['type']==2){// convert video
//		if(!convertVideo($row['filename'],$row['extension'])){
//			throw new Exception('Convert video error!');
//		}
//	}
}


$categories = array('Music','Sports','Gaming','Education','Movies','TV Shows','News');
?>

<html>
<head>
    <title></title>
    <link rel="stylesheet" href="css/reset.css" />
    <link rel="stylesheet" href="css/text.css" />
    <link rel="stylesheet" href="css/960_12_col.css" />
    <link rel="stylesheet" href="css/mainstyle.css" />

	<?php if(isset($msg['uploadSuccess'])&&$row['type']==2): ?>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<script>
		$(document).ready(function(){
			$('#loading_spinner').show();
			var name = "<?php echo $row['filename']?>";
			var type = "<?php echo $row['type']?>";
			var ext = "<?php echo $row['extension']?>";
			var id = "<?php echo $media->getID() ?>";
			var post_data = 'filename='+name+'&filetype='+type+'&fileExt='+ext+'&id='+id;
			$.ajax({url: "convert.php", type: 'POST', data: post_data,
				success: function(result){
				    $('#loading_spinner').hide();
				    $("#my_update_panel").html(result);
				},
				error: function() {
				    alert("Something went wrong!");
				}
			});
		});
		</script>
	<?php endif; ?>
</head>
<body>
    <!-- container is a wrapper for all main sections, and defines bounds of
    any content on the screen -->
    <div id="container" class="container_12">

    	<!-- IMPORT HEADER -->
    	<?php require_once 'partials/header.php' ?>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align: center">

            <!-- Upload Successful --> 
            <?php if(isset($msg['uploadSuccess'])): ?>
		<?php if($row['type']==2){
			echo '<img id="loading_spinner" src="images/loading.gif" height="42" width="42"><br>';
			echo '<div id="my_update_panel"><h3>Server is converting video, please wait...</h3></div>';
		} else {
			echo '<h3>Here is a <a href="media.php?mediaID='.$media->getID().'">link to your media</a></h3>';
		}
		?>
            <!-- User is ready to upload -->
            <?php else: ?>
            <?php
	    if(isset($msg)){
		foreach($msg as $v){
			echo $v;
		}
	    } else {
		echo '<h1>Upload</h1>';
	    }
            ?>

            <form method="post" action="upload.php" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" accept="audio/*,video/*,image/*" />
                <br /><br />

                <label for="title">Title</label>
                <input class="contentInput" type="text" name="title" value="<?php if(isset($row['title'])){echo $row['title'];}?>">
                <br /><br />

                <label for="description">Description</label>
                <input class="contentInput" type="text" name="description" value="<?php if(isset($row['description'])){echo $row['description'];}?>"> 
                <br /><br />

                <label for="category">Category</label>
                <select name="category"> 
                <?php
		$i=0;
                foreach($categories as $category) {
                    echo sprintf('<option value=%d %s>%s</option>',
                        $i,
                        $row['category']==$category? 'selected="selected"' : '',
                        $category);
			$i++;
                }
                ?>
                </select><br><br>

                <label for="keywords">Keywords (separate by semicolon)</label>
                <input class="contentInput" type="text" name="keywords" value="<?php if(isset($_POST['keywords'])){echo $_POST['keywords'];}?>">
                <br /><br />
		<hr>
		<input type="radio" name="share_type" value=2 checked> Shared with everybody<br>
		<input type="radio" name="share_type" value=1> Shared with friends<br>
		<input type="radio" name="share_type" value=0> Not shared<br> 
		<hr>
		<input type="radio" name="download_type" value=2 checked> Downloadable by everybody<br>
		<input type="radio" name="download_type" value=1> Downloadable by friends<br>
		<input type="radio" name="download_type" value=0> Not downloadable<br> 
		<hr>
		<input type="checkbox" name="allow_discussion" value=1 checked>Allow Discussion<br>
		<input type="checkbox" name="allow_rating" value=1 checked>Allow Rating<br>
		<hr>
                <input class="contentSubmit" type="submit" name="upload" value="Upload">
            </form>
            
            <?php endif; ?>
        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
