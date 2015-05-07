<?php
require_once 'Media.php';

class Browser {
	private $resultsPerPage = 5;
	private $category = 0;
	private $sorttype = 0;
	private $page = 1;

	public function __construct($category = 0, $page = 1, $sorttype = 0) {
		//validate arguments before setting corresponding instance vars
		$this->category = $category;
		$this->sorttype = $sorttype;
		if(intval($page) >= 1 && intval($page) <= $this->getNumPages()) {
			$this->page = $page;
		}
	}
	
	public function getPage() {
		return $this->page;
	}
	public function getSelectedCategory() {
		return $this->category;
	}

	public function getTotalNumResults() {
		$conn = Connection::conn();
		if($this->category == 0) { // 0 for any
			$sql = "select count(*) as count from Media order by date desc";
		} else {
			$sql = sprintf("select count(*) as count from Media where category=%d order by date desc",
				$this->category-1);
		}
		$retval = mysql_query($sql, $conn);
		if(!$retval) {
			throw new sqlException('count num browse results failed');
		}
		$row = mysql_fetch_array($retval, MYSQL_ASSOC);
		return intval($row['count']);
	}
	
	public function getNumPages() {
		return intval(ceil($this->getTotalNumResults()/$this->resultsPerPage));
	}

	public function getResults() {
		$conn = Connection::conn();
		if($this->sorttype == 0){
			$sort = 'order by date desc';
		} else if($this->sorttype == 1){
			$sort = 'order by view_count desc';
		} else {
			$sort = 'order by rating desc';
		}
		if($this->category == 0) {
			$sql = sprintf("select * from Media %s limit %d,%d",
				$sort,
				($this->page-1)*$this->resultsPerPage,
				$this->resultsPerPage);
		} else {
			$sql = sprintf("select * from Media where category=%d %s limit %d,%d",
				$this->category-1,
				$sort,
				($this->page-1)*$this->resultsPerPage,
				$this->resultsPerPage);
		}
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
