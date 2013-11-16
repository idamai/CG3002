<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<title>Login</title>
		<link href="../css/bootstrap.css" rel="stylesheet" media="screen">
		<link href="../css/regCustom.css" rel="stylesheet" media="screen">
		<script src="../js/jquery-1.10.2.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<style type="text/css">
      body {
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
      }

      .form-signin {
        max-width: 300px;
        padding: 19px 29px 29px;
        margin: 0 auto 20px;
        background-color: #fff;
        border: 1px solid #e5e5e5;
        -webkit-border-radius: 5px;
           -moz-border-radius: 5px;
                border-radius: 5px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
                box-shadow: 0 1px 2px rgba(0,0,0,.05);
      }
      .form-signin .form-signin-heading,
      .form-signin .checkbox {
        margin-bottom: 10px;
      }
      .form-signin input[type="text"],
      .form-signin input[type="password"] {
        font-size: 16px;
        height: auto;
        margin-bottom: 15px;
        padding: 7px 9px;
      }

    </style>
	</head>
	<body>
	  <div class="container">
		 
	  <div class = "hero-unit">
				<h2>HyperMarket - Regional Server</h2>
	  </div>
	  
	<?php	
	//lib methods
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
	//end of lib
	
	$username = $_POST['username'];
	$pass = md5($_POST['password']);
	if($username!=null)
	{
		$con= dbconnect();
		$username = mysql_real_escape_string($username);
	
		$query = mysql_query("SELECT * FROM admin WHERE username='$username'");
		$count = mysql_num_rows($query);
		if($count==1)
		{
			while($row = mysql_fetch_array($query))
			{
				if($row['password']==$pass)
				{
					$_SESSION['username'] = $row['username'];
					echo("redirecting...");
				}
				else
				{
					echo("<p style='color:#3379f0;'>Password doesn't match. Try again! </p>");
					unset($_SESSION['username']);
				}
			}
		}
		else{
			echo("<p style='color:#3379f0;'>User username does not exist. Please register a new account. </p>");
		}
		dbclose($con);
	}
	?>
	
	<?php
	if (!isset($_SESSION['username'])) {
	?>
      <form class="form-signin" method="post">
        <h2 class="form-signin-heading">Login</h2>
        <input type="text" class="input-block-level" name="username" placeholder="Username">
        <input type="password" class="input-block-level" name="password" placeholder="Password">
        <button class="btn btn-lg btn-primary" type="submit">Login</button>
      </form>

    <?php } else{ ?>
		<script>window.location.href = "index.php"; </script>
	<?php	 } ?>
	
    </div> <!-- /container -->
	</body>
</html>