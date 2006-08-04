<?php
include("config.php");
include("languages/$lang");
$title="You are";
include("header.php");

connectdb();
bigtitle();
if (empty($username)) {
	$username = "unknown";
}
if (empty($password)) {
	$password = "unknown";
}
echo "You are $username and your password is $password<br>";
include ("footer.php");
?>
