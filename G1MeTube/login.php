<?php
include_once "function.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){
	header('Location: browse.php');
	exit;
}
if(isset($_POST['login'])) {
	if($_POST['username'] == "" || $_POST['password'] == "") {
		$login_error = "One or more fields are missing.";
	}
	else {
		$check = user_pass_check($_POST['username'],$_POST['password']); // Call functions from function.php
		if($check == 1) {
			$login_error = "User ".$_POST['username']." not found.";
		}
		elseif($check==2) {
			$login_error = "Incorrect password.";
		}
		else if($check==0){
			header('Location: browse.php');
		}
	}
}
//if(isset($_SESSION['username'])){
//	header('Location: browse.php');
//}
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
        	<!--Logged In-->
        	<h1>Log In</h1>
            <?php //error messages
                if(isset($login_error)) {
                    echo '<span class="red"><h6>'.$login_error.'</h6></span>';
                }
            ?>

        	<form method="post" action="login.php">
   	        	<label for="username">Username: </label>
	        	<input class="contentInput" type="text" name="username" value="<?php if(isset($values)){echo $values['email'];}?>" size="30" />	
	        	<br /><br />
			<!--
   	        	<label for="email">Email: </label>
	        	<input class="contentInput" type="text" name="email" value="<?php if(isset($values)){echo $values['email'];}?>" size="30" />	
	        	<br /><br />
			-->
   	     		<label for="password">Password: </label>
   	     		<input class="contentInput" type="password" name="password" size="30" /> 
                <br /><br />
   	     		<input class="contentSubmit" type="submit" name="login" value="Log In">
		        <input name="reset" type="reset" class="contentSubmit" value="Reset">
        	</form>

        	<br />
	<!--
            <a href="register">Register</a> | 
            <a href="forgotPassword?user=<?php echo $values['email'];?>">I Forgot my Password</a>
	-->


        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
</body>
</html>
