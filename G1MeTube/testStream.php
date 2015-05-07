<?php
require_once 'models/VideoStream.php';

if(isset($_GET['name']) && isset($_GET['type']) && isset($_GET['extension'])) {
	$filename = $_GET['name'].addslashes('.').$_GET['extension'];

	if($_GET['type'] == 0) {
		$path = 'uploads/'.$filename;
		if(file_exists($path)){
			$filesize = sprintf("%u", filesize($path));
			header('Content-type: image/'.$_GET['extension']);
			header("Content-Length: $filesize");
			readfile($path);
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	} else if($_GET['type'] == 1) {
		$path = 'uploads/'.$filename;
		if(file_exists($path)){
			$stream = new VideoStream($path, 'audio/'.$_GET['extension']);
			$stream->start();
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	} else if($_GET['type'] == 2) {
		$path = 'videos/'.$_GET['extension'].'/'.$filename;
		if(file_exists($path)){
			$stream = new VideoStream($path, 'video/'.$_GET['extension']);
			$stream->start();
		} else {
			header("HTTP/1.1 404 Not Found");
		}
	} else {
		throw new InvalidStateException('unrecognized media type');
	}
}
?>
