<?

include("config.php");

updatecookie();


include("languages/$lang");
$title=$l_report_title;

connectdb();
include("header.php");



if(checklogin())
{
  die();
}

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");

$playerinfo=$result->fields;

// Find out what my ship is
$res = $db->Execute("SELECT * FROM $dbtables[ship_types],$dbtables[ships] WHERE $dbtables[ship_types].type_id=$dbtables[ships].type AND $dbtables[ships].player_id=$playerinfo[player_id] AND $dbtables[ships].ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;
$shipavg = $shipinfo[hull] + $shipinfo[engines] + $shipinfo[power] + $shipinfo[computer] + $shipinfo[sensors] + $shipinfo[armour] + $shipinfo[shields] + $shipinfo[beams] + $shipinfo[torp_launchers] + $shipinfo[cloak];
$shipavg /= 10;
bigtitle();

echo "<B>$l_player: $playerinfo[character_name]</B><br><B>$l_ship: $shipinfo[ship_name]</B><br>";
echo "<b>Ship class:</b> $shipinfo[name]<br>";
echo "<b>Current ship value:</b> ".NUMBER(value_ship($shipinfo[ship_id]))." credits<br>";
if ($shipinfo[fur_tech]=="Y") {
	echo "This ship contains illegal Furangee tech upgrades!<br>";
}
echo "<B>$l_credits on hand: </b>" . NUMBER($playerinfo[credits]) . "<br>";
echo "<BR>";
echo "<p align=center>";
echo "<img src=\"images/big$shipinfo[image]\" border=0></p>";
//echo "<TABLE BORDER=0 CELLSPACING=5 CELLPADDING=0 WIDTH=\"100%\">";
//echo "<TR><TD>";

echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=1 WIDTH=\"100%\">";
echo "<TR BGCOLOR=\"$color_header\"><TD COLSPAN=3 align=center><B>$l_ship_levels</B></TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_hull</TD><TD align=center>$l_level $shipinfo[hull]</TD><TD align=center>Max $l_level $shipinfo[maxhull]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_engines</TD><TD align=center>$l_level $shipinfo[engines]</TD><TD align=center>Max $l_level $shipinfo[maxengines]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_power</TD><TD align=center>$l_level $shipinfo[power]</TD><TD align=center>Max $l_level $shipinfo[maxpower]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_computer</TD><TD align=center>$l_level $shipinfo[computer]</TD><TD align=center>Max $l_level $shipinfo[maxcomputer]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_sensors</TD><TD align=center>$l_level $shipinfo[sensors]</TD><TD align=center>Max $l_level $shipinfo[maxsensors]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_armour</TD><TD align=center>$l_level $shipinfo[armour]</TD><TD align=center>Max $l_level $shipinfo[maxarmour]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_shields</TD><TD align=center>$l_level $shipinfo[shields]</TD><TD align=center>Max $l_level $shipinfo[maxshields]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_beams</TD><TD align=center>$l_level $shipinfo[beams]</TD><TD align=center>Max $l_level $shipinfo[maxbeams]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_torp_launch</TD><TD align=center>$l_level $shipinfo[torp_launchers]</TD><TD align=center>Max $l_level $shipinfo[maxtorp_launchers]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_cloak</TD><TD align=center>$l_level $shipinfo[cloak]</TD><TD align=center>Max $l_level $shipinfo[maxcloak]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD><i>$l_shipavg</i></TD><TD align=center>$l_level " . NUMBER($shipavg, 2) . "</TD><td></td></TR>";
echo "</TABLE><P>";

//echo "</TD><TD VALIGN=TOP>";
echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=1 WIDTH=\"100%\">";
$holds_used = $shipinfo[ship_ore] + $shipinfo[ship_organics] + $shipinfo[ship_goods] + $shipinfo[ship_colonists];
$holds_max = NUM_HOLDS($shipinfo[hull]);
echo "<TR BGCOLOR=\"$color_header\"><TD><B>$l_holds</B></TD><TD ALIGN=RIGHT><B>" . NUMBER($holds_used) . " / " . NUMBER($holds_max) . "</B></TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_ore</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[ship_ore]) . "</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_organics</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[ship_organics]) . "</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_goods</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[ship_goods]) . "</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_colonists</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[ship_colonists]) . "</TD></TR>";
//echo "<TR><TD>&nbsp;</TD></TR>";
echo "</TABLE><P>";
echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=1 WIDTH=\"100%\">";
$armour_pts_max = NUM_ARMOUR($shipinfo[armour]);
$ship_fighters_max = NUM_FIGHTERS($shipinfo[computer]);
$torps_max = NUM_TORPEDOES($shipinfo[torp_launchers]);
echo "<TR BGCOLOR=\"$color_header\"><TD COLSPAN=2 align=center><B>$l_arm_weap</B></TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_armourpts</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[armour_pts]) . " / " . NUMBER($armour_pts_max) . "</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_fighters</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[ship_fighters]) . " / " . NUMBER($ship_fighters_max) . "</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_torps</TD><TD ALIGN=RIGHT>" . NUMBER($shipinfo[torps]) . " / " . NUMBER($torps_max) . "</TD></TR>";
//echo "<TR><TD>&nbsp;</TD></TR>";
echo "</TABLE><p>";
//echo "</TD><TD VALIGN=TOP>";
echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=1 WIDTH=\"100%\">";
$energy_max = NUM_ENERGY($shipinfo[power]);
echo "<TR BGCOLOR=\"$color_header\"><TD><B>$l_energy</B></TD><TD ALIGN=RIGHT><B>" . NUMBER($shipinfo[ship_energy]) . " / " . NUMBER($energy_max) . "</B></TD></TR>";
echo "</TABLE><P>";
echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=1 WIDTH=\"100%\">";
echo "<TR BGCOLOR=\"$color_header\"><TD COLSPAN=2 align=center><B>$l_devices</B></TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_beacons</TD><TD ALIGN=RIGHT>$shipinfo[dev_beacon]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_warpedit</TD><TD ALIGN=RIGHT>$shipinfo[dev_warpedit]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_genesis</TD><TD ALIGN=RIGHT>$shipinfo[dev_genesis]</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_deflect</TD><TD ALIGN=RIGHT>".NUMBER($shipinfo[dev_minedeflector])."</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_ewd</TD><TD ALIGN=RIGHT>$shipinfo[dev_emerwarp]</TD></TR>";
$escape_pod = ($shipinfo[dev_escapepod] == 'Y') ? $l_yes : $l_no;
$fuel_scoop = ($shipinfo[dev_fuelscoop] == 'Y') ? $l_yes : $l_no;
$lssd = ($shipinfo[dev_lssd] == 'Y') ? $l_yes : $l_no;
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_escape_pod</TD><TD ALIGN=RIGHT>$escape_pod</TD></TR>";
echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_fuel_scoop</TD><TD ALIGN=RIGHT>$fuel_scoop</TD></TR>";
echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_lssd</TD><TD ALIGN=RIGHT>$lssd</TD></TR>";
$sectorwmd = ($shipinfo[dev_sectorwmd] == 'Y') ? $l_yes : $l_no;
if ($sectorwmd == $l_yes) {
	echo "<TR BGCOLOR=\"$color_line1\"><TD>Sector-WMD</TD><TD ALIGN=RIGHT>$sectorwmd</TD></TR>";
}
//echo "<TR><TD>&nbsp;</TD></TR>";
echo "</TABLE>";

//echo "</TD></TR>";
//echo "</TABLE>";



TEXT_GOTOMAIN();

include("footer.php");

?>

