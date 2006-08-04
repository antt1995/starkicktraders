<?
include("config.php");
include("languages/$lang");

$title=$l_new_title2;

include("header.php");

// Allowed email domains for new users. Request by Shaun 3/10/05, coded by Ben
$allowed = 	array("trial.danger.com", "tmail.com", "hiptop.suncom.com", "fidohiptop.ca", "developer.danger.com", "hiptop.suncom.net", "eplus-hiptop.de", "cwhiptop.com", "edgehiptop.com", "hiptop.one.at", "starhub-hiptop.com");

bigtitle();

connectdb();

if($account_creation_closed)
{
  die($l_new_closed_message);
}
$character=htmlspecialchars($character);
$shipname=htmlspecialchars($shipname);
$character=ereg_replace("[^[:digit:][:space:][:alpha:][\']]"," ",$character);
$shipname=ereg_replace("[^[:digit:][:space:][:alpha:][\']]"," ",$shipname);

$username = $HTTP_POST_VARS['username']; //This needs to STAY before the db query
// Clean up username
$username = mysql_escape_string(strtolower($username));

if(!get_magic_quotes_gpc())
{
  $username = addslashes($username);
  $character = addslashes($character);
  $shipname = addslashes($shipname);
}
// Check for validity
$flag=0;
// Stop blank entries
if ($username=='' || $character=='' || $shipname=='' || $checkbox!="yes") { echo "$l_new_blank<BR>"; $flag=1;}
// Check for a valid email address
if(ereg("^.+@.+\\..+$", $username)) {
	// Okay it looks like an email address
	list($mbox, $maildomain) = split("@", $username); 
	// Check for Hiptop Domains Request by Shaun 3/10/05, coded by Ben
	$valid = false;
	for ($i = 0; $i < count($allowed); $i++) {
		if (strstr($maildomain,$allowed[$i])) {
			$valid = true;
		}
	}
	if (!$valid) {
		echo "You must register with a valid Hiptop email address! Please go back and try again.<br>";
		$flag=1;	
	}
}
/*
if(ereg("^.+@.+\\..+$", $username)) {
	// Okay it looks like an email address
	list($username, $mailDomain) = split("@", $username); 
	if (!checkdnsrr($mailDomain,"A") && !checkdnsrr($mailDomain, "MX")) {
	 // this email domain doesn't exist! bad dog! no biscuit!
	 echo "$username is an invalid email address.<br>Please enter your real email address!<br>DNS problem<br>";
	 $flag=1; 
	}
	
} else {
	echo "$username is an invalid email address.<br>Please enter your real email address!<br>ereg problem<br>";
	$flag=1; 
}
*/

if ($flag==0) {
	$result = $db->Execute ("select email, character_name, ship_name, ip_address from $dbtables[players],$dbtables[ships] where email='$username' OR character_name='$character' OR ship_name='$shipname' OR ip_address='$ip'");
	if ($result>0)
	{
  		while (!$result->EOF && $flag==0)
  		{
    	$row = $result->fields;
    	if (strtolower($row[email])==strtolower($username)) { echo "$l_new_inuse  $l_new_4gotpw1 <a href=mail.php?mail=$username>$l_clickme</a> $l_new_4gotpw2<BR>"; $flag=1;}
    	if (strtolower($row[character_name])==strtolower($character)) { echo "$l_new_inusechar<BR>"; $flag=1;}
    	if (strtolower($row[ship_name])==strtolower($shipname)) { echo "$l_new_inuseship<BR>"; $flag=1;}
		if ($row[ip_address]==$ip) {
			// Check to see if this person is playing on a device
			$browser="hiptop";
			if ($browser != "hiptop" && $browser !="treo") {
				$flag=1;
				echo "In order to prevent multiple account play we restrict account creation under certain circumstances.<br>This is one of them.<br>If you are not trying to create multiple accounts then we are sorry, but game slots for your network are full. Please try another time.<br>";
				echo "<b>Suspected multiple account creation attempt logged. Forum posting pending...</b><br>";
				playerlog(1,LOG_RAW,"Suspected multiple account creation attempt logged from $ip. Offender is $row[character_name] email $row[email]. They tried to register $character with email address $username.");
			}
		}
    	$result->MoveNext();
  	}
}

if ($flag==0)
{
  /* insert code to add player to database */
  $makepass="";
  $syllables="er,in,tia,wol,fe,pre,vet,jo,nes,al,len,son,cha,ir,ler,bo,ok,tio,nar,sim,ple,bla,ten,toe,cho,co,lat,spe,ak,er,po,co,lor,pen,cil,li,ght,wh,at,the,he,ck,is,mam,bo,no,fi,ve,any,way,pol,iti,cs,ra,dio,sou,rce,sea,rch,pa,per,com,bo,sp,eak,st,fi,rst,gr,oup,boy,ea,gle,tr,ail,bi,ble,brb,pri,dee,kay,en,be,se";
  $syllable_array=explode(",", $syllables);
  srand((double)microtime()*1000000);
  for ($count=1;$count<=2;$count++) {
    if (rand()%10 == 1) {
      $makepass .= sprintf("%0.0f",(rand()%50)+1);
    } else {
      $makepass .= sprintf("%s",$syllable_array[rand()%62]);
    }
  }
  $stamp=date("Y-m-d H:i:s");
  $query = $db->Execute("SELECT MAX(turns_used + turns) AS mturns FROM $dbtables[players] WHERE character_name NOT LIKE '%furangee%'");
  $res = $query->fields;

  $mturns = $res[mturns];

  if($mturns > $max_turns)
  		$mturns = $max_turns;
		$md5pass = md5($makepass);
		//$db->Execute("LOCK TABLES forums.phpbb_users WRITE");
		//$result3 = $db->Execute("SELECT MAX(user_id) AS total FROM forums.phpbb_users");
		//$row=$result3->fields;
		//$user_id=$row[total]+1;
		//$result3 = $db->Execute("INSERT INTO forums.phpbb_users SET user_id=$user_id, username='$character',user_email='$username',user_password='$md5pass',user_regdate=UNIX_TIMESTAMP(),user_sig='Captain of the ship $shipname',user_interests='Captain of the ship $shipname'");
		$player_id=newplayer($username, $character, $makepass, $shipname);
	  	if (!$player_id) {
			echo $db->ErrorMsg() . "<br>";
	  	} else {
			$l_new_message = str_replace("[pass]", $makepass, $l_new_message);
			mail("$username", "$l_new_topic", "$l_new_message\r\n\r\nhttp://www$gamedomain/skt","From: $admin_mail\r\nReply-To: $admin_mail\r\nX-Mailer: PHP/" . phpversion());
			if($display_password)
			{
			   echo "<b>".$l_new_pwis . " " . $makepass . "</b><BR><BR>";
			}
			echo "$l_new_pwsent<BR>";
			echo "You now need to check your email and log in with the password sent to you.<br>If you do not log in within 1 hour, your account will automatically be deleted and you will have to re-register.<br>Thank you registering to play Starkick Traders!<br><br>";
			echo "<img src=images/pictorial/leaving-earth.jpg><br>";
			//echo "You have also been registered onto the forums as user $character with the same password.<br><br>";
			// Set the cookies
			//$userpass = $username."+".$makepass;
			//SetCookie("userpass",$userpass,time()+(3600*24),$gamepath,$gamedomain);
			//setcookie("username",$username,time()+(3600*48));
			//setcookie("password",$makepass,time()+(3600*48));
			//$password = $makepass;
	  	}
	}
} else {

  echo $l_new_err;
}
echo "<br><a href=login.php>Return to the login page</a><br>";
include("footer.php");
?>
