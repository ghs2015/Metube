<?php
include_once "models/Account.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){
        $myAccount = unserialize($_SESSION['myAccount']);
} else {
        header("Location: browse.php");
}

//redirect to myAccount to cancel changes
if(isset($_POST['cancelChangesToAccount'])) {
	header("Location: myAccount.php");
	exit;
} else if(isset($_POST['saveChangesToAccount'])){

	$att = array();	
	if(!empty($_POST['password'])){
		//echo "password ".$_POST['password']."<br>";
		$salt = mt_rand(0, 65535);
		$att['passwordhash'] = hash('sha256', $_POST['password'].$salt);
		$att['passwordsalt'] = $salt;
	}
	if($myAccount->getFname()!=$_POST['fname']){
		//echo "fname ".$_POST['fname']."<br>";
		$att['fname'] = $_POST['fname'];
	}
	if($myAccount->getLname()!=$_POST['lname']){
		//echo "lname ".$_POST['lname']."<br>";
		$att['lname'] = $_POST['lname'];
	}
	if($myAccount->getSex()!=$_POST['sex']){
		//echo "sex ".$_POST['sex']."<br>";
		$att['sex'] = $_POST['sex'];
	}
	if($myAccount->getDob()!=$_POST['dob']){
		//echo "dob ".$_POST['dob']."<br>";
		$att['dob'] = $_POST['dob'];
	}

	if(empty($att)){
		$msg['unchanged'] = '<h3>Profile information is unchanged.</h3>';
	} else {
		$myAccount->updateAtt($att);
		$myAccount = Account::fromID($myAccount->getID());
		$_SESSION['myAccount'] = serialize($myAccount);
		header("Location: myAccount.php");
		exit;
	}
} else if(isset($_POST['deleteAccount'])) {
		$myAccount->delete();
		$_SESSION = array(); // destroy session
		$_SESSION['accountDeleted'] = true;
		header("Location: myAccount.php");
		exit;
}

$thisPage = 'editAccount.php';

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
            <h1>Edit your account</h1>
            <?php
            	if(isset($msg['unchanged'])) {
                    echo $msg['unchanged'];
                }
	?>

            <!-- The onkeypress event is a javascript event to prevent submission of the
            form upon an Enter press. Since there are two submit inputs, cancel and
            save, it is ambiguous (from user perspective) which should be submitted
            when pressing Enter. Since cancel comes first in the html, cancel is
            actually the input submitted, which is not what we want. Thus onkeypress.
            -->

	    <form method="post" action="<?php echo $thisPage;?>" onkeypress="return event.keyCode != 13;">
	    Username: <input type="text" class="contentInput" name="username" value="<?php echo $myAccount->getUsername();?>" disabled> <br /> <br />
	    Email: <input type="text" class="contentInput" name="email" value="<?php echo $myAccount->getEmail();?>" disabled> <br /> <br />
	    Password: <input type="password" class="contentInput" name="password" placeholder="Enter new password"> <br />
            ex. not change if empty. <br /> <br />
	    <label for="fname">First name: </label>
	    <input type="text" class="contentInput" name="fname" value="<?php echo $myAccount->getFname();?>"> <br /><br />

	    <label for="lname">Last name: </label>
	    <input type="text" class="contentInput" name="lname" value="<?php echo $myAccount->getLname();?>"> <br /><br />

	    <label for="sex">Sex: </label>
	    <input type="radio" name="sex" value=0 <?php if($myAccount->getSex()==0) echo "checked"?> >Male
	    <input type="radio" name="sex" value=1 <?php if($myAccount->getSex()==1) echo "checked"?> >Female <br /><br />

	    <label for="dob">Birthday: </label>
	    <input type="date" name="dob" value="<?php echo $myAccount->getDob();?>"> <br /><br />

	    <input type="submit" class="contentSubmit" name="cancelChangesToAccount" value="cancel">
	    <input type="submit" class="contentSubmit" name="saveChangesToAccount" value="save">
	    </form>

        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
