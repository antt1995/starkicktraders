<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_title_shipyard;
include("header.php");

if ($browser == "hiptop" | $browser == "treo") {
	include("shipyard3.php");
	die();
}

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

echo "<h2>Welcome to our shipyard!</h2>";
echo "<font size=2 color=white><b>Sector: $playerinfo[sector]<br>Technological Capability: $techLv[$techLevel]</font></p>";
?>

  <table width=100% border=1 cellpadding=5>
    <tr bgcolor=<? echo $color_line2 ?>><td width=10% align=center>
    <font size=2 color=white><b>Class</b></font>
    </td>
    <td width=* align=center>
    <font size=2 color=white><b>Class Properties</b></font>
    </tr>
    <?
    $first=1;
    foreach($ships as $curship)
    {
      echo "<tr><td align=center>" .
           "<a style=\"text-decoration: none\" href=shipyard.php?stype=$curship[type_id]><img style=\"border: none\" src=images/$curship[image]><br>" .
           "<font size=2 color=white>Class <b>$curship[name]</a></b></font>";
      
      if($curship[type_id] == $shipinfo[type])
        echo "<font size=2 color=white><br>(Current)</font>";

      if($first == 1)
      {
        $first = 0;
        echo "</td><td rowspan=100 valign=top>";
		if(isset($stype))
		{
			$res2 = $db->Execute("SELECT * FROM $dbtables[ship_types] WHERE type_id=$stype AND buyable = 'Y' AND tech_level<=$techLevel LIMIT 1");
			if ($res2->EOF) {
				$stype=null;
			}
		}     
        if(!isset($stype))
          echo "<center><b>Select a ship from those shown on the left to see more information on it</b></center>";
        else
        {
          //get info for selected ship class
          foreach($ships as $testship)
          {
            if($testship[type_id] == $stype)
            {
              $sship = $testship;
              break;
            }
          }
          
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
		  $std_value = $upgrade_cost*(round(mypw($upgrade_factor, $sship[minhull]))+round(mypw($upgrade_factor, $sship[minengines]))+round(mypw($upgrade_factor, $sship[minpower]))+round(mypw($upgrade_factor, $sship[mincomputer]))+round(mypw($upgrade_factor, $sship[minsensors]))+round(mypw($upgrade_factor, $sship[minbeams]))+round(mypw($upgrade_factor, $sship[mintorp_launchers]))+round(mypw($upgrade_factor, $sship[minshields]))+round(mypw($upgrade_factor, $sship[minarmour]))+round(mypw($upgrade_factor, $sship[mincloak])));
		  $max_value = $upgrade_cost*(round(mypw($upgrade_factor, $sship[maxhull]))+round(mypw($upgrade_factor, $sship[maxengines]))+round(mypw($upgrade_factor, $sship[maxpower]))+round(mypw($upgrade_factor, $sship[maxcomputer]))+round(mypw($upgrade_factor, $sship[maxsensors]))+round(mypw($upgrade_factor, $sship[maxbeams]))+round(mypw($upgrade_factor, $sship[maxtorp_launchers]))+round(mypw($upgrade_factor, $sship[maxshields]))+round(mypw($upgrade_factor, $sship[maxarmour]))+round(mypw($upgrade_factor, $sship[maxcloak])));
		  //$bestprice = floor($std_value+sqrt($max_value))*1000;
          
          echo "<table border=0 cellpadding=5>" .
               "<tr><td valign=top>" .
               "<font size=4 color=white><b>$sship[name]</b></font><p>" .
               "<font size=2 color=silver><b>$sship[description]</b></font><p>" .
               "</td><td valign=top><img src=images/big$sship[image]></td></tr>" .
               "</table>" .
               "<table border=0 cellpadding=2>" .
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
               "<td valign=bottom>$cloak_bars</td></tr>";
			   if ($playerinfo[player_id] == 1) {
			   	echo "<tr><td><font color=white size=2><b>Equipment value as specified: </b></td>" .
               	"<td><font color=white size=2><b>" . NUMBER($std_value) . " C</b></td></tr>";
			   }
               echo "<tr><td><font color=white size=4><b><br>Price: </b></td>" .
               "<td><font color=red size=4><b><br>" . NUMBER($sship[cost_credits]) . " C</b></td></tr>" .
			   "<tr><td><font color=white size=4><b><br>You have: </b></td>" .
               "<td><font color=green size=4><b><br>" . NUMBER($playerinfo[credits]) . " C</b></td></tr>" .
               "<tr><td><font color=white size=4><b>Current ship<br>trade-in value: </b></td>" .
               "<td><font color=white size=4><b><br>" . NUMBER(0.8*value_ship($playerinfo[currentship])) . " C</b></td></tr>" .
               "</table><p>";
		  echo "<form action=shipyard2.php method=POST>" .
                 "<input type=hidden name=stype value=$stype>" .
                 "&nbsp;<input type=submit value=Purchase>" .
                 "</form>";              
        }
        
        echo "</td></tr>";
      }     
      else
        echo "</td></tr>";
    }
    ?>
  
  </table>
  <p>
<?

TEXT_GOTOMAIN();

include("footer.php");

?>