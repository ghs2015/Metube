<?php
include_once "models/Account.php";
if(!isset($_SESSION)){
    session_start();
}

if(isset($_SESSION['myAccount'])){ // logged in, get user info
        $myAccount = unserialize($_SESSION['myAccount']);
}

$accounts = Account::getAllAccounts();
?>
<html>
	<head>
                <link rel="stylesheet" href="css/reset.css" />
                <link rel="stylesheet" href="css/text.css" />
                <link rel="stylesheet" href="css/960_12_col.css" />
                <link rel="stylesheet" href="css/mainstyle.css" />
	</head>
	<body>
        <div id="container" class="container_12">
		<?php require_once 'partials/header.php' ?>
                <div id="content" class="grid_12" style="text-align: center">
		<h3>Accounts</h3>
		<table id="accountsTable" style="width:600px;margin:auto">
			<?php
			foreach($accounts as $account) {
				if(isset($myAccount)){
					if($myAccount->getID()==$account->getID()){
						continue 1;
					}
				}
			?>
				<tr>
					<td>
						<a href="<?php echo 'account.php?accountID='.$account->getID();?>"><?php echo $account->getUsername();?></a>
					</td>
				<!-- subscribe -->
					<td>
						<?php
						if(isset($myAccount)) {
							if($myAccount->isSubscribed($account->getID())) { ?>
								<form method="post" action="account.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="unsubscribe" value="unsubscribe">
								</form>
							<?php
							} else {
							?>
								<form method="post" action="account.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="subscribe" value="subscribe">
								</form>
						<?php
							}
						}
						?>
					</td>
				<!-- friend -->
					<td>
						<?php
						if(isset($myAccount)) {
							if($myAccount->isFriend($account->getID())) { ?>
								<form method="post" action="friend.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="removeFriend" value="remove friend">
								</form>
							<?php
							} else if(!$account->isBlocked($myAccount->getID())){
							?>
								<form method="post" action="friend.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="addFriend" value="add friend">
								</form>
						<?php
							}
						}
						?>
					</td>
				<!-- contact -->
					<td>
						<?php
						if(isset($myAccount)) {
							if($myAccount->isContact($account->getID())) { ?>
								<form method="post" action="contact.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="removeContact" value="remove contact">
								</form>
							<?php
							} else if(!$account->isBlocked($myAccount->getID())){
							?>
								<form method="post" action="contact.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="addContact" value="add contact">
								</form>
						<?php
							}
						}
						?>
					</td>
				<!-- bock from add -->
					<td>
						<?php
						if(isset($myAccount)) {
							if($myAccount->isBlocked($account->getID())) { ?>
								<form method="post" action="blockuser.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="unblock" value="unblock">
								</form>
							<?php
							} else {
							?>
								<form method="post" action="blockuser.php">
									<input type="hidden" name="accountID" value="<?php echo $account->getID();?>">
									<input type="submit" class="large_button" name="block" value="block">
								</form>
						<?php
							}
						}
						?>
					</td>
				</tr>
			<?php
			}
			?>
		</table>
                </div>
                <?php require_once 'partials/footer.php' ?>
        </div>
	</body>
</html>
