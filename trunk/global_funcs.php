<?
// Separate userpass into username & password to support the legacy of multiple cookies for login.
if (preg_match("/global_funcs.php/i", $PHP_SELF)) {
      echo "You can not access this file directly!";
      die();
}

/*
if ($userpass != '' and $userpass != '+') {
  $username = substr($userpass, 0, strpos($userpass, "+"));
  $password = substr($userpass, strpos($userpass, "+")+1);

}

// Ensure lang is set
$found = 0;
if(!empty($lang))
{
  if(!preg_match("/^[\w]+$/", $lang)) 
  {
     $lang = $default_lang;

  }
  foreach($avail_lang as $key => $value)
  {
    if($lang == $value[file])
    {
      //SetCookie("lang",$lang,time()+(3600*24),$gamepath,$gamedomain);
      $found = 1;
      break;
    }
  }

  if($found == 0)
    $lang = $default_lang;
*/

if (!isset($lang) || empty($lang))
  $lang = $default_lang;
$lang = $lang . ".inc";
//Log constants

define(LOG_LOGIN, 1);
define(LOG_LOGOUT, 2);
define(LOG_ATTACK_OUTMAN, 3);           //sent to target when better engines
define(LOG_ATTACK_OUTSCAN, 4);          //sent to target when better cloak
define(LOG_ATTACK_EWD, 5);              //sent to target when EWD engaged
define(LOG_ATTACK_EWDFAIL, 6);          //sent to target when EWD failed
define(LOG_ATTACK_LOSE, 7);             //sent to target when he lost
define(LOG_ATTACKED_WIN, 8);            //sent to target when he won
define(LOG_TOLL_PAID, 9);               //sent when paid a toll
define(LOG_HIT_MINES, 10);              //sent when hit mines
define(LOG_SHIP_DESTROYED_MINES, 11);   //sent when destroyed by mines
define(LOG_PLANET_DEFEATED_D, 12);      //sent when one of your defeated planets is destroyed instead of captured
define(LOG_PLANET_DEFEATED, 13);        //sent when a planet is defeated
define(LOG_PLANET_NOT_DEFEATED, 14);    //sent when a planet survives
define(LOG_RAW, 15);                    //this log is sent as-is
define(LOG_TOLL_RECV, 16);              //sent when you receive toll money
define(LOG_DEFS_DESTROYED, 17);         //sent for destroyed sector defenses
define(LOG_PLANET_EJECT, 18);           //sent when ejected from a planet due to alliance switch
define(LOG_BADLOGIN, 19);               //sent when bad login
define(LOG_PLANET_SCAN, 20);            //sent when a planet has been scanned
define(LOG_PLANET_SCAN_FAIL, 21);       //sent when a planet scan failed
define(LOG_PLANET_CAPTURE, 22);         //sent when a planet is captured
define(LOG_SHIP_SCAN, 23);              //sent when a ship is scanned
define(LOG_SHIP_SCAN_FAIL, 24);         //sent when a ship scan fails
define(LOG_FURANGEE_ATTACK, 25);        //furangees send this to themselves
define(LOG_STARVATION, 26);             //sent when colonists are starving... Is this actually used in the game?
define(LOG_TOW, 27);                    //sent when a player is towed
define(LOG_DEFS_DESTROYED_F, 28);       //sent when a player destroys fighters
define(LOG_DEFS_KABOOM, 29);            //sent when sector fighters destroy you
define(LOG_HARAKIRI, 30);               //sent when self-destructed
define(LOG_TEAM_REJECT, 31);            //sent when player refuses invitation
define(LOG_TEAM_RENAME, 32);            //sent when renaming a team
define(LOG_TEAM_M_RENAME, 33);          //sent to members on team rename
define(LOG_TEAM_KICK, 34);              //sent to booted player
define(LOG_TEAM_CREATE, 35);            //sent when created a team
define(LOG_TEAM_LEAVE, 36);             //sent when leaving a team
define(LOG_TEAM_NEWLEAD, 37);           //sent when leaving a team, appointing a new leader
define(LOG_TEAM_LEAD, 38);              //sent to the new team leader
define(LOG_TEAM_JOIN, 39);              //sent when joining a team
define(LOG_TEAM_NEWMEMBER, 40);         //sent to leader on join
define(LOG_TEAM_INVITE, 41);            //sent to invited player
define(LOG_TEAM_NOT_LEAVE, 42);         //sent to leader on leave
define(LOG_ADMIN_HARAKIRI, 43);         //sent to admin on self-destruct
define(LOG_ADMIN_PLANETDEL, 44);        //sent to admin on planet destruction instead of capture
define(LOG_DEFENCE_DEGRADE, 45);        //sent sector fighters have no supporting planet
define(LOG_PLANET_CAPTURED, 46);            //sent to player when he captures a planet
define(LOG_BOUNTY_CLAIMED,47);            //sent to player when they claim a bounty
define(LOG_BOUNTY_PAID,48);            //sent to player when their bounty on someone is paid
define(LOG_BOUNTY_CANCELLED,49);            //sent to player when their bounty is refunded
define(LOG_SPACE_PLAGUE,50);            // sent when space plague attacks a planet
define(LOG_PLASMA_STORM,51);           // sent when a plasma storm attacks a planet
define(LOG_BOUNTY_FEDBOUNTY,52);       // Sent when the federation places a bounty on a player
define(LOG_PLANET_BOMBED,53);     //Sent after bombing a planet
define(LOG_ADMIN_ILLEGVALUE, 54);        //sent to admin on planet destruction instead of capture
define(LOG_SPECIAL_TRADE, 55);		//Used to log a special furangee trade
define(LOG_PLANET_SURVIVED, 56);		//Used to log when a planet is attacked, survives and so does the attacker
define(LOG_FURANGEE_TRADE, 57);		//send when a furangee trades

// Database tables variables
$dbtables['ibank_accounts'] = "${db_prefix}ibank_accounts";
$dbtables['links'] = "${db_prefix}links";
$dbtables['planets'] = "${db_prefix}planets";
$dbtables['traderoutes'] = "${db_prefix}traderoutes";
$dbtables['news'] = "${db_prefix}news";
$dbtables['ships'] = "${db_prefix}ships";
$dbtables['teams'] = "${db_prefix}teams";
$dbtables['universe'] = "${db_prefix}universe";
$dbtables['zones'] = "${db_prefix}zones";
$dbtables['messages'] = "${db_prefix}messages";
$dbtables['furangee'] = "${db_prefix}furangee";
$dbtables['sector_defence'] = "${db_prefix}sector_defence";
$dbtables['scheduler'] = "${db_prefix}scheduler";
$dbtables['ip_bans'] = "${db_prefix}ip_bans";
$dbtables['IGB_transfers'] = "${db_prefix}IGB_transfers";
$dbtables['logs'] = "${db_prefix}logs";
$dbtables['gen_id'] = "${db_prefix}gen_id";
$dbtables['bounty'] = "${db_prefix}bounty";
$dbtables['movement_log'] = "${db_prefix}movement_log";
$dbtables['config'] = "${db_prefix}config";
$dbtables['profile'] = "${db_prefix}profile";
$dbtables['players'] = "${db_prefix}players";
$dbtables['ship_types'] = "${db_prefix}ship_types";
$dbtables['mstatus'] = "${db_prefix}mstatus";
$dbtables['missions'] = "${db_prefix}missions";
$dbtables['kills'] = "${db_prefix}kills";
$dbtables['medals'] = "${db_prefix}medals";
$dbtables['scan_log'] = "${db_prefix}scan_log";
$dbtables['browser'] = "${db_prefix}browser";
$dbtables['ibank_statement'] = "${db_prefix}ibank_statement";

// Tech Levels
$techLv = array ("Low","Average","Above Average","High","Very High","Stellar","Top Secret");

function mypw($one,$two)
{
   return pow($one*1,$two*1);
}

function bigtitle()
{
  global $title;
  echo "<H1>$title</H1>\n";
}

function TEXT_GOTOMAIN()
{
  global $l_global_mmenu;
  echo $l_global_mmenu;
}

function TEXT_GOTOLOGIN()
{
global $l_global_mlogin;
  echo $l_global_mlogin;
}

function TEXT_JAVASCRIPT_BEGIN()
{
  echo "\n<SCRIPT LANGUAGE=\"JavaScript\">\n";
  echo "<!--\n";
}

function TEXT_JAVASCRIPT_END()
{
  echo "\n// -->\n";
  echo "</SCRIPT>\n";
}

function checklogin()
{
  $flag = 0;

  global $username, $l_global_needlogin, $l_global_died;
  global $password, $l_login_died, $l_die_please;
  global $db, $dbtables;
  global $link_forums, $l_forums;
  global $start_armour,$start_fighters,$start_energy;

  $result1 = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username' LIMIT 1");
  $playerinfo = $result1->fields;
  $res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship] LIMIT 1");
  $shipinfo = $res->fields;
	//echo "Username = $username Password = $password";
  /* Check the cookie to see if username/password are empty - check password against database */
  if($username == "" or $password == "" or strtolower($password) != strtolower($playerinfo['password']))
  {
    echo $l_global_needlogin;
    echo "<br>If you are having problems logging in please <a href=logout.php>logout here</a> and try logging in again.<br>If problems persist please email <a href=\"mailto:support@berigames.com?subject=Login Problem\">support@berigames.com</a><br>";
    $flag = 1;
  }

  /* Check for destroyed ship */
  if($shipinfo[ship_destroyed] == "Y")
  {
  	// Release any ships being towed if they were not already
    if ($shipinfo[tow] > 0) {
		$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$shipinfo[sector] WHERE ship_id=$shipinfo[tow]");
  	}

    /* if the player has an escapepod, set the player up with a new ship */
    if($shipinfo[dev_escapepod] == "Y")
    {
      $result2 = $db->Execute("UPDATE $dbtables[players] SET sector=0, on_planet='N' where email='$username'");
	  $result2 = $db->Execute("UPDATE $dbtables[ships] SET type=1, hull=0, engines=0, power=0, computer=0,sensors=0, beams=0, torp_launchers=0, torps=0, armour=0, armour_pts=$start_armour, cloak=0, shields=0, sector=0, ship_ore=0, ship_organics=0, ship_energy=$start_energy, ship_colonists=0, ship_goods=0, ship_fighters=$start_fighters, tow=0, on_planet='N', dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, ship_destroyed='N',dev_lssd='N' WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
      echo "<h2>$l_login_died</h2>";
	  echo "<br><image src=images/pictorial/pod.jpg>";
      $flag = 1;
    }
    else
    {
      /* if the player doesn't have an escapepod */
      echo $l_global_died;
		echo "<br>";
      echo $l_die_please;
      $flag = 1;
    }
  }
  //global $server_closed;
  $result = $db->Execute("SELECT * FROM $dbtables[config] LIMIT 1");
  $row = $result->fields;
  //global $l_login_closed_message;
  if($row[server_closed] == 'Y' && $flag==0 && $playerinfo[player_id] !=1)
  {
    //echo $l_login_closed_message;
	echo $row[closed_message];
	echo "<br><br>";
	if(!empty($link_forums))
  		echo "<A HREF=\"$link_forums\" TARGET=\"_blank\">$l_forums</A> - ";
    $flag=1;
  }
  // Check to see if they have subscribed or not and if not do not allow PC access
  global $force_subscription;
  if ($row[force_subscription] == 'Y') {
	  global $isHiptop;
	  if(!$isHiptop && ($playerinfo['subscribed'] == null || !strpos($playerinfo['subscribed'],"payment"))) {
		$flag=2;
		} 
  }
  return $flag;
}

function connectdb()
{
  /* connect to database - and if we can't stop right there */
  global $dbhost;
  global $dbport;
  global $dbuname;
  global $dbpass;
  global $dbname;
  global $default_lang;
  global $lang;
  global $gameroot;
  global $db_type;
  global $db_persistent;
  global $db;
  global $ADODB_FETCH_MODE;

  $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

  if(!empty($dbport))
    $dbhost.= ":$dbport";

  $db = ADONewConnection("$db_type");
  if($db_persistent == 1)
    $result = $db->PConnect("$dbhost", "$dbuname", "$dbpass", "$dbname");
  else
    $result = $db->Connect("$dbhost", "$dbuname", "$dbpass", "$dbname");

  if(!$result)
    die ("Unable to connect to the database");
}

function updatecookie()
{
  // refresh the cookie with username/password/id/res - times out after 60 mins, and player must login again.
  global $gamepath;
  global $gamedomain;
  //global $userpass;
  global $username;
  global $password;
  // The new combined cookie login.
  //$userpass = $username."+".$password;
  //SetCookie("userpass",$userpass,time()+(3600*24),$gamepath,$gamedomain);
  //if ($userpass != '' and $userpass != '+') {
  //setcookie("username",$username,time()+(3600*1000));
  //setcookie("password",$password,time()+(3600*1000));
}


function playerlog($sid, $log_type, $data = "")
{
  global $db, $dbtables, $playerinfo; $shipinfo;
  global $SERVER_NAME;
  $furangee = "N";
  $res = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id=$sid AND email LIKE '%furangee'");
  if (!$res->EOF) {
  	$furangee = "Y";
  }
  /* write log_entry to the player's log - identified by player's player_id - sid. */
  if ($sid != "" && !empty($log_type))
  {
    $db->Execute("INSERT INTO $dbtables[logs] (log_id,player_id,type,time,data,furangee) VALUES('', $sid, $log_type, NOW(), '$data','$furangee')");
	//echo "<hr>SID = $sid and playerinfo sid = ".$playerinfo[player_id];
	if ($sid != $playerinfo[player_id])
	{
		$res=$db->Execute("SELECT email,alerts,alert2 FROM $dbtables[players] WHERE player_id='$sid' LIMIT 1");
		$alert = $res->fields[alerts];
		$alert2 = $res->fields[alert2];
		if (!$db->EOF && $alert == 'Y' && $log_type != LOG_DEFENCE_DEGRADE && ($log_type != LOG_FURANGEE_TRADE || $alert2 == 'Y')) {
			$email = $res->fields[email];
			$log[type] = $log_type;
			$log[data] = $data;
			$event = log_parse($log);
			//echo "<hr>Reported to $email the following ".$event[title]."<hr>".$event[text]."<hr>";
			
			mail($email,"SKT LE1:".$event[title],$event[text]."\r\n\r\nEmail alerts can be switched off in the Options section of the game.\r\n\r\nhttp://$SERVER_NAME/skt/login.php","From: \"Starkick Trader LE  Alert\"<skt@mpgames.com>\r\nX-Mailer: PHP/" . phpversion());
		}
	}
  }
}


function adminlog($log_type, $data = "")
{
  global $db, $dbtables;
  /* write log_entry to the admin log  */
  if (!empty($log_type))
  {
    $db->Execute("INSERT INTO $dbtables[logs] VALUES('', 0, $log_type, NOW(), '$data')");
  }
}

function gen_score($sid)
{
  global $ore_price;
  global $organics_price;
  global $goods_price;
  global $energy_price;
  global $upgrade_cost;
  global $upgrade_factor;
  global $dev_genesis_price;
  global $dev_beacon_price;
  global $dev_emerwarp_price;
  global $dev_warpedit_price;
  global $dev_minedeflector_price;
  global $dev_escapepod_price;
  global $dev_fuelscoop_price;
  global $dev_lssd_price;
  global $fighter_price;
  global $torpedo_price;
  global $armour_price;
  global $colonist_price;
  global $base_ore;
  global $base_goods;
  global $base_organics;
  global $base_credits;
  global $db, $dbtables;

  $calc_hull = "ROUND(pow($upgrade_factor,hull))";
  $calc_engines = "ROUND(pow($upgrade_factor,engines))";
  $calc_power = "ROUND(pow($upgrade_factor,power))";
  $calc_computer = "ROUND(pow($upgrade_factor,computer))";
  $calc_sensors = "ROUND(pow($upgrade_factor,sensors))";
  $calc_beams = "ROUND(pow($upgrade_factor,beams))";
  $calc_torp_launchers = "ROUND(pow($upgrade_factor,torp_launchers))";
  $calc_shields = "ROUND(pow($upgrade_factor,shields))";
  $calc_armour = "ROUND(pow($upgrade_factor,armour))";
  $calc_cloak = "ROUND(pow($upgrade_factor,cloak))";
  $calc_levels = "($calc_hull+$calc_engines+$calc_power+$calc_computer+$calc_sensors+$calc_beams+$calc_torp_launchers+$calc_shields+$calc_armour+$calc_cloak)*$upgrade_cost";

  $calc_torps = "torps*$torpedo_price";
  $calc_armour_pts = "armour_pts*$armour_price";
  $calc_ship_ore = "ship_ore*$ore_price";
  $calc_ship_organics = "ship_organics*$organics_price";
  $calc_ship_goods = "ship_goods*$goods_price";
  $calc_ship_energy = "ship_energy*$energy_price";
  $calc_ship_colonists = "ship_colonists*$colonist_price";
  $calc_ship_fighters = "ship_fighters*$fighter_price";
  $calc_equip = "$calc_torps+$calc_armour_pts+$calc_ship_ore+$calc_ship_organics+$calc_ship_goods+$calc_ship_energy+$calc_ship_colonists+$calc_ship_fighters";

  $calc_dev_warpedit = "dev_warpedit*$dev_warpedit_price";
  $calc_dev_genesis = "dev_genesis*$dev_genesis_price";
  $calc_dev_beacon = "dev_beacon*$dev_beacon_price";
  $calc_dev_emerwarp = "dev_emerwarp*$dev_emerwarp_price";
  $calc_dev_escapepod = "IF(dev_escapepod='Y', $dev_escapepod_price, 0)";
  $calc_dev_fuelscoop = "IF(dev_fuelscoop='Y', $dev_fuelscoop_price, 0)";
  $calc_dev_lssd = "IF(dev_lssd='Y', $dev_lssd_price, 0)";
  $calc_dev_minedeflector = "dev_minedeflector*$dev_minedeflector_price";
  $calc_dev = "$calc_dev_warpedit+$calc_dev_genesis+$calc_dev_beacon+$calc_dev_emerwarp+$calc_dev_escapepod+$calc_dev_fuelscoop+$calc_dev_minedeflector+$calc_dev_lssd";
  $calc_planet_goods = "SUM($dbtables[planets].organics)+SUM($dbtables[planets].ore)+SUM($dbtables[planets].goods)+SUM($dbtables[planets].energy)";
   //$calc_planet_goods = "SUM($dbtables[planets].organics)*$organics_price+SUM($dbtables[planets].ore)*$ore_price+SUM($dbtables[planets].goods)*$goods_price+SUM($dbtables[planets].energy)*$energy_price";
  $calc_planet_colonists = "SUM($dbtables[planets].colonists)*$colonist_price";
$calc_planet_defence = "SUM($dbtables[planets].fighters)*$fighter_price+SUM(IF($dbtables[planets].base='Y', $base_credits+$base_ore*$ore_price+$base_organics*$organics_price+$base_goods*$goods_price, 0))+SUM($dbtables[planets].torps)*$torpedo_price";
  $calc_planet_credits = "SUM($dbtables[planets].credits)";
  $res = $db->Execute("SELECT $dbtables[players].credits+$calc_planet_goods+$calc_planet_colonists+$calc_planet_defence+$calc_planet_credits AS score FROM $dbtables[players] LEFT JOIN $dbtables[planets] ON $dbtables[planets].owner=player_id WHERE player_id=$sid");
  $row = $res->fields;
  $score = $row[score];
  // New ship code - neatly calcs for every ship owned! Furangee tech is half price
  $res = $db->Execute("SELECT SUM($calc_levels+$calc_equip+$calc_dev) AS score FROM $dbtables[ships] WHERE player_id=$sid AND ship_destroyed='N' AND fur_tech='Y'");
  $row = $res->fields;
  $score += ($row[score]);
  $res = $db->Execute("SELECT SUM($calc_levels+$calc_equip+$calc_dev) AS score FROM $dbtables[ships] WHERE player_id=$sid AND ship_destroyed='N' AND fur_tech='N'");
  $row = $res->fields;
  $score += $row[score];  
  
  $res = $db->Execute("SELECT balance, loan FROM $dbtables[ibank_accounts] where player_id = $sid");
  if($res)
  {
     $row = $res->fields;
     $score += ($row[balance] - $row[loan]);
  }
  if ($score < 1000) {
	$score = 1000;
  }
  $score = ROUND(SQRT($score));
  $db->Execute("UPDATE $dbtables[players] SET score=$score WHERE player_id=$sid");

  return $score;
}

function value_ship($sid)
{
  global $ore_price;
  global $organics_price;
  global $goods_price;
  global $energy_price;
  global $upgrade_cost;
  global $upgrade_factor;
  global $dev_genesis_price;
  global $dev_beacon_price;
  global $dev_emerwarp_price;
  global $dev_warpedit_price;
  global $dev_minedeflector_price;
  global $dev_escapepod_price;
  global $dev_fuelscoop_price;
  global $dev_lssd_price;
  global $fighter_price;
  global $torpedo_price;
  global $armour_price;
  global $colonist_price;
  global $db, $dbtables;

  $calc_hull = "ROUND(pow($upgrade_factor,hull))";
  $calc_engines = "ROUND(pow($upgrade_factor,engines))";
  $calc_power = "ROUND(pow($upgrade_factor,power))";
  $calc_computer = "ROUND(pow($upgrade_factor,computer))";
  $calc_sensors = "ROUND(pow($upgrade_factor,sensors))";
  $calc_beams = "ROUND(pow($upgrade_factor,beams))";
  $calc_torp_launchers = "ROUND(pow($upgrade_factor,torp_launchers))";
  $calc_shields = "ROUND(pow($upgrade_factor,shields))";
  $calc_armour = "ROUND(pow($upgrade_factor,armour))";
  $calc_cloak = "ROUND(pow($upgrade_factor,cloak))";
  $calc_levels = "($calc_hull+$calc_engines+$calc_power+$calc_computer+$calc_sensors+$calc_beams+$calc_torp_launchers+$calc_shields+$calc_armour+$calc_cloak)*$upgrade_cost";
  $calc_minhull = "ROUND(pow($upgrade_factor,minhull))";
  $calc_minengines = "ROUND(pow($upgrade_factor,minengines))";
  $calc_minpower = "ROUND(pow($upgrade_factor,minpower))";
  $calc_mincomputer = "ROUND(pow($upgrade_factor,mincomputer))";
  $calc_minsensors = "ROUND(pow($upgrade_factor,minsensors))";
  $calc_minbeams = "ROUND(pow($upgrade_factor,minbeams))";
  $calc_mintorp_launchers = "ROUND(pow($upgrade_factor,mintorp_launchers))";
  $calc_minshields = "ROUND(pow($upgrade_factor,minshields))";
  $calc_minarmour = "ROUND(pow($upgrade_factor,minarmour))";
  $calc_mincloak = "ROUND(pow($upgrade_factor,mincloak))";
  $calc_minlevels = "($calc_minhull+$calc_minengines+$calc_minpower+$calc_mincomputer+$calc_minsensors+$calc_minbeams+$calc_mintorp_launchers+$calc_minshields+$calc_minarmour+$calc_mincloak)*$upgrade_cost";

  $calc_torps = "torps*$torpedo_price";
  $calc_armour_pts = "armour_pts*$armour_price";
  $calc_ship_ore = "ship_ore*$ore_price";
  $calc_ship_organics = "ship_organics*$organics_price";
  $calc_ship_goods = "ship_goods*$goods_price";
  $calc_ship_energy = "ship_energy*$energy_price";
  $calc_ship_colonists = "ship_colonists*$colonist_price";
  $calc_ship_fighters = "ship_fighters*$fighter_price";
  $calc_equip = "$calc_torps+$calc_armour_pts+$calc_ship_ore+$calc_ship_organics+$calc_ship_goods+$calc_ship_energy+$calc_ship_colonists+$calc_ship_fighters";

  $calc_dev_warpedit = "dev_warpedit*$dev_warpedit_price";
  $calc_dev_genesis = "dev_genesis*$dev_genesis_price";
  $calc_dev_beacon = "dev_beacon*$dev_beacon_price";
  $calc_dev_emerwarp = "dev_emerwarp*$dev_emerwarp_price";
  $calc_dev_escapepod = "IF(dev_escapepod='Y', $dev_escapepod_price, 0)";
  $calc_dev_fuelscoop = "IF(dev_fuelscoop='Y', $dev_fuelscoop_price, 0)";
  $calc_dev_lssd = "IF(dev_lssd='Y', $dev_lssd_price, 0)";
  $calc_dev_minedeflector = "dev_minedeflector*$dev_minedeflector_price";
  $calc_dev = "$calc_dev_warpedit+$calc_dev_genesis+$calc_dev_beacon+$calc_dev_emerwarp+$calc_dev_escapepod+$calc_dev_fuelscoop+$calc_dev_minedeflector+$calc_dev_lssd";
  $res = $db->Execute("SELECT SUM($calc_levels -$calc_minlevels +$calc_equip +$calc_dev +cost_credits) AS score, fur_tech FROM $dbtables[ships],$dbtables[ship_types] WHERE ship_id=$sid AND ship_destroyed='N' AND type=type_id GROUP BY fur_tech");
  $row = $res->fields;
  if ($row[fur_tech]=="Y") {
  	$row[score] = $row[score]/2;
  }
  return $row[score]; 
  
}


function db_kill_player($player_id,$ship_id,$killer_id)
{ 
  global $default_prod_ore;
  global $default_prod_organics;
  global $default_prod_goods;
  global $default_prod_energy;
  global $default_prod_fighters;
  global $default_prod_torp;
  global $gameroot;
  global $db,$dbtables;
  global $l_killheadline,$l_news_killed;
  global $ibank_interest, $ibank_loaninterest;
  global $ore_price;
  global $organics_price;
  global $goods_price;
  global $energy_price;
  global $upgrade_cost;
  global $upgrade_factor;
  global $dev_genesis_price;
  global $dev_beacon_price;
  global $dev_emerwarp_price;
  global $dev_warpedit_price;
  global $dev_minedeflector_price;
  global $dev_escapepod_price;
  global $dev_fuelscoop_price;
  global $dev_lssd_price;
  global $fighter_price;
  global $torpedo_price;
  global $armour_price;
  global $colonist_price;
  global $base_credits;
  global $base_ore;
  global $base_goods;
  global $base_organics;
  global $sector_max;
    
  $db->Execute("UPDATE $dbtables[players] SET sector=0, on_planet='N' WHERE player_id=$player_id");
  $res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE ship_id=$ship_id");
  $shipinfo = $res->fields;
  if ($shipinfo[tow] > 0) {
  	if ($killer_id==-2) {
		$sector = rand(1,$sector_max);
		$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$sector WHERE ship_id=$shipinfo[tow]");
	} else {
		$db->Execute("UPDATE $dbtables[ships] SET player_id=0,on_planet='N',sector=$shipinfo[sector] WHERE ship_id=$shipinfo[tow]");
	}
  }
  $db->Execute("UPDATE $dbtables[ships] SET type=1, hull=0, engines=0, power=0, computer=0,sensors=0, beams=0, torp_launchers=0, torps=0, armour=0, armour_pts='$start_armour', cloak=0, shields=0, sector=0, ship_ore=0, ship_organics=0, ship_energy='$start_energy', ship_colonists=0, ship_goods=0, ship_fighters='$start_fighters', tow=0, on_planet='N', dev_warpedit=0, dev_genesis=0, dev_beacon=0, dev_emerwarp=0, dev_escapepod='N', dev_fuelscoop='N', dev_minedeflector=0, ship_destroyed='Y',dev_lssd='N',cleared_defences=' ',fur_tech='N' where ship_id=$ship_id AND player_id=$player_id");

  $db->Execute("DELETE from $dbtables[bounty] WHERE placed_by = $player_id");
  // Now the IGB gets to recover their loan if there is one
  if ($killer_id != -1) {
	$db->Execute("UPDATE $dbtables[ibank_accounts] SET loantime=0 WHERE player_id=$player_id");
	if ($killer_id != -2) {
		$playerDeath = true;
	} else {
		// Suicide
		$db->Execute("UPDATE $dbtables[players] SET turns=turns-1500, turns_used=turns_used+1500 WHERE player_id=$player_id");
	}
	include("sched_IGB.php");
	$playerDeath=false;
  }
  $res = $db->Execute("SELECT DISTINCT sector_id FROM $dbtables[planets] WHERE owner='$player_id' AND base='Y'");
  $i=0;

  while(!$res->EOF && $res)
  {
    $sectors[$i] = $res->fields[sector_id];
    $i++;
    $res->MoveNext();
  }

  $db->Execute("UPDATE $dbtables[planets] SET owner=0,fighters=0, base='N' WHERE owner=$player_id");

  if(!empty($sectors))
  {
    foreach($sectors as $sector)
    {
      calc_ownership($sector);
    }
  }
  $db->Execute("DELETE FROM $dbtables[sector_defence] where player_id=$player_id");

  $res = $db->Execute("SELECT zone_id FROM $dbtables[zones] WHERE corp_zone='N' AND owner=$player_id");
  $zone = $res->fields;

$db->Execute("UPDATE $dbtables[universe] SET zone_id=1 WHERE zone_id=$zone[zone_id]");



$query = $db->Execute("select character_name,email from $dbtables[players] where player_id='$player_id'");
$name = $query->fields;
// Update the killers stats
// Do we have an entry for this player already?
$res=$db->Execute("SELECT * FROM $dbtables[kills] WHERE player_id=$killer_id");
if ($res->EOF) {
	$res=$db->Execute("INSERT INTO $dbtables[kills] SET player_id=$killer_id");
}
$res=$db->Execute("SELECT * FROM $dbtables[kills] WHERE player_id=$playerinf[player_id]");
if ($res->EOF) {
	$res=$db->Execute("INSERT INTO $dbtables[kills] SET player_id=$playerinf[player_id]");
}
// Find out if furangee
if (strstr($name[email],"furangee")) {
	$db->Execute("UPDATE $dbtables[kills] SET fks=fks+1 WHERE player_id=$killer_id");
} else {
	// Player killed
	$db->Execute("UPDATE $dbtables[kills] SET pks=pks+1 WHERE player_id=$killer_id");
}
// Update killed stats
$db->Execute("UPDATE $dbtables[kills] SET deaths=deaths+1 WHERE player_id=$player_id");

$headline = $name[character_name] . $l_killheadline;


$newstext=str_replace("[name]",$name[character_name],$l_news_killed);

$news = $db->Execute("INSERT INTO $dbtables[news] (headline, newstext, user_id, date, news_type) VALUES ('$headline','$newstext','$player_id',NOW(), 'killed')");

// Clear bank BUG!!!
$db->Execute("UPDATE $dbtables[ibank_accounts] SET balance='0' WHERE player_id=$player_id") ;
// Final clean up
$db->Execute("UPDATE $dbtables[players] SET credits=0 WHERE player_id=$player_id");
}

function NUMBER($number, $decimals = 0)
{
  global $local_number_dec_point;
  global $local_number_thousands_sep;
  return str_replace(",",", ",number_format($number, $decimals, $local_number_dec_point, $local_number_thousands_sep));  
}

function NUM_HOLDS($level_hull)
{
  global $level_factor;
  return round(mypw($level_factor, $level_hull) * 100);
}

function NUM_ENERGY($level_power)
{
  global $level_factor;
  return round(mypw($level_factor, $level_power) * 500);
}

function NUM_FIGHTERS($level_computer)
{
  global $level_factor;
  return round(mypw($level_factor, $level_computer) * 100);
}

function NUM_TORPEDOES($level_torp_launchers)
{
  global $level_factor;
  return round(mypw($level_factor, $level_torp_launchers) * 100);
}

function NUM_ARMOUR($level_armour)
{
  global $level_factor;
  return round(mypw($level_factor, $level_armour) * 100);
}

function NUM_BEAMS($level_beams)
{
  global $level_factor;
  return round(mypw($level_factor, $level_beams) * 100);
}

function NUM_SHIELDS($level_shields)
{
  global $level_factor;
  return round(mypw($level_factor, $level_shields) * 100);
}

function SCAN_SUCCESS($level_scan, $level_cloak)
{
  return (5 + $level_scan - $level_cloak) * 5;
}

function SCAN_ERROR($level_scan, $level_cloak)
{
  global $scan_error_factor;

  $sc_error = (4 + $level_scan / 2 - $level_cloak / 2) * $scan_error_factor;

  if($sc_error<1)
  {
    $sc_error=1;
  }
  if($sc_error>99)
  {
    $sc_error=99;
  }

  return $sc_error;
}

function explode_mines($sector, $num_mines)
{
    global $db, $dbtables;

    $result3 = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$sector' and defence_type ='M' order by quantity ASC");
    echo $db->ErrorMsg();
    //Put the defence information into the array "defenceinfo"
    if($result3 > 0)
    {
       while(!$result3->EOF && $num_mines > 0)
       {
          $row = $result3->fields;
          if($row[quantity] > $num_mines)
          {
             $update = $db->Execute("UPDATE $dbtables[sector_defence] set quantity=quantity - $num_mines where defence_id = $row[defence_id]");
             $num_mines = 0;
          }
          else
          {
             $update = $db->Execute("DELETE FROM $dbtables[sector_defence] WHERE defence_id = $row[defence_id]");
             $num_mines -= $row[quantity];
          }
          $result3->MoveNext();
       }
    }

}

function destroy_fighters($sector, $num_fighters)
{
    global $db, $dbtables;

    $result3 = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$sector' and defence_type ='F' order by quantity ASC");
    echo $db->ErrorMsg();
    //Put the defence information into the array "defenceinfo"
    if($result3 > 0)
    {
       while(!$result3->EOF && $num_fighters > 0)
       {
          $row=$result3->fields;
          if($row[quantity] > $num_fighters)
          {
             $update = $db->Execute("UPDATE $dbtables[sector_defence] set quantity=quantity - $num_fighters where defence_id = $row[defence_id]");
             $num_fighters = 0;
          }
          else
          {
             $update = $db->Execute("DELETE FROM $dbtables[sector_defence] WHERE defence_id = $row[defence_id]");
             $num_fighters -= $row[quantity];
          }
          $result3->MoveNext();
       }
    }

}

function message_defence_owner($sector, $message)
{
    global $db, $dbtables;
    $result3 = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$sector' ");
    echo $db->ErrorMsg();
    //Put the defence information into the array "defenceinfo"
	$anOwner = 0;
    if($result3 > 0)
    {
       while(!$result3->EOF)
       {
		  $anOwner = $result3->fields[player_id];
          playerlog($anOwner,LOG_RAW, $message);
          $result3->MoveNext();
       }
    }
	return $anOwner;
}

function distribute_toll($sector, $toll, $total_fighters)
{
    global $db, $dbtables;

    $result3 = $db->Execute ("SELECT * FROM $dbtables[sector_defence] WHERE sector_id='$sector' AND defence_type ='F' ");
    echo $db->ErrorMsg();
    //Put the defence information into the array "defenceinfo"
    if($result3 > 0)
    {
       while(!$result3->EOF)
       {
          $row = $result3->fields;
          $toll_amount = ROUND(($row['quantity'] / $total_fighters) * $toll);
          $db->Execute("UPDATE $dbtables[players] set credits=credits + $toll_amount WHERE player_id = $row[player_id]");
          playerlog($row[player_id], LOG_TOLL_RECV, "$toll_amount|$sector");
          $result3->MoveNext();
       }
    }

}

function defence_vs_defence($player_id)
{
   global $db, $dbtables;

   $result1 = $db->Execute("SELECT * from $dbtables[sector_defence] where player_id = $player_id");
   if($result1 > 0)
   {
      while(!$result1->EOF)
      {
         $row=$result1->fields;
         $deftype = $row[defence_type] == 'F' ? 'Fighters' : 'Mines';
         $qty = $row['quantity'];
         $result2 = $db->Execute("SELECT * from $dbtables[sector_defence] where sector_id = $row[sector_id] and player_id <> $player_id ORDER BY quantity DESC");
         if($result2 > 0)
         {
            while(!$result2->EOF && $qty > 0)
            {
               $cur = $result2->fields;
               $targetdeftype = $cur[defence_type] == 'F' ? $l_fighters : $l_mines;
               if($qty > $cur['quantity'])
               {
                  $db->Execute("DELETE FROM $dbtables[sector_defence] WHERE defence_id = $cur[defence_id]");
                  $qty -= $cur['quantity'];
                  $db->Execute("UPDATE $dbtables[sector_defence] SET quantity = $qty where defence_id = $row[defence_id]");
                  playerlog($cur[player_id], LOG_DEFS_DESTROYED, "$cur[quantity]|$targetdeftype|$row[sector_id]");
                  playerlog($row[player_id], LOG_DEFS_DESTROYED, "$cur[quantity]|$deftype|$row[sector_id]");
               }
               else
               {
                  $db->Execute("DELETE FROM $dbtables[sector_defence] WHERE defence_id = $row[defence_id]");
                  $db->Execute("UPDATE $dbtables[sector_defence] SET quantity=quantity - $qty WHERE defence_id = $cur[defence_id]");
                  playerlog($cur[player_id], LOG_DEFS_DESTROYED, "$qty|$targetdeftype|$row[sector_id]");
                  playerlog($row[player_id], LOG_DEFS_DESTROYED, "$qty|$deftype|$row[sector_id]");
                  $qty = 0;
               }
               $result2->MoveNext();
            }
         }
         $result1->MoveNext();
      }
      $db->Execute("DELETE FROM $dbtables[sector_defence] WHERE quantity <= 0");
   }
}

function kick_off_planet($player_id,$whichteam)
{
   global $db, $dbtables;

   $result1 = $db->Execute("SELECT * from $dbtables[planets] where owner = '$player_id' ");

   if($result1 > 0)
   {
      while(!$result1->EOF)
      {
         $row = $result1->fields;
         $result2 = $db->Execute("SELECT * from $dbtables[players] where on_planet = 'Y' and planet_id = '$row[planet_id]' and player_id <> '$player_id' ");
         if($result2 > 0)
         {
            while(!$result2->EOF )
            {
               $cur = $result2->fields;
               $db->Execute("UPDATE $dbtables[players] SET on_planet = 'N',planet_id = '0' WHERE player_id='$cur[player_id]'");
               playerlog($cur[player_id], LOG_PLANET_EJECT, "$cur[sector]|$row[character_name]");
               $result2->MoveNext();
            }
         }
         $result1->MoveNext();
      }
   }
}


function calc_ownership($sector)
{
  global $min_bases_to_own, $l_global_warzone, $l_global_nzone, $l_global_team, $l_global_player;
  global $db, $dbtables;

  $res = $db->Execute("SELECT owner, corp FROM $dbtables[planets] WHERE sector_id=$sector AND base='Y'");
  $num_bases = $res->RecordCount();

  $i=0;
  if($num_bases > 0)
  {

   while(!$res->EOF)
    {
      $bases[$i] = $res->fields;
      $i++;
      $res->MoveNext();
    }
  }
  else
    return "Sector ownership didn't change";

  $owner_num = 0;

  foreach($bases as $curbase)
  {
    $curcorp=-1;
    $curship=-1;
    $loop = 0;
    while ($loop < $owner_num)
    {
      if($curbase[corp] != 0)
      {
        if($owners[$loop][type] == 'C')
        {
          if($owners[$loop][id] == $curbase[corp])
          {
            $curcorp=$loop;
            $owners[$loop][num]++;
          }
        }
      }

      if($owners[$loop][type] == 'S')
      {
        if($owners[$loop][id] == $curbase[owner])
        {
          $curship=$loop;
          $owners[$loop][num]++;
        }
      }

      $loop++;
    }

    if($curcorp == -1)
    {
      if($curbase[corp] != 0)
      {
         $curcorp=$owner_num;
         $owner_num++;
         $owners[$curcorp][type] = 'C';
         $owners[$curcorp][num] = 1;
         $owners[$curcorp][id] = $curbase[corp];
      }
    }

    if($curship == -1)
    {
      if($curbase[owner] != 0)
      {
        $curship=$owner_num;
        $owner_num++;
        $owners[$curship][type] = 'S';
        $owners[$curship][num] = 1;
        $owners[$curship][id] = $curbase[owner];
      }
    }
  }

  // We've got all the contenders with their bases.
  // Time to test for conflict

  $loop=0;
  $nbcorps=0;
  $nbships=0;
  while($loop < $owner_num)
  {
    if($owners[$loop][type] == 'C')
      $nbcorps++;
    else
    {
      $res = $db->Execute("SELECT team FROM $dbtables[players] WHERE player_id=" . $owners[$loop][id]);
      if($res && $res->RecordCount() != 0)
      {
        $curship = $res->fields;
        $ships[$nbships]=$owners[$loop][id];
        $scorps[$nbships]=$curship[team];
        $nbships++;
      }
    }

    $loop++;
  }

  //More than one corp, war
  if($nbcorps > 1)
  {
    $db->Execute("UPDATE $dbtables[universe] SET zone_id=4 WHERE sector_id=$sector");
    return $l_global_warzone;
  }

  //More than one unallied ship, war
  $numunallied = 0;
  foreach($scorps as $corp)
  {
    if($corp == 0)
      $numunallied++;
  }
  if($numunallied > 1)
  {
    $db->Execute("UPDATE $dbtables[universe] SET zone_id=4 WHERE sector_id=$sector");
    return $l_global_warzone;
  }

  //Unallied ship, another corp present, war
  if($numunallied > 0 && $nbcorps > 0)
  {
    $db->Execute("UPDATE $dbtables[universe] SET zone_id=4 WHERE sector_id=$sector");
    return $l_global_warzone;
  }

  //Unallied ship, another ship in a corp, war
  if($numunallied > 0)
  {
    $query = "SELECT team FROM $dbtables[players] WHERE (";
    $i=0;
    foreach($ships as $ship)
    {
      $query = $query . "player_id=$ship";
      $i++;
      if($i!=$nbships)
        $query = $query . " OR ";
      else
        $query = $query . ")";
    }
    $query = $query . " AND team!=0";
    $res = $db->Execute($query);
    if($res->RecordCount() != 0)
    {
      $db->Execute("UPDATE $dbtables[universe] SET zone_id=4 WHERE sector_id=$sector");
      return $l_global_warzone;
    }
  }


  //Ok, all bases are allied at this point. Let's make a winner.
  $winner = 0;
  $i = 1;
  while ($i < $owner_num)
  {
    if($owners[$i][num] > $owners[$winner][num])
      $winner = $i;
    elseif($owners[$i][num] == $owners[$winner][num])
    {
      if($owners[$i][type] == 'C')
        $winner = $i;
    }
    $i++;
  }

  if($owners[$winner][num] < $min_bases_to_own)
  {
    $db->Execute("UPDATE $dbtables[universe] SET zone_id=1 WHERE sector_id=$sector");
    return $l_global_nzone;
  }


  if($owners[$winner][type] == 'C')
  {
    $res = $db->Execute("SELECT zone_id FROM $dbtables[zones] WHERE corp_zone='Y' && owner=" . $owners[$winner][id]);
    $zone = $res->fields;

    $res = $db->Execute("SELECT team_name FROM $dbtables[teams] WHERE id=" . $owners[$winner][id]);
    $corp = $res->fields;

    $db->Execute("UPDATE $dbtables[universe] SET zone_id=$zone[zone_id] WHERE sector_id=$sector");
    return "$l_global_team $corp[team_name]!";
  }
  else
  {
    $onpar = 0;
    foreach($owners as $curowner)
    {
      if($curowner[type] == 'S' && $curowner[id] != $owners[$winner][id] && $curowner[num] == $owners[winners][num])
        $onpar = 1;
        break;
    }

    //Two allies have the same number of bases
    if($onpar == 1)
    {
      $db->Execute("UPDATE $dbtables[universe] SET zone_id=1 WHERE sector_id=$sector");
      return $l_global_nzone;
    }
    else
    {
      $res = $db->Execute("SELECT zone_id FROM $dbtables[zones] WHERE corp_zone='N' && owner=" . $owners[$winner][id]);
      $zone = $res->fields;

      $res = $db->Execute("SELECT character_name FROM $dbtables[players] WHERE player_id=" . $owners[$winner][id]);
      $ship = $res->fields;

      $db->Execute("UPDATE $dbtables[universe] SET zone_id=$zone[zone_id] WHERE sector_id=$sector");
      return "$l_global_player $ship[character_name]!";
    }
  }
}

function player_insignia_name($a_username) {

// Somewhat inefficient, but I think this is the best way to do this.

global $db, $dbtables, $username, $player_insignia;
global $l_insignia;

$res = $db->Execute("SELECT score FROM $dbtables[players] WHERE email='$a_username'");
$playerinfo = $res->fields;  
$score_array = array('1000', '3000', '6000', '9000', '12000', '15000', '20000', '40000', '60000', '80000', '100000', '120000', '160000', '200000', '250000', '300000', '350000', '400000', '450000', '500000', '1000000','2000000','4000000','8000000');

for ( $i=0; $i<count($score_array); $i++)
{           
    if ( $playerinfo[score] < $score_array[$i])
     {
       $player_insignia = $l_insignia[$i];
       break;    
     }
}

if(!isset($player_insignia))
  $player_insignia = end($l_insignia);

return $player_insignia;

}

function t_port($ptype) {

global $l_ore, $l_none, $l_energy, $l_organics, $l_goods, $l_special;

switch ($ptype) {
    case "ore":
        $ret=$l_ore;
        break;
    case "none":
        $ret=$l_none;
        break;
    case "energy":
        $ret=$l_energy;
        break;
    case "organics":
        $ret=$l_organics;
        break;
    case "goods":
        $ret=$l_goods;
        break;
    case "special":
        $ret=$l_special;
        break;


}

return $ret;
}

function GenNextID($id)
{
  global $db, $dbtables;

  $db->Execute("LOCK TABLES $dbtables[gen_id] write");
  $res = $db->Execute("SELECT count FROM $dbtables[gen_id] WHERE name='$id'");
  if(!$res)
    return(-1);
  $count = $res->fields[count];
  $res = $db->Execute("UPDATE $dbtables[gen_id] SET count=count+1 WHERE name='$id'");
  $count++;
  $db->Execute("UNLOCK TABLES");
  return($count);
}

function stripnum($str)
{
  $str=(string)$str;
  $output = ereg_replace("[^0-9.]","",$str);
  return $output;
}
function collect_bounty($attacker,$bounty_on)
{
   global $db,$dbtables,$l_by_thefeds;
   // Find out if there are any bounties on this person
   $res = $db->Execute("SELECT * FROM $dbtables[bounty],$dbtables[players] WHERE bounty_on = $bounty_on AND bounty_on = player_id");
   if($res)
   {
      while(!$res->EOF)
      {
         $bountydetails = $res->fields;
		 // Is this a Federation fine?
         if($res->fields[placed_by] == 0)
         {
			playerlog($attacker, LOG_RAW, "The Federation thanks you greatly for destroying $bountydetails[character_name]/'s ship and pays you triple the salvage amount for the remains.");
		 }
         else
         {
		 	// This is a player to player bounty
            $res2 = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id = $bountydetails[placed_by]");
            $placed = $res2->fields[character_name];
			$update = $db->Execute("UPDATE $dbtables[players] SET credits = credits + $bountydetails[amount] WHERE player_id = $attacker");
			playerlog($attacker, LOG_BOUNTY_CLAIMED, "$bountydetails[amount]|$bountydetails[character_name]|$placed");
            playerlog($bountydetails[placed_by],LOG_BOUNTY_PAID,"$bountydetails[amount]|$bountydetails[character_name]");
			$delete = $db->Execute("DELETE FROM $dbtables[bounty] WHERE bounty_id = $bountydetails[bounty_id]");
         }        
         $res->MoveNext();
      }
   }
}

function cancel_bounty($bounty_on)
{
   global $db,$dbtables;
   $res = $db->Execute("SELECT * FROM $dbtables[bounty],$dbtables[players] WHERE bounty_on = $bounty_on AND bounty_on = player_id");
   if($res)
   {
      while(!$res->EOF)
      {
         $bountydetails = $res->fields;
         if($bountydetails[placed_by] <> 0)
         {
            $update = $db->Execute("UPDATE $dbtables[players] SET credits = credits + $bountydetails[amount] WHERE player_id = $bountydetails[placed_by]");
    
            playerlog($bountydetails[placed_by],LOG_BOUNTY_CANCELLED,"$bountydetails[amount]|$bountydetails[character_name]");
         }
         $delete = $db->Execute("DELETE FROM $dbtables[bounty] WHERE bounty_id = $bountydetails[bounty_id]");
         $res->MoveNext();
      }
   }
}

function get_player($player_id)
{
   global $db,$dbtables;
   $res = $db->Execute("SELECT character_name from $dbtables[players] where player_id = $player_id");
   if($res)
   {
      $row = $res->fields;
      $character_name = $row[character_name];
      return $character_name;
   }
   else
   {
      return "Unknown";
   }
}

function log_move($player_id,$sector_id)
{
   global $db,$dbtables;
   $res = $db->Execute("INSERT INTO $dbtables[movement_log] (player_id,sector_id,time) VALUES ($player_id,$sector_id,NOW())");
}

function isLoanPending($player_id)
{
  global $db, $dbtables;
  global $IGB_lrate;

  $res = $db->Execute("SELECT loan, UNIX_TIMESTAMP(loantime) AS time FROM $dbtables[ibank_accounts] WHERE player_id=$player_id");
  if($res)
  {
    $account=$res->fields;

    if($account[loan] == 0)
      return false;

    $curtime=time();
    $difftime = ($curtime - $account[time]) / 60;
    if($difftime > $IGB_lrate)
      return true;
    else
      return false;
  }
  else
    return false;

}
function log_parse($entry)
{
  global $l_log_title;
  global $l_log_text;
  global $l_log_pod;
  global $l_log_nopod;
  global $space_plague_kills;

  switch($entry[type])
  {
    case LOG_LOGIN: //data args are : [ip]
    case LOG_LOGOUT: 
    case LOG_BADLOGIN:
    case LOG_HARAKIRI:
    $retvalue[text] = str_replace("[ip]", "$entry[data]", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
  
    case LOG_ATTACK_OUTMAN: //data args are : [player]
    case LOG_ATTACK_OUTSCAN: 
    case LOG_ATTACK_EWD:
    case LOG_ATTACK_EWDFAIL:
    case LOG_SHIP_SCAN:
    case LOG_SHIP_SCAN_FAIL:
    case LOG_FURANGEE_ATTACK:
    case LOG_TEAM_NOT_LEAVE:
    $retvalue[text] = str_replace("[player]", "$entry[data]", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ATTACK_LOSE: //data args are : [player] [pod]
    list($name, $pod)= split ("\|", $entry[data]);
        
    $retvalue[text] = str_replace("[player]", "$name", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    if($pod == 'Y')
      $retvalue[text] = $retvalue[text] . $l_log_pod;
    else
      $retvalue[text] = $retvalue[text] . $l_log_nopod;
    break;

    case LOG_ATTACKED_WIN: //data args are : [player] [armor] [fighters]
    list($name, $armor, $fighters)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "$name", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[armor]", "$armor", $retvalue[text]);
    $retvalue[text] = str_replace("[fighters]", "$fighters", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TOLL_PAID: //data args are : [toll] [sector]
    case LOG_TOLL_RECV:
    list($toll, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[toll]", "$toll", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_HIT_MINES: //data args are : [mines] [sector]
    list($mines, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[mines]", "$mines", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_SHIP_DESTROYED_MINES: //data args are : [sector] [pod]
    case LOG_DEFS_KABOOM:
    list($sector, $pod)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    if($pod == 'Y')
      $retvalue[text] = $retvalue[text] . $l_log_pod;
    else
      $retvalue[text] = $retvalue[text] . $l_log_nopod;
    break;

    case LOG_PLANET_DEFEATED_D: //data args are :[planet_name] [sector] [name]
    case LOG_PLANET_DEFEATED:
    case LOG_PLANET_SCAN:
    case LOG_PLANET_SCAN_FAIL:
	case LOG_PLANET_SURVIVED:
    list($planet_name, $sector, $name)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[planet_name]", "$planet_name", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[text] = str_replace("[name]", "$name", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_PLANET_NOT_DEFEATED: //data args are : [planet_name] [sector] [name] [ore] [organics] [goods] [salvage] [credits]
    list($planet_name, $sector, $name, $ore, $organics, $goods, $salvage, $credits)= split ("\|", $entry[data]);
	if ($ore == 0 && $organics ==0 && $goods == 0 && $salvage == 0) {
		$retvalue[text] = "$name attacked your planet $planet_name in sector $sector but the citizens organized a valiant defense and managed to destroy the opponent. Unfortunately, they were not able to salvage anything from the wreckage.";
	} else {
		$retvalue[text] = str_replace("[planet_name]", "$planet_name", $l_log_text[$entry[type]]);
		$retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
		$retvalue[text] = str_replace("[name]", "$name", $retvalue[text]);
		$retvalue[text] = str_replace("[ore]", "$ore", $retvalue[text]);
		$retvalue[text] = str_replace("[goods]", "$goods", $retvalue[text]);
		$retvalue[text] = str_replace("[organics]", "$organics", $retvalue[text]);
	    $retvalue[text] = str_replace("[salvage]", "$salvage", $retvalue[text]);
    	$retvalue[text] = str_replace("[credits]", "$credits", $retvalue[text]);
	}
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
	
    case LOG_FURANGEE_TRADE: // Furangee trades on a planet
    case LOG_RAW: //data is stored as a message
    $retvalue[title] = $l_log_title[$entry[type]];
    $retvalue[text] = $entry[data];    
    break;

    case LOG_DEFS_DESTROYED: //data args are : [quantity] [type] [sector]
    list($quantity, $type, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[quantity]", "$quantity", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[type]", "$type", $retvalue[text]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
  
    case LOG_PLANET_EJECT: //data args are : [sector] [player]
    list($sector, $name)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[name]", "$name", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
    
    case LOG_STARVATION: //data args are : [sector] [starvation]
    list($sector, $starvation)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[starvation]", "$starvation", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TOW: //data args are : [sector] [newsector] [hull]
    list($sector, $newsector, $hull)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[newsector]", "$newsector", $retvalue[text]);
    $retvalue[text] = str_replace("[hull]", "$hull", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_DEFS_DESTROYED_F: //data args are : [fighters] [sector]
    list($fighters, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[fighters]", "$fighters", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TEAM_REJECT: //data args are : [player] [teamname]
    list($player, $teamname)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "$player", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[teamname]", "$teamname", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TEAM_RENAME: //data args are : [team]
    case LOG_TEAM_M_RENAME:
    case LOG_TEAM_KICK:
    case LOG_TEAM_CREATE:
    case LOG_TEAM_LEAVE:
    case LOG_TEAM_LEAD:
    case LOG_TEAM_JOIN:
    case LOG_TEAM_INVITE:
    $retvalue[text] = str_replace("[team]", "$entry[data]", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_TEAM_NEWLEAD: //data args are : [team] [name]
    case LOG_TEAM_NEWMEMBER:
    list($team, $name)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[team]", "$team", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[name]", "$name", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ADMIN_HARAKIRI: //data args are : [player] [ip]
    list($player, $ip)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "$player", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[ip]", "$ip", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ADMIN_ILLEGVALUE: //data args are : [player] [quantity] [type] [holds]
    list($player, $quantity, $type, $holds)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[player]", "$player", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[quantity]", "$quantity", $retvalue[text]);
    $retvalue[text] = str_replace("[type]", "$type", $retvalue[text]);
    $retvalue[text] = str_replace("[holds]", "$holds", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_ADMIN_PLANETDEL: //data args are : [attacker] [defender] [sector]
    list($attacker, $defender, $sector)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[attacker]", "$attacker", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[defender]", "$defender", $retvalue[text]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_DEFENCE_DEGRADE: //data args are : [sector] [degrade]
    list($sector, $degrade)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[degrade]", "$degrade", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;

    case LOG_PLANET_CAPTURED: //data args are : [sector] [cols] [credits] [owner]
    list($sector, $cols, $credits, $owner)= split ("\|", $entry[data]);
	$retvalue[text] = str_replace("[sector]", "$sector", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[cols]", "$cols", $retvalue[text]);
    $retvalue[text] = str_replace("[credits]", "$credits", $retvalue[text]);
    $retvalue[text] = str_replace("[owner]", "$owner", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
    case LOG_BOUNTY_CLAIMED:
    list($amount,$bounty_on,$placed_by) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[amount]", "$amount", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[bounty_on]", "$bounty_on", $retvalue[text]);
    $retvalue[text] = str_replace("[placed_by]", "$placed_by", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_BOUNTY_PAID:
    list($amount,$bounty_on) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[amount]", "$amount", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[bounty_on]", "$bounty_on", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_BOUNTY_CANCELLED:
    list($amount,$bounty_on) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[amount]", "$amount", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[bounty_on]", "$bounty_on", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
case LOG_BOUNTY_FEDBOUNTY:
    $retvalue[text] = str_replace("[amount]", "$entry[data]", $l_log_text[$entry[type]]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_SPACE_PLAGUE:
    list($name,$sector) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[name]", "$name", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $percentage = $space_plague_kills * 100;
    $retvalue[text] = str_replace("[percentage]", "$percentage", $retvalue[text]);
    //$retvalue[text] = str_replace("[percentage]", "20", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_PLASMA_STORM:
    list($name,$sector,$percentage) = split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[name]", "$name", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[text] = str_replace("[percentage]", "$percentage", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_PLANET_BOMBED:
    list($planet_name, $sector, $name, $beams, $torps, $figs)= split ("\|", $entry[data]);
    $retvalue[text] = str_replace("[planet_name]", "$planet_name", $l_log_text[$entry[type]]);
    $retvalue[text] = str_replace("[sector]", "$sector", $retvalue[text]);
    $retvalue[text] = str_replace("[name]", "$name", $retvalue[text]);
    $retvalue[text] = str_replace("[beams]", "$beams", $retvalue[text]);
    $retvalue[text] = str_replace("[torps]", "$torps", $retvalue[text]);
    $retvalue[text] = str_replace("[figs]", "$figs", $retvalue[text]);
    $retvalue[title] = $l_log_title[$entry[type]];
    break;
 case LOG_SPECIAL_TRADE:
	$retvalue[text] = "Did a special trade with $entry[data]";
	$retvalue[title] = "Special trade";
	break;

  }
  return $retvalue;
}

function MakeBars($level, $max)
{
  global $l_n_a;
  
  $diff = $max - $level;
  $img = "";

  for ($i=0;$i<$level;$i++)
  {
    $bright = floor($i / 5) + 1;
    if($bright > 5)
      $bright = 5;
    $img .= "<img src=images/dialon$bright.gif>&nbsp;";
  }

  for ($i=0;$i<$diff;$i++)
  {
    $img .= "<img src=images/dialoff.gif>&nbsp;";
  }

  if($img == "")
    $img = "<font size=2><b>$l_n_a</b></font>";

  return $img;
}

function newplayer($email, $char, $pass, $ship_name)
{
  global $db, $dbtables, $ip;
  global $start_credits, $start_turns, $default_lang;
  global $start_armour, $start_energy, $start_fighters, $max_turns;

  $stamp=date("Y-m-d H:i:s");

  $query = $db->Execute("SELECT MAX(turns_used + turns) AS mturns FROM $dbtables[players] WHERE email NOT LIKE '%furangee%'");
  $res = $query->fields;

  $mturns = $res[mturns];

  if($mturns > $max_turns)
    $mturns = $max_turns;

  if($mturns < $start_turns)
    $mturns = $start_turns;

  //Create player

  $db->Execute("INSERT INTO $dbtables[players] VALUES(" .
               "''," .             //player_id
               "'$char'," .        //character_name
               "'$pass'," .        //password
               "'$email'," .       //email
               "$start_credits," . //credits
			   "0," .				// sector
               "$mturns," .        //turns
			   "'N',".				// on planet
               "0," .              //turns_used
               "'$stamp'," .       //last_login
               "0," .              //rating
               "0," .              //score
               "0," .              //team
               "0," .              //team_invite
               "'N'," .            //interface
               "'$ip'," .      		//ip_address
			   "0," .				// planet_id
               "0," .              //preset1
               "0," .              //preset2
               "0," .              //preset3
               "'Y'," .            //trade_colonists
               "'N'," .            //trade_fighters
               "'N'," .            //trade_torps
               "'Y'," .            //trade_energy
			   "''," .			// cleared defenses
               "'$default_lang'," .//lang
			   "'Y'," .				// alerts
               "'Y'," .             //alert2
			   "''," . 				// subscribed
			   "1,1,1,1," .			// sale prices
			   "0," .              //currentship
			   "0,0,0" .			// additional presets
               ")");
   //Get the new ship's id
  $res = $db->Execute("SELECT player_id from $dbtables[players] WHERE email='$email'");
  $player_id = $res->fields[player_id]; 
 	//echo "Player id = $player_id<br>";
  //Create player's ship

  $db->Execute("INSERT INTO $dbtables[ships] VALUES(" .
               "''," .             //ship_id
               "LAST_INSERT_ID()," .     //player_id
               "1," .            //type
               "'$ship_name'," .   //name
               "'N'," .            //destroyed
               "0," .              //hull
               "0," .              //engines
               "0," .              //power
               "0," .              //computer
               "0," .              //sensors
               "0," .              //beams
               "0," .              //torp_launchers
               "0," .              //torps
               "0," .              //shields
               "0," .              //armour
               "$start_armour," .  //armour_pts
               "0," .              //cloak
               "0," .              //sector_id
               "0," .              //ore
               "0," .              //organics
               "0," .              //goods
               "$start_energy," .  //energy
               "0," .              //colonists
               "$start_fighters," .//fighters
			   "'',".					// ship damage
               "'N'," .            //on_planet
               "0," .              //dev_warpedit
               "0," .              //dev_genesis
               "0," .              //dev_beacon
               "0," .              //dev_emerwarp
               "'N'," .            //dev_escapepod
               "'N'," .            //dev_fuelscoop
               "0," .              //dev_minedeflector
               "0," .              //planet_id
               "''," .             //cleared_defences
               "'N'," .            //dev_lssd
			   "'N'," .				// dev_sectorwmd
			   "'N'" .
               ")");

  echo $db->ErrorMsg();
  
  //Insert current ship in players table
  $db->Execute("UPDATE $dbtables[players] SET currentship=LAST_INSERT_ID() WHERE player_id=$player_id");

  //Create player's zone
  $zone_name = "$char" . "\'s Territory";
  $db->Execute("INSERT INTO $dbtables[zones] VALUES(" .
               "''," .             //zone_id
               "'$zone_name'," .   //zone_name
               "$player_id," .     //owner
               "'N'," .            //corp_zone
               "'Y'," .            //allow_beacon
               "'Y'," .            //allow_attack
               "'Y'," .            //allow_planetattack
               "'Y'," .            //allow_warpedit
               "'Y'," .            //allow_planet
               "'Y'," .            //allow_trade
               "'Y'," .            //allow_defenses
               "0" .               //max_hull
               ")");

  //Create the IGB account
  $db->Execute("INSERT INTO $dbtables[ibank_accounts] (player_id,balance,loan) VALUES ($player_id,0,0)");
	// Create the profile
  $db->Execute("INSERT INTO $dbtables[profile] (player_id) VALUES ($player_id)");
  // Insert welcome message
  $timestamp = date("Y\-m\-d H\:i\:s");
  $message = addslashes("Welcome to Starkick Traders (SKT)! If you need some help, choose the help link, look in the forums or ask one of the players in the top 10. Remember, if you get lost, use the Nav Computer. It can almost always give you the route back to Sector 0 where you will be safe.\n\nThe rules of SKT are simple: \n1. No swearing, foul language or cussing\n2. Play only one account.\n3. Game Admins have ultimate authority and final decision. Complaints are sent to singularity space.\nPlease post bugs reports etc. to the forums.\n\nGood luck out there!\n\nThe Federation.");
  $db->Execute("INSERT INTO $dbtables[messages] SET sender_id=1, recp_id='$player_id', subject='Welcome!',sent='$timestamp', message='$message'");
  return $player_id;
}

function calc_dist($src,$dst) {
  global $db;
  global $dbtables;

  $results = $db->Execute("SELECT x,y,z FROM ".$dbtables['universe'].
                          " WHERE sector_id=$src OR sector_id=$dst");

// Make sure you check for this when calling this function.
  if(!$results) return 0;


  $x = $results->fields['x'];
  $y = $results->fields['y'];
  $z = $results->fields['z'];

  $results->MoveNext();

  $x -= $results->fields['x'];
  $y -= $results->fields['y'];
  $z -= $results->fields['z'];

  $x = sqrt($x*$x + $y*$y + $z*$z);

// Make sure it's never less than 1.
//  if($x > 1) return 1;

  return $x;
}
?>
