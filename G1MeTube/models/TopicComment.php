<?php

class TopicComment  { 
	private $id;
	private $comment;
	private $date;
	private $Topic_id;
	private $User_id;

	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->comment = $row['comment'];
		$this->date = $row['date'];
		$this->Topic_id = $row['Topic_id'];
		$this->User_id = $row['User_id'];
	}

	/* accessors */
	public function getID() {
                return $this->id;
        }
	public function getComment() {
		return $this->comment;
	}
	public function getDate() {
		return $this->date;
	}
	public function getTopicId() {
		return $this->Topic_id;
	}
	public function getUserId() {
		return $this->User_id;
	}
	
	/* static functions */
	public static function create($row) {
		$conn = Connection::conn();
		$sql = sprintf("insert into TopicComment (comment, date, Topic_id, User_id) values('%s', now(), %d, %d)",
			mysql_real_escape_string($row['comment']),
			mysql_real_escape_string($row['Topic_id']),
			mysql_real_escape_string($row['User_id']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('create topiccomment failed');
		}
		return mysql_insert_id();
	}
}
?>
