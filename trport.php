<?
include("config.php");
updatecookie();


include("languages/$lang");
$title = "Furangee Special Trader";
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

//-------------------------------------------------------------------------------------------------


$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
// Find out what my ship is
$res = $db->Execute("SELECT * FROM $dbtables[ship_types],$dbtables[ships] WHERE $dbtables[ship_types].type_id=$dbtables[ships].type AND $dbtables[ships].player_id=$playerinfo[player_id] AND $dbtables[ships].ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;
// Check that the player can actual trade or not
$res = $db->Execute("SELECT character_name FROM $dbtables[players] JOIN $dbtables[furangee] WHERE email=furangee_id AND sector='$playerinfo[sector]' AND orders='4' AND sector !=0") or die(mysql_error());
$furangee_info = $res->fields;
if ($res->EOF) {
	bigtitle();
	echo "The Furangee is no longer in this sector!<BR><br>";
	TEXT_GOTOMAIN();
	die();
}
//-------------------------------------------------------------------------------------------------

$title="Furangee Special Trader ".$furangee_info[character_name];
  bigtitle();

$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
$sectorinfo = $res->fields;

// Create dropdowns when called
function dropdown($element_name,$current_value, $max_value)
{
$i = $current_value;
$dropdownvar = "<select size='1' name='$element_name'";
$dropdownvar = "$dropdownvar >\n";
while ($i <= $max_value)
 {
 if ($current_value == $i)
  {
  $dropdownvar = "$dropdownvar        <option value='$i' selected>$i</option>\n";
  }
 else
  {
  $dropdownvar = "$dropdownvar        <option value='$i'>$i</option>\n";
  }
 $i++;
 }
$dropdownvar = "$dropdownvar       </select>\n";
return $dropdownvar;
}



  echo "I can fit you up with some hot techs but don't let the Federation find out!<P>\n";
  $l_creds_to_spend=str_replace("[credits]",NUMBER($playerinfo[credits]),$l_creds_to_spend);
  echo "$l_creds_to_spend<BR>\n";
  echo " <FORM ACTION=trport2.php METHOD=POST>\n";
  $hull_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[hull])) * $furangee_price;
  $engine_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[engines])) * $furangee_price;
  $power_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[power])) * $furangee_price;
  $computer_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[computer])) * $furangee_price;
  $sensors_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[sensors])) * $furangee_price;
  $beams_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[beams])) * $furangee_price;
  $armour_upgrade_cost = $upgrade_cost * round(pow((float)$upgrade_factor, (int)$shipinfo[armour])) * $furangee_price;
  $cloak_upgrade_cost=$upgrade_cost*round(pow((float)$upgrade_factor, (int)$shipinfo[cloak])) * $furangee_price;
  $torp_launchers_upgrade_cost=$upgrade_cost*round(pow((float)$upgrade_factor, (int)$shipinfo[torp_launchers])) * $furangee_price;
  $shields_upgrade_cost=$upgrade_cost*round(pow((float)$upgrade_factor, (int)$shipinfo[shields])) * $furangee_price;

  echo "  <TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=1>\n";
  echo "   <TR BGCOLOR=\"$color_header\">\n";
  //echo "    <TD><B>$l_ship_levels</B></TD>\n";
  echo "    <TD><B>Spec</B></TD>\n";
  echo "    <td align=center><B>+1 $l_cost</B></TD>\n";
  echo "    <td align=center><B>Now</B></TD>\n";
  echo "    <td align=center><B>$l_upgrade</B></TD>\n";
  echo "   </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line1\">\n";
  echo "    <TD>$l_hull</TD>\n";
  echo "    <td align=center>".NUMBER($hull_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[hull]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("hull_upgrade",$shipinfo[hull],$shipinfo[maxhull]);
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line2\">\n";
  echo "    <TD>$l_engines</TD>\n";
  echo "    <td align=center>".NUMBER($engine_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[engines]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("engine_upgrade",$shipinfo[engines],$shipinfo[maxengines]);
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line1\">\n";
  echo "    <TD>$l_power</TD>\n";
  echo "    <td align=center>".NUMBER($power_upgrade_cost)."</td>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[power]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("power_upgrade",$shipinfo[power],$shipinfo[maxpower]);
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line2\">\n";
  echo "    <TD>$l_computer</TD>\n";
  echo "    <td align=center>".NUMBER($computer_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[computer]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("computer_upgrade",$shipinfo[computer],$shipinfo[maxcomputer]);
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line1\">\n";
  echo "    <TD>$l_sensors</TD>\n";
  echo "    <td align=center>".NUMBER($sensors_upgrade_cost)."</td>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[sensors]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("sensors_upgrade",$shipinfo[sensors],$shipinfo[maxsensors]);
  echo "    </TD>\n";
  echo "  </TR>\n"; 
  echo "  <TR BGCOLOR=\"$color_line2\">\n";
  echo "    <TD>$l_beams</TD>\n";
  echo "    <td align=center>".NUMBER($beams_upgrade_cost)."</td>";
  echo "    <td align=center>" . NUMBER($shipinfo[beams]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("beams_upgrade",$shipinfo[beams],$shipinfo[maxbeams]);
  echo "    </TD>\n";
  echo "  </TR>\n";  
  echo "  <TR BGCOLOR=\"$color_line1\">\n";
  echo "    <TD>$l_armour</TD>\n";
  echo "    <td align=center>".NUMBER($armour_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[armour]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("armour_upgrade",$shipinfo[armour],$shipinfo[maxarmour]);
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line2\">\n";
  echo "    <TD>$l_torp_launch</TD>\n";
  echo "    <td align=center>".NUMBER($torp_launchers_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[torp_launchers]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("torp_launchers_upgrade",$shipinfo[torp_launchers],$shipinfo[maxtorp_launchers]);
  echo "    </TD>\n";
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line1\">\n"; 
  echo "    <TD>$l_shields</TD>\n";
  echo "    <td align=center>".NUMBER($shields_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[shields]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("shields_upgrade",$shipinfo[shields],$shipinfo[maxshields]);
  echo "    </TD>\n";      
  echo "  </TR>\n";
  echo "  <TR BGCOLOR=\"$color_line2\">\n";
  echo "    <TD>$l_cloak</TD>\n";
  echo "    <td align=center>".NUMBER($cloak_upgrade_cost)."</TD>\n";
  echo "    <td align=center>" . NUMBER($shipinfo[cloak]) . "</TD>\n";
  echo "    <td align=center>";
  echo dropdown("cloak_upgrade",$shipinfo[cloak],$shipinfo[maxcloak]);
  echo "    </TD>\n";
  echo "  </TR>\n";
 
  echo " </TABLE>\n";  
  echo " <BR>\n";
  echo "<INPUT TYPE=SUBMIT VALUE=$l_buy >";
  echo "</FORM>\n";
echo "\n";
echo "<BR><BR>\n";
TEXT_GOTOMAIN();
echo "\n";

include("footer.php");

?>
