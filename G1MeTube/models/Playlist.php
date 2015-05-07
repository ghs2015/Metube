<?php
require_once 'Media.php';

class Playlist {
	private $id;
	private $name;
	private $User_id;
	
	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->User_id = $row['User_id'];
	}
	
	/* accessors */
	public function getID() {
		return $this->id;
	}
	public function getName() {
		return $this->name;
	}
	public function getUserID() {
		return $this->User_id;
	}
	//return media in no particular order
	public function getMedia() {
		$conn = Connection::conn();
		//get media id's
		$sql = sprintf("select Media_id from PlaylistMedias where Playlist_id=%d", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select playlist media ids query failed');
		}
		$Media_ids = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$Media_ids[] = $row['Media_id'];
		}
		
		//get media for id's
		$media = array();
		foreach($Media_ids as $Media_id) {
			$sql = sprintf("select * from Media where id=%d", 
				mysql_real_escape_string($Media_id));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new sqlException('select playlist media query failed');
			}
			if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
				$media[] = new Media($row);
			} else {
				throw new InternalConsistencyException('PlaylistMediais had Media_id not found in Media');
			}
		}
		
		return $media;
	}
	public function containsMedia($Media_id) {
		if(!is_string($Media_id) && !is_int($Media_id)) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select * from PlaylistMedias where Playlist_id=%d and Media_id=%d", 
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($Media_id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select media failed');
		}
		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	
	/* mutators */
	public function addMedia($Media_id) {
		if(!is_string($Media_id) && !is_int($Media_id)) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		//get media id's
		$sql = sprintf("insert into PlaylistMedias(Playlist_id, Media_id) values(%d, %d)", 
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($Media_id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('add media to playlist failed');
		}
	}
	public function removeMedia($Media_id) {
		if(!is_string($Media_id) && !is_int($Media_id)) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("delete from PlaylistMedias where Playlist_id=%d and Media_id=%d", 
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($Media_id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException("remove media from playlist failed");
		}
	}
	public function delete() {
		$conn = Connection::conn();
		$sql = sprintf("delete from Playlist where id=%d",
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('delete playlist failed');
		}
		//PlaylistMedias entries will automatically delete by cascade
	}
	
	/* static functions */
	public static function fromID($Playlist_id) {
		if(!(is_int($Playlist_id) || is_string($Playlist_id))) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select * from Playlist where id=%d", 
			mysql_real_escape_string($Playlist_id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select playlist query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			//no playlist with $Playlist_id found
			return false;
		}
		return new Playlist($row);
	}
	public static function create($row) {
		$conn = Connection::conn();
		$sql = sprintf("insert into Playlist(User_id, name) values(%d, '%s')",
			mysql_real_escape_string($row['User_id']),
			mysql_real_escape_string($row['name']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('create playlist failed');
		}
		return mysql_insert_id();
	}
}
?>
