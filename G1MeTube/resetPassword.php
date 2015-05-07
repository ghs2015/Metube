<?php

if(!isset($_SESSION)){
    session_start();
}

$processorClasses = array('ResetPasswordProcessor');
$postSignals = array('resetPassword');


if(isset($msg['passwordReset'])) {
	$_SESSION['userID'] = $msg['userID'];
}

?>

<!DOCTYPE html>
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

        <!-- HEADER REGION -->
        <div id="header" class="alpha grid_12">
            <?php require 'partials/header.php';?>
        </div>

        <!-- CONTENT REGION -->
        <div id="content" class="grid_12" style="text-align:center">
            <?php
            //case 1: successfully changed password
            if(isset($msg['passwordReset'])):
            ?>
            <h3>Password successfully changed!</h3>
            
            <?php
            //case 2: invalid attempt
            elseif(!$validAttempt):
            ?>
            <h3>This link is invalid. You waited too long to follow the reset link or your password has already been reset.</h3>
            Retrieve another link from here: <a href="forgotPassword">forgot password</a>
        
            <?php
            //case 3: valid attempt
            else:
            ?>
			<h1>Enter your new password</h1>
            <?php
			if(isset($msg['passwordEmpty'])) {
				echo '<h6 id="passwordEmptyMsg">Password is required.</h6>';
			}
			if(isset($msg['passwordsDoNotMatch'])) {
				echo '<h6 id="passwordsDoNotMatchMsg">The specified passwords to not match.</h6>';
			}
			?>
			<form method="post" action="<?php echo $thisPage;?>">
					Choose a Password: 
					<input type="password" name="password"> <br /><br />

					Confirm Password: 
					<input type="password" name="confirmPassword"> <br /><br />
                    
					<input type="submit" name="resetPassword" value="Change password">
			</form>
            <?php
            endif;
            ?>
        </div> <!-- End Content -->

        <!-- FOOTER REGION -->
        <div id="footer" class="grid_12">
            <?php require 'partials/footer.php';?>
        </div>

    </div>
</body>
</html>
