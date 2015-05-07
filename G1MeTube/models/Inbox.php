<?php
require_once 'models/Thread.php';

class Inbox {
	private $account;

	public function __construct(Account $account) {
		$this->account = $account;
	}
	
	public function getThreads() {
		$conn = Connection::conn();
		$sql = sprintf("select * from MessageThreads where user1ID=%d or user2ID=%d",
			mysql_real_escape_string($this->account->getID()),
			mysql_real_escape_string($this->account->getID()));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('select message threads query failed.');
		}
		$threads = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$threads[] = new Thread($row);
		}
		return $threads;
	}
}
?>
