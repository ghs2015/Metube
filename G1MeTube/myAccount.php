<?php
include_once "models/Account.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
	$myAccount = unserialize($_SESSION['myAccount']);
} else if(isset($_SESSION['accountDeleted'])){ // account deleted
	$accountDeleted = true;
} else { // not logged in, redirect to browse.php
	header("Location: browse.php");
}


$thisPage = "editAccount.php";
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
            <!-- Account just deleted --> 
	    <?php if(isset($accountDeleted)): ?>
	    <h1 id="accountDeletedMsg">Account deleted.</h1>

	    <!-- Normal View--> 
	    <?php else: ?>
	    <h1>Welcome to your account, <?php echo $myAccount->getUsername();?>!</h1>
	    Username: <?php echo $myAccount->getUsername();?> <br /> <br />
	    Email: <?php echo $myAccount->getEmail();?> <br /> <br />
	    First name: <?php echo $myAccount->getFname();?> <br /> <br />
	    Last name: <?php echo $myAccount->getLname();?> <br /> <br />
	    Sex: <?php if($myAccount->getSex()==0){echo "Male";}else{echo "Female";}?> <br /> <br />
	    Birthday: <?php echo $myAccount->getDob();?> <br /> <br />
	    <a href="inbox.php" style="font-size: 16px">My Inbox</a><br /><br />
	    <form method="post" action="<?php echo $thisPage;?>" style="display: inline">
	    <input class="contentSubmit" type="submit" name="editAccount" value="Edit Account" >
	    </form>
	    <form method="post" action="<?php echo $thisPage;?>" onsubmit="return confirm('Are you sure you want to delete your account and videos forever? THIS CANNOT BE UNDONE.')" style="display: inline">
	    <input class="contentSubmit" type="submit" name="deleteAccount" value="Delete Account">
	    </form>

	    <?php endif; ?>
        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
