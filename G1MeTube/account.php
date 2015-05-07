<?php
include_once "models/Account.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
        $myAccount = unserialize($_SESSION['myAccount']);
} else {
        header("Location: login.php");
        exit;

}

//if there is no passed accountID, redirect to accounts
if(isset($_GET['accountID'])){
	$accountID = $_GET['accountID'];
} else if (isset($_POST['accountID'])){
	$accountID = $_POST['accountID'];
} else {
	header("Location: accounts.php");
	exit;
}

$account = Account::fromID($accountID);

//if you're looking at your own account, redirect to myAccount
//if($myAccount->getID() == $accountID) {
//	header("Location: myAccount.php");
//	exit;
//}

if(isset($_POST['subscribe'])) {
	$myAccount->subscribe($accountID);
	if(isset($_SERVER['HTTP_REFERER'])){
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
	}
} else if(isset($_POST['unsubscribe'])) {
	$myAccount->unsubscribe($accountID);
	if(isset($_SERVER['HTTP_REFERER'])){
		header("Location: ".$_SERVER['HTTP_REFERER']);
		exit;
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
<div id="container" class="container_12">
		<?php require_once 'partials/header.php' ?>
<div id="content" class="grid_12" style="text-align: center">
		<?php
		//case 1: account not found
		if(!$account):
			echo '<h3>The account could not be found.</h3>';
		//case 2: viewing someone else's account
		else:
			echo sprintf("This is %s's account.", $account->getUsername());
			
			if(isset($myAccount)) {
				if($myAccount->isSubscribed($accountID)) { ?>
					<h3>You are subscribed to this account.</h3>
					<form method="post" action="account.php?accountID=<?php echo $accountID;?>">
						<input type="submit" name="unsubscribe" value="unsubscribe">
					</form>
				<?php
				} else {
				?>
					<form method="post" action="account.php?accountID=<?php echo $accountID;?>">
						<input type="submit" name="subscribe" value="subscribe">
					</form>
			<?php
				}
			}
			?>
		
		<?php
		endif;
		?>
        </div> <!-- End Content -->

        <!-- IMPORT FOOTER -->
        <?php require_once 'partials/footer.php' ?>

    </div>
	</body>
</html>
