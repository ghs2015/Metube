<?php
require_once 'models/Media.php';
require_once 'models/Account.php';

class AdvancedSearcher {
	
	//the input string divided into tokens
	private $wordTokens;
	private $authorTokens;
	
	//Media results
	private $results;
	
	//find all media with part of the title or description matching at least one search token
	//or with at least one entire keyword matching at least one search token
	public function searchAdvanced($row) {

		if(isset($row['words'])){
			if(!is_string($row['words']))
				throw new InvalidArgumentException();
			$words = trim(htmlspecialchars($row['words']));
			//filter explode result to keep out empty strings
			$this->wordTokens = array_filter(explode(' ', $words));
		} else {
			$this->searchWithoutWords($row);
			return;
		}

		if(isset($row['category'])){
			if($row['category']!=0){
				$category = sprintf(" and category=%d", $row['category']-1);// 0 for Any
			} else {
				$category = '';
			}
		} else {
			$category = '';
		}

		if(isset($row['extension'])){
			if($row['extension']!='Any'){
				$extension = sprintf(" and extension='%s'", $row['extension']);
			} else {
				$extension = '';
			}
		} else {
			$extension = '';
		}

		if(isset($row['author'])){
			if($account = Account::fromUsername($row['author'])){
				$author = sprintf(" and User_id=%d", $account->getID());
			} else {
				$this->results = array();
				return;
			}
		} else {
			$author = '';
		}

		if(isset($row['type'])){
			if($row['type']!=0){
				$type = sprintf(" and type=%d", $row['type']-1);// 0 for Any
			} else {
				$type = '';
			}
		} else {
			$type = '';
		}

		if(isset($row['dateA'])){
			$dateA = sprintf(" and date>%s", $row['dateA']);
		} else {
			$dateA = '';
		}

		if(isset($row['dateB'])){
			$dateB = sprintf(" and date<%s", $row['dateB']);
		} else {
			$dateB = '';
		}

		$all = $category . $extension . $author . $type . $dateA . $dateB;

		$this->results = array();
		
		//match keywords
		//store media ID's with Media_id as key and boolean true as value
		//with this method, no Media_id can be put into Media_ids more than once
		$Media_ids = array();
		foreach($this->wordTokens as $token) {
			$conn = Connection::conn();
			//match keywords
			//token must match whole keyword
			$escToken = mysql_real_escape_string($token);
			$sql = sprintf("select distinct MediaKeywords.Media_id from Media LEFT JOIN MediaKeywords ON Media.id = MediaKeywords.Media_id where ".
				"keyword rlike ' %s ' or keyword rlike ' %s$' or keyword rlike '^%s ' or keyword rlike '^%s$'",
				$escToken, $escToken, $escToken, $escToken);
			$sql .= $all;
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new Exception('select Media_id from keyword query failed');
			}
			while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
				$Media_ids[$row['Media_id']] = true;
			}
		}
		
		//match title or description
		foreach($this->wordTokens as $token) {
			//token must match whole word in title or description
			$sql = sprintf("select id from Media where ".
				"title rlike ' %s ' or title rlike ' %s$' or title rlike '^%s ' or title rlike '^%s$' or ".
				"description rlike ' %s ' or description rlike ' %s$' or description rlike '^%s ' or description rlike '^%s$'",
				$escToken, $escToken, $escToken, $escToken,
				$escToken, $escToken, $escToken, $escToken);
			$sql .= $all;
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new sqlException('select media matching search query failed');
			}
			while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
				$Media_ids[$row['id']] = true;
			}
		}
		
		//convert IDs to media
		foreach($Media_ids as $Media_id => $dummy) {
			$sql = sprintf("select * from Media where id='%s'",
				mysql_real_escape_string($Media_id));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new sqlException('select media from Media_id failed');
			}
			$row = mysql_fetch_array($retval, MYSQL_ASSOC);
			if(!$row) {
				$e = new InternalConsistencyException('found Media_id in MediaKeywords with no matching media item');
				Log::logException($e);
			} else {
				$this->results[] = new Media($row);
			}
		}
	}
	
	
	//returns results in no particular order
	public function getResults() {
		return $this->results;
	}

	public function searchWithoutWords($row){
		if(isset($row['category'])){
			if($row['category']!=0){
				$category = sprintf(" and category=%d", $row['category']-1);// 0 for Any
			} else {
				$category = '';
			}
		} else {
			$category = '';
		}

		if(isset($row['extension'])){
			if($row['extension']!='Any'){
				$extension = sprintf(" and extension='%s'", $row['extension']);
			} else {
				$extension = '';
			}
		} else {
			$extension = '';
		}

		if(isset($row['author'])){
			if($account = Account::fromUsername($row['author'])){
				$author = sprintf(" and User_id=%d", $account->getID());
			} else {
				$this->results = array();
				return;
			}
		} else {
			$author = '';
		}

		if(isset($row['type'])){
			if($row['type']!=0){
				$type = sprintf(" and type=%d", $row['type']-1);// 0 for Any
			} else {
				$type = '';
			}
		} else {
			$type = '';
		}

		if(isset($row['dateA'])){
			$dateA = sprintf(" and date>%s", $row['dateA']);
		} else {
			$dateA = '';
		}

		if(isset($row['dateB'])){
			$dateB = sprintf(" and date<%s", $row['dateB']);
		} else {
			$dateB = '';
		}

		$all = $category . $extension . $author . $type . $dateA . $dateB;

		$this->results = array();

		//match keywords
		//store media ID's with Media_id as key and boolean true as value
		//with this method, no Media_id can be put into Media_ids more than once
		$Media_ids = array();

		if(substr($all,0,4)==' and'){
			$all = substr($all,4);
		}
		if(empty($all)){
			return;
		}
		$sql = "select id from Media where ". $all;
		$conn = Connection::conn();
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new Exception('select media matching search query failed');
		}
		while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$Media_ids[$row['id']] = true;
		}
		//convert IDs to media
		foreach($Media_ids as $Media_id => $dummy) {
			$sql = sprintf("select * from Media where id='%s'",
					mysql_real_escape_string($Media_id));
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new sqlException('select media from Media_id failed');
			}
			$row = mysql_fetch_array($retval, MYSQL_ASSOC);
			if(!$row) {
				$e = new InternalConsistencyException('found Media_id in MediaKeywords with no matching media item');
				Log::logException($e);
			} else {
				$this->results[] = new Media($row);
			}
		}
	}
}
?>
