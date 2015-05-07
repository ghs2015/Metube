<div id="leftHead" class="alpha grid_5">
    <h1><span class="red">Me</span>Tube<span class="red">.</span></h1>
</div>
<div id="rightHead" class="grid_7 omega">
	
        <!-- If logged in, show account button -->
            <div id="upperRightHead">
		<?php if(isset($_SESSION['myAccount'])){ 
			$myAccount = unserialize($_SESSION['myAccount']);
		?>
			<a class="headLink" href="channelpage.php?channelID=<?php echo $myAccount->getID();?>">Home</a> |
		<? }?>
    		<a href="advancedsearch.php" class="headLink">Advanced Search</a> |
    		<a href="wordcloud.php" class="headLink">Word Cloud</a> |
		<?php if(isset($_SESSION['myAccount'])): ?>
			<a href="upload.php" class="headLink">Upload</a> | 
			<a href="myAccount.php" class="headLink">My Account</a> |
			<form method="post" action="logout.php" style="display:inline;">
			    <input class="linkButton" type="submit" name="logout" value="Log Out">
			</form>
		<!-- Not Logged in. Show register and login info -->
		<?php else: ?>
			<a href="login.php" class="headLink"><strong>Sign In</strong></a> |
			<a href="register.php" class="headLink"><strong>Register</strong></a>
		<?php endif; ?>
            </div>

    <form method="get" action="search.php">
    	<input type="text" id="search" name="searchQuery" value="<?php if(isset($searchQuery)) echo $searchQuery;?>" />
    	<input type="submit" id="search_button" value="search" />
    </form>
</div>
<div id="menuHead" class="alpha grid_12">
    <ul>
    	<li><a href="browse.php" class="headLink" style="text-decoration:none" >Explore</a></li>
    	<li><a href="accounts.php" class="headLink" style="text-decoration:none">Users</a></li>
	<?php if(isset($_SESSION['myAccount'])): ?>  
    	<li><a href="playlists.php" class="headLink" style="text-decoration:none">Playlists</a></li>
    	<li><a href="favorite.php" class="headLink" style="text-decoration:none">Favorites</a></li>
  	<li><a href="subscriptions.php" class="headLink" style="text-decoration:none">Channels</a></li>
    	<li><a href="friend.php" class="headLink" style="text-decoration:none">Friends</a></li>
    	<li><a href="contact.php" class="headLink" style="text-decoration:none">Contacts</a></li>
    	<li><a href="message.php" class="headLink" style="text-decoration:none">Messages</a></li>
    	<li><a href="group.php" class="headLink" style="text-decoration:none">Groups</a></li>
	<?php endif; ?>
    </ul>
</div>
<hr />
