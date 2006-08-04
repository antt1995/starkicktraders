<?


include("config.php");
updatecookie();

include("languages/$lang");
$title = "$l_pre_title";

include("header.php");

connectdb();

if(checklogin())
{
  die();
}

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;

bigtitle();

if(!isset($change))
{
  echo "<FORM ACTION=preset.php METHOD=POST>";
  echo "Preset 1: <INPUT TYPE=TEXT NAME=preset1 SIZE=6 MAXLENGTH=6 VALUE=$playerinfo[preset1]><BR>";
  echo "Preset 2: <INPUT TYPE=TEXT NAME=preset2 SIZE=6 MAXLENGTH=6 VALUE=$playerinfo[preset2]><BR>";
  echo "Preset 3: <INPUT TYPE=TEXT NAME=preset3 SIZE=6 MAXLENGTH=6 VALUE=$playerinfo[preset3]><BR>";
  echo "Preset 4: <INPUT TYPE=TEXT NAME=preset4 SIZE=6 MAXLENGTH=6 VALUE=$playerinfo[preset4]><BR>";
  echo "Preset 5: <INPUT TYPE=TEXT NAME=preset5 SIZE=6 MAXLENGTH=6 VALUE=$playerinfo[preset5]><BR>";
  echo "Preset 6: <INPUT TYPE=TEXT NAME=preset6 SIZE=6 MAXLENGTH=6 VALUE=$playerinfo[preset6]><BR>";

  echo "<INPUT TYPE=HIDDEN NAME=change VALUE=1>";
  echo "<BR><INPUT TYPE=SUBMIT VALUE=$l_pre_save><BR><BR>";
  echo "</FORM>";
}
else
{
  $preset[1] = round(abs($preset1));
  $preset[2] = round(abs($preset2));
  $preset[3] = round(abs($preset3));
  $preset[4] = round(abs($preset4));
  $preset[5] = round(abs($preset5));
  $preset[6] = round(abs($preset6));
  $valid = true;
  for ($i=1;$i<7;$i++) {
	  if($preset[$i] > $sector_max)
	  {
		$l_pre_exceed = str_replace("[preset]", "$i", $l_pre_exceed);
		$l_pre_exceed = str_replace("[sector_max]", $sector_max, $l_pre_exceed);
		echo $l_pre_exceed;
		$valid = false;
	  }
  }
  if ($valid)
  {
    $update = $db->Execute("UPDATE $dbtables[players] SET preset1=$preset[1],preset2=$preset[2],preset3=$preset[3],preset4=$preset[4],preset5=$preset[5],preset6=$preset[6] WHERE player_id=$playerinfo[player_id]");
	for ($i=1;$i<7;$i++) {
    	echo "Preset $i set to <a href=rsmove.php?engage=1&destination=$preset[$i]>$preset[$i]</a><br>";
	}
  }
}
echo "<br>";
TEXT_GOTOMAIN();

include("footer.php");

?> 


