<?php
require_once 'models/Media.php';
require_once 'models/Account.php';

class Searcher {
	//the input string
	private $searchString;
	
	//the input string divided into tokens
	private $searchTokens;
	
	//Media results
	private $results;
	
	//find all media with part of the title or description matching at least one search token
	//or with at least one entire keyword matching at least one search token
	public function searchAdvanced($row){
	}
	public function search($searchString) {
		if(!is_string($searchString))
			throw new InvalidArgumentException();
		$searchString = trim(htmlspecialchars($searchString));
		$this->searchString = $searchString;
		
		//filter explode result to keep out empty strings
		$this->searchTokens = array_filter(explode(' ', $searchString));
		
		$this->results = array();
		
		//match keywords
		//store media ID's with Media_id as key and boolean true as value
		//with this method, no Media_id can be put into Media_ids more than once
		$Media_ids = array();
		foreach($this->searchTokens as $token) {
			$conn = Connection::conn();
			//match keywords
			//token must match whole keyword
			$escToken = mysql_real_escape_string($token);
			$sql = sprintf("select distinct Media_id from MediaKeywords where ".
				"keyword rlike ' %s ' or keyword rlike ' %s$' or keyword rlike '^%s ' or keyword rlike '^%s$'",
				$escToken, $escToken, $escToken, $escToken);
			$retval = mysql_query($sql, $conn);
			if(!$retval) {
				throw new Exception('select Media_id from keyword query failed');
			}
			while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
				$Media_ids[$row['Media_id']] = true;
			}
		}
		
		//match title or description
		foreach($this->searchTokens as $token) {
			//token must match whole word in title or description
			$sql = sprintf("select id from Media where ".
				"title rlike ' %s ' or title rlike ' %s$' or title rlike '^%s ' or title rlike '^%s$' or ".
				"description rlike ' %s ' or description rlike ' %s$' or description rlike '^%s ' or description rlike '^%s$'",
				$escToken, $escToken, $escToken, $escToken,
				$escToken, $escToken, $escToken, $escToken);
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
	
	public function getSearchString() {
		return $this->searchString;
	}
	
	//returns results in no particular order
	public function getResults() {
		return $this->results;
	}
}
?>
