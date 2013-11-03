<?php
function dbconnect()
{
	$con = mysql_connect("127.0.0.1", "root", "password");
	mysql_select_db("regional", $con);
	if(!$con)
	{
		die("Connection Failed!");
	}
	else{
		return $con;
	}
}

function dbclose($con)
{
	mysql_close($con);
}

$uploaddir = realpath('./') . '/';
$uploadfile = $uploaddir .'receive/'. basename($_FILES['file_contents']['name']);
//use id and password from creds of shop servers and check against all shop creds
$con  = dbconnect();
$query = mysql_query("SELECT * FROM local_stores");
while($row = mysql_fetch_array($query))
{
	if ($row['deleted'] == 0) 
	{
		$id = $row['id'];
		$key = $row['password'];
		echo '<pre>';
			//while loop check against all shop servers
			if ($_POST['id'] == $id && $_POST['key'] == $key) {
			    if(move_uploaded_file($_FILES['file_contents']['tmp_name'], $uploadfile)) {
			    	echo "File is valid, and was successfully uploaded.\n";
			    } else {
				    echo "Possible file upload attack!\n";
			    }
			} else {
			    echo "Possible file upload attack!\n";
			}
			echo 'Here is some more debugging info:';
			print_r($_FILES);
			echo "\n<hr />\n";
			print_r($_POST);
	print "</pr" . "e>\n";
	}
}

?>