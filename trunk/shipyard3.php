<?
include_once("config.php");
updatecookie();

include_once("languages/$lang");
$title=$l_title_shipyard;
include_once("header.php");

connectdb();

if(checklogin())
{
  die();
}
bigtitle();

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;

$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;

// Check that player is at a special port
$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]' AND port_type = 'special'");
$techLevel = 0;
if ($res->EOF) {
	echo "There is no shipyard in this sector.<br><br>";
	TEXT_GOTOMAIN();
	include("footer.php");
	die();
} else {
	// Get the tech level of the port
	$row = $res->fields;
	$techLevel = $row[tech_level];
}
// Get the ships that are available at this port
$res = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE buyable = 'Y' AND tech_level<=$techLevel ORDER BY cost_credits ASC");
while(!$res->EOF)
{
  $ships[] = $res->fields;
  $res->MoveNext();
}


if(isset($stype))
{
  $lastship = end($ships);
  //if($stype < 1 || $stype > $lastship[type_id]) {
    // "Unknown ship class.<br>";
  //} else {
	$res2 = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE type_id=$stype AND buyable = 'Y' AND tech_level<=$techLevel LIMIT 1");
	if ($res2->EOF) {
		echo "Unknown ship class.<br>";
	} else {
		$sship = $res2->fields;
		$hull_bars = MakeBars($sship[minhull], $sship[maxhull]);
		$engines_bars = MakeBars($sship[minengines], $sship[maxengines]);
		$power_bars = MakeBars($sship[minpower], $sship[maxpower]);
		$computer_bars = MakeBars($sship[mincomputer], $sship[maxcomputer]);
		$sensors_bars = MakeBars($sship[minsensors], $sship[maxsensors]);
		$armour_bars = MakeBars($sship[minarmour], $sship[maxarmour]);
		$shields_bars = MakeBars($sship[minshields], $sship[maxshields]);
		$beams_bars = MakeBars($sship[minbeams], $sship[maxbeams]);
		$torp_launchers_bars = MakeBars($sship[mintorp_launchers], $sship[maxtorp_launchers]);
		$cloak_bars = MakeBars($sship[mincloak], $sship[maxcloak]);
	?>
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	<td width="100%"><img border=1 ALIGN="right" src="<?echo "images/big$sship[image]"; ?>" width="120" height="90">
	<font size=4 color=white>
	<?
	
	 echo "<b>$sship[name]</b></font><br><font size=2 color=silver><b>$sship[description]";
	 
	?>
	<br>
	</font></td>
	</tr></table>
	<?		 
		echo "<table border=0 cellpadding=2>" .
			 "<tr><td valign=top><font size=4 color=white><b>Ship Components Levels</b></font><br>&nbsp;</td></tr>" .
               "<tr><td><font size=2><b>Hull</b> ($sship[minhull]/$sship[maxhull])</td>" .
               "<td valign=bottom>$hull_bars</td></tr>" .
               "<tr><td><font size=2><b>Engines</b> ($sship[minengines]/$sship[maxengines])</td>" .
               "<td valign=bottom>$engines_bars</td></tr>" .
               "<tr><td><font size=2><b>Power</b> ($sship[minpower]/$sship[maxpower])</td>" .
               "<td valign=bottom>$power_bars</td></tr>" .
               "<tr><td><font size=2><b>Computer</b> ($sship[mincomputer]/$sship[maxcomputer])</td>" .
               "<td valign=bottom>$computer_bars</td></tr>" .
               "<tr><td><font size=2><b>Sensors</b> ($sship[minsensors]/$sship[maxsensors])</td>" .
               "<td valign=bottom>$sensors_bars</td></tr>" .
               "<tr><td><font size=2><b>Armour</b> ($sship[minarmour]/$sship[maxarmour])</td>" .
               "<td valign=bottom>$armour_bars</td></tr>" .
               "<tr><td><font size=2><b>Shields</b> ($sship[minshields]/$sship[maxshields])</td>" .
               "<td valign=bottom>$shields_bars</td></tr>" .
               "<tr><td><font size=2><b>Beams</b> ($sship[minbeams]/$sship[maxbeams])</td>" .
               "<td valign=bottom>$beams_bars</td></tr>" .
               "<tr><td><font size=2><b>Torpedo Launchers</b> ($sship[mintorp_launchers]/$sship[maxtorp_launchers])</td>" .
               "<td valign=bottom>$torp_launchers_bars</td></tr>" .
               "<tr><td><font size=2><b>Cloak</b> ($sship[mincloak]/$sship[maxcloak])</td>" .
			 "<td valign=bottom>$cloak_bars</td></tr>" .
			 "<tr><td><font color=white size=4><b><br>Price: </b></td>" .
			 "<td><font color=red size=4><b><br>" . NUMBER($sship[cost_credits]) . " C</b></td></tr>" .
			 "<tr><td><font color=white size=4><b><br>You have: </b></td>" .
			 "<td><font color=green size=4><b><br>" . NUMBER($playerinfo[credits]) . " C</b></td></tr>" .
			 "<tr><td><font color=white size=4><b>Current ship<br>trade-in value: </b></td>" .
			 "<td><font color=white size=4><b><br>" . NUMBER(0.8*value_ship($playerinfo[currentship])) . " C</b></td></tr>".
			 "</table><p>";
		  echo "<form action=shipyard2.php method=POST>" .
			   "<input type=hidden name=stype value=$stype>" .
			   "<input type=submit value=Purchase>" .
			   "</form><br>";
		echo "<font size=2 color=silver><b><a href=shipyard3.php>Return to shipyard</a></b></font><p>";
  }
} else {

?>

  <table width=100% border=1 cellpadding=5>
    <tr bgcolor=<? echo $color_line2 ?>><td width=10% align=center>
<?
echo "<font size=2 color=white><b>Welcome to our shipyard!<br>";
echo "Sector: $playerinfo[sector]<br>Technological Capability: $techLv[$techLevel]</font></b>";

?>
    </td>
    </tr>
    <?
    $first=1;
    foreach($ships as $curship)
    {
      echo "<tr><td align=center>" .
           "<a style=\"text-decoration: none\" href=shipyard3.php?stype=$curship[type_id]><img style=\"border: none\" src=images/$curship[image]><br>" .
           "<font size=2 color=white>Class <b>$curship[name]</a></b></font>";
      
      if($curship[type_id] == $shipinfo[type])
        echo "<font size=2 color=white><br>(Current)</font>";
	  echo "</td></tr>";
	  //echo "<tr><td>".$curship[description]."</td></tr>";
	}
?>
</table>
<?
}
TEXT_GOTOMAIN();

include("footer.php");

?>
