<? 

include("config.php");
include("languages/$lang");

$title="Game Settings";
include("header.php");


bigtitle();

//-------------------------------------------------------------------------------------------------
$line_color = $color_line1;

function line($item, $value)
{
  global $line_color, $color_line1, $color_line2;
  
  echo "<TR BGCOLOR=\"$line_color\"><TD>$item</TD><TD>$value</TD></TR>\n";
  if($line_color == $color_line1)
  { 
    $line_color = $color_line2; 
  }
  else
  { 
    $line_color = $color_line1; 
  }
}


  
  echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>";
   line("Game version:",$game_name);
   line("Average tech level needed to hit mines",$mine_hullsize);
   line("Averaged Tech level When Emergency Warp Degrades",$ewd_maxhullsize);
   
   $num = NUMBER($sector_max);
   line("Number of Sectors",$num);
   line("Maximum Links per sector",$link_max);
   line("Maximum average tech level for Federation Sectors",$fed_max_hull);
   
   $bank_enabled = $allow_ibank ? "Yes" : "No";
   line("Intergalactic Bank Enabled",$bank_enabled);
   
   if($allow_ibank)
   {
     $rate = $ibank_interest * 100;
     line("IGB Interest rate per update",$rate);
     $rate = $ibank_loaninterest * 100;
     line("IGB Loan rate per update",$rate);	 
	 line("IGB Debt Collection enabled after",NUMBER(($IGB_crate/60),2)." hours");
   }
   if($sofa_on) {
   	 line("Sub-Orbital Fighter Attacks (SOFA)","Allowed");
	}
   line("Tech Level upgrade for Bases",$basedefense);
   
   $num = NUMBER($colonist_limit);
   line("Colonists Limit",$num."&nbsp;");
   
   $num = NUMBER($max_turns);
   line("Maximum number of accumulated turns",$num);
   line("Number of turns player starts with",NUMBER($start_turns));
   line("Maximum number of planets per sector",$max_planets_sector);
   line("Maximum number of traderoutes per player",$max_traderoutes_player);
   line("Colonist Production Rate",$colonist_production_rate);
   line("Unit of Energy used per sector fighter",$energy_per_fighter);
   
   $rate = $defence_degrade_rate * 100;
   line("Sector fighter degradation percentage rate",$rate);
   line("Number of planets with bases need for sector ownership&nbsp;",$min_bases_to_own);
   
   $rate = NUMBER(($interest_rate - 1) * 100 , 3);
   line("Planet interest rate",$rate);
   
   $rate = 1 / $colonist_production_rate;
   
   $num = NUMBER($rate/$fighter_prate);
   line("Colonists needed to produce 1 Fighter each turn",$num);
   
   $num = NUMBER($rate/$torpedo_prate);
   line("Colonists needed to produce 1 Torpedo each turn",$num);
   
   $num = NUMBER($rate/$ore_prate);
   line("Colonists needed to produce 1 Ore each turn",$num);
   
   $num = NUMBER($rate/$organics_prate);
   line("Colonists needed to produce 1 Organics each turn",$num);
   
   $num = NUMBER($rate/$goods_prate);
   line("Colonists needed to produce 1 Goods each turn",$num);
   
   $num = NUMBER($rate/$energy_prate);
   line("Colonists needed to produce 1 Energy each turn",$num);
   
   	if ($credits_prate==0) {
   		line("Colonists do not make Credits in this game","");
	} else {
   		$num = NUMBER($rate/$credits_prate);
   		line("Colonists needed to produce 1 Credits each turn",$num);
	}
  echo "</TABLE><BR><BR>\n";


$title="Game Scheduler Settings";
bigtitle();
connectdb();
  // Get the real values from the database
  $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_turns' LIMIT 1");
  $schedule_info = $res->fields;

  $line_color = $color_line1;

  echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>";
   line("Ticks happen every",$sched_ticks ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_turns.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Turns will happen every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_defenses.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Defenses will be checked every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_furangee.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Furangees will play every",$schedule_info[ticks_full] ." minutes&nbsp;");  
   
   if($allow_ibank) {
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_IGB.php' LIMIT 1");
   $schedule_info = $res->fields;
     line("Interest on IGB accounts will be calculated every&nbsp;", $schedule_info[ticks_full] ." minutes&nbsp;");
   }
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_news.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("News will be generated every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_planets.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Planets will generate production every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_ports.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Ports will regenerate every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_tow.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Ships will be towed from fed sectors every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_ranking.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Rankings will be generated every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_degrade.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Sector Defences will degrade every",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_apocalypse.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("The planetary apocalypse will occur every&nbsp;",$schedule_info[ticks_full] ." minutes&nbsp;");
   $res=$db->Execute("SELECT ticks_full FROM $dbtables[scheduler] WHERE file = 'sched_mooring.php' LIMIT 1");
   $schedule_info = $res->fields;
   line("Mooring fees will be charged every&nbsp;",$schedule_info[ticks_full] ." minutes&nbsp;");
  echo "</TABLE>";


echo "<BR><BR>";

if(empty($username))
{
  TEXT_GOTOLOGIN();
}
else
{
  TEXT_GOTOMAIN();
}

include("footer.php");

?>
