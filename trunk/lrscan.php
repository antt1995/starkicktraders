<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_lrs_title;
include("header.php");

connectdb();
if(checklogin())
{
  die();
}

bigtitle();

srand((double)microtime() * 1000000);

//-------------------------------------------------------------------------------------------------


// get user info
$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;
// get ship info
$result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo = $result->fields;

if($sector == "*")
{
  if(!$allow_fullscan)
  {
    echo "$l_lrs_nofull<BR><BR>";
    TEXT_GOTOMAIN();
    include("footer.php");
    die();
  }
  if($playerinfo[turns] < $fullscan_cost)
  {
    echo "$l_lrs_noturns<BR><BR>";
    TEXT_GOTOMAIN();
    include("footer.php");
    die();
  }

  echo "$l_lrs_used " . NUMBER($fullscan_cost) . " $l_lrs_turns. " . NUMBER($playerinfo[turns] - $fullscan_cost) . " $l_lrs_left.<BR><BR>";

  // deduct the appropriate number of turns
  $db->Execute("UPDATE $dbtables[players] SET turns=turns-$fullscan_cost, turns_used=turns_used+$fullscan_cost where player_id='$playerinfo[player_id]'");

  // user requested a full long range scan
  $l_lrs_reach=str_replace("[sector]",$playerinfo[sector],$l_lrs_reach);
  echo "$l_lrs_reach<BR><BR>";

  // get sectors which can be reached from the player's current sector
  $result = $db->Execute("SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]' ORDER BY link_dest");
  if ($browser == "treo") {
  	echo "<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=1 WIDTH=100%>";
  	echo "<TR BGCOLOR=\"$color_header\"><TD align=left><b>Sector</b></TD><TD align=center><img src=images/warps.gif width=12 height=12></TD><TD align=center><img src=images/ships.gif width=12 height=12></TD><TD><B>$$$</B></TD><TD align=center><img src=images/planet.gif width=12 height=12></TD><TD align=center><img src=images/ms.gif width=12 height=12></TD><TD align=center><img src=images/figs.gif width=12 height=12></TD>";
  } else if ($browser == "hiptop") {
	echo "<TABLE BORDER=0 CELLSPACING=1 CELLPADDING=1 WIDTH=240>";
  	echo "<TR BGCOLOR=\"$color_header\"><TD align=center><B>$l_sector</B><br>&nbsp;</TD><TD align=center><B>$l_lrs_links</B><br><img src=images/warps.gif width=12 height=12></TD><TD align=center><B>$l_lrs_ships</B><br><img src=images/ships.gif width=12 height=12></TD><TD><B>$l_port</B><br>$$$</TD><TD align=center><B>$l_planets</B><br><img src=images/planet.gif width=12 height=12></TD><TD align=center><B>$l_mines</B><br><img src=images/ms.gif width=12 height=12></TD><TD align=center><B>$l_fighters</B><br><img src=images/figs.gif width=12 height=12></TD>";
  } else {
		echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>";
  		echo "<TR BGCOLOR=\"$color_header\"><TD><B>$l_sector</B></TD><TD align=center><B># of Warp Links</B></TD><TD align=center><B>Ships</B></TD><TD align=center><B>$l_port</B></TD><TD align=center><B>Planets</B></TD><TD align=center><B>$l_mines</B></TD><TD align=center><B>Fighters</B></TD>";
  }
  echo "</TR>";
  $color = $color_line1;
  while(!$result->EOF)
  {
    $row = $result->fields;
    // get number of sectors which can be reached from scanned sector
    $result2 = $db->Execute("SELECT COUNT(*) AS count FROM $dbtables[links] WHERE link_start='$row[link_dest]'");
    $row2 = $result2->fields;
    $num_links = $row2[count];

    // get number of ships in scanned sector
    $result2 = $db->Execute("SELECT COUNT(*) AS count FROM $dbtables[ships] WHERE sector='$row[link_dest]' AND on_planet='N'");
    $row2 = $result2->fields;
    $num_ships = $row2[count];

   // get port type and discover the presence of a planet in scanned sector
    $result2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$row[link_dest]'");
    $result3 = $db->Execute("SELECT planet_id FROM $dbtables[planets] WHERE sector_id='$row[link_dest]'");
    $resultSDa = $db->Execute("SELECT SUM(quantity) as mines from $dbtables[sector_defence] WHERE sector_id='$row[link_dest]' and defence_type='M'");
    $resultSDb = $db->Execute("SELECT SUM(quantity) as fighters from $dbtables[sector_defence] WHERE sector_id='$row[link_dest]' and defence_type='F'");

    $sectorinfo = $result2->fields;
    $defM = $resultSDa->fields;
    $defF = $resultSDb->fields;
    $port_type = $sectorinfo[port_type];
    $has_planet = $result3->RecordCount();
    $has_mines = NUMBER($defM[mines]);
    $has_fighters = NUMBER($defF[fighters]);

    if ($port_type != "none") {
      $icon_alt_text = ucfirst(t_port($port_type));
      $icon_port_type_name = $port_type . ".gif";
      $image_string = "<img height=12 width=12 alt=\"$icon_alt_text\" src=\"images/$icon_port_type_name\">";
    } else {
      $image_string = "&nbsp;";
    }


    //echo "<TR BGCOLOR=\"$color\"><TD><A HREF=move.php?sector=$row[link_dest]>$row[link_dest]</A><br><A HREF=lrscan.php?sector=$row[link_dest]>[Scan]</A></TD><TD align=center>$num_links</TD><TD align=center>$num_ships</TD><TD align=center>$image_string</TD><TD align=center>$has_planet</TD><TD align=center>$has_mines</TD><TD align=center>$has_fighters</TD></TR>";
	echo "<TR BGCOLOR=\"$color\"><TD><A HREF=move.php?sector=$row[link_dest]>$row[link_dest]</A><br><A HREF=lrscan.php?sector=$row[link_dest]>[Scan]</A></TD><TD align=center>$num_links</TD><TD align=center>$num_ships</TD><TD WIDTH=12>$image_string</TD><TD align=center>$has_planet</TD><TD align=center>$has_mines</TD><TD align=center>$has_fighters</TD></TR>";
    if($color == $color_line1)
    {
      $color = $color_line2;
    }
    else
    {
      $color = $color_line1;
    }
    $result->MoveNext();
  }
  echo "</TABLE>";
  /* Last Ship Seen Device table */
  if($shipinfo['dev_lssd'] == 'Y')
  {
  	/**************************************************************/
	$result = $db->Execute("SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]' ORDER BY link_dest");
	echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>";
  	echo "<TR BGCOLOR=\"$color_header\"><TD><B>$l_sector</B></TD><TD align=center><B>$l_lss</B></TD></TR>";
	$color = $color_line1;
  	while(!$result->EOF)
	{
		$row = $result->fields;
		// Log scan, if we don't know that sector already
		$res = $db->Execute("SELECT * FROM $dbtables[scan_log] WHERE player_id=$playerinfo[player_id] and sector_id=$row[link_dest]");
		if ($res->RowCount() == 0) {
			$res = $db->Execute("INSERT INTO $dbtables[scan_log] (player_id,sector_id,time) VALUES ($playerinfo[player_id],$row[link_dest],NOW())");
		}
		
		echo "<TR BGCOLOR=\"$color\"><TD><A HREF=move.php?sector=$row[link_dest]>$row[link_dest]</A></TD>";
		$resx = $db->Execute("SELECT * from $dbtables[movement_log] WHERE player_id <> $playerinfo[player_id] AND sector_id = $row[link_dest] ORDER BY time DESC LIMIT 1");
        if(!$resx)
        {
           echo "<TD align=center>None</TD>";
        }
        else
        {
           $myrow = $resx->fields;
           echo "<TD align=center>" . get_player($myrow[player_id]) . "</TD>";
        }
		echo "</TR>";
		if($color == $color_line1)
		{
		  $color = $color_line2;
		}
		else
		{
		  $color = $color_line1;
		}
		$result->MoveNext();
	}
	echo "</TABLE>";	
  } // End of last see device code
  if($num_links == 0)
  {
	echo "$l_none.";
  }
  else
  {
    echo "<BR>$l_lrs_click";
  }
  if ($browser =="treo" | $browser=="hiptop") {
	  echo "<p><b>Legend</b><br>
	  <img src=images/warps.gif width=12 height=12> - number of warp links<br>
	  <img src=images/ships.gif width=12 height=12> - number of ships<br>
	  <img src=images/planet.gif width=12 height=12> - number of planets<br>
	  <img src=images/ms.gif width=12 height=12> - number of mines<br>
	  <img src=images/figs.gif width=12 height=12> - number of fighters<br>
	  $$$ - Port Type</p>";
	}
}
else
{
  // user requested a single sector (standard) long range scan

  // get scanned sector information
  $result2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$sector'");
  $sectorinfo = $result2->fields;

  // get sectors which can be reached through scanned sector
  $result3 = $db->Execute("SELECT link_dest FROM $dbtables[links] WHERE link_start='$sector' ORDER BY link_dest ASC");

  $i=0;

  if($result3 > 0)
  {
    while(!$result3->EOF)
    {
      $links[$i] = $result3->fields[link_dest];
      $i++;
      $result3->MoveNext();
    }
  }
  $num_links=$i;

  // get sectors which can be reached from the player's current sector
  $result3a = $db->Execute("SELECT link_dest FROM $dbtables[links] WHERE link_start='$playerinfo[sector]'");

  $i=0;

  $flag=0;

  if($result3a > 0)
  {
    while(!$result3a->EOF)
    {
      if($result3a->fields[link_dest] == $sector)
      {
        $flag=1;
      }
      $i++;
      $result3a->MoveNext();
    }
  }

  if($flag == 0)
  {
    echo "$l_lrs_cantscan<BR><BR>";
    TEXT_GOTOMAIN();
    die();
  }

  echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>";
  echo "<TR BGCOLOR=\"$color_header\"><TD><B>$l_sector $sector";
  if($sectorinfo[sector_name] != "")
  {
    echo " ($sectorinfo[sector_name])";
  }
  echo "</B></TR>";
  echo "</TABLE><BR>";

  echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 WIDTH=100%>";
  echo "<TR BGCOLOR=\"$color_line2\"><TD><B>$l_links</B></TD></TR>";
  echo "<TR><TD>";
  if($num_links == 0)
  {
    echo "$l_none";
    $link_bnthelper_string="<!--links:N:-->";
  }
  else
  {
    $link_bnthelper_string="<!--links:Y";
    for($i = 0; $i < $num_links; $i++)
    {
      echo "$links[$i]";
      $link_bnthelper_string=$link_bnthelper_string . ":" . $links[$i];
      if($i + 1 != $num_links)
      {
        echo ", ";
      }
    }
    $link_bnthelper_string=$link_bnthelper_string . ":-->";
  }
  echo "</TD></TR>";
  echo "<TR BGCOLOR=\"$color_line2\"><TD><B>$l_ships</B></TD></TR>";
  echo "<TR><TD>";
  if($sector != 0)
  {
    // get ships located in the scanned sector
    $result4 = $db->Execute("SELECT $dbtables[players].player_id,ship_name,character_name,cloak FROM $dbtables[players],$dbtables[ships] WHERE $dbtables[ships].sector='$sector' AND $dbtables[ships].on_planet='N' AND $dbtables[ships].player_id=$dbtables[players].player_id");
    if($result4->EOF)
    {
      echo "None detected";
    }
    else
    {
      $num_detected = 0;
      while(!$result4->EOF)
      {
        $row = $result4->fields;
		if ($row[tow]>0) {
			// Cloak is reduced
			$row[cloak] = min($row[cloak]-1,0);
		}
		// If the ship is unowned you can always see it
		if ($row[player_id] == 0) {
			$num_detected++;
          	echo $row['ship_name'] . " (" . $row['character_name'] . ")<BR>";
		} else {
			// display other ships in sector - unless they are successfully cloaked
			$success = SCAN_SUCCESS($shipinfo[sensors], $row[cloak]);
			if($success < 5)
			{
			  $success = 5;
			}
			if($success > 95)
			{
			  $success = 95;
			}
			$roll = rand(1, 100);
			if($roll < $success)
			{
			  $num_detected++;
			  echo $row['ship_name'] . " (" . $row['character_name'] . ")<BR>";
			}
		}
        $result4->MoveNext();
      }
      if(!$num_detected)
      {
        echo "None detected";
      }
    }
  }
  else
  {
    echo "$l_lrs_zero";
  }
  echo "</TD></TR>";
  echo "<TR BGCOLOR=\"$color_line2\"><TD><B>$l_port</B></TD></TR>";
  echo "<TR><TD>";
  if($sectorinfo[port_type] == "none")
  {
    echo "$l_none";
    $port_bnthelper_string="<!--port:none:0:0:0:0:-->";
  }
  else
  {
    if ($sectorinfo[port_type] != "none") {
      $port_type = $sectorinfo[port_type];
      $icon_alt_text = ucfirst(t_port($port_type));
      $icon_port_type_name = $port_type . ".gif";
      $image_string = "<img align=absmiddle height=12 width=12 alt=\"$icon_alt_text\" src=\"images/$icon_port_type_name\">";
    }
    echo "$image_string " . t_port($sectorinfo[port_type]);

    $port_bnthelper_string="<!--port:" . $sectorinfo[port_type] . ":" . $sectorinfo[port_ore] . ":" . $sectorinfo[port_organics] . ":" . $sectorinfo[port_goods] . ":" . $sectorinfo[port_energy] . ":-->";
  }
  echo "</TD></TR>";
  echo "<TR BGCOLOR=\"$color_line2\"><TD><B>Planets</B></TD></TR>";
  echo "<TR><TD>";
  $query = $db->Execute("SELECT name, owner FROM $dbtables[planets] WHERE sector_id=$sectorinfo[sector_id]");

  if($query->EOF)
  {
    echo "$l_none";
    $planet_bnthelper_string="<!--planet:N:::-->";
  }

  while(!$query->EOF)
  {
    $planet = $query->fields;
    if(empty($planet[name]))
      echo "$l_unnamed";
    else
      echo "$planet[name]";

    if($planet[owner] == 0)
    {
      echo " ($l_unowned)";
    }
    else
    {
      $result5 = $db->Execute("SELECT character_name FROM $dbtables[players] WHERE player_id=$planet[owner]");
      $planet_owner_name = $result5->fields;
      echo " ($planet_owner_name[character_name])";
    }
    $query->MoveNext();
  }
  $resultSDa = $db->Execute("SELECT SUM(quantity) as mines from $dbtables[sector_defence] WHERE sector_id='$sector' and defence_type='M'");
  $resultSDb = $db->Execute("SELECT SUM(quantity) as fighters from $dbtables[sector_defence] WHERE sector_id='$sector' and defence_type='F'");
  $defM = $resultSDa->fields;
  $defF = $resultSDb->fields;

  echo "</TD></TR>";
  echo "<TR BGCOLOR=\"$color_line1\"><TD><B>$l_mines</B></TD></TR>";
  $has_mines =  NUMBER($defM[mines] ) ;
  echo "<TR><TD>" . $has_mines;
  echo "</TD></TR>";
  echo "<TR BGCOLOR=\"$color_line2\"><TD><B>$l_fighters</B></TD></TR>";
  $has_fighters =  NUMBER($defF[fighters] ) ;
  echo "<TR><TD>" . $has_fighters;
  echo "</TD></TR>";
  if($shipinfo['dev_lssd'] == 'Y')
  {
     echo "<TR BGCOLOR=\"$color_line2\"><TD><B>$l_lss</B></TD></TR>";
     echo "<TR><TD>";
     $resx = $db->Execute("SELECT * from $dbtables[movement_log] WHERE player_id <> $playerinfo[player_id] AND sector_id = $sector ORDER BY time DESC LIMIT 1");
     if(!$resx)
     {
        echo "None";
     }
     else
     {
        $myrow = $resx->fields;
        echo get_player($myrow[player_id]);
     }
  }
  else
  {
  echo "<TR><TD>";
  }
  echo "</TD></TR>";
  echo "</TABLE><BR>";

  echo "<a href=move.php?sector=$sector>$l_clickme</a> $l_lrs_moveto $sector.";
}


//-------------------------------------------------------------------------------------------------
$rspace_bnthelper_string="<!--rspace:" . $sectorinfo[distance] . ":" . $sectorinfo[angle1] . ":" . $sectorinfo[angle2] . ":-->";
echo $link_bnthelper_string;
echo $port_bnthelper_string;
echo $planet_bnthelper_string;
echo $rspace_bnthelper_string;
echo "<BR><BR>";
TEXT_GOTOMAIN();

include("footer.php");

?>
