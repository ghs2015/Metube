<?php
require_once "function.php";

class WordCloud {
	private $str;
	private $keywords;
	
	public function __construct() {
		$this->keywords = $this->getData();
		$this->generateStr();
	}
	
	/* accessors */
	public function getString() {
		return $this->str;
	}
	public function generateStr() {
		$this->str = "[";
		foreach($this->keywords as $k => $v){
			if($this->str != "["){ // if not the first element, then add ',' before
				$this->str .= ",";
			}
			$s = sprintf("{\"text\":\"%s\",\"size\":%d}",$k,$v);
			$this->str .= $s;
		}
		$this->str .= "]";
	}
        public function getData() {
                $conn = Connection::conn();
                $sql = sprintf("select keyword from MediaKeywords");
                $retval = mysql_query($sql, $conn);
                if(!$retval) {
                        throw new Exception('select message query failed');
                }
		$keywords = array();
		$n = 0;
                while($row = mysql_fetch_array($retval, MYSQL_ASSOC)) {
			$n++;
			if(array_key_exists($row['keyword'],$keywords)){
				$keywords[$row['keyword']] ++;
			} else {
				$keywords[$row['keyword']] = 1;
			}
                }
		foreach($keywords as $k => $v){
			$keywords[$k] = ceil($v/$n * 800);
		}
                return $keywords;
        }
}
