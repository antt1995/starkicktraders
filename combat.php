<?

function calcplanetbeams()
{
    global $playerinfo;
	global $shipinfo;
    global $ownerinfo;
	global $ownershipinfo;
    global $sectorinfo;
    global $basedefense;
    global $planetinfo;
    global $db, $dbtables;

    $energy_available = $planetinfo[energy];
    $base_factor = ($planetinfo[base] == 'Y') ? $basedefense : 0;
    $planetbeams = NUM_BEAMS($ownershipinfo[beams] + $base_factor);
    $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    while(!$res->EOF)
    {
		// I'm on the planet so the beams are enhanced by my beams
		// Find out what my ship has
		$onplanetplayer = $res->fields;
		// Find out what my ship has - only active ships can fight!
		$res2 = $db->Execute("SELECT beams FROM $dbtables[ships] WHERE player_id=$onplanetplayer[player_id] AND ship_id=$onplanetplayer[currentship] LIMIT 1");
        $planetbeams = $planetbeams + NUM_BEAMS($res2->fields[beams]);
        $res->MoveNext();
    }

    if ($planetbeams > $energy_available)
        $planetbeams = $energy_available;

    return $planetbeams;
}

function calcplanetfighters()
{
    global $planetinfo;

    $planetfighters = $planetinfo[fighters];
    return $planetfighters;
}


function calcplanettorps()
{
    global $playerinfo;
	global $shipinfo;
    global $ownerinfo;
global $ownershipinfo;
    global $sectorinfo;
    global $level_factor;
    global $basedefense;
    global $planetinfo;
    global $db, $dbtables;

    $base_factor = ($planetinfo[base] == 'Y') ? $basedefense : 0;

    $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    $torp_launchers = round(mypw($level_factor, ($ownershipinfo[torp_launchers])+ $base_factor)) * 2;
    $torps = $planetinfo[torps];
    if($res)
    {
       while(!$res->EOF)
       {
	   		// I'm on the planet so the torps are enhanced by my torps
			// Find out who I am
			$onplanetship = $res->fields;
			// Find out what my ship has
			$res2 = $db->Execute("SELECT torp_launchers FROM $dbtables[ships] WHERE player_id=$onplanetship[player_id] AND ship_id=$onplanetship[currentship] LIMIT 1");
           $ship_torps =  round(mypw($level_factor, $res2->fields[torp_launchers])) * 10;
           $torp_launchers = $torp_launchers + $ship_torps;
           $res->MoveNext();
       }
    }
    if ($torp_launchers > $torps)
    {
        $planettorps = $torps;
    }
    else
        $planettorps = $torp_launchers;
    $planetinfo[torps] -= $planettorps;

    return $planettorps;
}

function calcplanetshields()
{
    global $playerinfo;
	global $shipinfo;
    global $ownerinfo;
	global $ownershipinfo;
    global $sectorinfo;
    global $basedefense;
    global $planetinfo;
    global $db, $dbtables;


    $base_factor = ($planetinfo[base] == 'Y') ? $basedefense : 0;
    $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    $planetshields = NUM_SHIELDS($ownershipinfo[shields] + $base_factor);
    $energy_available = $planetinfo[energy];
    while(!$res->EOF)
    {
		// Find out who I am
		$onplanetship = $res->fields;
		// Find out what my ship has
		$res2 = $db->Execute("SELECT shields FROM $dbtables[ships] WHERE player_id=$onplanetship[player_id] AND ship_id=$onplanetship[currentship] LIMIT 1");
        $planetshields += NUM_SHIELDS($res2->fields[shields]);
        $res->MoveNext();
    }

    if ($planetshields > $energy_available)
    {
        $planetshields = $energy_available;
    }
    $planetinfo[energy] -= $planetshields;
    return $planetshields;
}


function planetbombing()
{
    global $playerinfo;
    global $ownerinfo;
	global $ownershipinfo;
    global $sectorinfo;
    global $planetinfo;
    global $planetbeams;
    global $planetfighters;
    global $attackerfighters;
    global $planettorps;
    global $torp_dmg_rate;
    global $l_cmb_atleastoneturn;
    global $db, $dbtables;
    global $l_bombsaway;
    global $l_bigfigs;
    global $l_bigbeams;
    global $l_bigtorps;
    global $l_strafesuccess;
	global $shipinfo;
$debug = false;
	//echo "<pre> Planet INFO \n";
	//print_r($planetinfo);
	//echo "</pre>";
	// Check to see if there are enough fighters
	$res = $db->Execute("SELECT ship_fighters FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship] AND ship_fighters>0");
	if ($res->RowCount()==0) {
        echo "You need fighters to be able to SOFA!<BR><BR>";
        TEXT_GOTOMAIN();
        include("footer.php");
        die();
    } else {
		$row=$res->fields;
		$attackerfighters = $row[ship_fighters];
	}
	// Immediately reduce the ship's fighters to zero
	$res = $db->Execute("UPDATE $dbtables[ships] SET ship_fighters=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    if($playerinfo[turns] < 1)
    {
        echo "$l_cmb_atleastoneturn<BR><BR>";
        TEXT_GOTOMAIN();
        include("footer.php");
        die();
    }
	echo "<h2>SOFA Attack!</h2>";
    echo "<b>$l_bombsaway</b><br><br>\n";

    $attackerfighterslost = 0;
    $planetfighterslost = 0;
    $attackerfightercapacity = NUM_FIGHTERS($shipinfo[computer]);
    $ownerfightercapacity = NUM_FIGHTERS($ownershipinfo[computer]);
    $beamsused = 0;
    $planettorps = calcplanettorps();
    $planetbeams = calcplanetbeams();
    $planetfighters = calcplanetfighters();
    //$attackerfighters = $shipinfo[ship_fighters];
        if ($debug) echo "FigsCapacity $attackerfightercapacity <BR>\n";
        if ($debug) echo "Figsused $attackerfighters<BR>\n";

	// Beams can only take out up to half of the attacking fighters
    if($planetbeams <= floor($attackerfighters/2))
    {
        $attackerfighterslost=$planetbeams;
        $beamsused=$planetbeams;
    }
    else
    {
        $attackerfighterslost=floor($attackerfighters/2);
        $beamsused=floor($attackerfighters/2);
    }
	if ($beamsused>0) {
		echo "<b>Some of your fighters get past the planet's beams! Others are not so lucky...</b><br><br>";
	} else {
		echo "<b>The planet's beams fail to fire!</b><br><br>";
	}
	// next are the torpedoe attacks - this if statement should always be true
    if($attackerfighters>$attackerfighterslost)
    {
        if ($debug) echo "pfigs $planetfighterslost mefigs $attackerfighters - $attackerfighterslost<BR>\n";

        $attackerfighterslost+=$planettorps*$torp_dmg_rate;
		if ($planettorps>0) {
			echo "<b>The planet's missile torpedoes take out some of your fighters!</b><br><br>";
		} else {
			echo "<b>The planet fails to fire any torpedoes! What luck!</b><br><br>";
		}
        if($attackerfighters<=$attackerfighterslost)
            echo "<b>$l_bigtorps</b><br><br>";
        else
        {
            echo "<b>$l_strafesuccess</b><br><br>";
			$planetfighterslost=$attackerfighters-$attackerfighterslost;
            if ($debug) echo "bigguy figs go boom $planetfighterslost<BR>\n";
            if($planetfighterslost>$planetfighters)
            {
                $planetfighterslost = $planetfighters;
            }
        }
    }

    if ($debug) echo "total figs go boom $planetfighterslost<BR>\n";
    echo "<br><br>\n";
playerlog($ownerinfo[player_id], LOG_PLANET_BOMBED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]|".NUMBER($beamsused)."|".NUMBER($planettorps)."|".NUMBER($planetfighterslost));

    $res = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
    $res = $db->Execute("UPDATE $dbtables[planets] SET energy=energy-$beamsused,fighters=fighters-$planetfighterslost, torps=torps-$planettorps WHERE planet_id=$planetinfo[planet_id]");
    $res = $db->Execute("UNLOCK TABLES");
}




function planetcombat()
{
	global $upgrade_cost, $upgrade_factor;
    global $playerinfo;
	global $shipinfo;
    global $ownerinfo;
	global $ownershipinfo;
    global $sectorinfo;
    global $planetinfo;
    global $torpedo_price;
    global $colonist_price;
    global $ore_price;
    global $organics_price;
    global $goods_price;
    global $energy_price;
	global $ibank_paymentfee;
    global $planetbeams;
    global $planetfighters;
    global $planetshields;
    global $planettorps;
    global $attackerbeams;
    global $attackerfighters;
    global $attackershields;
    global $attackertorps;
    global $attackerarmor;
    global $torp_dmg_rate;
    global $level_factor;
    global $attackertorpdamage;
    global $start_energy;
    global $min_value_capture;
    global $l_cmb_atleastoneturn;
    global $l_cmb_atleastoneturn, $l_cmb_shipenergybb, $l_cmb_shipenergyab, $l_cmb_shipenergyas, $l_cmb_shiptorpsbtl, $l_cmb_shiptorpsatl;
    global $l_cmb_planettorpdamage, $l_cmb_attackertorpdamage, $l_cmb_beams, $l_cmb_fighters, $l_cmb_shields, $l_cmb_torps;
    global $l_cmb_torpdamage, $l_cmb_armor, $l_cmb_you, $l_cmb_planet, $l_cmb_combatflow, $l_cmb_defender, $l_cmb_attackingplanet;
    global $l_cmb_youfireyourbeams, $l_cmb_defenselost, $l_cmb_defenselost2, $l_cmb_planetarybeams, $l_cmb_planetarybeams;
    global $l_cmb_youdestroyedplanetshields, $l_cmb_beamsexhausted, $l_cmb_breachedyourshields, $l_cmb_destroyedyourshields;
    global $l_cmb_breachedyourarmor, $l_cmb_destroyedyourarmor, $l_cmb_torpedoexchangephase, $l_cmb_nofightersleft;
    global $l_cmb_youdestroyfighters, $l_cmb_planettorpsdestroy, $l_cmb_planettorpsdestroy2, $l_cmb_torpsbreachedyourarmor;
    global $l_cmb_planettorpsdestroy3, $l_cmb_youdestroyedallfighters, $l_cmb_youdestroyplanetfighters, $l_cmb_fightercombatphase;
    global $l_cmb_youdestroyedallfighters2, $l_cmb_youdestroyplanetfighters2, $l_cmb_allyourfightersdestroyed, $l_cmb_fightertofighterlost;
    global $l_cmb_youbreachedplanetshields, $l_cmb_shieldsremainup, $l_cmb_fighterswarm, $l_cmb_swarmandrepel, $l_cmb_engshiptoshipcombat;
    global $l_cmb_shipdock, $l_cmb_approachattackvector, $l_cmb_noshipsdocked, $l_cmb_yourshipdestroyed, $l_cmb_escapepod;
    global $l_cmb_finalcombatstats, $l_cmb_youlostfighters, $l_cmb_youlostarmorpoints, $l_cmb_energyused, $l_cmb_planetdefeated;
    global $l_cmb_citizenswanttodie, $l_cmb_youmaycapture, $l_cmb_youmaycapture2, $l_cmb_planetnotdefeated, $l_cmb_planetstatistics;
    global $l_cmb_fighterloststat, $l_cmb_energyleft;
    global $db, $dbtables;
    //$debug = true;




    if($playerinfo[turns] < 1)
    {
        echo "$l_cmb_atleastoneturn<BR><BR>";
        TEXT_GOTOMAIN();
        include("footer.php");
        die();
    }

    // Planetary defense system calculation

    $planetbeams 		= calcplanetbeams();
    $planetfighters		= calcplanetfighters();
    $planetshields		= calcplanetshields();
    $planettorps		= calcplanettorps();

    // Attacking ship calculations

    $attackerbeams		= NUM_BEAMS($shipinfo[beams]);
    $attackerfighters	= $shipinfo[ship_fighters];
    $attackershields	= NUM_SHIELDS($shipinfo[shields]);
    $attackertorps		= round(mypw($level_factor, $shipinfo[torp_launchers])) * 2;
    $attackerarmor		= $shipinfo[armour_pts];

    // Now modify player beams, shields and torpedos on available materiel
    $starting_energy = $shipinfo[ship_energy];

    // Beams
    if ($debug)
        echo "$l_cmb_shipenergybb: $shipinfo[ship_energy]<BR>\n";
    if ($attackerbeams > $shipinfo[ship_energy])
        $attackerbeams   = $shipinfo[ship_energy];
    $shipinfo[ship_energy] = $shipinfo[ship_energy] - $attackerbeams;
    if ($debug)
        echo "$l_cmb_shipenergyab (before shields): $shipinfo[ship_energy]<BR>\n";

    // Shields
    if ($attackershields > $shipinfo[ship_energy])
        $attackershields = $shipinfo[ship_energy];
    $shipinfo[ship_energy] = $shipinfo[ship_energy] - $attackershields;
    if ($debug)
        echo "$l_cmb_shipenergyas: $shipinfo[ship_energy]<BR>\n";

    // Torpedos
    if ($debug)
        echo "$l_cmb_shiptorpsbtl: $attackertorps ($shipinfo[torps] / $shipinfo[torp_launchers])<BR>\n";
    if ($attackertorps > $shipinfo[torps])
        $attackertorps = $shipinfo[torps];
    $shipinfo[torps] = $shipinfo[torps] - $attackertorps;
    if ($debug)
        echo "$l_cmb_shiptorpsatl: $attackertorps ($shipinfo[torps] / $shipinfo[torp_launchers])<BR>\n";

    // Setup torp damage rate for both Planet and Ship
    $planettorpdamage	= $torp_dmg_rate * $planettorps;
    $attackertorpdamage	= $torp_dmg_rate * $attackertorps;
    if ($debug)
        echo "$l_cmb_planettorpdamage: $planettorpdamage<BR>\n";
    if ($debug)
        echo "$l_cmb_attackertorpdamage: $attackertorpdamage<BR>\n";


    echo "
    <CENTER>
    <HR>
    <table width='75%' border='0'>
    <tr ALIGN='CENTER'>
    <td width='9%' height='27'></td>
    <td width='12%' height='27'><FONT COLOR='WHITE'>$l_cmb_beams</FONT></td>
    <td width='17%' height='27'><FONT COLOR='WHITE'>$l_cmb_fighters</FONT></td>
    <td width='18%' height='27'><FONT COLOR='WHITE'>$l_cmb_shields</FONT></td>
    <td width='11%' height='27'><FONT COLOR='WHITE'>$l_cmb_torps</FONT></td>
    <td width='22%' height='27'><FONT COLOR='WHITE'>$l_cmb_torpdamage</FONT></td>
    <td width='11%' height='27'><FONT COLOR='WHITE'>$l_cmb_armor</FONT></td>
    </tr>
    <tr ALIGN='CENTER'>
    <td width='9%'> <FONT COLOR='RED'>$l_cmb_you</td>
    <td width='12%'><FONT COLOR='RED'><B>$attackerbeams</B></FONT></td>
    <td width='17%'><FONT COLOR='RED'><B>$attackerfighters</B></FONT></td>
    <td width='18%'><FONT COLOR='RED'><B>$attackershields</B></FONT></td>
    <td width='11%'><FONT COLOR='RED'><B>$attackertorps</B></FONT></td>
    <td width='22%'><FONT COLOR='RED'><B>$attackertorpdamage</B></FONT></td>
    <td width='11%'><FONT COLOR='RED'><B>$attackerarmor</B></FONT></td>
    </tr>
    <tr ALIGN='CENTER'>
    <td width='9%'> <FONT COLOR='#6098F8'>$l_cmb_planet</FONT></td>
    <td width='12%'><FONT COLOR='#6098F8'><B>$planetbeams</B></FONT></td>
    <td width='17%'><FONT COLOR='#6098F8'><B>$planetfighters</B></FONT></td>
    <td width='18%'><FONT COLOR='#6098F8'><B>$planetshields</B></FONT></td>
    <td width='11%'><FONT COLOR='#6098F8'><B>$planettorps</B></FONT></td>
    <td width='22%'><FONT COLOR='#6098F8'><B>$planettorpdamage</B></FONT></td>
    <td width='11%'><FONT COLOR='#6098F8'><B>N/A</B></FONT></td>
    </tr>
    </table>
    <HR>
    </CENTER>
    ";
    // Begin actual combat calculations

    $planetdestroyed   = 0;
    $attackerdestroyed = 0;

    echo "<BR><CENTER><B><FONT SIZE='+2'>$l_cmb_combatflow</FONT></B><BR><BR>\n";
    echo "<table width='75%' border='0'><tr align='center'><td><FONT COLOR='RED'>$l_cmb_you</FONT></td><td><FONT COLOR='#6098F8'>$l_cmb_defender</FONT></td>\n";
    echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_attackingplanet $playerinfo[sector]</b></FONT></td><td></td>";
    echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youfireyourbeams</b></FONT></td><td></td>\n";
    if($planetfighters > 0 && $attackerbeams > 0)
    {
        if($attackerbeams > $planetfighters)
        {
            $l_cmb_defenselost = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_defenselost);
            echo "<tr align='center'><td></td><td><FONT COLOR='#6098F8'><B>$l_cmb_defenselost</B></FONT>";
            $planetfighters = 0;
            $attackerbeams = $attackerbeams - $planetfighters;
        }
        else
        {
            $l_cmb_defenselost2 = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_defenselost2);
            $planetfighters = $planetfighters - $attackerbeams;
            echo "<tr align='center'><td></td><td><FONT COLOR='#6098F8'><B>$l_cmb_defenselost2</B></FONT>";
            $attackerbeams = 0;
        }
    }

    if($attackerfighters > 0 && $planetbeams > 0)
    {
        // If there are more beams on the planet than attacker has fighters
        if($planetbeams > round($attackerfighters / 2))
        {
            // Half the attacker fighters
            $temp = round($attackerfighters / 2);
            // Attacker loses half his fighters
            $lost = $attackerfighters - $temp;
            // Set attacker fighters to 1/2 it's original value
            $attackerfighters = $temp;
            // Subtract half the attacker fighters from available planetary beams
            $planetbeams = $planetbeams - $lost;
            $l_cmb_planetarybeams = str_replace("[cmb_temp]", $temp, $l_cmb_planetarybeams);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_planetarybeams</B></FONT><TD></TD>";
        }
        else
        {
            $l_cmb_planetarybeams2 = str_replace("[cmb_planetbeams]", $planetbeams, $l_cmb_planetarybeams2);
            $attackerfighters = $attackerfighters - $planetbeams;
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_planetarybeams2</B></FONT><TD></TD>";
            $planetbeams = 0;
        }
    }
    if($attackerbeams > 0)
    {
        if($attackerbeams > $planetshields)
        {
            $attackerbeams = $attackerbeams - $planetshields;
            $planetshields = 0;
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youdestroyedplanetshields</FONT></B><td></td>";
        }
        else
        {
            $l_cmb_beamsexhausted = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_beamsexhausted);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_beamsexhausted</FONT></B><td></td>";
            $planetshields = $planetshields - $attackerbeams;
            $attackerbeams = 0;
        }
    }
    if($planetbeams > 0)
    {
        if($planetbeams > $attackershields)
        {
            $planetbeams = $planetbeams - $attackershields;
            $attackershields = 0;
            echo "<tr align='center'><td></td><td><FONT COLOR='#6098F8'><B>$l_cmb_breachedyourshields</FONT></B></td>";
        }
        else
        {
            $attackershields = $attackershields - $planetbeams;
            $l_cmb_destroyedyourshields = str_replace("[cmb_planetbeams]", $planetbeams, $l_cmb_destroyedyourshields);
            echo "<tr align='center'><td></td><FONT COLOR='#6098F8'><B>$l_cmb_destroyedyourshields</FONT></B></td>";
            $planetbeams = 0;
        }
    }
    if($planetbeams > 0)
    {
        if($planetbeams > $attackerarmor)
        {
            $attackerarmor = 0;
            echo "<tr align='center'><td></td><td><FONT COLOR='#6098F8'><B>$l_cmb_breachedyourarmor</B></FONT></td>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $planetbeams;
            $l_cmb_destroyedyourarmor = str_replace("[cmb_planetbeams]", $planetbeams, $l_cmb_destroyedyourarmor);
            echo "<tr align='center'><td></td><td><FONT COLOR='#6098F8'><B>$l_destroyedyourarmor</FONT></B></td>";
        }
    }
    echo "<tr align='center'><td><FONT COLOR='YELLOW'><B>$l_cmb_torpedoexchangephase</b></FONT></td><td><b><FONT COLOr='YELLOW'>$l_cmb_torpedoexchangephase</b></FONT></td><BR>";
    if($planetfighters > 0 && $attackertorpdamage > 0)
    {
        if($attackertorpdamage > $planetfighters)
        {
            $l_cmb_nofightersleft = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_nofightersleft);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_nofightersleft</FONT></B></td><td></td>";
            $planetfighters = 0;
            $attackertorpdamage = $attackertorpdamage - $planetfighters;
        }
        else
        {
            $planetfighters = $planetfighters - $attackertorpdamage;
            $l_cmb_youdestroyfighters = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_youdestroyfighters);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youdestroyfighters</FONT></B></td><td></td>";
            $attackertorpdamage = 0;
        }
    }
    if($attackerfighters > 0 && $planettorpdamage > 0)
    {
        if($planettorpdamage > round($attackerfighters / 2))
        {
            $temp = round($attackerfighters / 2);
            $lost = $attackerfighters - $temp;
            $attackerfighters = $temp;
            $planettorpdamage = $planettorpdamage - $lost;
            $l_cmb_planettorpsdestroy = str_replace("[cmb_temp]", $temp, $l_cmb_planettorpsdestroy);
            echo "<tr align='center'><td></td><td><FONT COLOR='RED'><B>$l_cmb_planettorpsdestroy</B></FONT></td>";
        }
        else
        {
            $attackerfighters = $attackerfighters - $planettorpdamage;
            $l_cmb_planettorpsdestroy2 = str_replace("[cmb_planettorpdamage]", $planettorpdamage, $l_cmb_planettorpsdestroy2);
            echo "<tr align='center'><td></td><td><FONT COLOR='RED'><B>$l_cmb_planettorpsdestroy2</B></FONT></td>";
            $planettorpdamage = 0;
        }
    }
    if($planettorpdamage > 0)
    {
        if($planettorpdamage > $attackerarmor)
        {
            $attackerarmor = 0;
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_torpsbreachedyourarmor</B></FONT></td><td></td>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $planettorpdamage;
            $l_cmb_planettorpsdestroy3 = str_replace("[cmb_planettorpdamage]", $planettorpdamage, $l_cmb_planettorpsdestroy3);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_planettorpsdestroy3</B></FONT></td><td></td>";
        }
    }
    if($attackertorpdamage > 0 && $planetfighters > 0)
    {
        $planetfighters = $planetfighters - $attackertorpdamage;
        if ($planetfighters < 0)
        {
            $planetfighters = 0;
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youdestroyedallfighters</B></FONT></td><td></td>";
        }
        else
        {
            $l_cmb_youdestroyplanetfighters = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_youdestroyplanetfighters);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youdestroyplanetfighters</B></FONT></td><td></td>";
        }
    }
    echo "<tr align='center'><td><FONT COLOR='YELLOW'><B>$l_cmb_fightercombatphase</b></FONT></td><td><b><FONT COLOr='YELLOW'>$l_cmb_fightercombatphase</b></FONT></td><BR>";
    if($attackerfighters > 0 && $planetfighters > 0)
    {
        if($attackerfighters > $planetfighters)
        {
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youdestroyedallfighters2</B></FONT></td><td></td>";
            $tempplanetfighters = 0;
        }
        else
        {
            $l_cmb_youdestroyplanetfighters2 = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_youdestroyplanetfighters2);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youdestroyplanetfighters2</B></FONT></td><td></td>";
            $tempplanetfighters = $planetfighters - $attackerfighters;
        }
        if($planetfighters > $attackerfighters)
        {
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_allyourfightersdestroyed</B></FONT></td><td></td>";
            $tempplayfighters = 0;
        }
        else
        {
            $tempplayfighters = $attackerfighters - $planetfighters;
            $l_cmb_fightertofighterlost = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_fightertofighterlost);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_fightertofighterlost</B></FONT></td><td></td>";
        }
        $attackerfighters = $tempplayfighters;
        $planetfighters = $tempplanetfighters;
    }
    if($attackerfighters > 0 && $planetshields > 0)
    {
        if($attackerfighters > $planetshields)
        {
            $attackerfighters = $attackerfighters - round($planetshields / 2);
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_youbreachedplanetshields</B></FONT></td><td></td>";
            $planetshields = 0;
        }
        else
        {
            $l_cmb_shieldsremainup = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_shieldsremainup);
            echo "<tr align='center'><td></td><FONT COLOR='#6098F8'><B>$l_cmb_shieldsremainup</B></FONT></td>";
            $planetshields = $planetshields - $attackerfighters;
        }
    }
    if($planetfighters > 0)
    {
        if($planetfighters > $attackerarmor)
        {
            $attackerarmor = 0;
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_fighterswarm</B></FONT></td><td></td>";
        }
        else
        {
            $attackerarmor = $attackerarmor - $planetfighters;
            echo "<tr align='center'><td><FONT COLOR='RED'><B>$l_cmb_swarmandrepel</B></FONT></td><td></td>";
        }
    }

    echo "</TABLE></CENTER>\n";
    // Send each docked ship in sequence to attack agressor
    $result4 = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    $playersonplanet = $result4->RecordCount();

    if ($playersonplanet > 0)
    {
        $l_cmb_shipdock = str_replace("[cmb_shipsonplanet]", $shipsonplanet, $l_cmb_shipdock);
        echo "<BR><BR><CENTER>$l_cmb_shipdock<BR>$l_cmb_engshiptoshipcombat</CENTER><BR><BR>\n";
        while (!$result4->EOF)
        {
            $onplanet = $result4->fields;
            //$shipinfo[ship_fighters] 	= $attackerfighters;
            //$shipinfo[armour_pts] 	= $attackerarmor;
            //$shipinfo[torps]			= $shipinfo[torps] - $attackertorps;

            if ($attackerfighters < 0)
                $attackerfighters = 0;
            if ($attackertorps    < 0)
                $attackertorps = 0;
            if ($attackershields  < 0)
                $attackershields = 0;
            if ($attackerbeams    < 0)
                $attackerbeams = 0;
            if ($attackerarmor    < 1)
                break;

            echo "<BR>-$onplanet[character_name] $l_cmb_approachattackvector-<BR>";
            shiptoship($onplanet[currentship]);
            $result4->MoveNext();
        }
    }
    else
        echo "<BR><BR><CENTER>$l_cmb_noshipsdocked</CENTER><BR><BR>\n";

    if($attackerarmor < 1)
    {
		// Planet attacked and attacked blew up
        $free_ore = round($shipinfo[ship_ore]/2);
        $free_organics = round($shipinfo[ship_organics]/2);
        $free_goods = round($shipinfo[ship_goods]/2);
        $ship_value=$upgrade_cost*(round(mypw($upgrade_factor, $shipinfo[hull]))+round(mypw($upgrade_factor, $shipinfo[engines]))+round(mypw($upgrade_factor, $shipinfo[power]))+round(mypw($upgrade_factor, $shipinfo[computer]))+round(mypw($upgrade_factor, $shipinfo[sensors]))+round(mypw($upgrade_factor, $shipinfo[beams]))+round(mypw($upgrade_factor, $shipinfo[torp_launchers]))+round(mypw($upgrade_factor, $shipinfo[shields]))+round(mypw($upgrade_factor, $shipinfo[armor]))+round(mypw($upgrade_factor, $shipinfo[cloak])));
        $ship_salvage_rate=rand(0,10);
        $ship_salvage=$ship_value*$ship_salvage_rate/100;
		// Triple salvage if this is an outlaw
		$result3 = $db->Execute ("SELECT amount FROM $dbtables[bounty] WHERE placed_by=0 AND bounty_on=$playerinfo[player_id]");
		$fugitive="";
		if (!$result3->EOF) {
			echo "Triple hull salvage goes to $ownerinfo[character_name] because you were a fugitive!<br>";
			$ship_salvage *=3;
			$fugitive = "Fugitive from justice ";
		}

        echo "<BR><CENTER><FONT SIZE='+2' COLOR='RED'><B>$l_cmb_yourshipdestroyed</FONT></B></CENTER><BR>";
		// news
		$headline="Planet Attacked!";
		$newstext=$fugitive.$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." led an attack today against ".$ownerinfo[character_name]."'s planet ".$planetinfo[name]." but was brought down by heavy planetary defenses. ";
		if ($playerinfo[score] < $ownerinfo[score]) {
			$newstext = $newstext . $playerinfo[character_name]." knew it would be an uphill battle and it was. Too bad.";
		} else {
			$newstext = $newstext . $playerinfo[character_name]." thought it would be an easy battle but underestimated the planet defenses. Next time look before you leap.";
		}
		if ($fugitive != "") {
			$newstext .= " $ownerinfo[character_name] collected triple salvage from the outlaw's ship wreckage!";
		}
		$newstext = addslashes($newstext);
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");

        if($shipinfo[dev_escapepod] == "Y")
        {
            echo "<CENTER><FONT COLOR='WHITE'>$l_cmb_escapepod</FONT></CENTER><BR><BR>";
            $db->Execute("UPDATE $dbtables[players] SET on_planet='N',sector=0 WHERE player_id=$playerinfo[player_id]");
			// Immediately release any ships being towed 
    		if ($shipinfo[tow] > 0) {
				$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$shipinfo[sector] WHERE ship_id=$shipinfo[tow]");
  			}
		$db->Execute("UPDATE $dbtables[ships] SET ship_destroyed ='Y', sector=0, tow=0 WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
            collect_bounty($planetinfo[owner],$playerinfo[player_id]);
        }
        else
        {
            db_kill_player($playerinfo[player_id],$playerinfo[currentship],$planetinfo[owner]);
            collect_bounty($planetinfo[owner],$playerinfo[player_id]);
        }
    }
    else
    {
		// ********************* Attacker survived ****************************
        $free_ore=0;
        $free_goods=0;
        $free_organics=0;
        $ship_salvage_rate=0;
        $ship_salvage=0;
        $planetrating = $ownershipinfo[hull] + $ownershipinfo[engines] + $ownershipinfo[computer] + $ownershipinfo[beams] + $ownershipinfo[torp_launchers] + $ownershipinfo[shields] + $ownershipinfo[armor];
        if($ownerinfo[rating]!=0)
        {
            $rating_change=($ownerinfo[rating]/abs($ownerinfo[rating]))*$planetrating*10;
        }
        else
        {
            $rating_change=-100;
        }
        echo "<CENTER><BR><B><FONT SIZE='+2'>$l_cmb_finalcombatstats</FONT></B><BR><BR>";
        $fighters_lost = $shipinfo[ship_fighters] - $attackerfighters;
        $l_cmb_youlostfighters = str_replace("[cmb_fighters_lost]", $fighters_lost, $l_cmb_youlostfighters);
        $l_cmb_youlostfighters = str_replace("[cmb_playerinfo_ship_fighters]", $shipinfo[ship_fighters], $l_cmb_youlostfighters);
        echo "$l_cmb_youlostfighters<BR>";
        $armor_lost = $shipinfo[armour_pts] - $attackerarmor;
        $l_cmb_youlostarmorpoints = str_replace("[cmb_armor_lost]", $armor_lost, $l_cmb_youlostarmorpoints);
        $l_cmb_youlostarmorpoints = str_replace("[cmb_playerinfo_armour_pts]", $shipinfo[armour_pts], $l_cmb_youlostarmorpoints);
        $l_cmb_youlostarmorpoints = str_replace("[cmb_attackerarmor]", $attackerarmor, $l_cmb_youlostarmorpoints);
        echo "$l_cmb_youlostarmorpoints<BR>";
        $energy=$shipinfo[ship_energy];
        $energy_lost = $starting_energy - $shipinfo[ship_energy];
        $l_cmb_energyused = str_replace("[cmb_energy_lost]", $energy_lost, $l_cmb_energyused);
        $l_cmb_energyused = str_replace("[cmb_playerinfo_ship_energy]", $starting_energy, $l_cmb_energyused);
        echo "$l_cmb_energyused<BR></CENTER>";
        $db->Execute("UPDATE $dbtables[players] SET rating=rating-$rating_change WHERE player_id=$playerinfo[player_id]");
		$db->Execute("UPDATE $dbtables[ships] SET ship_energy=$energy,ship_fighters=ship_fighters-$fighters_lost, torps=torps-$attackertorps,armour_pts=armour_pts-$armor_lost WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
    }

    $result4 = $db->Execute("SELECT * FROM $dbtables[players] WHERE planet_id=$planetinfo[planet_id] AND on_planet='Y'");
    $shipsonplanet = $result4->RecordCount();

    if($planetshields < 1 && $planetfighters < 1 && $attackerarmor > 0 && $shipsonplanet == 0)
    {
		// ************************  Planet defeated ***********************************
        echo "<BR><BR><CENTER><FONT COLOR='GREEN'><B>$l_cmb_planetdefeated</b></FONT></CENTER><BR><BR>";
		// news
		$headline="Planetary Defeat!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." defeated ".$ownerinfo[character_name]."s planet ".$planetinfo[name].". ";
		if ($playerinfo[score] < $ownerinfo[score]) {
			$newstext = $newstext . $playerinfo[character_name]." knew it would be a difficult battle but succeeded!";
		} else {
			$newstext = $newstext . $playerinfo[character_name]." crushed the weak.";
		}
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
	
        if($min_value_capture != 0)
        {
            $playerscore = gen_score($playerinfo[player_id]);
            $playerscore *= $playerscore;

            $planetscore = $planetinfo[organics] * $organics_price + $planetinfo[ore] * $ore_price + $planetinfo[goods] * $goods_price + $planetinfo[energy] * $energy_price + $planetinfo[fighters] * $fighter_price + $planetinfo[torps] * $torpedo_price + $planetinfo[colonists] * $colonist_price + $planetinfo[credits];
            $planetscore = $planetscore * $min_value_capture / 100;

            //            echo "playerscore $playerscore, planetscore $planetscore";
            if($playerscore < $planetscore)
            {
                echo "<CENTER>$l_cmb_citizenswanttodie</CENTER><BR><BR>";
                $db->Execute("DELETE FROM $dbtables[planets] WHERE planet_id=$planetinfo[planet_id]");
                playerlog($ownerinfo[player_id], LOG_PLANET_DEFEATED_D, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                adminlog(LOG_ADMIN_PLANETDEL, "$playerinfo[character_name]|$ownerinfo[character_name]|$playerinfo[sector]");
                gen_score($ownerinfo[player_id]);
            }
            else
            {
                $l_cmb_youmaycapture = str_replace("[cmb_planetinfo_planet_id]", $planetinfo[planet_id], $l_cmb_youmaycapture);
                echo "<CENTER><font color=red>$l_cmb_youmaycapture</font></CENTER><BR><BR>";
                playerlog($ownerinfo[player_id], LOG_PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
                gen_score($ownerinfo[player_id]);
				// Make sure that credits are reduced on the planet
                $update7a = $db->Execute("UPDATE $dbtables[planets] SET owner=0, fighters=0, torps=torps-$planettorps, base='N', defeated='Y',credits=(credits*(1-$ibank_paymentfee*5)) WHERE planet_id=$planetinfo[planet_id]");
            }
        }
        else
        {
            $l_cmb_youmaycapture2 = str_replace("[cmb_planetinfo_planet_id]", $planetinfo[planet_id], $l_cmb_youmaycapture2);
            echo "<CENTER>$l_cmb_youmaycapture2</CENTER><BR><BR>";
            playerlog($ownerinfo[player_id], LOG_PLANET_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
            gen_score($ownerinfo[player_id]);
            $update7a = $db->Execute("UPDATE $dbtables[planets] SET owner=0,fighters=0, torps=torps-$planettorps, base='N', defeated='Y', credits=(credits*(1-$ibank_paymentfee*5)) WHERE planet_id=$planetinfo[planet_id]");
        }
        calc_ownership($planetinfo[sector_id]);
    }
    else if($attackerarmor < 1)
    {
		// *****************************   Planet not defeated and attacker blew up ************************
        echo "<BR><BR><CENTER><FONT COLOR='#6098F8'><B>$l_cmb_planetnotdefeated</B></FONT></CENTER><BR><BR>";
        if ($debug)
            echo "<BR><BR>$l_cmb_planetstatistics<BR><BR>";
        $fighters_lost = $planetinfo[fighters] - $planetfighters;
        $l_cmb_fighterloststat = str_replace("[cmb_fighters_lost]", $fighters_lost, $l_cmb_fighterloststat);
        $l_cmb_fighterloststat = str_replace("[cmb_planetinfo_fighters]", $planetinfo[fighters], $l_cmb_fighterloststat);
        $l_cmb_fighterloststat = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_fighterloststat);
        if ($debug)
            echo "$l_cmb_fighterloststat<BR>";
        $energy=$planetinfo[energy];
        if ($debug)
            echo "$l_cmb_energyleft: $planetinfo[energy]<BR>";
        playerlog($ownerinfo[player_id], LOG_PLANET_NOT_DEFEATED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]|".NUMBER($free_ore)."|".NUMBER($free_organics)."|".NUMBER($free_goods)."|$ship_salvage_rate|".NUMBER($ship_salvage));
        gen_score($ownerinfo[player_id]);
        $update7b = $db->Execute("UPDATE $dbtables[planets] SET energy=$energy,fighters=fighters-$fighters_lost, torps=torps-$planettorps, ore=ore+$free_ore, goods=goods+$free_goods, organics=organics+$free_organics, credits=credits+$ship_salvage WHERE planet_id=$planetinfo[planet_id]");
        if ($debug)
            echo "<BR>Set: energy=$energy, fighters lost=$fighters_lost, torps=$planetinfo[torps], sectorid=$sectorinfo[sector_id]<BR>";
    } else {
		// *****************************   Planet not defeated and attacker survived ************************
        echo "<BR><BR><CENTER><FONT COLOR='#6098F8'><B>$l_cmb_planetnotdefeated</B></FONT></CENTER><BR><BR>";
        $fighters_lost = $planetinfo[fighters] - $planetfighters;
        $l_cmb_fighterloststat = str_replace("[cmb_fighters_lost]", $fighters_lost, $l_cmb_fighterloststat);
        $l_cmb_fighterloststat = str_replace("[cmb_planetinfo_fighters]", $planetinfo[fighters], $l_cmb_fighterloststat);
        $l_cmb_fighterloststat = str_replace("[cmb_planetfighters]", $planetfighters, $l_cmb_fighterloststat);
        $energy=$planetinfo[energy];
        playerlog($ownerinfo[player_id], LOG_PLANET_SURVIVED, "$planetinfo[name]|$playerinfo[sector]|$playerinfo[character_name]");
        gen_score($ownerinfo[player_id]);
        $update7b = $db->Execute("UPDATE $dbtables[planets] SET energy=$energy,fighters=fighters-$fighters_lost, torps=torps-$planettorps WHERE planet_id=$planetinfo[planet_id]");
		// news
		$headline="Planet Attack Fails!";
		$newstext=$playerinfo[character_name]." in the ship ".$shipinfo[ship_name]." attacked ".$ownerinfo[character_name]."'s planet ".$planetinfo[name]." but the planet's citizens put up an unexpected defense and forced $playerinfo[character_name] to retreat! SUN will continue to report this story as the news breaks!";
		$newstext = addslashes($newstext);
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
 	}
    $update = $db->Execute("UPDATE $dbtables[players] SET turns=turns-1, turns_used=turns_used+1 WHERE player_id=$playerinfo[player_id]");
}

function shiptoship($target_id)
{
	global $upgrade_cost, $upgrade_factor;
    global $attackerbeams;
    global $attackerfighters;
    global $attackershields;
    global $attackertorps;
    global $attackerarmor;
    global $attackertorpdamage;
    global $start_energy;
    global $playerinfo;
    global $l_cmb_attackershields, $l_cmb_attackertorps, $l_cmb_attackerarmor, $l_cmb_attackertorpdamage;
    global $l_cmb_startingstats, $l_cmb_statattackerbeams, $l_cmb_statattackerfighters, $l_cmb_statattackershields, $l_cmb_statattackertorps;
    global $l_cmb_statattackerarmor, $l_cmb_statattackertorpdamage, $l_cmb_isattackingyou, $l_cmb_beamexchange, $l_cmb_beamsdestroy;
    global $l_cmb_beamsdestroy2, $l_cmb_nobeamsareleft, $l_cmb_beamshavenotarget, $l_cmb_fighterdestroyedbybeams, $l_cmb_beamsdestroystillhave;
    global $l_cmb_fighterunhindered, $l_cmb_youhavenofightersleft, $l_cmb_breachedsomeshields, $l_cmb_shieldsarehitbybeams, $l_cmb_nobeamslefttoattack;
    global $l_cmb_yourshieldsbreachedby, $l_cmb_yourshieldsarehit, $l_cmb_hehasnobeamslefttoattack, $l_cmb_yourbeamsbreachedhim;
    global $l_cmb_yourbeamshavedonedamage, $l_cmb_nobeamstoattackarmor, $l_cmb_yourarmorbreachedbybeams, $l_cmb_yourarmorhitdamaged;
    global $l_cmb_torpedoexchange, $l_cmb_hehasnobeamslefttoattackyou, $l_cmb_yourtorpsdestroy, $l_cmb_yourtorpsdestroy2;
    global $l_cmb_youhavenotorpsleft, $l_cmb_hehasnofighterleft, $l_cmb_torpsdestroyyou, $l_cmb_someonedestroyedfighters, $l_cmb_hehasnotorpsleftforyou;
    global $l_cmb_youhavenofightersanymore, $l_cmb_youbreachedwithtorps, $l_cmb_hisarmorishitbytorps, $l_cmb_notorpslefttoattackarmor;
    global $l_cmb_yourarmorbreachedbytorps, $l_cmb_yourarmorhitdmgtorps, $l_cmb_hehasnotorpsforyourarmor, $l_cmb_fightersattackexchange;
    global $l_cmb_enemylostallfighters, $l_cmb_helostsomefighters, $l_cmb_youlostallfighters, $l_cmb_youalsolostsomefighters, $l_cmb_hehasnofightersleftattack;
    global $l_cmb_younofightersattackleft, $l_cmb_youbreachedarmorwithfighters, $l_cmb_youhitarmordmgfighters, $l_cmb_youhavenofighterstoarmor;
    global $l_cmb_hasbreachedarmorfighters, $l_cmb_yourarmorishitfordmgby, $l_cmb_nofightersleftheforyourarmor, $l_cmb_hehasbeendestroyed;
    global $l_cmb_escapepodlaunched, $l_cmb_yousalvaged, $l_cmb_youdidntdestroyhim, $l_cmb_shiptoshipcombatstats;
    global $db, $dbtables;

    //$db->Execute("LOCK TABLES $dbtables[players] WRITE, $dbtables[ships] WRITE, $dbtables[universe] WRITE, $dbtables[zones] READ");
    $result2 = $db->Execute ("SELECT * FROM $dbtables[ships] WHERE ship_id=$target_id");
    $targetshipinfo=$result2->fields;
    $result2 = $db->Execute ("SELECT * FROM $dbtables[players] WHERE player_id=$targetshipinfo[player_id]");
    $targetinfo=$result2->fields;

    $targetbeams = NUM_BEAMS($targetshipinfo[beams]);
    if($targetbeams>$targetshipinfo[ship_energy])
    {
        $targetbeams=$targetshipinfo[ship_energy];
    }
    $targetshipinfo[ship_energy]=$targetshipinfo[ship_energy]-$targetbeams;
    $targetshields = NUM_SHIELDS($targetshipinfo[shields]);
    if($targetshields>$targetshipinfo[ship_energy])
    {
        $targetshields=$targetshipinfo[ship_energy];
    }
    $targetshipinfo[ship_energy]=$targetshipinfo[ship_energy]-$targetshields;

    $targettorpnum = round(mypw($level_factor,$targetshipinfo[torp_launchers]))*2;
    if($targettorpnum > $targetshipinfo[torps])
    {
        $targettorpnum = $targetshipinfo[torps];
    }
    $targettorpdmg = $torp_dmg_rate*$targettorpnum;
    $targetarmor = $targetshipinfo[armour_pts];
    $targetfighters = $targetshipinfo[ship_fighters];
    $targetdestroyed = 0;
    $playerdestroyed = 0;
    echo "-->$targetshipinfo[ship_name] $l_cmb_isattackingyou<BR><BR>";
	// ***************** BEAM EXCHANGE *************************
    echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_beamexchange</font><BR>";
    if($targetfighters > 0 && $attackerbeams > 0)
    {
        if($attackerbeams > round($targetfighters / 2))
        {
            $temp = round($targetfighters/2);
            $lost = $targetfighters-$temp;
            $targetfighters = $temp;
            $attackerbeams = $attackerbeams-$lost;
            $l_cmb_beamsdestroy = str_replace("[cmb_lost]", $lost, $l_cmb_beamsdestroy);
            echo "<-- $l_cmb_beamsdestroy<BR>";
        }
        else
        {
            $targetfighters = $targetfighters-$attackerbeams;
            $l_cmb_beamsdestroy2 = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_beamsdestroy2);
            echo "--> $l_cmb_beamsdestroy2<BR>";
            $attackerbeams = 0;
        }
    }
    elseif ($targetfighters > 0 && $attackerbeams < 1)
    echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_nobeamsareleft</font><BR>";
    else
        echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_beamshavenotarget</font><BR>";
    if($attackerfighters > 0 && $targetbeams > 0)
    {
        if($targetbeams > round($attackerfighters / 2))
        {
            $temp=round($attackerfighters/2);
            $lost=$attackerfighters-$temp;
            $attackerfighters=$temp;
            $targetbeams=$targetbeams-$lost;
            $l_cmb_fighterdestroyedbybeams = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_fighterdestroyedbybeams);
            $l_cmb_fighterdestroyedbybeams = str_replace("[cmb_lost]", $lost, $l_cmb_fighterdestroyedbybeams);
            echo "--> $l_cmb_fighterdestroyedbybeams";
        }
        else
        {
            $attackerfighters=$attackerfighters-$targetbeams;
            $l_cmb_beamsdestroystillhave = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_beamsdestroystillhave);
            $l_cmb_beamsdestroystillhave = str_replace("[cmb_targetbeams]", $targetbeams, $l_cmb_beamsdestroystillhave);
            $l_cmb_beamsdestroystillhave = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_beamsdestroystillhave);
            echo "<-- $l_cmb_beamsdestroystillhave<BR>";
            $targetbeams=0;
        }
    }
    elseif ($attackerfighters > 0 && $targetbeams < 1)
    echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_fighterunhindered</font><BR>";
    else
        echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_youhavenofightersleft</font><BR>";
    if($attackerbeams > 0)
    {
        if($attackerbeams > $targetshields)
        {
            $attackerbeams=$attackerbeams-$targetshields;
            $targetshields=0;
            $l_cmb_breachedsomeshields = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_breachedsomeshields);
            echo "<-- $l_cmb_breachedsomeshields<BR>";
        }
        else
        {
            $l_cmb_shieldsarehitbybeams = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_shieldsarehitbybeams);
            $l_cmb_shieldsarehitbybeams = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_shieldsarehitbybeams);
            echo "$l_cmb_shieldsarehitbybeams<BR>";
            $targetshields=$targetshields-$attackerbeams;
            $attackerbeams=0;
        }
    }
    else
    {
        $l_cmb_nobeamslefttoattack = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_nobeamslefttoattack);
        echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_nobeamslefttoattack</font><BR>";
    }
    if($targetbeams > 0)
    {
        if($targetbeams > $attackershields)
        {
            $targetbeams=$targetbeams-$attackershields;
            $attackershields=0;
            $l_cmb_yourshieldsbreachedby = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourshieldsbreachedby);
            echo "--> $l_cmb_yourshieldsbreachedby<BR>";
        }
        else
        {
            $l_cmb_yourshieldsarehit = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourshieldsarehit);
            $l_cmb_yourshieldsarehit = str_replace("[cmb_targetbeams]", $targetbeams, $l_cmb_yourshieldsarehit);
            echo "<-- $l_cmb_yourshieldsarehit<BR>";
            $attackershields=$attackershields-$targetbeams;
            $targetbeams=0;
        }
    }
    else
    {
        $l_cmb_hehasnobeamslefttoattack = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasnobeamslefttoattack);
        echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_hehasnobeamslefttoattack</font><BR>";
    }
    if($attackerbeams > 0)
    {
        if($attackerbeams > $targetarmor)
        {
            $targetarmor=0;
            $l_cmb_yourbeamsbreachedhim = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourbeamsbreachedhim);
            echo "--> $l_cmb_yourbeamsbreachedhim<BR>";
        }
        else
        {
            $targetarmor=$targetarmor-$attackerbeams;
            $l_cmb_yourbeamshavedonedamage = str_replace("[cmb_attackerbeams]", $attackerbeams, $l_cmb_yourbeamshavedonedamage);
            $l_cmb_yourbeamshavedonedamage = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourbeamshavedonedamage);
            echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_yourbeamshavedonedamage</font><BR>";
        }
    }
    else
    {
        $l_cmb_nobeamstoattackarmor = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_nobeamstoattackarmor);
        echo "<FONT SIZE='+2' COLOR='RED'>$l_cmb_nobeamstoattackarmor</font><BR>";
    }
    if($targetbeams > 0)
    {
        if($targetbeams > $attackerarmor)
        {
            $attackerarmor=0;
            $l_cmb_yourarmorbreachedbybeams = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourarmorbreachedbybeams);
            echo "--> $l_cmb_yourarmorbreachedbybeams<BR>";
        }
        else
        {
            $attackerarmor=$attackerarmor-$targetbeams;
            $l_cmb_yourarmorhitdamaged = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourarmorhitdamaged);
            $l_cmb_yourarmorhitdamaged = str_replace("[cmb_targetbeams]", $targetbeams, $l_cmb_yourarmorhitdamaged);
            echo "<-- $l_cmb_yourarmorhitdamaged<BR>";
        }
    }
    else
    {
        $l_cmb_hehasnobeamslefttoattackyou = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasnobeamslefttoattackyou);
        echo "$l_cmb_hehasnobeamslefttoattackyou<BR>";
    }
	// ****************** TORPEDO EXCHANGE ***********************
    echo "<BR><FONT SIZE='+2' COLOR='RED'>$l_cmb_torpedoexchange</font><BR>";
    if($targetfighters > 0 && $attackertorpdamage > 0)
    {
        if($attackertorpdamage > round($targetfighters / 2))
        {
            $temp=round($targetfighters/2);
            $lost=$targetfighters-$temp;
            $targetfighters=$temp;
            $attackertorpdamage=$attackertorpdamage-$lost;
            $l_cmb_yourtorpsdestroy = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourtorpsdestroy);
            $l_cmb_yourtorpsdestroy = str_replace("[cmb_lost]", $lost, $l_cmb_yourtorpsdestroy);
            echo "--> $l_cmb_yourtorpsdestroy<BR>";
        }
        else
        {
            $targetfighters=$targetfighters-$attackertorpdamage;
            $l_cmb_yourtorpsdestroy2 = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourtorpsdestroy2);
            $l_cmb_yourtorpsdestroy2 = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_yourtorpsdestroy2);
            echo "<-- $l_cmb_yourtorpsdestroy2<BR>";
            $attackertorpdamage=0;
        }
    }
    elseif ($targetfighters > 0 && $attackertorpdamage < 1)
    {
        $l_cmb_youhavenotorpsleft = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youhavenotorpsleft);
        echo "$l_cmb_youhavenotorpsleft<BR>";
    }
    else
    {
        $l_cmb_hehasnofighterleft = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasnofighterleft);
        echo "$_cmb_hehasnofighterleft<BR>";
    }
    if($attackerfighters > 0 && $targettorpdmg > 0)
    {
        if($targettorpdmg > round($attackerfighters / 2))
        {
            $temp=round($attackerfighters/2);
            $lost=$attackerfighters-$temp;
            $attackerfighters=$temp;
            $targettorpdmg=$targettorpdmg-$lost;
            $l_cmb_torpsdestroyyou = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_torpsdestroyyou);
            $l_cmb_torpsdestroyyou = str_replace("[cmb_lost]", $lost, $l_cmb_torpsdestroyyou);
            echo "--> $l_cmb_torpsdestroyyou<BR>";
        }
        else
        {
            $attackerfighters=$attackerfighters-$targettorpdmg;
            $l_cmb_someonedestroyedfighters = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_someonedestroyedfighters);
            $l_cmb_someonedestroyedfighters = str_replace("[cmb_targettorpdmg]", $targettorpdmg, $l_cmb_someonedestroyedfighters);
            echo "<-- $l_cmb_someonedestroyedfighters<BR>";
            $targettorpdmg=0;
        }
    }
    elseif ($attackerfighters > 0 && $targettorpdmg < 1)
    {
        $l_cmb_hehasnotorpsleftforyou = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasnotorpsleftforyou);
        echo "$l_cmb_hehasnotorpsleftforyou<BR>";
    }
    else
    {
        $l_cmb_youhavenofightersanymore = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youhavenofightersanymore);
        echo "$l_cmb_youhavenofightersanymore<BR>";
    }
    if($attackertorpdamage > 0)
    {
        if($attackertorpdamage > $targetarmor)
        {
            $targetarmor=0;
            $l_cmb_youbreachedwithtorps = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youbreachedwithtorps);
            echo "--> $l_cmb_youbreachedwithtorps<BR>";
        }
        else
        {
            $targetarmor=$targetarmor-$attackertorpdamage;
            $l_cmb_hisarmorishitbytorps = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hisarmorishitbytorps);
            $l_cmb_hisarmorishitbytorps = str_replace("[cmb_attackertorpdamage]", $attackertorpdamage, $l_cmb_hisarmorishitbytorps);
            echo "<-- $l_cmb_hisarmorishitbytorps<BR>";
        }
    }
    else
    {
        $l_cmb_notorpslefttoattackarmor = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_notorpslefttoattackarmor);
        echo "$l_cmb_notorpslefttoattackarmor<BR>";
    }
    if($targettorpdmg > 0)
    {
        if($targettorpdmg > $attackerarmor)
        {
            $attackerarmor=0;
            $l_cmb_yourarmorbreachedbytorps = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourarmorbreachedbytorps);
            echo "<-- $l_cmb_yourarmorbreachedbytorps<BR>";
        }
        else
        {
            $attackerarmor=$attackerarmor-$targettorpdmg;
            $l_cmb_yourarmorhitdmgtorps = str_replace("[cmb_targettorpdmg]", $targettorpdmg, $l_cmb_yourarmorhitdmgtorps);
            $l_cmb_yourarmorhitdmgtorps = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourarmorhitdmgtorps);
            echo "<-- $l_cmb_yourarmorhitdmgtorps<BR>";
        }
    }
    else
    {
        $l_cmb_hehasnotorpsforyourarmor = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasnotorpsforyourarmor);
        echo "$l_cmb_hehasnotorpsforyourarmor<BR>";
    }
	// *******************8 FIGHTERS EXCHANGE ******************	
    echo "<BR><FONT SIZE='+2' COLOR='RED'>$l_cmb_fightersattackexchange</FONT><BR>";
    if($attackerfighters > 0 && $targetfighters > 0)
    {
        if($attackerfighters > $targetfighters)
        {
            $l_cmb_enemylostallfighters = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_enemylostallfighters);
            echo "--> $l_cmb_enemylostallfighters<BR>";
            $temptargfighters=0;
        }
        else
        {
            $l_cmb_helostsomefighters = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_helostsomefighters);
            $l_cmb_helostsomefighters = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_helostsomefighters);
            echo "$l_cmb_helostsomefighters<BR>";
            $temptargfighters=$targetfighters-$attackerfighters;
        }
        if($targetfighters > $attackerfighters)
        {
            echo "<-- $l_cmb_youlostallfighters<BR>";
            $tempplayfighters=0;
        }
        else
        {
            $l_cmb_youalsolostsomefighters = str_replace("[cmb_targetfighters]", $targetfighters, $l_cmb_youalsolostsomefighters);
            echo "<-- $l_cmb_youalsolostsomefighters<BR>";
            $tempplayfighters=$attackerfighters-$targetfighters;
        }
        $attackerfighters=$tempplayfighters;
        $targetfighters=$temptargfighters;
    }
    elseif ($attackerfighters > 0 && $targetfighters < 1)
    {
        $l_cmb_hehasnofightersleftattack = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasnofightersleftattack);
        echo "$l_cmb_hehasnofightersleftattack<BR>";
    }
    else
    {
        $l_cmb_younofightersattackleft = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_younofightersattackleft);
        echo "$l_cmb_younofightersattackleft<BR>";
    }
    if($attackerfighters > 0)
    {
        if($attackerfighters > $targetarmor)
        {
            $targetarmor=0;
            $l_cmb_youbreachedarmorwithfighters = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youbreachedarmorwithfighters);
            echo "--> $l_cmb_youbreachedarmorwithfighters<BR>";
        }
        else
        {
            $targetarmor=$targetarmor-$attackerfighters;
            $l_cmb_youhitarmordmgfighters = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youhitarmordmgfighters);
            $l_cmb_youhitarmordmgfighters = str_replace("[cmb_attackerfighters]", $attackerfighters, $l_cmb_youhitarmordmgfighters);
            echo "<-- $l_cmb_youhitarmordmgfighters<BR>";
        }
    }
    else
    {
        $l_cmb_youhavenofighterstoarmor = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youhavenofighterstoarmor);
        echo "$l_cmb_youhavenofighterstoarmor<BR>";
    }
    if($targetfighters > 0)
    {
        if($targetfighters > $attackerarmor)
        {
            $attackerarmor=0;
            $l_cmb_hasbreachedarmorfighters = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hasbreachedarmorfighters);
            echo "<-- $l_cmb_hasbreachedarmorfighters<BR>";
        }
        else
        {
            $attackerarmor=$attackerarmor-$targetfighters;
            $l_cmb_yourarmorishitfordmgby = str_replace("[cmb_targetfighters]", $targetfighters, $l_cmb_yourarmorishitfordmgby);
            $l_cmb_yourarmorishitfordmgby = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_yourarmorishitfordmgby);
            echo "--> $l_cmb_yourarmorishitfordmgby<BR>";
        }
    }
    else
    {
        $l_cmb_nofightersleftheforyourarmor = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_nofightersleftheforyourarmor);
        echo "$l_cmb_nofightersleftheforyourarmor<BR>";
    }
    if($targetarmor < 1)
    {
        $l_cmb_hehasbeendestroyed = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_hehasbeendestroyed);
        echo "<BR>$l_cmb_hehasbeendestroyed<BR>";
		// news
		$headline="Ship blown up on planet!";
		$newstext="The ship ".$targetshipinfo[ship_name]." was destroyed today by ".$playerinfo[character_name]." during a planet attack. ";
        if($targetshipinfo[dev_escapepod] == "Y")
        {
            $rating=round($targetinfo[rating]/2);
            echo "$l_cmb_escapepodlaunched<BR><BR>";
            echo "<BR><BR>player_id=$targetinfo[player_id]<BR><BR>";
            $test = $db->Execute("UPDATE $dbtables[players] SET on_planet='N',rating='$rating',sector=0 WHERE player_id=$targetinfo[player_id]");
			// Immediately release any ships being towed 
    		if ($targetshipinfo[tow] > 0) {
				$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$targetshipinfo[sector] WHERE ship_id=$targetshipinfo[tow]");
  			}
			$db->Execute("UPDATE $dbtables[ships] SET ship_destroyed ='Y', sector=0, tow=0 WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
            playerlog($targetinfo[player_id],LOG_ATTACK_LOSE, "$playerinfo[character_name]|Y");
            collect_bounty($playerinfo[player_id],$targetinfo[player_id]);
			$newstext = $newstext.$targetinfo[character_name]." survived and podded off to Sector 0.";
        }
        else
        {
            playerlog($targetinfo[player_id], LOG_ATTACK_LOSE, "$playerinfo[character_name]|N");
            db_kill_player($targetinfo[player_id],$targetinfo[currentship],$playerinfo[player_id]);
            collect_bounty($playerinfo[player_id],$targetinfo[player_id]);
			$newstext .= $targetinfo[character_name]." did not survive the attack.";
        }
		$newstext = addslashes($newstext);
		$player_id = $playerinfo[player_id];
		$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'attack')");
		
        if($attackerarmor > 0)
        {
            $rating_change=round($targetinfo[rating]*$rating_combat_factor);
            $free_ore = round($targetshipinfo[ship_ore]/2);
            $free_organics = round($targetshipinfo[ship_organics]/2);
            $free_goods = round($targetshipinfo[ship_goods]/2);
            $free_holds = NUM_HOLDS($shipinfo[hull]) - $shipinfo[ship_ore] - $shipinfo[ship_organics] - $shipinfo[ship_goods] - $shipinfo[ship_colonists];
            if($free_holds > $free_goods)
            {
                $salv_goods=$free_goods;
                $free_holds=$free_holds-$free_goods;
            }
            elseif($free_holds > 0)
            {
                $salv_goods=$free_holds;
                $free_holds=0;
            }
            else
            {
                $salv_goods=0;
            }
            if($free_holds > $free_ore)
            {
                $salv_ore=$free_ore;
                $free_holds=$free_holds-$free_ore;
            }
            elseif($free_holds > 0)
            {
                $salv_ore=$free_holds;
                $free_holds=0;
            }
            else
            {
                $salv_ore=0;
            }
            if($free_holds > $free_organics)
            {
                $salv_organics=$free_organics;
                $free_holds=$free_holds-$free_organics;
            }
            elseif($free_holds > 0)
            {
                $salv_organics=$free_holds;
                $free_holds=0;
            }
            else
            {
                $salv_organics=0;
            }
            $ship_value=$upgrade_cost*(round(mypw($upgrade_factor, $targetshipinfo[hull]))+round(mypw($upgrade_factor, $targetshipinfo[engines]))+round(mypw($upgrade_factor, $targetshipinfo[power]))+round(mypw($upgrade_factor, $targetshipinfo[computer]))+round(mypw($upgrade_factor, $targetshipinfo[sensors]))+round(mypw($upgrade_factor, $targetshipinfo[beams]))+round(mypw($upgrade_factor, $targetshipinfo[torp_launchers]))+round(mypw($upgrade_factor, $targetshipinfo[shields]))+round(mypw($upgrade_factor, $targetshipinfo[armor]))+round(mypw($upgrade_factor, $targetshipinfo[cloak])));
            $ship_salvage_rate=rand(10,20);
            $ship_salvage=$ship_value*$ship_salvage_rate/100;
			// Triple salvage if this is an outlaw
		  	$result3 = $db->Execute ("SELECT amount FROM $dbtables[bounty] WHERE placed_by=0 AND bounty_on=$targetinfo[player_id]");
		  	if (!$result3->EOF) {
		  		$ship_salvage *=3;
				echo "You destroyed a Federation fugitive! Triple hull salvage!<br>";
		  	}
            $l_cmb_yousalvaged = str_replace("[cmb_salv_ore]", NUMBER($salv_ore), $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salv_organics]", NUMBER($salv_organics), $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salv_goods]", NUMBER($salv_goods), $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salvage_rate]", $ship_salvage_rate, $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_salvage]", NUMBER($ship_salvage), $l_cmb_yousalvaged);
            $l_cmb_yousalvaged = str_replace("[cmb_number_rating_change]", NUMBER(abs($rating_change)), $l_cmb_yousalvaged);
            echo "$l_cmb_yousalvaged";
            $update3 = $db->Execute ("UPDATE $dbtables[players] SET credits=credits+$ship_salvage WHERE player_id=$playerinfo[player_id]");
			$update3 = $db->Execute ("UPDATE $dbtables[ships] SET ship_ore=ship_ore+$salv_ore, ship_organics=ship_organics+$salv_organics, ship_goods=ship_goods+$salv_goods WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
        }
    }
    else
    {
        $l_cmb_youdidntdestroyhim = str_replace("[cmb_targetinfo_ship_name]", $targetshipinfo[ship_name], $l_cmb_youdidntdestroyhim);
        echo "$l_cmb_youdidntdestroyhim<BR>";
        $target_rating_change=round($targetinfo[rating]*.1);
        $target_armor_lost=$targetshipinfo[armour_pts]-$targetarmor;
        $target_fighters_lost=$targetshipinfo[ship_fighters]-$targetfighters;
        $target_energy=$targetshipinfo[ship_energy];
        playerlog($targetinfo[player_id], LOG_ATTACKED_WIN, "$playerinfo[character_name]|$target_armor_lost|$target_fighters_lost");
        $update4 = $db->Execute ("UPDATE $dbtables[ships] SET ship_energy=$target_energy,ship_fighters=ship_fighters-$target_fighters_lost, armour_pts=armour_pts-$target_armor_lost, torps=torps-$targettorpnum WHERE player_id=$targetinfo[player_id] AND ship_id=$targetinfo[currentship]");
	}
    $db->Execute("UNLOCK TABLES");
}
?>
