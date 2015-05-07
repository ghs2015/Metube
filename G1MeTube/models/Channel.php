<?php
require_once 'models/Account.php';
require_once 'models/Media.php';

class Channel {
	private $ownerAccount;
	
	public function __construct(Account $account) {
		$this->ownerAccount = $account;
	}
	
	/* accessors */
	public function getOwnerAccount() {
		return $this->ownerAccount;
	}
	public static function fromID($ChannelID) {
		return new Channel(Account::fromID($ChannelID));
	}
	//return media in no particular order
	public function getMedia() {
		$conn = Connection::conn();
		$sql = sprintf("select * from Media where User_id=%d", 
			mysql_real_escape_string($this->ownerAccount->getID()));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('select media query failed');
		}
		$media = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$media[] = new Media($row);
		}
		return $media;
	}
}
?>
