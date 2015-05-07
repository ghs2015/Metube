<?php
class Connection {	
	public static function conn() {
		$dbhost = 'mysql1.cs.clemson.edu';
		$dbuser = 'mtbprjct_80tr';
		$dbpass = '1liuhua2zhuozhao';
		$dbname = 'metubeproject_2squ';

		$conn = mysql_connect($dbhost, $dbuser, $dbpass);
		if(!$conn) {
			throw new sqlException('connect to database failed');
		}
		if(!mysql_select_db($dbname)) {
			throw new sqlException('select table in database failed');
		}
		return $conn;
	}
}
?>
