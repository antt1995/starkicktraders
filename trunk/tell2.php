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
From: 
<?php echo $username; ?>
<br>
To: 
<?php echo $email1." ".$email2." ".$email3." ".$email4." ".$email5;?>
<br>
Subject: 
<?php echo $name;?> wants you to try Starkick Traders <br>
Message: 
<form name="form1" method="post" action="tell3.php">
  <input type="hidden" name="email1" value="<?php echo $email1;?>">
  <input type="hidden" name="email2" value="<?php echo $email2;?>">
  <input type="hidden" name="email3" value="<?php echo $email3;?>">
  <input type="hidden" name="email4" value="<?php echo $email4;?>">
  <input type="hidden" name="email5" value="<?php echo $email5;?>">
  <input type="hidden" name="name" value="<?php echo $name;?>">
  <pre>Hello!   </pre>
  <pre></pre>
  <pre>Your friend is part of an exciting online multiplayer game</pre>
  <pre>called Starkick Traders.</pre>
  <pre>But don't take their word for it! Check it</pre>
  <pre>out for yourself. It's a game of exploration and empire building inside</pre>
  <pre>a 24 hour a day persistent universe.</pre>
  <pre></pre>
  <pre>Your friend's name in Starkick Traders is:<?php echo $playerinfo[character_name] ?></pre>
  <pre>and their Ship name is:<?php echo $shipinfo[ship_name] ?></pre>
  <pre>You can message them, or anyone else in Starkick Traders, from the</pre>
  <pre>Send Messages menu in the game. </pre>
  <pre>So get a ship of your very own and start conquering the galaxy! Click here:</pre>
  <pre>http://www.mpgames.com/skt/intro.php?ref=<?php echo $username;?></pre>
  <pre>Thanks a lot and see you in the game!</pre>
  <pre><input type="submit" name="Submit" value="Submit"></pre>
</form>
<?
TEXT_GOTOMAIN();
include("footer.php");

?>
