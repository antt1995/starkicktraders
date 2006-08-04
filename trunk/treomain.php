<?php

$basefontsize = 0;
$stylefontsize = "8Pt";
$picsperrow = 5;

if($screenres == 640)
  $picsperrow = 3;

if($screenres >= 1024)
{
  $basefontsize = 1;
  $stylefontsize = "12Pt";
  $picsperrow = 7;
}


$flag = checklogin();
if($flag)
{
	if ($flag == 2) {
		die("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL=subscribe.php?err=1\">");
	} else {
		 die();
	}
}

//-------------------------------------------------------------------------------------------------


$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
if($playerinfo['cleared_defences'] > ' ')
{
   echo "You return to your previous Sector...<BR>";
   $res = $db->Execute("UPDATE $dbtables[players] SET cleared_defences='', turns=turns-1, turns_used=turns_used+1 WHERE email='$username'");
   echo "<a href=main.php>$l_clicktocontinue</a>";
   die();
}

$res = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id='$playerinfo[sector]'");
$sectorinfo = $res->fields;

srand((double)microtime() * 1000000);

if($playerinfo[on_planet] == "Y")
{
  $res2 = $db->Execute("SELECT planet_id, owner FROM $dbtables[planets] WHERE planet_id=$playerinfo[planet_id]");
  if($res2->RecordCount() != 0)
  {
    //echo "<A HREF=planet.php?kk=".date("U")."&planet_id=$playerinfo[planet_id]>$l_clickme</A> $l_toplanetmenu    <BR>";
	$planet_id=$playerinfo[planet_id];
	$id=$playerinfo[player_id];
	include("metaplanet.php");

    //-------------------------------------------------------------------------------------------------
    die();
  }
  else
  {
    $db->Execute("UPDATE $dbtables[players] SET on_planet='N' WHERE player_id=$playerinfo[player_id]");
    echo "<BR>$l_nonexistant_pl<BR><BR>";
  }
}

$res = $db->Execute("SELECT * FROM $dbtables[links] WHERE link_start='$playerinfo[sector]' ORDER BY link_dest ASC");

$i = 0;
if($res > 0)
{
  while(!$res->EOF)
  {
    $links[$i] = $res->fields[link_dest];
    $i++;
    $res->MoveNext();
  }
}
$num_links = $i;

$res = $db->Execute("SELECT * FROM $dbtables[planets] WHERE sector_id='$playerinfo[sector]'");

$i = 0;
if($res > 0)
{
  while(!$res->EOF)
  {
    $planets[$i] = $res->fields;
    $i++;
    $res->MoveNext();
  }
}
$num_planets = $i;

$res = $db->Execute("SELECT * FROM $dbtables[sector_defence],$dbtables[players] WHERE $dbtables[sector_defence].sector_id='$playerinfo[sector]'
                                                    AND $dbtables[players].player_id = $dbtables[sector_defence].player_id ");
$i = 0;
if($res > 0)
{
  while(!$res->EOF)
  {
    $defences[$i] = $res->fields;
    $i++;
    $res->MoveNext();
  }
}
$num_defences = $i;

$res = $db->Execute("SELECT zone_id,zone_name FROM $dbtables[zones] WHERE zone_id='$sectorinfo[zone_id]'");
$zoneinfo = $res->fields;

$planettypes[0]= "tinyplanet.gif";
$planettypes[1]= "smallplanet.gif";
$planettypes[2]= "mediumplanet.gif";
$planettypes[3]= "largeplanet.gif";
$planettypes[4]= "hugeplanet.gif";

// New ship code
// Find out what my ship is
$res = $db->Execute("SELECT * FROM $dbtables[ship_types],$dbtables[ships] WHERE $dbtables[ship_types].type_id=$dbtables[ships].type AND $dbtables[ships].player_id=$playerinfo[player_id] AND $dbtables[ships].ship_id=$playerinfo[currentship]");
$shipinfo = $res->fields;

// Mission Check
include("mission.php");
?>
<div align="center"><img src="images/startitle.gif"> 
  <table border=0 cellspacing=0 cellpadding=0 bgcolor="$color_line2">
    <tr> 
      <td align="center"> 
        <? echo player_insignia_name($username);?>
        <b>
        <? echo $playerinfo[character_name]."</b>".$l_abord ?>
        <b> <a href="report.php?kk=<? echo date("U")?>">
        <? echo $shipinfo[ship_name] ?>
        </a> 
<?
$towedimage="";
if ($shipinfo[tow]>0) {
	$res = $db->Execute("SELECT * FROM $dbtables[ships],$dbtables[ship_types] WHERE ship_id=$shipinfo[tow] AND type_id=type");
	$towedship = $res->fields;
	echo " towing the <font color=white>$towedship[ship_name]</font>";
	$towedimage = "<img src=\"images/$towedship[image]\" border=0>";
}
?>
</b> </td>
    </tr>
  </table>
<?
 $result = $db->Execute("SELECT * FROM $dbtables[messages] WHERE recp_id='".$playerinfo[player_id]."' AND notified='N'");
 if ($result->RecordCount() > 1)
 {
	echo "<BR><center><a href=readmail.php?kk=". date("U").">*** ".$l_youhave . $result->RecordCount() . $l_messages_wait." ***</a></CENTER><BR>";
 } else if ($result->RecordCount() == 1)
 {
	echo "<BR><center><a href=readmail.php?kk=". date("U").">*** You have a message waiting for you ***</a></CENTER><BR>";
    //$db->Execute("UPDATE $dbtables[messages] SET notified='Y' WHERE recp_id='".$playerinfo[player_id]."'");
 }

?>
  <table width="100%" border="0" cellspacing="0" cellpadding="1">
    <tr> 
      <td><a href="report.php">
        <?php echo "<img src=\"images/$shipinfo[image]\" border=0 align=left>$towedimage"; ?>
        </a></td><td align=top>
      Turns available:<br>
        <b>
        <? echo NUMBER($playerinfo[turns]) ?>
        </b><br>
        Turns used:<br>
        <b>
        <? echo NUMBER($playerinfo[turns_used]); ?>
        </b><br>
      </td>
    </tr>
  </table>
</div>
<div align="center">Score:<b> 
  <? echo NUMBER($playerinfo[score])?>
  </b> </div>
<table width="100%" cellpadding=0 cellspacing=1 border=0 align=center>
<?
if($zoneinfo[zone_id] < 5)
  $zoneinfo[zone_name] = $l_zname[$zoneinfo[zone_id]];
?>
	<td colspan=2 align=center> <font color=silver size=<? echo $basefontsize + 2; ?> face="arial"> 
      You are in sector: </font><font color=white><b> 
      <? echo $playerinfo[sector]; ?>
      </b><br>
      <a href="<? echo "zoneinfo.php?kk=".date("U")."&zone=$zoneinfo[zone_id]"; ?>"><b> 
      <? echo "<font size=", $basefontsize + 2," face=\"arial\">$zoneinfo[zone_name]</font>"; ?>
      </b></a></font></td>
</tr>
</table>
<center>
  <font size=<? echo $basefontsize+2; ?> face="arial" color=white><b> 
  <?
if(!empty($sectorinfo[beacon]))
{
  echo "<font color=white size=", $basefontsize + 2," face=\"arial\"><b>", $sectorinfo[beacon], "</b></font>";
}


?>
  <br>
  <? echo $l_tradingport ?>
  :&nbsp; 
  <?
if($sectorinfo[port_type] != "none")
{
  echo "<a href=port.php?kk=".date("U").">", ucfirst(t_port($sectorinfo[port_type])), "</a>";
  $port_bnthelper_string="<!--port:" . $sectorinfo[port_type] . ":" . $sectorinfo[port_ore] . ":" . $sectorinfo[port_organics] . ":" . $sectorinfo[port_goods] . ":" . $sectorinfo[port_energy] . ":-->";
}
else
{
  echo "</b><font size=", $basefontsize+2,">$l_none</font><b>";
  $port_bnthelper_string="<!--port:none:0:0:0:0:-->";
}
if($sectorinfo[port_type] == 'special') {
    echo "<p>$l_main_shipyard";
	echo "<br>$l_main_spacedock";
}
?>
  </b></font> 
</center>
<br>

<center><b><font size=<? echo $basefontsize+2; ?> face="arial" color=white><? echo $l_planet_in_sec . $sectorinfo[sector_id];?>:</font></b></center>
<center>
<?

if($num_planets > 0)
{
  $totalcount=0;
  $curcount=0;
  $i=0;
  while($i < $num_planets)
  {
    if($planets[$i][owner] != 0)
    {
      $result5 = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=" . $planets[$i][owner]);
      $planet_owner = $result5->fields;
	  $result6 = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=" . $planets[$i][owner]." AND ship_id=$planet_owner[currentship]");
      $planet_owner_ship = $result6->fields;

      $planetavg = $planet_owner_ship[hull] + $planet_owner_ship[engines] + $planet_owner_ship[computer] + $planet_owner_ship[beams] + $planet_owner_ship[torp_launchers] + $planet_owner_ship[shields] + $planet_owner_ship[armour];
      $planetavg /= 7;

      if($planetavg < 8)
        $planetlevel = 0;
      else if ($planetavg < 12)
        $planetlevel = 1;
      else if ($planetavg < 16)
        $planetlevel = 2;
      else if ($planetavg < 20)
        $planetlevel = 3;
      else
        $planetlevel = 4;
    }
    else
      $planetlevel=0;
    //echo "<td align=center valign=top>";
    //echo "<td align=center>";
	echo "<center>";
    echo "<A HREF=planet.php?kk=".date("U")."&planet_id=" . $planets[$i][planet_id] . ">";
    echo "<img src=\"images/$planettypes[$planetlevel]\" border=0></a><BR><font size=", $basefontsize + 1, " color=#ffffff face=\"arial\">";
    if(empty($planets[$i][name]))
    {
      echo $l_unnamed;
      $planet_bnthelper_string="<!--planet:Y:Unnamed:";
    }
    else
    {
      echo $planets[$i][name];
      $planet_bnthelper_string="<!--planet:Y:" . $planets[$i][name] . ":";
    }

    if($planets[$i][owner] == 0)
    {
      echo "<br>($l_unowned)";
      $planet_bnthelper_string=$planet_bnthelper_string . "Unowned:-->";
    }
    else
    {
       echo "<br>($planet_owner[character_name])";
	   	if ($planets[$i][corp] !=0) {
	  		$result7 = $db->Execute("SELECT team_name FROM $dbtables[teams] WHERE id=" . $planets[$i][corp]." LIMIT 1");
			$row = $result7->fields;
			echo "<br>(<font color=red>$row[team_name]</font>)";
	  	}	   
      $planet_bnthelper_string=$planet_bnthelper_string . $planet_owner[character_name] . ":N:-->";
    }
    echo "</font></center><br>";

    $totalcount++;
    if($curcount == $picsperrow - 1)
    {
      //echo "</tr><tr>";
      $curcount=0;
    }
    else
      $curcount++;
    $i++;
  }
}
else
{
  //echo "<td align=center><div align=center>";
  echo "<br><center><font color=white size=", $basefontsize +2, ">$l_none</font></center><br><br>";
  $planet_bnthelper_string="<!--planet:N:::-->";
}
?>
</center>
<center><b><font size=<? echo $basefontsize+2; ?> face="arial" color=white><? echo $l_ships_in_sec . $sectorinfo[sector_id];?>:</font><br></b></center>
<?
// ************************ Ships in sector ************************
if($playerinfo[sector] != 0)
{
	/****************************** NEW CODE  *******************************/

    $result4 = $db->Execute("SELECT $dbtables[ships].*,$dbtables[ship_types].image,$dbtables[players].character_name,$dbtables[teams].team_name FROM $dbtables[ships],$dbtables[ship_types],$dbtables[players] LEFT OUTER JOIN $dbtables[teams] ON $dbtables[players].team = $dbtables[teams].id WHERE $dbtables[ships].player_id != $playerinfo[player_id] AND $dbtables[ships].sector=$playerinfo[sector] AND $dbtables[ships].on_planet='N' AND type_id=type AND $dbtables[ships].player_id=$dbtables[players].player_id");
   	$totalcount=0;

   	echo "<center>";
    while(!$result4->EOF)
    {
    	$targetship=$result4->fields;
		// Is the ship owned or not? Unowned ships are always visible
		if ($targetship[player_id]==0) {
			echo "<a href=ship.php?kk=".date("U")."&ship_id=$targetship[ship_id]><img src=\"images/", $targetship[image],"\" border=0></a><BR><font size=", $basefontsize +1, " color=#ffffff face=\"arial\">$targetship[ship_name]<br>(Unowned)<br></font>";
			$totalcount++;
        } else { 	
			// If the ship is towing anything then it's cloak is going to be weakened
			if ($targetship[tow]>0) {
				$targetship[cloak] = min($targetship[cloak]-1,0);
			}
			$success = SCAN_SUCCESS($shipinfo[sensors], $targetship[cloak]);
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
				// Get the player's medals
				$medRes = $db->Execute("SELECT graphic,medal_name FROM award_winners, $dbtables[medals], $dbtables[config] WHERE character_name='".addslashes($targetship[character_name])."' AND award_winners.type_id=$dbtables[medals].type_id AND award_winners.game_num=($dbtables[config].game_num-1)");
				if (!$medRes->EOF) {
					$medalList = "&nbsp;<a href=ranking.php?detail=".addslashes($targetship[character_name]).">";
					while (!$medRes->EOF) {
						$medal = $medRes->fields;
						$medalList .= "<image src='images/$medal[graphic]' alt='$medal[medal_name]' border=0>";
						$medRes->MoveNext();
					}
					$medalList .= "</a>";
				}
				if ($targetship[team_name]) {
					echo "<a href=ship.php?kk=".date("U")."&ship_id=$targetship[ship_id]><img src=\"images/", $targetship[image],"\" border=0></a><BR><font size=", $basefontsize +1, " color=#ffffff face=\"arial\">$targetship[ship_name]<br>($targetship[character_name])$medalList&nbsp;(<font color=#33ff00>$targetship[team_name]</font>)</font>";
				}
				else
				{
					echo "<a href=ship.php?kk=".date("U")."&ship_id=$targetship[ship_id]><img src=\"images/", $targetship[image],"\" border=0></a><BR><font size=", $basefontsize +1, " color=#ffffff face=\"arial\">$targetship[ship_name]<br>($targetship[character_name])$medalList</font>";
				}
				// ********** Display any towed ship too ************* //
				if ($targetship[tow] > 0) {
					$result6 = $db->Execute("SELECT * FROM $dbtables[ships],$dbtables[ship_types] WHERE ship_id=$targetship[tow] AND type_id=type");
					$towedship = $result6->fields;
					echo "<br><a href=ship.php?kk=".date("U")."&ship_id=$targetship[ship_id]><img src=\"images/", $towedship[image],"\" border=0></a><BR><font size=", $basefontsize +1, " color=#ffffff face=\"arial\">$towedship[ship_name]<br>(towed by $targetship[character_name])</font>";
				}
				echo "<br>";
	
				$totalcount++;
			} // End if success roll
		} // End If owned ship or not
        $result4->MoveNext();
	} // End while ships in sector
	if($totalcount == 0)
    {
    	echo "<br><font size=2 color=white>None Detected</font><br><br>";
		echo "</center>";
		$displayed=true;
    }
}
else
{
    echo "<center><br><font size=2 color=white>$l_sector_0</font><br><br>";
	echo "</center>";
}
// Sector defenses
if($num_defences>0) echo "<b><center><font face=\"arial\" color=white>$l_sector_def</font><br></center></b>";
?>
<center>
<?
if($num_defences > 0)
{
  $totalcount=0;
  $curcount=0;
  $i=0;
  while($i < $num_defences)
  {

    $defence_id = $defences[$i]['defence_id'];
    //echo "<td align=center valign=top>";
    if($defences[$i]['defence_type'] == 'F')
    {
       echo "<a href=modify-defences.php?kk=".date("U")."&defence_id=$defence_id><img src=\"images/fighters.gif\" border=0></a><BR><font size=", $basefontsize + 1, " color=#ffffff face=\"arial\"><BR>";
       $def_type = $l_fighters;
       $mode = $defences[$i]['fm_setting'];
       if($mode == 'attack')
         $mode = $l_md_attack;
       else
        $mode = $l_md_toll;
       $def_type .= $mode;
    }
    elseif($defences[$i]['defence_type'] == 'M')
    {
       echo "<a href=modify-defences.php?kk=".date("U")."&defence_id=$defence_id><img src=\"images/mines.gif\" border=0></a><BR><font size=", $basefontsize + 1, " color=#ffffff face=\"arial\">";
       $def_type = $l_mines;
    }
    $char_name = $defences[$i]['character_name'];
    $qty = $defences[$i]['quantity'];
    echo "$char_name ( $qty $def_type )";
    echo "</font><BR>";

    $totalcount++;
    if($curcount == $picsperrow - 1)
    {
      //echo "</tr><tr>";
      $curcount=0;
    }
    else
      $curcount++;
    $i++;
  }
  //echo "</td></tr></table>";
}
else
{
  //echo "<td align=center valign=top>";
  //echo "<br><font color=white size=", $basefontsize +2, ">None</font><br><br>";
  //echo "</td></tr></table>";
}
?>
</center>
<center><img src="images/lcorner.gif" width="8" height="11" border="0" alt=""><b> 
      <? echo $l_cargo; ?>
      </b> <img src="images/rcorner.gif" width="8" height="11" border="0" alt=""> 
</center>
      <table border=1 cellpadding=1 cellspacing=0 bgcolor="#005000" align="center" class=dis>
        <tr>
          <td nowrap align='left'>&nbsp;<img height=12 width=12 alt="<? echo $l_ore ?>" src="images/ore.gif">&nbsp;
            <? echo $l_ore ?>
            &nbsp;</td>
          <td nowrap align='right'><span class=mnu>&nbsp;
            <? echo NUMBER($shipinfo[ship_ore]); ?>
            &nbsp;</span></td>
        </tr>
        <tr>
          <td nowrap align='left'>&nbsp;<img height=12 width=12 alt="<? echo $l_organics ?>" src="images/organics.gif">&nbsp;
            <? echo $l_organics ?>
            &nbsp;</td>
          <td nowrap align='right'><span class=mnu>&nbsp;
            <? echo NUMBER($shipinfo[ship_organics]); ?>
            &nbsp;</span></td>
        </tr>
        <tr>
          <td nowrap align='left'>&nbsp;<img height=12 width=12 alt="<? echo $l_goods ?>" src="images/goods.gif">&nbsp;
            <? echo $l_goods ?>
            &nbsp;</td>
          <td nowrap align='right'><span class=mnu>&nbsp;
            <? echo NUMBER($shipinfo[ship_goods]); ?>
            &nbsp;</span></td>
        </tr>
        <tr>
          <td nowrap align='left'>&nbsp;<img height=12 width=12 alt="<? echo $l_energy ?>" src="images/energy.gif">&nbsp;
            <? echo $l_energy ?>
            &nbsp;</td>
          <td nowrap align='right'><span class=mnu>&nbsp;
            <? echo NUMBER($shipinfo[ship_energy]); ?>
            &nbsp;</span></td>
        </tr>
        <tr>
          <td nowrap align='left'>&nbsp;<img height=12 width=12 alt="<? echo $l_colonists ?>" src="images/colonists.gif">&nbsp;
            <? echo $l_colonists ?>
            &nbsp;</td>
          <td nowrap align='right'><span class=mnu>&nbsp;
            <? echo NUMBER($shipinfo[ship_colonists]); ?>
            &nbsp;</span></td>
        </tr>
        <tr>
          <td nowrap align='left'>&nbsp;<img height=12 width=12 alt="<? echo $l_credits ?>" src="images/credits.gif">&nbsp;
            <? echo $l_credits ?>
            &nbsp;</td>
          <td nowrap align='right'><span class=mnu>&nbsp;
            <? echo NUMBER($playerinfo[credits]); ?>
            &nbsp;</span></td>
        </tr>
      </table>

<center><img src="images/lcorner.gif" width="8" height="11" border="0" alt=""><b> 
  <? echo $l_main_warpto; ?>
  </b> <img src="images/rcorner.gif" width="8" height="11" border="0" alt=""> 
</center>
<table border="0" cellpadding="1" cellspacing="0" align="center">
  <?php
			if(!$num_links) {
				echo "<tr><td colspan=2>$l_no_warplink</td></tr>";
				$link_bnthelper_string="<!--links:N";
			} else {
  				$link_bnthelper_string="<!--links:Y";
  				for($i=0; $i<$num_links;$i++) {
     				echo "<tr><td nowrap align='left'><a href=\"move.php?kk=".date("U")."&sector=$links[$i]\">=&gt;&nbsp;$links[$i]</a></td><td nowrap align='right'><a href=\"lrscan.php?kk=".date("U")."&sector=$links[$i]\">[$l_scan]</a></td></tr>";
     				$link_bnthelper_string=$link_bnthelper_string . ":" . $links[$i];
  				}
			}
			$link_bnthelper_string=$link_bnthelper_string . ":-->";
			echo "<tr><td colspan=2 align=center><a href=\"lrscan.php?kk=".date("U")."&sector=*\">[$l_fullscan]</a></td></tr>";
		?>
</table>
<center>
<img src="images/lcorner.gif" width="8" height="11" border="0" alt=""> 
      <b> 
      <? echo $l_traderoutes ?>
      </b> <img src="images/rcorner.gif" width="8" height="11" border="0" alt=""> 
</center>
<table border="1" cellpadding="0" cellspacing="0" align="center" width=100%>
  <tr>
    <td valign=top align=center>
      <?php
			
			  $i=0;
			  $num_traderoutes = 0;
			
			/********* Port query ************************************ begin *********/
			  $query = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE source_type='P' AND source_id=$playerinfo[sector] AND owner=$playerinfo[player_id] ORDER BY dest_id ASC");
			  while(!$query->EOF)
			  {
				$traderoutes[$i]=$query->fields;
				$i++;
				$num_traderoutes++;
				$query->MoveNext();
			  }
			/********* Port query ************************************ end **********/
			
			/********* Sector Defense Trade route query *************** begin ********/
			/********* this is still under developement ***/
			  $query = $db->Execute("SELECT * FROM $dbtables[traderoutes] WHERE source_type='D' AND source_id=$playerinfo[sector] AND owner=$playerinfo[player_id] ORDER BY dest_id ASC");
			  while(!$query->EOF)
			  {
				$traderoutes[$i]=$query->fields;
				$i++;
				$num_traderoutes++;
				$query->MoveNext();
			  }
			/********* Defense querry ********************************* end **********/
			/********* Personal planet traderoute type query ********** begin ********/
			  $query = $db->Execute("SELECT * FROM $dbtables[planets], $dbtables[traderoutes] WHERE source_type='L' AND source_id=$dbtables[planets].planet_id AND $dbtables[planets].sector_id=$playerinfo[sector] AND $dbtables[traderoutes].owner=$playerinfo[player_id]");
			  while(!$query->EOF)
			  {
				$traderoutes[$i]=$query->fields;
				$i++;
				$num_traderoutes++;
				$query->MoveNext();
			  }
			/********* Personal planet traderoute type query ********* end **********/
			/********* Corperate planet traderoute type query ******** begin ********/
			  $query = $db->Execute("SELECT * FROM $dbtables[planets], $dbtables[traderoutes] WHERE source_type='C' AND source_id=$dbtables[planets].planet_id AND $dbtables[planets].sector_id=$playerinfo[sector] AND $dbtables[traderoutes].owner=$playerinfo[player_id]");
			  while(!$query->EOF)
			  {
				$traderoutes[$i]=$query->fields;
				$i++;
				$num_traderoutes++;
				$query->MoveNext();
			  }
			/********* Corperate planet traderoute type query ******** end **********/
			
			  if($num_traderoutes == 0)
				echo "<center><a class=dis>&nbsp;$l_none &nbsp;</a></center>";
			  else
			  {
				$i=0;
				while($i<$num_traderoutes)
				{
				  echo "&nbsp;<a class=mnu href=traderoute.php?kk=".date("U")."&engage=" . $traderoutes[$i][traderoute_id] . ">";
				  if($traderoutes[$i][source_type] == 'P')
					echo "$l_port&nbsp;";
				  elseif($traderoutes[$i][source_type] == 'D')
					echo "Def's ";
				  else
				  {
					$query = $db->Execute("SELECT name FROM $dbtables[planets] WHERE planet_id=" . $traderoutes[$i][source_id]);
					if(!$query || $query->RecordCount() == 0)
					  echo $l_unknown;
					else
					{
					  $planet = $query->fields;
					  if($planet[name] == "")
						echo "$l_unnamed ";
					  else
						echo "$planet[name] ";
					}
				  }
			
				  if($traderoutes[$i][circuit] == '1')
					echo "=&gt;&nbsp;";
				  else
					echo "&lt;=&gt;&nbsp;";
			
				  if($traderoutes[$i][dest_type] == 'P')
					echo $traderoutes[$i][dest_id];
				  elseif($traderoutes[$i][dest_type] == 'D')
					echo "Def's in " .  $traderoutes[$i][dest_id] . "";
				  else
				  {
					$query = $db->Execute("SELECT name FROM $dbtables[planets] WHERE planet_id=" . $traderoutes[$i][dest_id]);
					if(!$query || $query->RecordCount() == 0)
					  echo $l_unknown;
					else
					{
					  $planet = $query->fields;
					  if($planet[name] == "")
						echo $l_unnamed;
					  else
						echo $planet[name];
					}
				  }
				  echo "</a>&nbsp;<br>";
				  $i++;
				}
			  }
			
			?>
      </td></tr><tr><td align=center>
      <a class=mnu href=traderoute.php>
      <? echo $l_trade_control ?>
      </a>
    </td>
  </tr>
</table>
<br>
<center><img src="images/lcorner.gif" width="8" height="11" border="0" alt=""><b><? echo $l_realspace ?></b>
<img src="images/rcorner.gif" width="8" height="11" border="0" alt="">
</center>
<TABLE BORDER=1 CELLPADDING=1 CELLSPACING=0 BGCOLOR="#005000" align="center">
<TR><TD NOWRAP align=center>
<div class=mnu>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>&engage=1&destination=<? echo $playerinfo[preset1]; ?>">=&gt;&nbsp;<? echo $playerinfo[preset1]; ?></a>&nbsp;<a class=dis href=preset.php>[<? echo $l_set?>]</a>&nbsp;<br>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>&engage=1&destination=<? echo $playerinfo[preset2]; ?>">=&gt;&nbsp;<? echo $playerinfo[preset2]; ?></a>&nbsp;<a class=dis href=preset.php>[<? echo $l_set?>]</a>&nbsp;<br>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>&engage=1&destination=<? echo $playerinfo[preset3]; ?>">=&gt;&nbsp;<? echo $playerinfo[preset3]; ?></a>&nbsp;<a class=dis href=preset.php>[<? echo $l_set?>]</a>&nbsp;<br>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>&engage=1&destination=<? echo $playerinfo[preset4]; ?>">=&gt;&nbsp;<? echo $playerinfo[preset4]; ?></a>&nbsp;<a class=dis href=preset.php>[<? echo $l_set?>]</a>&nbsp;<br>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>&engage=1&destination=<? echo $playerinfo[preset5]; ?>">=&gt;&nbsp;<? echo $playerinfo[preset5]; ?></a>&nbsp;<a class=dis href=preset.php>[<? echo $l_set?>]</a>&nbsp;<br>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>&engage=1&destination=<? echo $playerinfo[preset6]; ?>">=&gt;&nbsp;<? echo $playerinfo[preset6]; ?></a>&nbsp;<a class=dis href=preset.php>[<? echo $l_set?>]</a>&nbsp;<br>
&nbsp;<a class=mnu href="rsmove.php?kk=<? echo date("U")?>">=&gt;&nbsp;<? echo $l_main_other;?></a>&nbsp;<br>
</div>
</td></tr>
</table>
<center><img src="images/lcorner.gif" width="8" height="11" border="0" alt="">
<b><? echo $l_commands ?></b><img src="images/rcorner.gif" width="8" height="11" border="0" alt="">
</center>

<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=0 BGCOLOR="#005000" align="center">

<TR><TD>
<?
if (!strstr($playerinfo[subscribed],"payment")) {
	echo "&nbsp;<a class=mnu href=subscribe.php?kk=".date("U").">Subscribe</a>&nbsp;<br>";
}
?>
&nbsp;<a class=mnu href="tell.php?kk=<? echo date("U")?>">Tell A Friend</a>&nbsp;<br>
&nbsp;<a class=mnu href="device.php?kk=<? echo date("U")?>"><? echo $l_devices ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="planet-report.php?kk=<? echo date("U")?>">Planet Report</a>&nbsp;<br>
&nbsp;<a class=mnu href="ships-report.php?kk=<? echo date("U")?>">Ships Report</a>&nbsp;<br>
&nbsp;<a class=mnu href="log.php?kk=<? echo date("U")?>"><? echo $l_log ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="defence-report.php?kk=<? echo date("U")?>"><? echo $l_sector_def ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="readmail.php?kk=<? echo date("U")?>"><? echo $l_read_msg ?></A>&nbsp;<br>
&nbsp;<a class=mnu href="mailto2.php?kk=<? echo date("U")?>"><? echo $l_send_msg ?></a>&nbsp;</td>
<td>
&nbsp;<a class=mnu href="ranking.php?kk=<? echo date("U")?>"><? echo $l_rankings ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="teams.php?kk=<? echo date("U")?>"><? echo $l_teams ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="self-destruct.php?kk=<? echo date("U")?>"><? echo $l_ohno ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="options.php?kk=<? echo date("U")?>"><? echo $l_options ?></a>&nbsp;<br>
&nbsp;<a class=mnu href="navcomp.php?kk=<? echo date("U")?>"><? echo $l_navcomp ?></a>&nbsp;<br>
<?
if ($ksm_allowed == true)
{
echo "&nbsp;<a class=mnu href=galaxy2.php?kk=".date("U").">$l_map</a>&nbsp;<br>";
}
?>
</td></tr>
<tr><td colspan=2 align=center>
<div class=mnu>
&nbsp;<a class=mnu href="faq.html">FAQ</a>&nbsp;<br>
&nbsp;<a class=mnu href="help.php">Help</a>&nbsp;<br>
&nbsp;<a class=mnu href="feedback.php"><? echo $l_feedback ?></a>&nbsp;<br>
<?
if(!empty($link_forums))
{
    echo "&nbsp;<a class=\"mnu\" href=\"$link_forums\" TARGET=\"_blank\">$l_forums</a>&nbsp;<br>";
}
?>
<a class=mnu href="logout.php"><? echo $l_logout ?></a>
</div>
</td></tr>
</table>
<?


//-------------------------------------------------------------------------------------------------

include("footer.php");

?>
