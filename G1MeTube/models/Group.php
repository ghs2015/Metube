<?php
require_once "models/Topic.php";
require_once "Connection.php";

class Group { 
	private $id;
	private $name;
	private $date;
	private $User_id;

	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->date = $row['date'];
		$this->User_id = $row['User_id'];
	}

	/* accessors */
	public function getID() {
		return $this->id;
	}
	public function getName() {
		return $this->name;
	}
	public function getDate() {
		return $this->date;
	}
	public function getUserId() {
		return $this->User_id;
	}
	public function contain($userID){
		$conn = Connection::conn();
                $sql = sprintf("select * from GroupUsers where Group_id=%d and User_id=%d",
                       mysql_real_escape_string($this->id),
                       mysql_real_escape_string($userID));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select group query failed');
                }
                if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
                        //no result found
                        return false;
                } else {
			return true;
		}
	}
	public function getAllUsers(){
		$conn = Connection::conn();
		$sql = sprintf("select User_id from GroupUsers where Group_id=%d",
				mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('get groupusers query failed');
		}
		$results = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$results[] = Account::fromID($row['User_id']);
		}
		return $results;
	}
	public function addUser($userID){
		if($this->contain($userID)){
			return;
		} else {
			$conn = Connection::conn();
			$sql = sprintf("insert into GroupUsers (Group_id, User_id) values (%d, %d)",
			       mysql_real_escape_string($this->id),
			       mysql_real_escape_string($userID));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new Exception('insert groupusers query failed');
			}
		}
	}
	public function deleteUser($userID){
		if(!$this->contain($userID)){
			return;
		} else {
			$conn = Connection::conn();
			$sql = sprintf("delete from GroupUsers where Group_id=%d and User_id=%d",
			       mysql_real_escape_string($this->id),
			       mysql_real_escape_string($userID));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new Exception('delete groupusers query failed');
			}
		}
	}
        public function getTopic() {
                $conn = Connection::conn();
                $sql = sprintf("select * from Topic where Group_id=%d",
                        mysql_real_escape_string($this->id));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select topic query failed');
                }
                $topics = array();
                while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
                        $topics[] = new Topic($row);
                }
                return $topics;
        }	
	/* static functions */
        public static function fromID($groupID) {
                if(!(is_int($groupID) || is_string($groupID))) {
                        throw new InvalidArgumentException();
                }
                $conn = Connection::conn();
                $sql = sprintf("select * from `Group` where id=%d",
                        mysql_real_escape_string($groupID));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select group query failed');
                }
                if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
                        //no account with $userID found
                        return false;
                }
                return new Group($row);
        }
	public static function getUserGroups($userID){
		$conn = Connection::conn();
		$sql = sprintf("select * from `GroupUsers` where User_id=%d", $userID);
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
                        throw new Exception('select group query failed');
                }
		$groups = array();
                while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$groups[] = Group::fromID($row['Group_id']);
                }
                return $groups;
	}
	public static function getAllGroups(){
		$conn = Connection::conn();
		$sql = sprintf("select * from `Group`");
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
                        throw new Exception('select group query failed');
                }
		$groups = array();
                while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$groups[] = new Group($row);
                }
                return $groups;
	}
        public function delete() {
                $conn = Connection::conn();
                $sql = sprintf("delete from `Group` where id=%d",
                        mysql_real_escape_string($this->id));
                $retval = mysql_query($sql, $conn);
                if(!$retval){ 
                        throw new Exception('delete user account query failed.');
                }
        }
	public static function create($row) {
		$conn = Connection::conn();
		$sql = sprintf("insert into `Group`(name, date, User_id) values('%s', now(), %d)",
			mysql_real_escape_string($row['name']),
			mysql_real_escape_string($row['User_id']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('create group failed');
		}
		$groupID = mysql_insert_id();

		// add creator to GroupUsers
		$sql = sprintf("insert into `GroupUsers`(Group_id, User_id) values(%d, %d)",
                        mysql_real_escape_string($groupID),
                        mysql_real_escape_string($row['User_id']));
		if(!$retval) {
                        throw new Exception('insert GroupUsers failed');
                }
		return $groupID;
	}
}
?>
