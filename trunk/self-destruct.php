<?
include("config.php");
updatecookie();

include("languages/$lang");

$title=$l_die_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

bigtitle();

$result = $db->Execute("SELECT player_id,character_name,currentship FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;

if(!isset($sure))
{
  echo "<FONT COLOR=RED><B>$l_die_rusure</B></FONT><BR><BR>";
  echo "<A HREF=$interface>$l_die_nonono</A> $l_die_what<BR><BR>";
  echo "<H1>Self-destructing has a 1500 turn penalty!</H1>";
  echo "<A HREF=self-destruct.php?sure=1>$l_yes!</A> $l_die_goodbye<BR><BR>";
}
elseif($sure == 1)
{
  echo "<FONT COLOR=RED><B>$l_die_check</B></FONT><BR><BR>";
  echo "<A HREF=$interface>$l_die_nonono</A> $l_die_what<BR><BR>";
  echo "<H1>Self-destructing has a 1500 turn penalty!</H1>";
  echo "<A HREF=self-destruct.php?sure=2>$l_yes!</A> $l_die_goodbye AND LOOSE 1500 TURNS!!!!!<BR><BR>";
}
elseif($sure == 2)
{
  echo "$l_die_count<BR>";
  echo "$l_die_vapor<BR><BR>";
  echo "$l_die_please.<BR>";
  db_kill_player($playerinfo[player_id],$playerinfo[currentship],-2);
  cancel_bounty($playerinfo[player_id]);
  playerlog(1, LOG_ADMIN_HARAKIRI, "$playerinfo[character_name]|$ip");
  playerlog($playerinfo[player_id], LOG_HARAKIRI, "$ip");
}
else
{
  echo "$l_die_exploit<BR><BR>";
}

if($sure != 2)
{
  TEXT_GOTOMAIN();
}

include("footer.php");

?>
