<?php

class MediaComment  { 
	private $id;
	private $comment;
	private $date;
	private $Media_id;
	private $User_id;

	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->comment = $row['comment'];
		$this->date = $row['date'];
		$this->Media_id = $row['Media_id'];
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
	public function getMediaId() {
		return $this->Media_id;
	}
	public function getUserId() {
		return $this->User_id;
	}
	
	public static function delete($row) {
		$conn = Connection::conn();
		$sql = sprintf("delete from MediaComment where id=%d",
			mysql_real_escape_string($row['id']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('delete comment failed');
		}
	}

	/* static functions */
	public static function create($row) {
		$conn = Connection::conn();
		$sql = sprintf("insert into MediaComment (comment, date, Media_id, User_id) values('%s', now(), %d, %d)",
			mysql_real_escape_string($row['comment']),
			mysql_real_escape_string($row['Media_id']),
			mysql_real_escape_string($row['User_id']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('create comment failed');
		}
		return mysql_insert_id();
	}
}
?>
