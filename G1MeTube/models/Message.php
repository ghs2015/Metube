<?php

class Message {
	private $id;
	private $message;
	private $date;
	private $isRead;
	private $User_id1;
	private $User_id2;
	
	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->message = $row['message'];
		$this->date = $row['date'];
		$this->isRead = $row['isRead'];
		$this->User_id1 = $row['User_id1'];
		$this->User_id2 = $row['User_id2'];
	}
	
	/* accessors */
	public function getID() {
		return $this->id;
	}
	public function getMessage() {
		return $this->message;
	}
	public function getDate() {
		return $this->date;
	}
	public function getIsRead() {
		return $this->isRead;
	}
	public function getSenderID() {
		return $this->User_id1;
	}
	public function getRecipientID() {
		return $this->User_id2;
	}
	
	/* static functions */
	public static function create($row) {
		$conn = Connection::conn();
		$sql = sprintf("insert into Message(message, date, User_id1, User_id2) values('%s', now(), %d, %d)",
			mysql_real_escape_string($row['message']),
			mysql_real_escape_string($row['User_id1']),
			mysql_real_escape_string($row['User_id2']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('send message failed');
		}
		return mysql_insert_id();
	}
        public static function fromID($messageID) {
                $conn = Connection::conn();
                $sql = sprintf("select * from Message where id=%d",
                        mysql_real_escape_string($messageID));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select message query failed');
                }
                if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
                        //no account with $userID found
                        return false;
                }
                return new Message($row);
        }
}
