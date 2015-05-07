<?php
require_once "models/TopicComment.php";
require_once "Connection.php";

class Topic { 
	private $id;
	private $title;
	private $date;
	private $Group_id;
	private $User_id;

	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->title = $row['title'];
		$this->date = $row['date'];
		$this->Group_id = $row['Group_id'];
		$this->User_id = $row['User_id'];
	}

	/* accessors */
	public function getID() {
		return $this->id;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getDate() {
		return $this->date;
	}
	public function getGroupId() {
		return $this->Group_id;
	}
	public function getUserId() {
		return $this->User_id;
	}
	
        public function getTopicComments() {
                $conn = Connection::conn();
                $sql = sprintf("select * from TopicComment where Topic_id=%d order by date asc",
                        mysql_real_escape_string($this->id));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select topiccomment query failed');
                }
                $topiccomments = array();
                while($row = mysql_fetch_array($retval)) {
                        $topiccomments[] = new TopicComment($row);
                }
                return $topiccomments;
        }

        public function postComment($row) {
		$row['Topic_id'] = $this->id;
                return TopicComment::create($row);
        }

        public static function fromID($topicID) {
                if(!is_int($topicID) and !is_string($topicID)) {
                        throw new InvalidArgumentException();
                }
                $conn = Connection::conn();
                $sql = sprintf("select * from Topic where id=%d",
                        mysql_real_escape_string($topicID));
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select topic query failed');
                }
                if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
                        return false;
                }
                return new Topic($row);
        }

	/* static functions */
	public static function create($row) {
		$conn = Connection::conn();
		$sql = sprintf("insert into Topic (title, date, Group_id, User_id) values('%s', now(), %d, %d)",
			mysql_real_escape_string($row['title']),
			mysql_real_escape_string($row['Group_id']),
			mysql_real_escape_string($row['User_id']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('create topic failed');
		}
		$topicID = mysql_insert_id();
		if(empty($row['comment'])){
			throw new Exception('comment is empty when creating topic');
		}
		unset($row['title']);
		unset($row['Group_id']);
		$row['Topic_id'] = $topicID;
		TopicComment::create($row);
		return $topicID;
	}
}
?>
