<?
include("combat.php");

$title=$l_planet_title;

//-------------------------------------------------------------------------------------------------

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo=$result->fields;

$result = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
$shipinfo=$result->fields;

$result2 = $db->Execute("SELECT * FROM $dbtables[universe] WHERE sector_id=$playerinfo[sector]");
$sectorinfo=$result2->fields;

$planet_id = stripnum($planet_id);

$result3 = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id='$planet_id' && sector_id=$playerinfo[sector]");
if($result3) {
  $planetinfo=$result3->fields;
} else {
  $planetinfo="";
}

bigtitle();

srand((double)microtime()*1000000);

if($planetinfo != "") {
/* if there is a planet in the sector show appropriate menu */
	if($playerinfo[sector] != $planetinfo[sector_id]) {
    	if($playerinfo[on_planet] == 'Y')
      		$db->Execute("UPDATE $dbtables[players] SET on_planet='N' WHERE player_id=$playerinfo[player_id]");
    	echo "$l_planet_none <p>";
    	TEXT_GOTOMAIN();
    	include("footer.php");
    	die();
  	}
  	if(($planetinfo[owner] == 0  || $planetinfo[defeated] == 'Y') && $command != "capture") {
    	if($planetinfo[owner] == 0) echo "$l_planet_unowned.<BR><BR>";
    	$capture_link="<a href=planet.php?planet_id=$planet_id&command=capture&kk=".date("U").">$l_planet_capture1</a>";
    	$l_planet_capture2=str_replace("[capture]",$capture_link,$l_planet_capture2);
    	echo "$l_planet_capture2.<BR><BR>";
    	echo "<BR>";
    	TEXT_GOTOMAIN();
    	include("footer.php");
    	die();
  	}
  	if($planetinfo[owner] != 0) {
    	$result3 = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$planetinfo[owner]");
    	$ownerinfo = $result3->fields;
		$result3 = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$ownerinfo[player_id] AND ship_id=$ownerinfo[currentship]");
    	$ownershipinfo = $result3->fields;
		// Check if ship destroyed or not
		if ($ownershipinfo[ship_destroyed] == 'Y') {
			$ownershipinfo[beams] = 0;
			$ownershipinfo[torp_launchers] = 0;
			$ownershipinfo[shields] = 0;
			$ownershipinfo[cloak] = 0;
		} 
  	}
  	if(empty($command)) {
    	/* ...if there is no planet command already */
		/* Print the welcome message */
		echo "<h2>";
    	if(empty($planetinfo[name])) {
        	$l_planet_unnamed=str_replace("[name]",$ownerinfo[character_name],$l_planet_unnamed);
      		echo "$l_planet_unnamed";
    	} else {
     		$l_planet_named=str_replace("[name]",$ownerinfo[character_name],$l_planet_named);
     		$l_planet_named=str_replace("[planetname]",$planetinfo[name],$l_planet_named);
      		echo "$l_planet_named";
    	}
		echo "</h2>";
     	echo "$l_turns_have $playerinfo[turns]<br>";
	 	if($planetinfo[sells] == "Y") {
        	echo "$l_planet_selling<BR>";
      	} else {
        	echo "$l_planet_not_selling<BR>";
      	}
		// Set up link strings
	 	$l_planet_leave_link = "<a href=planet.php?planet_id=$planet_id&command=leave&kk=".date("U").">" . $l_planet_leave_link . "</a>";
     	$l_planet_leave=str_replace("[leave]",$l_planet_leave_link,$l_planet_leave);
	    $l_planet_land_link = "<a href=planet.php?planet_id=$planet_id&command=land&kk=".date("U").">" . $l_planet_land_link . "</a>";
     	$l_planet_land=str_replace("[land]",$l_planet_land_link,$l_planet_land);

		if($planetinfo[owner] == $playerinfo[player_id] || ($planetinfo[corp] == $playerinfo[team] && $playerinfo[team] > 0)) {
      		/* owner menu */
	  		if($playerinfo[on_planet] == 'Y' && $playerinfo[planet_id] == $planet_id) {
        		echo "$l_planet_onsurface<p>";
        		echo "$l_planet_leave<BR>";
				echo "$l_main_spacedock<br>";
				echo "<a href=buildship.php?kk=".date("U").">Ship Building Menu</a><br>";
        		echo "$l_planet_logout<BR>";
      		} else {
        		echo "$l_planet_orbit<p>";
        		echo "$l_planet_land<BR>";
				echo "Land on the planet to access its Space Dock<br>";
      		}

     		$l_planet_name_link = "<a href=planet.php?planet_id=$planet_id&command=name&kk=".date("U").">" . $l_planet_name_link . "</a>";
     		$l_planet_name =str_replace("[name]",$l_planet_name_link,$l_planet_name2);
     
     		echo "$l_planet_name<BR>";
      		$l_planet_transfer_link="<a href=planet.php?planet_id=$planet_id&command=transfer&kk=".date("U").">" . $l_planet_transfer_link . "</a>";
      		$l_planet_transfer=str_replace("[transfer]",$l_planet_transfer_link,$l_planet_transfer);
      		echo "$l_planet_transfer<BR>";
      		$l_planet_tsell_link="<a href=planet.php?planet_id=$planet_id&command=sell&kk=".date("U").">" . $l_planet_tsell_link ."</a>";
      		$l_planet_tsell=str_replace("[toggle]",$l_planet_tsell_link,$l_planet_tsell);
      		echo "$l_planet_tsell<BR>";
			if ($planetinfo[owner] == $playerinfo[player_id]) {
				echo "<A HREF=planet-report.php?PRepType=3>Set commodity sale prices for all your planets.</A><BR>";
			}
      		if($planetinfo[base] == "N") {
         		$l_planet_bbase_link = "<a href=planet.php?planet_id=$planet_id&command=base&kk=".date("U").">" . $l_planet_bbase_link . "</a>";
         		$l_planet_bbase=str_replace("[build]",$l_planet_bbase_link,$l_planet_bbase);
        		echo "$l_planet_bbase<BR>";
      		} else {
				echo "$l_planet_hasbase<BR>";
      		}
	 		// Make an aliance planet
      		if ($playerinfo[player_id] == $planetinfo[owner]) {
        		if ($playerinfo[team] <> 0) {
	   				if ($planetinfo[corp] == 0) {
           				$l_planet_mcorp_linkC = "<a href=corp.php?planet_id=$planet_id&action=planetcorp&kk=".date("U").">" . $l_planet_mcorp_linkC . "</a>";
           				$l_planet_mcorp=str_replace("[planet]",$l_planet_mcorp_linkC,$l_planet_mcorp);
	 					echo "$l_planet_mcorp<BR>";
	   				} else {
        				$l_planet_mcorp_linkP = "<a href=corp.php?planet_id=$planet_id&action=planetpersonal&kk=".date("U").">" . $l_planet_mcorp_linkP . "</a>";
        				$l_planet_mcorp=str_replace("[planet]",$l_planet_mcorp_linkP,$l_planet_mcorp);
						echo "$l_planet_mcorp<BR>";
	   				}
         		}
      		}
			// Destroy planet option
    		if($playerinfo['player_id'] == $planetinfo['owner']) { 
       			if($destroy==1 && $allow_genesis_destroy) { 
          			echo "<font color=red>$l_planet_confirm</font><br><A HREF=planet.php?planet_id=$planet_id&destroy=2&kk=".date("U").">yes</A><br>"; 
          			echo "<A HREF=planet.php?planet_id=$planet_id&kk=".date("U").">no!</A><BR><br>"; 
       			} elseif($destroy==2 && $allow_genesis_destroy) { 
          			if($shipinfo[dev_genesis] > 0) {
						// ********  Change ranking *********
						$delta_rating = $planetinfo[colonists];
             			$update = $db->Execute("delete from $dbtables[planets] where planet_id='$planet_id'"); 
             			$update2=$db->Execute("UPDATE $dbtables[players] SET turns_used=turns_used+1, turns=turns-1, rating=rating-$delta_rating WHERE player_id=$playerinfo[player_id]"); 
						$update2=$db->Execute("UPDATE $dbtables[ships] SET dev_genesis=dev_genesis-1 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]"); 
						// Destroy all the ships on the planet that are being built
						$update3=$db->Execute("DELETE FROM $dbtables[ships] WHERE on_planet='Y' AND planet_id='$planet_id' AND ship_id != $playerinfo[currentship]");
						//echo "\n\n\n\n<!--DELETE FROM $dbtables[ships] WHERE on_planet='Y' AND planet_id='$planet_id' AND ship_colonists < 0-->\n\n\n\n";
             			$update3=$db->Execute("UPDATE $dbtables[players] SET on_planet='N' WHERE planet_id='$planet_id'");
						$update3=$db->Execute("UPDATE $dbtables[ships] SET on_planet='N' WHERE planet_id='$planet_id'"); 
             			calc_ownership($playerinfo[sector]);
             			echo "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL=main.php\">"; 
          			} else { 
             			echo "$l_gns_nogenesis<br>"; 
          			} 
       			} elseif($allow_genesis_destroy) { 
          			echo "<A onclick=\"javascript: alert ('alert:$l_planet_warning');\" HREF=planet.php?planet_id=$planet_id&destroy=1&kk=".date("U").">$l_planet_destroyplanet</a><br>"; 
       			} 
    		} 
	  		if($allow_ibank) {
				if ($browser == "treo") {
					 echo "$l_ifyouneedplan <A HREF=IGBtreo.php?planet_id=$planet_id&kk=".date("U").">$l_igb_term</A>.<BR>";
				} else {
  					echo "$l_ifyouneedplan <A HREF=IGB.php?planet_id=$planet_id&kk=".date("U").">$l_igb_term</A>.<BR>";
				}
	  		}
	  		echo "<A HREF =bounty.php?&kk=".date("U").">$l_by_placebounty</A><p>";

      		$l_planet_readlog_link="<a href=log.php?&kk=".date("U").">" . $l_planet_readlog_link ."</a>";
      		$l_planet_readlog=str_replace("[View]",$l_planet_readlog_link,$l_planet_readlog);
      		echo "<BR>$l_planet_readlog<BR>";


      		/* change production rates */
      		echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>";
     		echo "<TR BGCOLOR=\"$color_line1\">";
  		    echo "<TD COLSPAN=2 align=center>$l_current_qty</TD></TR>";
      		echo "<TR><TD><B>$l_ore</B></TD><TD>" . NUMBER($planetinfo[ore]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_organics</B></TD><TD>" . NUMBER($planetinfo[organics]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_goods</B></TD><TD>" . NUMBER($planetinfo[goods]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_energy</B></TD><TD>" . NUMBER($planetinfo[energy]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_colonists</B></TD><TD>" . NUMBER($planetinfo[colonists]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_credits</B></TD><TD>" . NUMBER($planetinfo[credits]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_fighters</B></TD><TD>" . NUMBER($planetinfo[fighters]) . "</TD></TR>";
      		echo "<TR><TD><B>$l_torps</B></TD><TD>" . NUMBER($planetinfo[torps]) . "</TD></TR>";
			echo "<TR><TD><B>Planet IQ</B></TD><TD>" . NUMBER($planetinfo[tech_level]) . "</TD></TR>";
	  		echo "</TABLE>";
      		echo "<FORM ACTION=planet.php?planet_id=$planet_id METHOD=POST>";
      		echo "<INPUT TYPE=HIDDEN NAME=command VALUE=productions><BR>";
	  		echo "<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=2>";
      		echo "<TR BGCOLOR=\"$color_line2\"><TD COLSPAN=2 align=center>$l_planet_perc</TD></TR>";
      		echo "<TR><TD><B>$l_ore</B></TD><TD><INPUT TYPE=TEXT NAME=pore VALUE=\"$planetinfo[prod_ore]\" SIZE=6 MAXLENGTH=6></TD></TR>";
      		echo "<TR><TD><B>$l_organics</B></TD><TD><INPUT TYPE=TEXT NAME=porganics VALUE=\"$planetinfo[prod_organics]\" SIZE=6 MAXLENGTH=6></TD></TR>";
      		echo "<TR><TD><B>$l_goods</B></TD><TD><INPUT TYPE=TEXT NAME=pgoods VALUE=\"$planetinfo[prod_goods]\" SIZE=6 MAXLENGTH=6></TD></TR>";
      		echo "<TR><TD><B>$l_energy</B></TD><TD><INPUT TYPE=TEXT NAME=penergy VALUE=\"$planetinfo[prod_energy]\" SIZE=6 MAXLENGTH=6></TD></TR>";
      		echo "<TR><TD><B>$l_fighters</B></TD><TD><INPUT TYPE=TEXT NAME=pfighters VALUE=\"$planetinfo[prod_fighters]\" SIZE=6 MAXLENGTH=6></TD></TR>";
      		echo "<TR><TD><B>$l_torps</B></TD><TD><INPUT TYPE=TEXT NAME=ptorp VALUE=\"$planetinfo[prod_torp]\" SIZE=6 MAXLENGTH=6></TD></TR>";
      		echo "<TR><TD COLSPAN=2 ALIGN=CENTER><INPUT TYPE=SUBMIT VALUE=$l_planet_update></TD></TR>";
      		echo "</TABLE>";
      		echo "</FORM>";
			echo "If the Production Percentages total is less than 100%, the remainder of the colonists will work to raise the planet's intelligence level.<br><br>";
    	} else {
      		/* visitor menu */
      		if($planetinfo[sells] == "Y") {
       			$l_planet_buy_link="<a href=planet.php?planet_id=$planet_id&command=buy&kk=".date("U").">" . $l_planet_buy_link ."</a>";
       			$l_planet_buy=str_replace("[buy]",$l_planet_buy_link,$l_planet_buy);
        		echo "$l_planet_buy<BR>";
      		}
     		$l_planet_att_link="<a href=planet.php?planet_id=$planet_id&command=attac&kk=".date("U").">" . $l_planet_att_link ."</a>";
      		$l_planet_att=str_replace("[attack]",$l_planet_att_link,$l_planet_att);
      		$l_planet_scn_link="<a href=planet.php?planet_id=$planet_id&command=scan&kk=".date("U").">" . $l_planet_scn_link ."</a>";
      		$l_planet_scn=str_replace("[scan]",$l_planet_scn_link,$l_planet_scn);
      		echo "$l_planet_att<BR>";
      		echo "$l_planet_scn<BR>";
      		if ($sofa_on) {
	  			if($shipinfo[ship_fighters] <1) { 
	   	   			echo "$l_sofa not possible - No fighters on board ship<br>";
    			} else { 
		  			echo "<a href=planet.php?planet_id=$planet_id&command=bom&kk=".date("U").">$l_sofa</a><BR>";
    			}
	  		}
		}
	} elseif($planetinfo[owner] !=0 && ($planetinfo[owner] == $playerinfo[player_id] || ($planetinfo[corp] == $playerinfo[team] && $playerinfo[team] > 0))) {
		/* player owns planet and there is a command */
    	if($command == "sell") {
      		if($planetinfo[sells] == "Y") {
        		/* set planet to not sell */
        		echo "$l_planet_nownosell<BR>";
        		$result4 = $db->Execute("UPDATE $dbtables[planets] SET sells='N' WHERE planet_id=$planet_id");
      		} else {
        		echo "$l_planet_nowsell<BR>";
        		$result4b = $db->Execute ("UPDATE $dbtables[planets] SET sells='Y' WHERE planet_id=$planet_id");
      		}
    	} elseif($command == "name") {
      		/* name menu */
      		echo "<form action=\"planet.php?planet_id=$planet_id&command=cname\" method=\"post\">";
      		echo "$l_planet_iname:  ";
      		echo "<input type=\"text\" name=\"new_name\" size=\"20\" maxlength=\"20\" value=\"$planetinfo[name]\"><BR><BR>";
      		echo "<input type=\"submit\" value=\"$l_submit\"><input type=\"reset\" value=\"$l_reset\"><BR><BR>";
      		echo "</form>";
    	} elseif($command == "cname") {
      		/* name2 menu */
      		$new_name = trim(strip_tags($new_name));
      		$result5 = $db->Execute("UPDATE $dbtables[planets] SET name='$new_name' WHERE planet_id=$planet_id");
      		$new_name = stripslashes($new_name);
      		echo "$l_planet_cname $new_name.";
		} elseif($command == "land") {
      		/* land menu */
      		echo "$l_planet_landed<BR><BR>";
	  		// Show a picture of their planet
			$update = $db->Execute("UPDATE $dbtables[players] SET on_planet='Y', planet_id=$planet_id WHERE player_id=$playerinfo[player_id]");
			// Put the ship on the planet too and any towed ship
			$update = $db->Execute("UPDATE $dbtables[ships] SET on_planet='Y', planet_id=$planet_id WHERE player_id=$playerinfo[player_id] AND (ship_id=$playerinfo[currentship] OR ship_id=$shipinfo[tow])");
    	} elseif($command == "leave") {
      		/* leave menu */
      		echo "$l_planet_left<BR><BR>";
      		$update = $db->Execute("UPDATE $dbtables[players] SET on_planet='N' WHERE player_id=$playerinfo[player_id]");
			// Take the ship off the planet too
			$update = $db->Execute("UPDATE $dbtables[ships] SET on_planet='N',planet_id=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
			// Towed ship - towed ships are always "on-planet"
			$update = $db->Execute("UPDATE $dbtables[ships] SET planet_id=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$shipinfo[tow]");
    	} elseif($command == "transfer") {
      		/* transfer menu */
      		$free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
      		$free_power = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy];
      		$l_planet_cinfo=str_replace("[cargo]",NUMBER($free_holds),$l_planet_cinfo);
      		$l_planet_cinfo=str_replace("[energy]",NUMBER($free_power),$l_planet_cinfo);
      		echo "$l_planet_cinfo<BR><BR>";
      		echo "<FORM ACTION=planet2.php?planet_id=$planet_id METHOD=POST>";
      		echo "<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0 CELLPADDING=1>";
      		//echo"<TR BGCOLOR=\"$color_header\"><TD><B>$l_commodity</B></TD><TD><B>$l_planet_onplanet</B></TD><TD><B>$l_ship</B></TD><TD><B>$l_planet_transfer_link</B></TD><TD><B>$l_planet_toplanet</B></TD><TD><B>$l_all?</B></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_header\"><TD>Commodity</TD><TD>Planet</TD><TD>Ship</TD><TD align=center>Amount</TD><TD>To?</TD><TD>All?</TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line1\"><TD>$l_ore</TD><TD>" . NUMBER($planetinfo[ore]) . "</TD><TD>" . NUMBER($shipinfo[ship_ore]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_ore SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tpore VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allore VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line2\"><TD>$l_organics</TD><TD>" . NUMBER($planetinfo[organics]) . "</TD><TD>" . NUMBER($shipinfo[ship_organics]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_organics SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tporganics VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allorganics VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line1\"><TD>$l_goods</TD><TD>" . NUMBER($planetinfo[goods]) . "</TD><TD>" . NUMBER($shipinfo[ship_goods]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_goods SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tpgoods VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allgoods VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line2\"><TD>$l_energy</TD><TD>" . NUMBER($planetinfo[energy]) . "</TD><TD>" . NUMBER($shipinfo[ship_energy]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_energy SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tpenergy VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allenergy VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line1\"><TD>$l_colonists</TD><TD>" . NUMBER($planetinfo[colonists]) . "</TD><TD>" . NUMBER($shipinfo[ship_colonists]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_colonists SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tpcolonists VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allcolonists VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line2\"><TD>$l_fighters</TD><TD>" . NUMBER($planetinfo[fighters]) . "</TD><TD>" . NUMBER($shipinfo[ship_fighters]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_fighters SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tpfighters VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allfighters VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line1\"><TD>$l_torps</TD><TD>" . NUMBER($planetinfo[torps]) . "</TD><TD>" . NUMBER($shipinfo[torps]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_torps SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tptorps VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=alltorps VALUE=-1></TD></TR>";
      		echo"<TR BGCOLOR=\"$color_line2\"><TD>$l_credits</TD><TD>" . NUMBER($planetinfo[credits]) . "</TD><TD>" . NUMBER($playerinfo[credits]) . "</TD><TD align=center><INPUT TYPE=TEXT NAME=transfer_credits SIZE=5 MAXLENGTH=20></TD><TD><INPUT TYPE=CHECKBOX NAME=tpcredits VALUE=-1></TD><TD><INPUT TYPE=CHECKBOX NAME=allcredits VALUE=-1></TD></TR>";
      		echo "</TABLE><BR>";
      		echo "<INPUT TYPE=SUBMIT VALUE=$l_planet_transfer_link>&nbsp;<INPUT TYPE=RESET VALUE=Reset>";
      		echo "</FORM>";
    	} elseif($command == "base") {
      		/* build a base */
      		if($planetinfo[ore] >= $base_ore && $planetinfo[organics] >= $base_organics && $planetinfo[goods] >= $base_goods && $planetinfo[credits] >= $base_credits)
      		{
      			// ** Create The Base
        		$update1 = $db->Execute("UPDATE $dbtables[planets] SET base='Y', ore=$planetinfo[ore]-$base_ore, organics=$planetinfo[organics]-$base_organics, goods=$planetinfo[goods]-$base_goods, credits=$planetinfo[credits]-$base_credits WHERE planet_id=$planet_id");
      			// ** Update User Turns
        		$update1b = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 where player_id=$playerinfo[player_id]");
      			// ** Refresh Plant Info
        		$result3 = $db->Execute("SELECT * FROM $dbtables[planets] WHERE planet_id=$planet_id");
        		$planetinfo=$result3->fields;
      			// ** Notify User Of Base Results
        		echo "$l_planet_bbuild<BR><BR>";
      			// ** Calc Ownership and Notify User Of Results
        		$ownership = calc_ownership($planetinfo[sector_id]);
        		if(!empty($ownership)) {
        			echo "$ownership<p>";
        		}
      		} else {
        		echo "$l_planet_baseinfo<BR><BR>";
      		}
    	} elseif($command == "productions") {
      		/* change production percentages */
      		$porganics = stripnum($porganics);
      		$pore = stripnum($pore);
      		$pgoods = stripnum($pgoods);
      		$penergy = stripnum($penergy);
      		$pfighters = stripnum($pfighters);
      		$ptorp = stripnum($ptorp);
      		if($porganics < 0.0 || $pore < 0.0 || $pgoods < 0.0 || $penergy < 0.0 || $pfighters < 0.0 || $ptorp < 0.0) {
        		echo "$l_planet_p_under<BR><BR>";
      		} elseif(($porganics + $pore + $pgoods + $penergy + $pfighters + $ptorp) > 100.0) {
        		echo "$l_planet_p_over<BR><BR>";
			} else {
        		$db->Execute("UPDATE $dbtables[planets] SET prod_ore=$pore,prod_organics=$porganics,prod_goods=$pgoods,prod_energy=$penergy,prod_fighters=$pfighters,prod_torp=$ptorp WHERE planet_id=$planet_id");
        		echo "$l_planet_p_changed<BR><BR>";
      		}
    	} else {
      		echo "$l_command_no<BR>";
    	}
	} else {
    	/* player doesn't own planet and there is a command */
    	if($command == "buy") {
      		if($planetinfo[sells] == "Y") {
        		$ore_price = $ownerinfo[ore_price];
        		$organics_price = $ownerinfo[organics_price];
        		$goods_price = $ownerinfo[goods_price];
        		$energy_price = $ownerinfo[energy_price];
				$free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
				$free_power = NUM_ENERGY($shipinfo[power]) - $shipinfo[ship_energy];

 				$l_trade_st_info=str_replace("[free_holds]",NUMBER($free_holds),$l_trade_st_info);
 				$l_trade_st_info=str_replace("[free_power]",NUMBER($free_power),$l_trade_st_info);
 				$l_trade_st_info=str_replace("[credits]",NUMBER($playerinfo[credits]),$l_trade_st_info);

 				echo "Welcome to $planetinfo[name] Trader!<br>";
				echo $l_trade_st_info;
				echo "<br>This is what we have to sell today:<br>";
				echo "<FORM ACTION=planet3.php?planet_id=$planet_id METHOD=POST>";
  				echo "<TABLE WIDTH=240 BORDER=0 CELLSPACING=0 CELLPADDING=0>";
  				echo "<TR BGCOLOR=\"$color_header\"><TD><B>Type</B></TD><TD><B>$l_amount</B></TD><TD><B>$l_price</B></TD><TD align=center><B>$l_buy</B></TD><TD><B>$l_cargo</B></TD></TR>";
				if ($ore_price>0) {
					echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_ore</TD><TD>" . NUMBER($planetinfo[ore]) . "</TD><TD align=center>".number_format($ore_price,2)."</TD><TD><INPUT TYPE=TEXT NAME=trade_ore SIZE=5 MAXLENGTH=20 VALUE=$amount_ore></TD><TD align=right>" . NUMBER($shipinfo[ship_ore]) . "</TD></TR>";
				} else {
					echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_ore</TD><TD>N/A</TD><TD align=center>Not for sale</TD><TD></TD><TD align=right>" . NUMBER($shipinfo[ship_ore]) . "</TD></TR>";
				}
				if ($organics_price>0) {
					echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_organics</TD><TD>" . NUMBER($planetinfo[organics]) . "</TD><TD align=center>".number_format($organics_price,2)."</TD><TD><INPUT TYPE=TEXT NAME=trade_organics SIZE=5 MAXLENGTH=20 VALUE=$amount_organics></TD><TD align=right>" . NUMBER($shipinfo[ship_organics]) . "</TD></TR>";
				} else {
					echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_organics</TD><TD>N/A</TD><TD align=center>Not for sale</TD><TD></TD><TD align=right>" . NUMBER($shipinfo[ship_organics]) . "</TD></TR>";
				}
				if ($goods_price>0) {
					echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_goods</TD><TD>" . NUMBER($planetinfo[goods]) . "</TD><TD align=center>".number_format($goods_price,2)."</TD><TD><INPUT TYPE=TEXT NAME=trade_goods SIZE=5 MAXLENGTH=20 VALUE=$amount_goods></TD><TD align=right>" . NUMBER($shipinfo[ship_goods]) . "</TD></TR>";
				} else {
					echo "<TR BGCOLOR=\"$color_line1\"><TD>$l_goods</TD><TD>N/A</TD><TD align=center>Not for sale</TD><TD></TD><TD align=right>" . NUMBER($shipinfo[ship_goods]) . "</TD></TR>";
				}
				if ($energy_price>0) {
					echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_energy</TD><TD>" . NUMBER($planetinfo[energy]) . "</TD><TD align=center>".number_format($energy_price,2)."</TD><TD><INPUT TYPE=TEXT NAME=trade_energy SIZE=5 MAXLENGTH=20 VALUE=$amount_energy></TD><TD align=right>" . NUMBER($shipinfo[ship_energy]) . "</TD></TR>";
				} else {
					echo "<TR BGCOLOR=\"$color_line2\"><TD>$l_energy</TD><TD>N/A</TD><TD align=center>Not for sale</TD><TD></TD><TD align=right>" . NUMBER($shipinfo[ship_energy]) . "</TD></TR>";
				}
  				echo "</TABLE><BR>";
  				echo "<INPUT TYPE=SUBMIT VALUE=$l_trade>";
  				echo "</FORM>";
      		} else {
        		echo "$l_planet_not_selling<BR>";
      		}
    	} elseif($command == "attac") {
			//check to see if sure...
    		if($planetinfo[sells] == "Y") {
            	$l_planet_buy_link="<a href=planet.php?planet_id=$planet_id&command=buy&kk=".date("U").">" . $l_planet_buy_link ."</a>";
		    	$l_planet_buy=str_replace("[buy]",$l_planet_buy_link,$l_planet_buy);
        		echo "$l_planet_buy<BR>";
      		} else {
        		echo "$l_planet_not_selling<BR>";
      		}
       		$l_planet_att_link="<a href=planet.php?planet_id=$planet_id&command=attack&kk=".date("U").">" . $l_planet_att_link ."</a>";
       		$l_planet_att=str_replace("[attack]",$l_planet_att_link,$l_planet_att);
       		$l_planet_scn_link="<a href=planet.php?planet_id=$planet_id&command=scan&kk=".date("U").">" . $l_planet_scn_link ."</a>";
       		$l_planet_scn=str_replace("[scan]",$l_planet_scn_link,$l_planet_scn);
      		echo "$l_planet_att <b>$l_planet_att_sure</b><BR>";
      		echo "$l_planet_scn<BR>";
			if ($sofa_on) {
	  			if($shipinfo[ship_fighters] <1) { 
	   	   			echo "$l_sofa not possible - No fighters on board ship<br>";
    			} else { 
		  			echo "<a href=planet.php?planet_id=$planet_id&command=bom&kk=".date("U").">$l_sofa</a><BR>";
    			}
			}
		} elseif($command == "attack") {
    		planetcombat();
    	} elseif($command == "bom") {
			//check to see if sure...
    		if($planetinfo[sells] == "Y" && $sofa_on) {
            	$l_planet_buy_link="<a href=planet.php?planet_id=$planet_id&command=buy&kk=".date("U").">" . $l_planet_buy_link ."</a>";
		       	$l_planet_buy=str_replace("[buy]",$l_planet_buy_link,$l_planet_buy);
        		echo "$l_planet_buy<BR>";
      		} else {
        		echo "$l_planet_not_selling<BR>";
      		}
       		$l_planet_att_link="<a href=planet.php?planet_id=$planet_id&command=attac&kk=".date("U").">" . $l_planet_att_link ."</a>";
       		$l_planet_att=str_replace("[attack]",$l_planet_att_link,$l_planet_att);
       		$l_planet_scn_link="<a href=planet.php?planet_id=$planet_id&command=scan&kk=".date("U").">" . $l_planet_scn_link ."</a>";
       		$l_planet_scn=str_replace("[scan]",$l_planet_scn_link,$l_planet_scn);
      		echo "$l_planet_att<BR>";
      		echo "$l_planet_scn<BR>";
	  		if($shipinfo[ship_fighters] <1) { 
	   	   		echo "$l_sofa not possible - No fighters on board ship!<br>";
      		} else { 
				echo "<a href=planet.php?planet_id=$planet_id&command=bomb&kk=".date("U").">$l_sofa</a> <b>$l_planet_att_sure</b><BR>";
	  		}
    	}
    	elseif($command == "bomb" && $sofa_on)
    	{
			if($shipinfo[ship_fighters] <1) { 
	   	   		echo "$l_sofa not possible - No fighters on board ship!<br>";
      		} else { 
 		   		planetbombing();
			}
    	}

    	elseif($command == "scan")
    	{
      		/* scan menu */
      		if($playerinfo[turns] < 1)
      		{
        		echo "$l_plant_scn_turn<BR><BR>";
	    		TEXT_GOTOMAIN();
        		include("footer.php");
        		die();
      		}
      		/* determine per cent chance of success in scanning target ship - based on player's sensors and opponent's cloak */
			//echo "DEBUG: $ownershipinfo[cloak] $shipinfo[sensors] <br>";
			// This seems really arbitrary and 7.5 looks a better number than 10
      		$success = (7.5 - $ownershipinfo[cloak] / 2 + $shipinfo[sensors]) * 5;
      		if($success < 5)
      		{
        		$success = 5;
      		} 
      		if($success > 95)
      		{
        		$success = 95;
      		}
			//echo "DEBUG: $success <br>";
      		$roll = rand(1, 100);
      		if($roll > $success)
      		{
        		/* if scan fails - inform both player and target. */
        		echo "$l_planet_noscan<BR><BR>";
        		TEXT_GOTOMAIN();
        		playerlog($ownerinfo[player_id], LOG_PLANET_SCAN_FAIL, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
        		include("footer.php");
        		die();
      		}
      		else
      		{
        		playerlog($ownerinfo[player_id], LOG_PLANET_SCAN, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
        		/* scramble results by scan error factor. */
				//echo "DEBUG: $targetinfo[cloak]<br>";
        		$sc_error= SCAN_ERROR($shipinfo[sensors], $ownershipinfo[cloak]);
				//echo "DEBUG: $sc_error<br>";
        		if(empty($planetinfo[name]))
        			$planetinfo[name] = $l_unnamed;
        		$l_planet_scn_report=str_replace("[name]",$planetinfo[name],$l_planet_scn_report);
        		$l_planet_scn_report=str_replace("[owner]",$ownerinfo[character_name],$l_planet_scn_report);
        		echo "$l_planet_scn_report<BR><BR>";
        		echo "<table>";
        		echo "<tr><td><b>$l_commodities:</b></td><td></td>";
        		echo "<tr><td>$l_organics:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_organics=NUMBER(round($planetinfo[organics] * $sc_error / 100));
          			echo "<td>$sc_planet_organics</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_ore:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_ore=NUMBER(round($planetinfo[ore] * $sc_error / 100));
          			echo "<td>$sc_planet_ore</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_goods:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_goods=NUMBER(round($planetinfo[goods] * $sc_error / 100));
          			echo "<td>$sc_planet_goods</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_energy:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_energy=NUMBER(round($planetinfo[energy] * $sc_error / 100));
          			echo "<td>$sc_planet_energy</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_colonists:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_colonists=NUMBER(round($planetinfo[colonists] * $sc_error / 100));
          			echo "<td>$sc_planet_colonists</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_credits:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_credits=NUMBER(round($planetinfo[credits] * $sc_error / 100));
          			echo "<td>$sc_planet_credits</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td><b>$l_defense:</b></td><td></td>";
        		echo "<tr><td>$l_base:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			echo "<td>$planetinfo[base]</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
       			echo "<tr><td>$l_base $l_torps:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_base_torp=NUMBER(round($planetinfo[torps] * $sc_error / 100));
          			echo "<td>$sc_base_torp</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_fighters:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_planet_fighters=NUMBER(round($planetinfo[fighters] * $sc_error / 100));
          			echo "<td>$sc_planet_fighters</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_beams:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_beams=NUMBER(round($ownershipinfo[beams] * $sc_error / 100));
					if ($planetinfo[base]=="Y") {
						$sc_beams++;
					}
          			echo "<td>$sc_beams</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_torp_launch:</td>";
        		$roll = rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_torp_launchers=NUMBER(round($ownershipinfo[torp_launchers] * $sc_error / 100));
          			if ($planetinfo[base]=="Y") {
						$sc_torp_launchers++;
					}
					echo "<td>$sc_torp_launchers</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "<tr><td>$l_shields</td>";
        		$roll=rand(1, 100);
        		if($roll < $success)
        		{
          			$sc_shields=NUMBER(round($ownershipinfo[shields] * $sc_error / 100));
          			if ($planetinfo[base]=="Y") {
						$sc_shields++;
					}
					echo "<td>$sc_shields</td></tr>";
        		}
        		else
        		{
          			echo "<td>???</td></tr>";
        		}
        		echo "</table><BR>";
//         $roll=rand(1, 100);
//         if($ownerinfo[sector] == $playerinfo[sector] && $ownerinfo[on_planet] == 'Y' && $roll < $success)
//         {
//           echo "<B>$ownerinfo[character_name] $l_planet_ison</B><BR>";
//         }
        
       			$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE on_planet = 'Y' and planet_id = $planet_id"); 

       			while(!$res->EOF)       
       			{ 
         			$row = $res->fields;       
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
           				echo "<B>$row[character_name] $l_planet_ison</B><BR>";
         			}  
         			$res->MoveNext();
       			}
        //
        
      		}
      		$update = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
    	}
    	elseif($command == "capture" &&  $planetinfo[owner] == 0)
    	{
      		echo "$l_planet_captured<BR>";
      		$update = $db->Execute("UPDATE $dbtables[planets] SET corp=null, owner=$playerinfo[player_id], base='N', defeated='N' WHERE planet_id=$planet_id");
      		$ownership = calc_ownership($planetinfo[sector_id]);

        	if(!empty($ownership))
				echo "$ownership<p>";
      		if($planetinfo[owner] != 0)
      		{
        		gen_score($planetinfo[owner]);
      		}
      
      		if($planetinfo[owner] != 0)
      		{
        		$res = $db->Execute("SELECT character_name FROM $dbtables[players] WHERE player_id=$planetinfo[owner]");
        		$query = $res->fields;
        		$planetowner=$query[character_name];
      		}
      		else
        		$planetowner="$l_planet_noone";

      		playerlog($playerinfo[player_id], LOG_PLANET_CAPTURED, "$planetinfo[sector_id]|$planetinfo[colonists]|$planetinfo[credits]|$planetowner");
			$dock = $db->Execute("SELECT ship_name,ship_id FROM $dbtables[ships] WHERE sector=$planetinfo[sector_id] AND on_planet='Y' AND planet_id=$planetinfo[planet_id] AND ship_id!=$playerinfo[currentship] AND ship_colonists<0");
			while (!$dock->EOF) {
				$ships = $dock->fields;
				$db->Execute("UPDATE $dbtables[ships] SET sector=0, on_planet='N', player_id=0, planet_id=0 WHERE ship_id=$ships[ship_id] LIMIT 1");
				$dock->MoveNext();
				echo "The $ships[ship_name] was found but it is irrepairable.<br>";
			}
      		// Capture and damage any ships that are in the space dock
			$dock = $db->Execute("SELECT ship_name FROM $dbtables[ships] WHERE sector=$planetinfo[sector_id] AND on_planet='Y' AND planet_id=$planetinfo[planet_id] AND ship_id!=$playerinfo[currentship]");
			while (!$dock->EOF) {
				$ships = $dock->fields;
				$dock->MoveNext();
				echo "You capture the $ships[ship_name] in the space dock.<br>";
			}
			$damage = rand(20,90)/100;
			if ($damage < 30) {
				echo "The spacedock suffered some damage.<br>";
			} else if ($damage < 60) {
				echo "The spacedock is significantly damaged.<br>";
			} else {
				echo "The spacedock is a wasteland of destruction.<br>";
			}
			$dock = $db->Execute("UPDATE $dbtables[ships] SET player_id=$playerinfo[player_id],hull=hull*$damage,engines=engines*$damage, power=power*$damage, computer=computer*$damage, sensors=sensors*$damage, beams=beams*$damage, torp_launchers=torp_launchers*$damage, shields=shields*$damage, armour=armour*$damage, cloak=cloak*$damage, torps=0, armour_pts=0, ship_ore=0, ship_organics=0, ship_goods=0, ship_energy=0, ship_colonists=0, ship_fighters=0, tow=0 WHERE sector=$planetinfo[sector_id] AND on_planet='Y' AND planet_id=$planetinfo[planet_id] AND ship_id!=$playerinfo[currentship]");
    	}
    	elseif($command == "capture" &&  ($planetinfo[owner] == 0 || $planetinfo[defeated] == 'Y'))
    	{
      		echo "$l_planet_notdef<BR>";
      		$db->Execute("UPDATE $dbtables[planets] SET defeated='N' WHERE planet_id=$planetinfo[planet_id]");
    	}
    	else
    	{
      		echo "$l_command_no<BR>";
    	}
  	}
}
else
{
  	echo "$l_planet_none<p>";
}
if($command != "")
{
  	echo "<BR><a href=planet.php?planet_id=$planet_id&kk=".date("U").">$l_clickme</a> $l_toplanetmenu<BR><BR>";
}

//-------------------------------------------------------------------------------------------------
TEXT_GOTOMAIN();

include("footer.php");


?>
