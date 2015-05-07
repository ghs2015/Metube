<?php
include_once "models/Account.php";
include_once "function.php";
if(!isset($_SESSION)){
    session_start();
}

$thisPage = 'register.php';

if(isset($_POST['registerUser'])) {

	$values['username'] = $_POST['username'];
	$values['email'] = $_POST['email'];
	$values['password'] = $_POST['password'];
	$values['confirmPassword'] = $_POST['confirmPassword'];
	$values['fname'] = $_POST['fname'];
	$values['lname'] = $_POST['lname'];
	$values['sex'] = $_POST['sex'];
	$values['dob'] = $_POST['dob'];

	if(empty($_POST['username'])){
		$msg['usernameEmpty']='Username required.<br>';
	} else if(strlen($_POST['username']) > 35) {
		$msg['usernameTooLong'] = 'Username is too long.<br>';
	}
	if(strpos($_POST['username'], '@') !== false) {
		$msg['usernameContains@Symbol'] = 'Your username cannot contain the @ symbol.<br>';
	}
	if(Account::fromUsername($_POST['username'])) {
		$msg['usernameTaken'] = 'Username is already taken.<br>';
	}

	$_POST['email'] = trimAndEscape($_POST['email']);
	$_POST['email'] = strtolower($_POST['email']);
	if(strlen($_POST['email']) > 255) {
		$msg['emailTooLong'] = 'Email is too long.<br>';
	} else if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$msg['emailInvalid'] = 'Please enter a valid email address.<br>'; 
	} else if(Account::fromEmail($_POST['email'])) {
		$msg['emailTaken'] = 'Email is already taken.<br>';
	}

	$_POST['password'] = $_POST['password'];
	if(empty($_POST['password'])) {
		$msg['passwordEmpty'] = 'Password is required.<br>';
	}
	if($_POST['password'] != $_POST['confirmPassword']) {
		$msg['passwordsDoNotMatch'] = 'The specified passwords to not match.<br>';
	}
	if(!isset($msg)){
		//hash password with salt
		$salt = mt_rand(0, 65535);
		$passwordhash = hash('sha256', $_POST['password'].$salt);

		$account = Account::create($_POST['username'], $_POST['email'], $passwordhash, $salt, $_POST['fname'], $_POST['lname'], $_POST['sex'], $_POST['dob'] );

		//if we got this far, everything succeeeded!
		$_SESSION['myAccount'] = serialize($account);
		$regSuccess = true;
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
</head>
<body>
    <!-- container is a wrapper for all main sections, and defines bounds of
    any content on the screen -->
    <div id="container" class="container_12">

    	<!-- IMPORT HEADER -->
    	<?php require_once 'partials/header.php' ?>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align: center">
            <?php if(isset($regSuccess)): ?> <!-- registration successful --> 
                <h3 id="registrationSuccessfulMsg">Registration successful! You are now logged in to your account. </h3>
                Have a &nbsp;<a href="myAccount.php" id="linkToYourAccount">link to your account</a>

            <?php elseif(isset($myAccount)): ?> <!-- user logged in -->
                You are already logged in. To register, first
                <form method="post" action="<?php echo $thisPage;?>">
                    <input type="submit" name="logout" value="log out">
                </form>
            <?php else: ?> <!-- user needs to register -->
                <h1>Register</h1>
		<h6>
                <?php
                    if(isset($msg)) {
			foreach($msg as $val){
                        	echo $val;
			}
                    }
                ?>
		</h6>
                <form method="post" action="<?php echo $thisPage;?>">   
                    <label for="username">Choose a Username: <font color="red">*</font></label>
                    <input type="text" class="contentInput" name="username" value="<?php if(isset($values['username'])){echo $values['username'];}?>"> <br /><br />

                    <label for="email">Your Email: <font color="red">*</font></label>
                    <input type="text" class="contentInput" name="email" value="<?php if(isset($values['email'])){echo $values['email'];}?>"> <br /><br />
    
                    <label for="password">Choose a Password: <font color="red">*</font></label>
                    <input type="password" class="contentInput" name="password"> <br /><br />

		    <label for="confirmPassword">Confirm Password: <font color="red">*</font></label>
		    <input type="password" class="contentInput" name="confirmPassword"> <br /> <br />

                    <label for="fname">First name: </label>
		    <input type="text" class="contentInput" name="fname" value="<?php if(isset($values['fname'])){echo $values['fname'];}?>"> <br /><br />

                    <label for="lname">Last name: </label>
		    <input type="text" class="contentInput" name="lname" value="<?php if(isset($values['lname'])){echo $values['lname'];}?>"> <br /><br />

		    <label for="sex">Sex: </label>
		    <input type="radio" name="sex" value=0 checked>Male
		    <input type="radio" name="sex" value=1 >Female <br /><br />

		    <label for="dob">Birthday: </label>
		    <input type="date" name="dob" value="<?php if(isset($values['dob'])){echo $values['dob'];}?>"> <br /><br />

		    <input type="submit" class="contentSubmit" name="registerUser" value="Register">
		    <!--<input name="reset" type="reset" class="contentSubmit" value="Reset">-->
		    </form>

            <?php endif; ?>

        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
