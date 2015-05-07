<?php
include_once "phpseclib.php";
include_once "models/Account.php";
include_once "sshconfig.php";
function user_exist_check ($username, $password){
	$query = "select * from account where name='$username'";
	$result = mysql_query( $query );
	if (!$result){
		die ("user_exist_check() failed. Could not query the database: <br />". mysql_error());
	}	
	else {
		$row = mysql_fetch_assoc($result);
		if($row == 0){
			$query = "insert into account values ('$username','$password')";
			echo "insert query:" . $query;
			$insert = mysql_query( $query );
			if($insert)
				return 1;
			else
				die ("Could not insert into the database: <br />". mysql_error());		
		}
		else{
			return 2;
		}
	}
}


function user_pass_check($username, $password)
{
        if(!($account = Account::fromUsername($username))){
                return 1;//username not found
        }
        //hash password and compare with stored hash
        $salt = $account->getPasswordSalt();
        $passwordhash = hash('sha256', $password.$salt);
        if($passwordhash != $account->getPasswordHash()) {
                return 2;//incorrect password
        } else {
                //login successful
		$_SESSION['myAccount'] = serialize($account);
                return 0;
        }
	
}

function updateMediaTime($mediaid)
{
	$query = "	update  media set lastaccesstime=NOW()
   						WHERE '$mediaid' = mediaid
					";
					 // Run the query created above on the database through the connection
    $result = mysql_query( $query );
	if (!$result)
	{
	   die ("updateMediaTime() failed. Could not query the database: <br />". mysql_error());
	}
}

function upload_error($result)
{
	//view erorr description in http://us2.php.net/manual/en/features.file-upload.errors.php
	switch ($result){
	case 1:
		return "UPLOAD_ERR_INI_SIZE";
	case 2:
		return "UPLOAD_ERR_FORM_SIZE";
	case 3:
		return "UPLOAD_ERR_PARTIAL";
	case 4:
		return "UPLOAD_ERR_NO_FILE";
	case 5:
		return "File has already been uploaded";
	case 6:
		return  "Failed to move file from temporary directory";
	case 7:
		return  "Upload file failed";
	}
}

function other()
{
	//You can write your own functions here.
}

function trimAndEscape($data) {
	$data = trim($data);
	$data = htmlspecialchars($data);
	return $data;
}

function getFileExt($filename) {
	//get file extension
	$fileNameComponents = explode('.', $filename);
	$fileExt = end($fileNameComponents);
	$fileExt = strtolower($fileExt);
	if(count($fileNameComponents) <= 1) {
		return false;
	} else {
		return $fileExt;
	}

}

function getMediaType($typeStr){
	if(strpos($typeStr, 'image')!==false) {
		return 0;
	} else if(strpos($typeStr, 'audio')!==false) {
		return 1;
	} else if(strpos($typeStr, 'video')!==false) {
		return 2;
	} else if(strpos($typeStr, 'flash')!==false) {
		return 2;
	} else {
		return -1;
	}
}

function convertVideo($name,$ext){
	$srcFolder = '/web/home/liuhuac/public_html/myMeTube/public_html/uploads/';
	$destFolder = '/web/home/liuhuac/public_html/myMeTube/public_html/videos/';

	$src = $srcFolder.$name.'.'.$ext;

	$oggFolder = $destFolder.'ogv/';
	$webmFolder = $destFolder.'webm/';
	$mp4Folder = $destFolder.'mp4/';


	if(file_exists("$oggFolder$name.ogv") ||
		file_exists("$webmFolder$name.webm") ||
		file_exists("$mp4Folder$name.mp4")){
		return false;
	}


	$ogg = "ffmpeg -i $src -acodec libvorbis -ac 2 -ab 96k -ar 44100 -b 345k -s 640x360 $oggFolder$name.ogv";
	$webm = "ffmpeg -i $src -acodec libvorbis -ac 2 -ab 96k -ar 44100 -b 345k -s 640x360 $webmFolder$name.webm";
	$mp4 = "ffmpeg -i $src -vcodec libx264 -acodec aac -strict -2 -s 640x360 $mp4Folder$name.mp4";


	$connection = ssh2_connect(ssh::$host);
	ssh2_auth_password($connection, ssh::$username, ssh::$password);
	ssh2_exec($connection, $ogg);
	ssh2_exec($connection, $webm);
	ssh2_exec($connection, $mp4);

	$webServerPath = '/home/liuhuac/public_html/myMeTube/public_html/videos';

	chmod("$webServerPath/ogv/$name.ogv",0644);
	chmod("$webServerPath/webm/$name.webm",0644);
	chmod("$webServerPath/mp4/$name.mp4",0644);

	return true;
}

?>
