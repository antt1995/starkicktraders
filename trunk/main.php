<?php
include("config.php");
include("languages/$lang");

updatecookie();
$title=$l_main_title;
connectdb();
if (isset($test)) {
	include("header.php");
	include("new_metamain.php");
} else if (isset($flash)) {
	include("flmain.php");
} else {
	include("header.php");
	if ($browser == "treo" || isset($treo)) {
		include("treomain.php");
	} else if ($browser == "up" || isset($up)) {
		include("upbrow.php");
	} else {
		include("metamain.php");
	}
}
?>
