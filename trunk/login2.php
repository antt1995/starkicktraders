<?
include("config.php");
include("languages/$lang");

connectdb();

//test to see if server is closed to logins
$playerfound = false;

$screen_res = $HTTP_POST_VARS[res];
if(empty($screen_res))
  $screen_res = 800;

$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$email'");
if($res)
{
  $playerfound = $res->RecordCount();
}
$playerinfo = $res->fields;

$lang=$playerinfo[lang];
if(empty($lang))
  $lang=$default_lang;
//SetCookie("lang",$lang,time()+(3600*24),$gamepath,$gamedomain);
include("languages/$lang" . ".inc");

/* took out the old interface, its not used anymore i guess
if($playerinfo[interface]=="N")
{
  $mainfilename="main.php";
  $interface="main.php";
}
else
{
  $mainfilename="maintext.php";
  $interface="maintext.php";
}
-- End of old interface code */ 

//setcookie("interface", $mainfilename);
$interface=$mainfilename;
//setcookie("screenres", $screen_res);
$screenres=$screen_res;

/* first placement of cookie - don't use updatecookie. */
//$userpass = $email."+".$pass;
//SetCookie("userpass",$userpass,time()+(3600*24),$gamepath,$gamedomain);
setcookie("username",$email,time()+(3600*1000));
//setcookie("username",$email);
$username = $email;
setcookie("password",$pass,time()+(3600*1000));
//setcookie("username",$email);
$password = $pass;

$banned = 0;
$res = $db->Execute("SELECT * FROM $dbtables[ip_bans] WHERE '$ip' LIKE ban_mask OR '$playerinfo[ip_address]' LIKE ban_mask");
if($res->RecordCount() != 0)
{
  //SetCookie("userpass","",0,$gamepath,$gamedomain);
  //SetCookie("userpass","",0); // Delete from default path as well.
  setcookie("username","",0);
  setcookie("password","",0);
  //setcookie("id","",0);
  //setcookie("res","",0);
  $banned = 1;
}

$result = $db->Execute("SELECT * FROM $dbtables[config] LIMIT 1");
$row = $result->fields;
if($row[server_closed] == 'Y')
{
  $title=$l_login_sclosed;
  include("header.php");
  echo $row[closed_message]."<br><br>";
  	if(!empty($link_forums))
  		echo "<A HREF=\"$link_forums\" TARGET=\"_blank\">$l_forums</A> - ";
  die();
}

$title=$l_main_title;
//$title=$l_login_title2;
include("header.php");

//bigtitle();

if($banned == 1)
{
   echo "<center><p><font size=3 color=red>$l_login_banned<p></center>";
   include("footer.php");
   die();
}

if($playerfound)
{
  if($playerinfo[password] == $pass)
  {
    // password is correct
	$res = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
	$shipinfo = $res->fields;
	
    if($shipinfo[ship_destroyed] == "N")
    {
      // player's ship has not been destroyed
      playerlog($playerinfo[player_id], LOG_LOGIN, $ip);
      $stamp = date("Y-m-d H-i-s");
      $update = $db->Execute("UPDATE $dbtables[players] SET last_login='$stamp',ip_address='$ip' WHERE player_id=$playerinfo[player_id]");
	  //TEXT_GOTOMAIN();
	  // Check for web access without subscription
	  if($force_subscription && !$isHiptop && ($playerinfo['subscribed'] == null || !strpos($playerinfo['subscribed'],"payment"))) {
	  //	die ("Ask for subscription!<br>Sub value=".$playerinfo['subscribed']."<br>");
		include("subscribe.php?err=2");
		die();
	  }	
	  if ($playerinfo[turns_used] == 0) {
	  	include("newplayer.html");
	  	die();
		} else {
			//die( "Go to main!");
			if ($browser == "up") {
				include ("upbrow.php");
			} else if ($browser == "treo") {
				include("treomain.php");
			} else {
				include("metamain.php");
			}
			die();	
      //	die("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL=$interface?id=" . $playerinfo[player_id] . "\">");
	  }
    }
    else
    {
      // player's ship has been destroyed
      if($shipinfo[dev_escapepod] == "Y")
      {
        $db->Execute("UPDATE $dbtables[players] SET sector=0,on_planet='N' where player_id=$playerinfo[player_id]");
		$db->Execute("UPDATE $dbtables[ships] SET type=1, hull=0,engines=0,power=0,computer=0,sensors=0,beams=0,torp_launchers=0,torps=0,armour=0,armour_pts='$start_armour',cloak=0,shields=0,sector=0,ship_ore=0,ship_organics=0,ship_energy='$start_energy',ship_colonists=0,ship_goods=0,ship_fighters='$start_fighters',tow=0,on_planet='N',dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,ship_destroyed='N',dev_lssd='N' where player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");
        echo $l_login_died;
      }
		else
		{
    	echo "<h1>You died in a horrible incident!</h1>";

        // Check if $newbie_nice is set, if so, verify ship limits.
		// If they are a subscriber - they get to start again too. We need the $'s!
		// EVERYONE GETS CLONED IN THIS GAME
			if ($newbie_nice == "YES")
			{
				/*
				// A newbie has to have only one ship and it has to be less than the newbie amounts
				$newbie_info = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id='$playerinfo[player_id]'
				$newbie_info = $db->Execute("SELECT * FROM $dbtables[ships] WHERE player_id='$playerinfo[player_id]' AND hull<='$newbie_hull' AND engines<='$newbie_engines' AND power<='$newbie_power' AND computer<='$newbie_computer' AND sensors<='$newbie_sensors' AND armour<='$newbie_armour' AND shields<='$newbie_shields' AND beams<='$newbie_beams' AND torp_launchers<='$newbie_torp_launchers' AND cloak<='$newbie_cloak' OR subscribed='subscr_payment'");
				$num_rows = $newbie_info->RecordCount();
				*/
				
				//if ($num_rows)
				//{
					echo "<BR><BR>$l_login_newbie<BR>";
			        $db->Execute("UPDATE $dbtables[players] SET sector=0,on_planet='N',credits='$start_credits' where player_id=$playerinfo[player_id]");
					$db->Execute("UPDATE $dbtables[ships] SET type=1, hull=0,engines=0,power=0,computer=0,sensors=0,beams=0,torp_launchers=0,torps=0,armour=0,armour_pts='$start_armour',cloak=0,shields=0,sector=0,ship_ore=0,ship_organics=0,ship_energy='$start_energy',ship_colonists=0,ship_goods=0,ship_fighters='$start_fighters',tow=0,on_planet='N',dev_warpedit=0,dev_genesis=0,dev_beacon=0,dev_emerwarp=0,dev_escapepod='N',dev_fuelscoop='N',dev_minedeflector=0,ship_destroyed='N',dev_lssd='N' where player_id=$playerinfo[player_id] AND ship_id=$playerinfo[currentship]");

					echo $l_login_newlife;
				//}
                //else
				//{
				//echo "<BR><BR>$l_login_looser";
				//}

			} // End if $newbie_nice
			else
			{
				echo "<BR><BR>$l_login_looser";
			}
		}
    }
  }
  else
  {
    // password is incorrect
    echo "$l_login_4gotpw1 <A HREF=mail.php?mail=$email>$l_clickme</A> $l_login_4gotpw2 <a href=login.php>$l_clickme</a> $l_login_4gotpw3 $ip...";
    playerlog($playerinfo[player_id], LOG_BADLOGIN, $ip);
  }
}
else
{
  echo "<B>$l_login_noone</B><BR>";
}

include("footer.php");

?>
