<?php
include("config.php");
include("languages/$lang");
if ($name == "") {
die ("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL=tell.php\">");
}
updatecookie();

connectdb();

$title="Refer A Friend";
include("header.php");

$flag = checklogin();
if($flag)
{
	 die();
}

//-------------------------------------------------------------------------------------------------


$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
$res = $db->Execute("SELECT ship_name FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;

?>
<h1>Refer a Friend </h1>
<hr>
<?php
	$email = "Hello!\n\n".
  "Your friend is part of an exciting multiplayer online game ".
  "called Starkick Traders! It is a game designed for devices like the Danger Sidekick, Treo, PocketPC but it works great on a PC too.\n".
  "But don't take our word for it! Check it ".
  "out for yourself. It's a game of exploration and empire building insided ".
  "a 24 hour a day persistent universe!\n".
  "\n".
  "Your friend's name in Starkick Traders is:".$playerinfo[character_name].
  " and their ship is called:".$shipinfo[ship_name]."\n".
  "You can message them or anyone else in Starkick Traders from the".
  " Send Messages menu in the game.\n\n".
  "So get a ship of your very own and start conquering the galaxy! Click here:\n".
  "http://www.mpgames.com/skt/intro.html\n\n".
  "Thanks a lot and see you in the game!\n";
  if ($name == "") {
  	$name = "Your friend";
}
 if ($email1 != "") {
   mail("$email1", "$name wants you to try Starkick Traders", "$email","From: $username\r\nReply-To: $username\r\nX-Mailer: PHP/" . phpversion());
   }
    if ($email2 != "") {
   mail("$email2", "$name wants you to try Starkick Traders", "$email","From: $username\r\nReply-To: $username\r\nX-Mailer: PHP/" . phpversion());
   }
    if ($email3 != "") {
   mail("$email3", "$name wants you to try Starkick Traders", "$email","From: $username\r\nReply-To: $username\r\nX-Mailer: PHP/" . phpversion());
   }
    if ($email4 != "") {
   mail("$email4", "$name wants you to try Starkick Traders", "$email","From: $username\r\nReply-To: $username\r\nX-Mailer: PHP/" . phpversion());
   }
    if ($email5 != "") {
   mail("$email5", "$name wants you to try Starkick Traders", "$email","From: $username\r\nReply-To: $username\r\nX-Mailer: PHP/" . phpversion());
   }
   ?>
   Messages sent!
<?
TEXT_GOTOMAIN();
include("footer.php");

?>
