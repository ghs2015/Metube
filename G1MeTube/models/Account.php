<?php
include_once "models/Channel.php";
include_once "models/Message.php";
include_once "Connection.php";

class Account  {
	private $id;
	private $username;
	private $email;
	private $channel;
	private $passwordhash;
	private $passwordsalt;
	private $fname;
	private $lname;
	private $sex;
	private $dob;
	
	protected function __construct(array $row) {
		$this->id = $row['id'];
		$this->email = $row['email'];
		$this->username = $row['name'];
		$this->passwordhash = $row['passwordhash'];
		$this->passwordsalt = $row['passwordsalt'];
		$this->fname = $row['fname'];
		$this->lname = $row['lname'];
		$this->sex = $row['sex'];
		$this->dob = $row['dob'];
	}
	
	/* accessors */
	public function getID() {
		return $this->id;
	}
	public function getEmail() {
		return $this->email;
	}
	public function getUsername() {
		return $this->username;
	}
	public function getPasswordHash() {
		return $this->passwordhash;
	}
	public function getPasswordSalt() {
		return $this->passwordsalt;
	}
	public function getFname() {
		return $this->fname;
	}
	public function getLname() {
		return $this->lname;
	}
	public function getSex() {
		return $this->sex;
	}
	public function getDob() {
		return $this->dob;
	}
	public function getChannel() {
		//lazily instantiate channel
		if(!isset($this->channel)) {
			$this->channel = new Channel($this);
		}
		return $this->channel;
	}
	public function sendMessage($row) {
                return Message::create($row);
        }
	public function getMessages() {
		$messages = array();
		
		//get playlists
		$conn = Connection::conn();
		$sql = sprintf("select * from Message where User_id1=%d or User_id2=%d", 
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('get Mesage query failed');
		}

		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$messages[] = new Message($row);
		}
		return $messages;
	}
	public function getPlaylists() {
		$playlists = array();
		
		//get playlists
		$conn = Connection::conn();
		$sql = sprintf("select * from Playlist where User_id=%d", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('get playlist query failed');
		}

		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$playlists[] = new Playlist($row);
		}
		return $playlists;
	}
	public function getGroups() {
		$groups = array();
		$conn = Connection::conn();
		$sql = sprintf("select * from GroupUsers where User_id=%d", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get group query failed');
		}
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$groups[] = Group::fromID($row['Group_id']);
		}
		return $groups;
	}
	public function getFriends() {
		$friends = array();
		
		$conn = Connection::conn();
		$sql = sprintf("select * from Friend where User_id1=%d", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get friends query failed');
		}

		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$friend = self::fromID($row['User_id2']);
			if(!$friend) {
				throw new InternalConsistencyException('friend exists but could not get friend account');
			}
			$friends[] = $friend;
		}
		return $friends;
	}
	public function getContacts() {
		$contacts = array();
		
		$conn = Connection::conn();
		$sql = sprintf("select * from Contact where User_id1=%d", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get contacts query failed');
		}

		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$contact = self::fromID($row['User_id2']);
			if(!$contact) {
				throw new InternalConsistencyException('contact exists but could not get contact account');
			}
			$contacts[] = $contact;
		}
		return $contacts;
	}
	public function getSubscribedChannels() {
		//refetch subscribed channels with each call to prevent stale data and old references
		$subscribedChannels = array();
		
		//get channels
		$conn = Connection::conn();
		$sql = sprintf("select * from Subscriptions where User_id_sub=%d", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get subscriptions query failed');
		}

		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$channelOwnerAccount = self::fromID($row['User_id_own']);
			if(!$channelOwnerAccount) {
				throw new InternalConsistencyException('subscription says channel exists but could not get owner account');
			}
			$subscribedChannels[] = new Channel($channelOwnerAccount);
		}
		return $subscribedChannels;
	}
	public function isSubscribed($channelOwnerID) {
		$conn = Connection::conn();
		$sql = sprintf('select * from Subscriptions where User_id_sub=%d && User_id_own=%d',
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($channelOwnerID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get subscriptions query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			return false;
		} else {
			return true;
		}
	}
	public function getFavorites() {
		$conn = Connection::conn();
		$sql = sprintf("select * from FavoriteMedias where User_id=%d",
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get favorite media query failed');
		}
		$favorites = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$media = Media::fromID($row['Media_id']);
			if(!$media) {
				throw new Exception('could not find media for Media_id in FavoriteMedias');
			}
			$favorites[] = $media;
		}
		return $favorites;
	}
	public function isFavorite($mediaID) {
		$conn = Connection::conn();
		$sql = sprintf("select * from FavoriteMedias where Media_id=%d and User_id=%d",
			mysql_real_escape_string($mediaID),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get favorite media query failed');
		}
		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	public function isBlockedBy($userID) {
		$conn = Connection::conn();
		$sql = sprintf("select * from Block where User_id1=%d and User_id2=%d",
			mysql_real_escape_string($userID),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get Block query failed');
		}
		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	public function isBlocked($userID) {
		$conn = Connection::conn();
		$sql = sprintf("select * from Block where User_id1=%d and User_id2=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($userID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get Block query failed');
		}
		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	public function addBlock($accountID) {
		if($this->id == $accountID){
			throw new Exception('cannot add self as contact');
		}
		$conn = Connection::conn();
		$sql = sprintf("insert into Block (User_id1, User_id2) values (%d, %d)",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('insert Block query failed');
		}
	}
	public function deleteBlock($accountID) {
		$conn = Connection::conn();
		$sql = sprintf("delete from Block where User_id1=%d and User_id2=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('delete Block query failed');
		}
	}
	public function isContact($accountID) {
		$conn = Connection::conn();
		$sql = sprintf("select * from Contact where User_id1=%d and User_id2=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get contact query failed');
		}
		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	public function addContact($accountID) {
		if($this->id == $accountID){
			throw new Exception('cannot add self as contact');
		}
		$conn = Connection::conn();
		$sql = sprintf("insert into Contact (User_id1, User_id2) values (%d, %d)",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('insert contact query failed');
		}
	}
	public function deleteContact($accountID) {
		$conn = Connection::conn();
		$sql = sprintf("delete from Contact where User_id1=%d and User_id2=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('delete contact query failed');
		}
	}

	/* mutators */
	public function updateAtt($att) { // update attributes
		$conn = Connection::conn();
		$sql = "update User set ";
		$isfirst = true;

		foreach($att as $k => $v){
			if($isfirst){
				$isfirst = false;
			} else {
				$sql .= ", ";
			}
			if($k=='sex'){
				$sql .= sprintf("$k=%d", mysql_real_escape_string($v));
			} else {
				$sql .= sprintf("$k='%s'", mysql_real_escape_string($v));
			}
		}
		$sql .= sprintf(" where id=%d", mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('update email failed');
		}
	}
	public function updateEmail($email) {
		$conn = Connection::conn();
		$sql = sprintf("update User set email='%s' where id=%d",
			mysql_real_escape_string($email),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('update email failed');
		}
	}
	public function updateUsername($username) {
		$conn = Connection::conn();
		$sql = sprintf("update User set name='%s' where id=%d",
			mysql_real_escape_string($username),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('update name failed');
		}
	}
	public function updatePassword($passwordhash, $passwordsalt) {
		$conn = Connection::conn();
		$sql = sprintf("update User set passwordhash='%s', passwordsalt=%d where id=%d",
			mysql_real_escape_string($passwordhash),
			mysql_real_escape_string($passwordsalt),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('update password failed');
		}
	}
	public function subscribe($channelOwnerID) {
		$conn = Connection::conn();
		$sql = sprintf('insert into Subscriptions(User_id_sub, User_id_own)'.
			' values '.
			'(%d, %d)', 
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($channelOwnerID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('insert subscription query failed');
		}
	}
	public function unsubscribe($channelOwnerID) {
		$conn = Connection::conn();
		$sql = sprintf('delete from Subscriptions where User_id_sub=%d && User_id_own=%d',
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($channelOwnerID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('delete subscription query failed');
		}
	}
	public function favorite($mediaID) {
		$conn = Connection::conn();
		$sql = sprintf("insert into FavoriteMedias(Media_id, User_id) values (%d, %d)",
			mysql_real_escape_string($mediaID),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('make media favorite failed');
		}
	}
	public function unfavorite($mediaID) {
		$conn = Connection::conn();
		$sql = sprintf("delete from FavoriteMedias where Media_id=%d and User_id=%d",
			mysql_real_escape_string($mediaID),
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('remove favorite media failed');
		}
	}
	public function addFriend($accountID) {
		if($this->id == $accountID){
			throw new Exception('cannot add self as friend');
		}
		$conn = Connection::conn();
		$sql = sprintf("insert into Friend (User_id1, User_id2) values (%d, %d)",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('add friend failed');
		}
	}
	public function deleteFriend($accountID) {
		if($this->id == $accountID){
			throw new Exception('cannot delete self as friend');
		}
		$conn = Connection::conn();
		$sql = sprintf("delete from Friend where User_id1=%d, User_id2=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($accountID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('delete friend failed');
		}
	}
	public function isWatchable($mediaID) {
		$media = Media::fromID($mediaID);
		if($this->id==$media->getUploadUserID()){// media owner
			return true; 
		}
		if($media->getShare()==2){// shared with everybody
			return true;
		} elseif ($media->getShare()==1){// shared with friends

			if(!$this->isFriend($media->getUploadUserID())){// not his friend
				return false;
			}
			// check whether in blocklist
			$conn = Connection::conn();
			$sql = sprintf("select * from MediaBlockView where Media_id=%d and User_id=%d",
					mysql_real_escape_string($mediaID),
					mysql_real_escape_string($this->id));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new Exception('query table MediaBlockView failed');
			}
			if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function isDownloadable($mediaID) {
		$media = Media::fromID($mediaID);
		if($this->id==$media->getUploadUserID()){// media owner
			return true; 
		}
		if($media->getDownloadType()==2){// downloadable by everybody
			return true;
		} elseif ($media->getDownloadType()==1){// downloadable by friends

			if(!$this->isFriend($media->getUploadUserID())){// not his friend
				return false;
			}
			// check whether in blocklist
			$conn = Connection::conn();
			$sql = sprintf("select * from MediaBlockDownload where Media_id=%d and User_id=%d",
					mysql_real_escape_string($mediaID),
					mysql_real_escape_string($this->id));
			$retval = mysql_query($sql, $conn);

			if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
				return true;
			} else {
				return false;
			}
		}
	}
	public function isFriend($friendID) {
		$conn = Connection::conn();
		$sql = sprintf("select * from Friend where User_id1=%d and User_id2=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($friendID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('check Friend failed');
		}

		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	public function delete() {
		//delete all files belonging to user
		$conn = Connection::conn();
/*		$sql = sprintf("select * from Media where upload_by_user_id=%d",
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select media for user query failed');
		}
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$media = new Media($row);
			$media->delete();
		}
*/	
		//delete account in database
		$sql = sprintf("delete from User where id=%d",
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('delete user account query failed.');
		}
	}
	
	/* static functions */
	public static function fromID($userID) {
		if(!(is_int($userID) || is_string($userID))) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select * from User where id=%d", 
			mysql_real_escape_string($userID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select user account query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			//no account with $userID found
			return false;
		}
		return new Account($row);
	}
	public static function fromEmail($email) {
		if(!(is_string($email))) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select * from User where email='%s'", 
			mysql_real_escape_string($email));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select user account query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			//no account with $email found
			return false;
		}
		return new Account($row);
	}
	public static function fromUsername($username) {
		if(!(is_string($username))) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select * from User where name='%s'", 
			mysql_real_escape_string($username));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select user account query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			//no account with $username found
			return false;
		}
		return new Account($row);
	}
	public static function getIDfromUsername($username) {
		if(!(is_string($username))) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select id from User where name='%s'", 
			mysql_real_escape_string($username));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select user account query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			//no account with $username found
			return false;
		}
		return $row['id'];
	}
	public static function isAccountExist($username) {
                if(!(is_string($username))) {
                        throw new InvalidArgumentException();
                }
                $conn = Connection::conn();
                $sql = sprintf("select * from User where name='%s'",
                        mysql_real_escape_string($username));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new sqlException('select user account query failed');
                }
                if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
                        //no account with $username found
                        return false;
                } else {
	                return true;
		}
        }
	public static function create($username, $email, $passwordhash, $passwordsalt, $fname, $lname, $sex, $dob) {
		$conn = Connection::conn();
		$sql = sprintf("insert into User " .
			"(name, email, passwordhash, passwordsalt, fname, lname, sex, dob) ".
			"values ".
			"('%s', '%s', '%s', %d, '%s', '%s', %d, '%s')",
			mysql_real_escape_string($username),
			mysql_real_escape_string($email),
			mysql_real_escape_string($passwordhash),
			mysql_real_escape_string($passwordsalt),
			mysql_real_escape_string($fname),
			mysql_real_escape_string($lname),
			mysql_real_escape_string($sex),
			mysql_real_escape_string($dob));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('insert user query failed');
		}
		$row['id'] =  mysql_insert_id();
		$row['name'] = $username;
		$row['email'] = $email;
		$row['passwordhash'] = $passwordhash;
		$row['passwordsalt'] = $passwordsalt;
		$row['fname'] = $fname;
		$row['lname'] = $lname;
		$row['sex'] = $sex;
		$row['dob'] = $dob;
		return new Account($row);
	}
	public static function getAllAccounts() {
		$conn = Connection::conn();
		$sql = sprintf('select * from User order by id');
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select all users query failed');
		}
		$results = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$results[] = new Account($row);
		}
		return $results;
	}
}
?>
