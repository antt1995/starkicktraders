<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_pr_title;

include("header.php");

connectdb();

if(checklogin())
{
  die();
}

// Get data about planets
$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;


// determine what type of report is displayed and display it's title
if($PRepType==1 || !isset($PRepType)) // display the commodities on the planets
{
  $title = "$title: Status";
  bigtitle();
  standard_report();
}
elseif($PRepType==2)                  // display the production values of your planets and allow changing
{
  $title = "$title: Production";
  bigtitle();
  planet_production_change();
}
elseif($PRepType==0)                  // For typing in manually to get a report menu
{
  $title = "$title: Menu";
  bigtitle();
  planet_report_menu();
}
elseif($PRepType==3)
{
  $title = "$title: Pricing";
  bigtitle();
  planet_pricing_menu();
}
else                                  // display the menu if no valid options are passed in
{
  $title = "$title: Status";
  bigtitle();
  standard_report();
}


// ---- Begin functions ------ //
function planet_report_menu()
{
  global $playerinfo;
  global $l_pr_teamlink;

  echo "<B><A HREF=planet-report.php?PRepType=1 NAME=Planet Status>Planet Status</A></B><BR>" .
       "Displays the amount of each Commodity that you have on your planets (Ore, Organics, Goods, Energy, Colonists, Credits, Fighters, and Torpedoes)<BR>" .
       "<BR>" .
       "<B><A HREF=planet-report.php?PRepType=2 NAME=Planet Status>Change Production</A></B> &nbsp;&nbsp; <B>Base Required</B> on Planet<BR>" .
       "This Report allows you to change the rate of production of commodities on planets that have a base.<BR>" .
       "<BR>";
  echo "<br><B><A HREF=planet-report.php?PRepType=3>Set Planet Commodity Sale Prices</A></B><BR>";
  echo "This allows you to set what prices traders will pay for your commodities if they buy them from your planets. Prices have to be at or below what ports charge.<br><br>";
  if ($playerinfo[team]>0)
  {
    echo "<BR>" .
         "<B><A HREF=alliance-planets.php>$l_pr_teamlink</A></B><BR> " . 
         "Commondity Report (like Planet Status) but for planets marked Corporate by you and/or your fellow alliance member<BR>" .
         "<BR>";
  }
}

function planet_pricing_menu()
{
  global $db;
  global $res;
  global $playerinfo;
  global $dbtables;
  global $username;
  global $sort;
  global $query;
  global $color_header, $color, $color_line1, $color_line2;
  global $l_pr_teamlink, $l_pr_clicktosort;
  global $l_sector, $l_name, $l_unnamed, $l_ore, $l_organics, $l_goods, $l_energy, $l_colonists, $l_credits, $l_fighters, $l_torps, $l_base, $l_selling, $l_pr_totals, $l_yes, $l_no;
  global $ore_price, $ore_delta, $organics_price, $organics_delta, $goods_price, $goods_delta, $energy_price, $energy_delta;
  
  $max_ore_price = $ore_price-$ore_delta;
  $max_organics_price = $organics_price-$organics_delta;
  $max_goods_price = $goods_price-$goods_delta;
  $max_energy_price = $energy_price-$energy_delta;
  $query = "SELECT * FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND base='Y'";

  echo "Planetary report <B><A HREF=planet-report.php?PRepType=0>menu</A></B><BR>" .
       "<BR>" .
       "<B><A HREF=planet-report.php?PRepType=1>Planet Status</A></B><BR>";
  echo "<br>";

  if ($playerinfo[team]>0)
  {
    echo "<BR>" .
         "<B><A HREF=alliance-planets.php>$l_pr_teamlink</A></B><BR> " . 
         "<BR>";
  }
  echo "This is where you can set the price of commodities on your planets. Prices cannot exceed the general port price and must be greater than 0.5 credits.<br>";
  echo "<FORM ACTION=planet-report-CE.php METHOD=POST>
  <table width=100% border=1 cellpadding=0>
    <tr> 
      <td>Commoditiy</td>
      <td> 
        <div align=center>Max Price</div>
      </td>
      <td align=center>Your Price</td>
	  <td align=center>Do Not Sell</td>
    </tr>
    <tr> 
      <td>$l_ore</td>
      <td> 
        <div align=center>$max_ore_price</div>
      </td>
      <td align=center>
        <input type=text size=5 name=my_ore_price value=".number_format($playerinfo[ore_price],2)." >
      </td><td align=center>";
	if ($playerinfo[ore_price] == 0) {
		echo "<input type=checkbox name=my_ore_price value=0 checked>";
	} else {
		echo "<input type=checkbox name=my_ore_price value=0>";
	}
	echo "</td>		
    </tr>
    <tr> 
      <td>$l_organics</td>
      <td> 
        <div align=center>$max_organics_price</div>
      </td>
      <td align=center> 
        <input type=text size=5 name=my_organics_price value=".number_format($playerinfo[organics_price],2).">
      </td><td align=center>";
	if ($playerinfo[organics_price] == 0) {
		echo "<input type=checkbox name=my_organics_price value=0 checked>";
	} else {
		echo "<input type=checkbox name=my_organics_price value=0>";
	}
	echo "</td>
    </tr>
    <tr> 
      <td>$l_goods</td>
      <td> 
        <div align=center>$max_goods_price</div>
      </td>
      <td align=center> 
        <input type=text size=5 name=my_goods_price value=".number_format($playerinfo[goods_price],2).">
      </td><td align=center>";
	if ($playerinfo[goods_price] == 0) {
		echo "<input type=checkbox name=my_goods_price value=0 checked>";
	} else {
		echo "<input type=checkbox name=my_goods_price value=0>";
	}
	echo "</td>
    </tr>
    <tr> 
      <td>$l_energy</td>
      <td> 
        <div align=center>$max_energy_price</div>
      </td>
      <td align=center> 
        <input type=text size=5 name=my_energy_price value=".number_format($playerinfo[energy_price],2).">
      </td><td align=center>";
	if ($playerinfo[energy_price] == 0) {
		echo "<input type=checkbox name=my_energy_price value=0 checked>";
	} else {
		echo "<input type=checkbox name=my_energy_price value=0>";
	}
	echo "</td>
    </tr>
  </table>
  <p> 
    <input type=submit name=Submit value=Submit>
    <input type=reset name=Submit2 value=Reset>
  </p>
</form>
<br>";
}

function standard_report()
{
  global $db;
  global $res;
  global $playerinfo;
  global $dbtables;
  global $username;
  global $sort;
  global $query;
  global $color_header, $color, $color_line1, $color_line2;
  global $l_pr_teamlink, $l_pr_clicktosort;
  global $l_sector, $l_name, $l_unnamed, $l_ore, $l_organics, $l_goods, $l_energy, $l_colonists, $l_credits, $l_fighters, $l_torps, $l_base, $l_selling, $l_pr_totals, $l_yes, $l_no;

  echo "Planetary report descriptions and <B><A HREF=planet-report.php?PRepType=0>menu</A></B><BR>" .
       "<BR>" .
       "<B><A HREF=planet-report.php?PRepType=2>Change Production</A></B> &nbsp;&nbsp; <B>Base Required</B> on Planet<BR>";
  echo "<br><B><A HREF=planet-report.php?PRepType=3>Set Planet Commodity Sale Prices</A></B><BR>";
  if ($playerinfo[team]>0)
  {
    echo "<BR>" .
         "<B><A HREF=alliance-planets.php>$l_pr_teamlink</A></B><BR> " . 
         "<BR>";
  }


  $query = "SELECT * FROM $dbtables[planets] WHERE owner=$playerinfo[player_id]";

  if(!empty($sort))
  {
    $query .= " ORDER BY";
    if($sort == "name")
    {
      $query .= " $sort ASC";
    }
    elseif($sort == "organics" || $sort == "ore" || $sort == "goods" || $sort == "energy" ||
      $sort == "colonists" || $sort == "credits" || $sort == "fighters")
    {
      $query .= " $sort DESC, sector_id ASC";
    }
    elseif($sort == "torp")
    {
      $query .= " torps DESC, sector_id ASC";
    }
    elseif($sort == "piq")
    {
      $query .= " tech_level DESC, sector_id ASC";
    }
	else
    {
      $query .= " sector_id ASC";
    }

  }
  else
  {
     $query .= " ORDER BY sector_id ASC";
  }
  if (isset($limit)) {
  	$query .= " LIMIT $limit,10";
  }
  $res = $db->Execute($query);

  $i = 0;
  if($res)
  {
    while(!$res->EOF)
    {
      $planet[$i] = $res->fields;
      $i++;
      $res->MoveNext();
    }
  }

  $num_planets = $i;
  if($num_planets < 1)
  {
    echo "<BR>$l_pr_noplanet";
  }
  else
  {

    echo "<BR>";
    echo "<H2>You have $num_planets planets</H2>";
	echo "Sort by maximum: <a href=planet-report.php>Sector</a> <a href=planet-report.php?sort=organics>Organics</a> <a href=planet-report.php?sort=ore>Ore</a> <a href=planet-report.php?sort=goods>Goods</a> <a href=planet-report.php?sort=energy>Energy</a> <a href=planet-report.php?sort=colonists>Colonists</a> <a href=planet-report.php?sort=credits>Credits</a> <a href=planet-report.php?sort=fighters>Fighters</a> <a href=planet-report.php?sort=piq>Planet IQ</a><p>";
    $total_organics = 0;
    $total_ore = 0;
    $total_goods = 0;
    $total_energy = 0;
    $total_colonists = 0;
    $total_credits = 0;
    $total_fighters = 0;
    $total_torp = 0;
    $total_base = 0;
    $total_corp = 0;
    $total_selling = 0;
	$total_piq = 0;
    $color = $color_line1;
    for($i=0; $i<$num_planets; $i++)
    {
      $total_organics += $planet[$i][organics];
      $total_ore += $planet[$i][ore];
      $total_goods += $planet[$i][goods];
      $total_energy += $planet[$i][energy];
      $total_colonists += $planet[$i][colonists];
      $total_credits += $planet[$i][credits];
      $total_fighters += $planet[$i][fighters];
      $total_torp += $planet[$i][torps];
	  $total_piq += $planet[$i][tech_level];
      if($planet[$i][base] == "Y")
      {
        $total_base += 1;
      }
      if($planet[$i][corp] > 0)
      {
        $total_corp += 1;
      }
      if($planet[$i][sells] == "Y")
      {
        $total_selling += 1;
      }
      if(empty($planet[$i][name]))
      {
        $planet[$i][name] = $l_unnamed;
      }
	  echo "<TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=2>";
      echo "<TR BGCOLOR=\"$color\">";
      echo "<TD COLSPAN=2 align=center><B>Planet: " . $planet[$i][name] . "<BR>Sector: <A HREF=rsmove.php?engage=1&destination=". $planet[$i][sector_id] . "&kk=".date("U").">". $planet[$i][sector_id] ."</A></B></TD>";
      echo "</TR>\n";
      echo "<TR><TD>Ore</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][ore]) . "</TD></TR>";
      echo "<TR><TD>Organics</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][organics]) . "</TD></TR>";
      echo "<TR><TD>Goods</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][goods]) . "</TD></TR>";
      echo "<TR><TD>Energy</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][energy]) . "</TD></TR>";
      echo "<TR><TD>Colonists</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][colonists]) . "</TD></TR>";
      echo "<TR><TD>Credits</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][credits]) . "</TD></TR>";
      //echo "<TD ALIGN=CENTER>" . "<INPUT TYPE=CHECKBOX NAME=TPCreds[] VALUE=\"" . $planet[$i]["planet_id"] . "\">" . "</TD>";
      echo "<TR><TD>Fighters</TD><TD ALIGN=CENTER>"  . NUMBER($planet[$i][fighters]) . "</TD></TR>";
      echo "<TR><TD>Torpedoes</TD><TD ALIGN=CENTER>"  . NUMBER($planet[$i][torps]) . "</TD></TR>";
      echo "<TR><TD>Base?</TD><TD ALIGN=CENTER>" . base_build_check($planet, $i) . "</TD></TR>";
      if($playerinfo[team] > 0)
        echo "<TR><TD>Alliance<BR>Planet</TD><TD ALIGN=CENTER>" . ($planet[$i][corp] > 0  ? "$l_yes" : "$l_no") . "</TD></TR>";
      echo "<TR><TD>Sells Commodities?</TD><TD ALIGN=CENTER>" . ($planet[$i][sells] == 'Y' ? "$l_yes" : "$l_no") . "</TD></TR>";
	  echo "<TR><TD>Planet IQ</TD><TD ALIGN=CENTER>" . NUMBER($planet[$i][tech_level]) . "</TD></TR>";
      //echo "</TR>";
      if($color == $color_line1)
      {
        $color = $color_line2;
      }
      else
      {
        $color = $color_line1;
      }
	 echo "</TABLE><P>";
    }
	echo "<TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=2>";
    // the next block displays the totals
    echo "<TR BGCOLOR=$color>";
    echo "<TD COLSPAN=2 ALIGN=CENTER><H2>$l_pr_totals</H2></TD>";
    echo "<TR><TD>Total Ore</TD><TD ALIGN=CENTER>" . NUMBER($total_ore) . "</TD></TR>";
    echo "<TR><TD>Total Organics</TD><TD ALIGN=CENTER>" . NUMBER($total_organics) . "</TD></TR>";
    echo "<TR><TD>Total Goods</TD><TD ALIGN=CENTER>" . NUMBER($total_goods) . "</TD></TR>";
    echo "<TR><TD>Total Energy</TD><TD ALIGN=CENTER>" . NUMBER($total_energy) . "</TD></TR>";
    echo "<TR><TD>Total Colonists</TD><TD ALIGN=CENTER>" . NUMBER($total_colonists) . "</TD></TR>";
    echo "<TR><TD>Total Credits</TD><TD ALIGN=CENTER>" . NUMBER($total_credits) . "</TD></TR>";
    echo "<TR><TD>Total Fighters</TD><TD ALIGN=CENTER>"  . NUMBER($total_fighters) . "</TD></TR>";
    echo "<TR><TD>Total Torpedoes</TD><TD ALIGN=CENTER>"  . NUMBER($total_torp) . "</TD></TR>";
    echo "<TR><TD>Total Bases</TD><TD ALIGN=CENTER>" . NUMBER($total_base) . "</TD></TR>";
    if($playerinfo[team] > 0)
      echo "<TR><TD>Number of Alliance Planets<TD ALIGN=CENTER>" . NUMBER($total_corp) . "</TD></TR>";
    echo "<TR><TD>Number of Planets Selling Commodities<TD ALIGN=CENTER>" . NUMBER($total_selling) . "</TD></TR>";
	echo "<TR><TD>Total Planet IQ</TD><TD ALIGN=CENTER>"  . NUMBER($total_piq) . "</TD></TR>";
    echo "</TABLE>";

    echo "<BR>";
    //echo "<INPUT TYPE=SUBMIT VALUE=\"Collect Credits\">  <INPUT TYPE=RESET VALUE=RESET>";
    //echo "</FORM>";
  }
}



function planet_production_change()
{
  global $db;
  global $res;
  global $playerinfo;
  global $dbtables;
  global $username;
  global $sort;
  global $query;
  global $color_header, $color, $color_line1, $color_line2;
  global $l_pr_teamlink, $l_pr_clicktosort;
  global $l_sector, $l_name, $l_unnamed, $l_ore, $l_organics, $l_goods, $l_energy, $l_colonists, $l_credits, $l_fighters, $l_torps, $l_base, $l_selling, $l_pr_totals, $l_yes, $l_no;


  $query = "SELECT * FROM $dbtables[planets] WHERE owner=$playerinfo[player_id] AND base='Y'";

  echo "Planetary report <B><A HREF=planet-report.php?PRepType=0>menu</A></B><BR>" .
       "<BR>" .
       "<B><A HREF=planet-report.php?PRepType=1>Planet Status</A></B><BR>";
  echo "<br><B><A HREF=planet-report.php?PRepType=3>Set Planet Commodity Sale Prices</A></B><BR>";
  if ($playerinfo[team]>0)
  {
    echo "<BR>" .
         "<B><A HREF=alliance-planets.php>$l_pr_teamlink</A></B><BR> " . 
         "<BR>";
  }

  if(!empty($sort))
  {
    $query .= " ORDER BY";
    if($sort == "name")
    {
      $query .= " $sort ASC";
    }
    elseif($sort == "organics" || $sort == "ore" || $sort == "goods" || $sort == "energy" || $sort == "fighters")
    {
      $query .= " prod_$sort DESC, sector_id ASC";
    }
    elseif($sort == "colonists" || $sort == "credits")
    {
      $query .= " $sort DESC, sector_id ASC";
    }
    elseif($sort == "torp")
    {
      $query .= " prod_torp DESC, sector_id ASC";
    }
    elseif($sort == "piq")
    {
      $query .= " tech_level DESC, sector_id ASC";
    }
    else
    {
      $query .= " sector_id ASC";
    }

  }
  else
  {
     $query .= " ORDER BY sector_id ASC";
  }

  $res = $db->Execute($query);

  $i = 0;
  if($res)
  {
    while(!$res->EOF)
    {
      $planet[$i] = $res->fields;
      $i++;
      $res->MoveNext();
    }
  }

  $num_planets = $i;
  if($num_planets < 1)
  {
    echo "<BR>$l_pr_noplanet";
  }
  else
  {
    echo "<FORM ACTION=planet-report-CE.php METHOD=POST>";

// ------ next block of echo's creates the header of the table
    echo "$l_pr_clicktosort<BR><BR>";
    echo "<TABLE WIDTH=\"100%\" BORDER=1 CELLSPACING=0 CELLPADDING=0>";
    echo "<TR BGCOLOR=\"$color_header\" VALIGN=BOTTOM>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=sector_id>Sector</A><BR>";
    echo "<A HREF=planet-report.php?PRepType=2&sort=name>Planet</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=ore>$l_ore</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=organics>Orgs</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=goods>$l_goods</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=energy>NRG</A></FONT></TD>";
    echo "</TR>";
    $total_colonists = 0;
    $total_credits = 0;
    $total_corp = 0;
    
    $temp_var = 0;

    $color = $color_line1;

    for($i=0; $i<$num_planets; $i++)
    {
      if(empty($planet[$i][name]))
      {
        $planet[$i][name] = $l_unnamed;
      }
      echo "<TR BGCOLOR=\"$color\">";
      echo "<TD ALIGN=CENTER><A HREF=rsmove.php?engage=1&destination=". $planet[$i][sector_id] . ">". $planet[$i][sector_id] ."</A><BR>". $planet[$i][name] . "</TD>";
      echo "<TD ALIGN=CENTER>" . "<input size=2 type=text name=\"prod_ore["      . $planet[$i]["planet_id"] . "]\" value=\"" . $planet[$i]["prod_ore"]      . "\">" . "</TD>";
      echo "<TD ALIGN=CENTER>" . "<input size=2 type=text name=\"prod_organics[" . $planet[$i]["planet_id"] . "]\" value=\"" . $planet[$i]["prod_organics"] . "\">" . "</TD>";
      echo "<TD ALIGN=CENTER>" . "<input size=2 type=text name=\"prod_goods["    . $planet[$i]["planet_id"] . "]\" value=\"" . $planet[$i]["prod_goods"]    . "\">" . "</TD>";
      echo "<TD ALIGN=CENTER>" . "<input size=2 type=text name=\"prod_energy["   . $planet[$i]["planet_id"] . "]\" value=\"" . $planet[$i]["prod_energy"]   . "\">" . "</TD>";
      echo "</TR>";

      if($color == $color_line1)
      {
        $color = $color_line2;
      }
      else
      {
        $color = $color_line1;
      }
    }
    echo "</TABLE><br>";
    echo "<TABLE WIDTH=\"100%\" BORDER=1 CELLSPACING=0 CELLPADDING=0>";
    echo "<TR BGCOLOR=\"$color_header\" VALIGN=BOTTOM>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=sector_id>Sector</A></FONT><br>";
    echo "<A HREF=planet-report.php?PRepType=2&sort=name>$l_name</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=colonists>Pops</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=credits>Creds</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=fighters>$l_fighters</A></FONT></TD>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=torp>Torps</A></FONT></TD>";
    echo "</TR>";
    $color = $color_line1;

    for($i=0; $i<$num_planets; $i++)
    {
      $total_colonists += $planet[$i][colonists];
      $total_credits += $planet[$i][credits];
      if(empty($planet[$i][name]))
      {
        $planet[$i][name] = $l_unnamed;
      }
      echo "<TR BGCOLOR=\"$color\">";
      echo "<TD ALIGN=CENTER><A HREF=rsmove.php?engage=1&destination=". $planet[$i][sector_id] . ">". $planet[$i][sector_id] ."</A><br>";
      echo $planet[$i][name] . "</TD>";
      echo "<TD ALIGN=CENTER>"  . NUMBER($planet[$i][colonists])              . "</TD>";
      echo "<TD ALIGN=CENTER>"  . NUMBER($planet[$i][credits])        . "</TD>";
      echo "<TD ALIGN=CENTER>" . "<input size=2 type=text name=\"prod_fighters[" . $planet[$i]["planet_id"] . "]\" value=\"" . $planet[$i]["prod_fighters"] . "\">" . "</TD>";
      echo "<TD ALIGN=CENTER>" . "<input size=2 type=text name=\"prod_torp["     . $planet[$i]["planet_id"] . "]\" value=\"" . $planet[$i]["prod_torp"]     . "\">" . "</TD>";
      echo "</TR>";
      if($color == $color_line1)
      {
        $color = $color_line2;
      }
      else
      {
        $color = $color_line1;
      }
    }
    echo "<TR BGCOLOR=$color>";
    echo "<TD ALIGN=CENTER>$l_pr_totals</TD>";
    echo "<TD ALIGN=CENTER>" . NUMBER($total_colonists) . "</TD>";
    echo "<TD ALIGN=CENTER>" . NUMBER($total_credits)   . "</TD>";
    echo "<TD>" . "" . "</TD>";
    echo "<TD>" . "" . "</TD>";
    echo "</TR>";
    echo "</TABLE><br>";
	
    echo "<TABLE WIDTH=\"100%\" BORDER=1 CELLSPACING=0 CELLPADDING=2>";
    echo "<TR BGCOLOR=\"$color_header\" VALIGN=BOTTOM>";
    echo "<TD ALIGN=CENTER><A HREF=planet-report.php?PRepType=2&sort=sector_id>Sector</A></FONT><br>";
    echo "<A HREF=planet-report.php?PRepType=2&sort=name>$l_name</A></FONT></TD>";
    if($playerinfo[team] > 0)
      echo "<TD ALIGN=CENTER>Alliance<br>Planet?</TD>";
    echo "<TD ALIGN=CENTER>$l_selling?</TD>";
	echo "<TD ALIGN=CENTER>Planet IQ</TD>";
    echo "</TR>";
    $color = $color_line1;

    for($i=0; $i<$num_planets; $i++)
    {
      if(empty($planet[$i][name]))
      {
        $planet[$i][name] = $l_unnamed;
      }
      echo "<TR BGCOLOR=\"$color\">";
      echo "<TD ALIGN=CENTER><A HREF=rsmove.php?engage=1&destination=". $planet[$i][sector_id] . ">". $planet[$i][sector_id] ."</A><br>";
      echo $planet[$i][name] . "</TD>";

      if($playerinfo[team] > 0)
        echo "<TD ALIGN=CENTER>" . corp_planet_checkboxes($planet, $i) . "</TD>";
      echo "<TD ALIGN=CENTER>" . selling_checkboxes($planet, $i)     . "</TD>";
	  echo "<TD ALIGN=CENTER>" . NUMBER($planet[$i][tech_level])   . "</TD>";
      echo "</TR>";
      if($color == $color_line1)
      {
        $color = $color_line2;
      }
      else
      {
        $color = $color_line1;
      }
    }
    echo "</TABLE>";
	
	
    echo "<BR>";
    echo "<INPUT TYPE=HIDDEN NAME=player_id VALUE=$playerinfo[player_id]>";
    echo "<INPUT TYPE=HIDDEN NAME=team_id   VALUE=$playerinfo[team]>";
    echo "<INPUT TYPE=SUBMIT VALUE=Submit>  <INPUT TYPE=RESET VALUE=Reset>";
    echo "</FORM>";
  }
}

function corp_planet_checkboxes($planet, $i)
{
 if($planet[$i][corp] <= 0)
    return("<INPUT TYPE=CHECKBOX NAME=corp[] VALUE=\"" . $planet[$i]["planet_id"] . "\">");
  elseif($planet[$i][corp] > 0)
    return("<INPUT TYPE=CHECKBOX NAME=corp[] VALUE=\"" . $planet[$i]["planet_id"] . "\" CHECKED>");
}

function selling_checkboxes($planet, $i)
{
  if($planet[$i][sells] != 'Y')
    return("<INPUT TYPE=CHECKBOX NAME=sells[] VALUE=\"" . $planet[$i]["planet_id"] . "\">");
  elseif($planet[$i][sells] == 'Y')
    return("<INPUT TYPE=CHECKBOX NAME=sells[] VALUE=\"" . $planet[$i]["planet_id"] . "\" CHECKED>");
}

function base_build_check($planet, $i)
{
  global $l_yes, $l_no;
  global $base_ore, $base_organics, $base_goods, $base_credits;

   if($planet[$i][base] == 'Y')
    return("$l_yes");
  elseif($planet[$i][ore] >= $base_ore && $planet[$i][organics] >= $base_organics && $planet[$i][goods] >= $base_goods && $planet[$i][credits] >= $base_credits)
    return("<A HREF=planet-report-CE.php?buildp=" . $planet[$i]["planet_id"] . "&builds=" . $planet[$i]["sector_id"] . ">Build</A>");
  else
    return("$l_no");
}

echo "<BR><BR>";

TEXT_GOTOMAIN();

include("footer.php");

?>