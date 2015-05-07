<?php
require_once 'MediaComment.php';
require_once 'Connection.php';

class Media  {
	private $id;
	private $title;
	private $description;
	private $category;
	private $extension;
	private $type;
	private $share_type;
	private $download_type;
	private $allow_discussion;
	private $allow_rating;
	private $view_count;
	private $date;
	private $rating;
	private $filename;
	private $path;
	private $User_id;

	public function __construct(array $row) {
		$this->id = $row['id'];
		$this->title = $row['title'];
		$this->description = $row['description'];
		$this->category = $row['category'];
		$this->extension = $row['extension'];
		$this->type = $row['type'];
		$this->share_type = $row['share_type'];
		$this->download_type = $row['download_type'];
		$this->allow_discussion = $row['allow_discussion'];
		$this->allow_rating = $row['allow_rating'];
		$this->view_count = $row['view_count'];
		$this->date = $row['date'];
		if(isset($row['rating'])){
			$this->rating = $row['rating'];
		}
		$this->filename = $row['filename'];
		$this->path = $row['path'];
		$this->User_id = $row['User_id'];
	}
	
	/* accessors */
	public function getID() {
		return $this->id;
	}
	public function getTitle() {
		return $this->title;
	}
	public function getDescription() {
		return $this->description;
	}
	public function getShortDescription() {
		return substr($this->description,0,40).'...';
	}
	public function getCategory() {
		return $this->category;
	}
	public function getExtension() {
		return $this->extension;
	}
	public function getType() {
		return $this->type;
	}
	public function getShare() {
		return $this->share_type;
	}
	public function getDownloadType() {
		return $this->download_type;
	}
	public function getDiscussion() {
		return $this->allow_discussion;
	}
	public function getRating() {
		return $this->allow_rating;
	}
	public function getRatingScore() {
		return $this->rating;
	}
	public function getView() {
		return $this->view_count;
	}
	public function getDate() {
		return $this->date;
	}
	public function getFilename() {
		return $this->filename;
	}
	public function getPath() {
		return $this->path;
	}
	public function getUploadUserID() {
		return $this->User_id;
	}
	public function updateRating() {
		$conn = Connection::conn();
		$sql = sprintf("select avg(rating) from MediaRatings where Media_id=%d",
				mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('select MediaRatings query failed');
		}
		$average = 0;
		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$average = $row['avg(rating)'];
		}
		$sql = sprintf("update Media set rating=%.1F where id=%d",
				mysql_real_escape_string($average),
				mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('update Media query failed');
		}
		$this->rating = $average;
	}
	public function addRate($rate,$userID) {
		$conn = Connection::conn();
		$sql = sprintf("insert into MediaRatings (rating,Media_id,User_id) values (%d,%d,%d)",
			mysql_real_escape_string($rate),
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($userID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('insert MediaRatings query failed');
		}

		$this->updateRating();
	}
	public function isUserRate($userID) {
		$conn = Connection::conn();
		$sql = sprintf("select * from MediaRatings where Media_id=%d and User_id=%d",
			mysql_real_escape_string($this->id),
			mysql_real_escape_string($userID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('select MediaRatings query failed');
		}

		if($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			return true;
		} else {
			return false;
		}
	}
	public function getComments() {
		$conn = Connection::conn();
		$sql = sprintf("select * from MediaComment where Media_id=%d order by date desc",
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select comments query failed');
		}
		$comments = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$comments[] = new MediaComment($row);
		}
		return $comments;
	}
	public function increaseViewCount() {
		$conn = Connection::conn();
		$sql = sprintf("update Media set view_count=view_count+1 where id=%d",
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('update view_count failed');
		}
		$this->view_count++;
	}
	
	/* mutators */
	public function addComment($row) {
		$row['Media_id'] = $this->id;
		return MediaComment::create($row);
	}
	public function deleteComment($row) {
		return MediaComment::delete($row);
	}
	public function delete() {
		//delete media entry
		//keywords will automatically delete by cascade relationship
		$conn = Connection::conn();
		$sql = sprintf("delete from Media where id='%d'", 
			mysql_real_escape_string($this->id));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('delete media query failed');
		}
		
		//delete file on server
		if(file_exists($this->path) && !unlink($this->path)) {
			//if deleting file failed, create an exception and log it, but don't halt execution
			$exc = new ServerFailedException(sprintf('could not delete media %s for deleted user account.', $this->path));
			Log::logException($exc);
		}
	}
	
	/* static functions */
	public static function fromID($mediaID) {
		if(!(is_int($mediaID) || is_string($mediaID))) {
			throw new InvalidArgumentException();
		}
		$conn = Connection::conn();
		$sql = sprintf("select * from Media where id=%d", 
			mysql_real_escape_string($mediaID));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('select media query failed');
		}
		if(!($row = mysql_fetch_array($retval, MYSQL_ASSOC))) {
			//no media with $mediaID found
			return false;
		}
		//construct and return media
		return new Media($row);
	}
	public static function create($row) {
		//insert media entry
		$conn = Connection::conn();
		$sql = sprintf("insert into Media (title, description, category, extension, type, share_type, download_type, allow_discussion, allow_rating, filename, path, User_id, date) ".
			"values ('%s', '%s', %d, '%s', %d, %d, %d, %d, %d, '%s', '%s', %d, now())",
			mysql_real_escape_string($row['title']),
			mysql_real_escape_string($row['description']),
			mysql_real_escape_string($row['category']),
			mysql_real_escape_string($row['extension']),
			mysql_real_escape_string($row['type']),
			mysql_real_escape_string($row['share_type']),
			mysql_real_escape_string($row['download_type']),
			mysql_real_escape_string($row['allow_discussion']),
			mysql_real_escape_string($row['allow_rating']),
			mysql_real_escape_string($row['filename']),
			mysql_real_escape_string($row['path']),
			mysql_real_escape_string($row['User_id']));
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('inserting media sql entry failed');
		}
		$mediaID = mysql_insert_id();
		
		//insert keyword entries
		foreach($row['keywords'] as $keyword) {
			$sql = sprintf("insert into MediaKeywords (Media_id, keyword) values (%d, '%s')",
				mysql_real_escape_string($mediaID),
				mysql_real_escape_string($keyword));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				//clean up sql media entry and throw exception
				$sql = sprintf("delete from Media where id='%d'", 
					mysql_real_escape_string($mediaID));
				mysql_query($sql, $conn);
				throw new sqlException('insert media keyword query failed');
			}
		}
		
		//finally, upload file
		$media = Media::fromID($mediaID);
		//return id of just-inserted media
		return $media;
	}
	//Get array of all possible categories for a media
	//e.g. Gaming, Entertainment, Blogs, etc.
	public static function getCategories() {
		$conn = Connection::conn();
		$sql = "select COLUMN_TYPE from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = 'Media' and COLUMN_NAME = 'category'";
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('get categories query failed');
		}
		$row = mysql_fetch_array($retval);
		$categories = str_replace("'", "", substr($row['COLUMN_TYPE'], 5, (strlen($row['COLUMN_TYPE'])-6)));
		$categories = explode(",", $categories);
		return $categories;
	}

	public function getRecommendation() {
		$conn = Connection::conn();
			$sql = sprintf("select * from Media where category=%d order by rand() limit 5",	$this->category);
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			//throw new Exception('get browse results query failed');
			return array();
		}
		$results = array();
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$results[] = new Media($row);
		}
		return $results;
	}
}
?>
