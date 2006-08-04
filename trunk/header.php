<?php
        $headers = getallheaders();

        $userAgent = $headers["User-Agent"];
 		if ($userAgent == "") {
         	$userAgent = $headers["user-agent"];
 		}
        $isHiptop = false;
$browser = "";
        /*
        *        This check to see if the browser is any version of MSIE on Windows CE. That way, if the browser
        *        gets updated, this should still detect that it is from a Windows CE machine.
        */
        if($userAgent != null && strpos($userAgent, "Danger") && strpos($userAgent, "hiptop"))
        {
                $isHiptop = true;
				$browser = "hiptop";
		$ck = "?kk=".date("U");
        } else if($userAgent != null && strpos($userAgent, "Blazer"))
        {
				$browser = "treo";
		$ck="?";
        } else if($userAgent != null && strpos($userAgent, "UP."))
        {
				$browser = "up";
		$ck="?";
        } else if($userAgent != null && strpos($userAgent, "240x320"))
        {
				$browser = "treo";
        } else {
		$ck = "?";
	}

//header("Cache-Control: no-cache, must-revalidate");
// Comment out the line below if you are running php 4.0.6 or earlier
ob_start("ob_gzhandler");

?>
<!doctype html public "-//w3c//dtd html 3.2//en">
<html>
<head>
<META NAME="HandheldFriendly" content="True">
<title><? echo $title; ?></title>
 <style type="text/css">
 <!--
<?
if($interface == "")
{
  $interface = "main.php";
}

if($interface == "main.php")
{
  echo "  a.mnu {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:white; font-weight:bold;}
  a.mnu:hover {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:#3366ff; font-weight:bold;}
  div.mnu {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:white; font-weight:bold;}
  span.mnu {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:white; font-weight:bold;}
  a.dis {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:silver; font-weight:bold;}
  a.dis:hover {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:#3366ff; font-weight:bold;}
  table.dis {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:silver; font-weight:bold;}
  table.dis:hover {text-decoration:none; font-size: 8Pt; font-family: Geneva, Arial, Helvetica, san-serif; color:#3366ff; font-weight:bold;}
  .portcosts1 {width:7em;border-style:none;font-family: Geneva, Arial, Helvetica, san-serif;font-size:12pt;background-color:$color_line1;color:#c0c0c0;}
  .portcosts2 {width:7em;border-style:none;font-family: Geneva, Arial, Helvetica, san-serif;font-size:12pt;background-color:$color_line2;color:#c0c0c0;}
  .headlines {text-decoration:none; font-size:8Pt; font-family:geneva,Arial,san-serif; font-weight:bold; color:white;}
  .headlines:hover {text-decoration:none; color:#3366ff;}
  .faderlines {background-color:$color_line2;}";
}
echo "\n  body {font-family: Geneva, Arial, Helvetica, san-serif; font-size: x-small;}\n";
?>
 -->
 </style>
</head>

<?

if(empty($no_body))

{

  if($interface=="main.php")
  {
  	echo "<body background=\"images/bgoutspace1.gif\" bgcolor=\"#000000\" text=\"#c0c0c0\" link=\"#00ff00\" vlink=\"#00ff00\" alink=\"#ff0000\">";
  }
  else
  {
  	echo "<body background=\"\" bgcolor=\"#000000\" text=\"#c0c0c0\" link=\"#00ff00\" vlink=\"#808080\" alink=\"#ff0000\">";
  }

}
echo "\n";

?>
