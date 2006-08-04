<?

include("config.php");
connectdb();
if(checklogin())
{
  die();
}
$title = "$l_opt2_title";
//-------------------------------------------------------------------------------------------------

if($newpass1 == $newpass2 && $password == $oldpass && $newpass1 != "")
{
  //$userpass = $username."+".$newpass1;
  SetCookie("password",$newpass1,time()+(3600*48));
  //setcookie("id",$id);
}
if(!preg_match("/^[\w]+$/", $newlang)) 
{
   $newlang = $default_lang;
}
$lang=$newlang;
//SetCookie("lang",$lang,time()+(3600*24)*365,$gamepath,$gamedomain);
include("languages/$lang" . ".inc");

include("header.php");
bigtitle();

if($newpass1 == "" && $newpass2 == "")
{
  echo $l_opt2_passunchanged;
}
elseif($password != $oldpass)
{
  echo $l_opt2_srcpassfalse;
}
elseif($newpass1 != $newpass2)
{
  echo $l_opt2_newpassnomatch;
}
else
{
  $res = $db->Execute("SELECT player_id,password FROM $dbtables[players] WHERE email='$username'");
  $playerinfo = $res->fields;
  if($oldpass != $playerinfo[password])
  {
    echo $l_opt2_srcpassfalse;
  }
  else
  {
    $res = $db->Execute("UPDATE $dbtables[players] SET password='$newpass1' WHERE player_id=$playerinfo[player_id]");
    if($res)
    {
      echo $l_opt2_passchanged;
    }
    else
    {
      echo $l_opt2_passchangeerr;
    }
  }
}
/*
$res = $db->Execute("UPDATE $dbtables[players] SET interface='$intrf' WHERE email='$username'");
if($res)
{
  echo $l_opt2_userintup;
}
else
{
  echo $l_opt2_userintfail;
}

$res = $db->Execute("UPDATE $dbtables[players] SET lang='$lang' WHERE email='$username'");
foreach($avail_lang as $curlang)
{
  if($lang == $curlang[file])
  {
    $l_opt2_chlang = str_replace("[lang]", "$curlang[name]", $l_opt2_chlang);
    
    echo $l_opt2_chlang;
    break;
  }
}
*/
if($alerts != 'Y')
  $alerts = 'N';

$res = $db->Execute("UPDATE $dbtables[players] SET alerts='$alerts' WHERE email='$username'");
if($res)
{
  echo "Email alert status updated.<BR>";
}
else
{
  echo "Email alert status update failed.<BR>";
}
if($alert2 != 'Y')
  $alert2 = 'N';

$res = $db->Execute("UPDATE $dbtables[players] SET alert2='$alert2' WHERE email='$username'");
if($res)
{
	if ($alert2 == "Y") {
  		echo "Alerts will be sent when a Furangee buys off one of your planets.<BR>";
	} else {
		echo "Alerts will not be sent when a Furangee buys off one of your planets.<BR>";
	}		
}
else
{
  echo "Email alert status update failed.<BR>";
}
$res = $db->Execute("SELECT player_id FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
$res2 = $db->Execute("SELECT * FROM $dbtables[profile] WHERE player_id='$playerinfo[player_id]'");
$profile = $res2->fields;
if ($profile[skill] != $skill) {
	echo "Professed skill level updated.<br>";
}
if ($profile[alignment] != $alignment) {
	echo "Alignment updated.<br>";
}
//$story = addslashes($story);
if ($profile[story] != $story) {
	echo "Background story updated.<br>";
}
$pic_url = addslashes($pic_url);
if ($profile[pic_url] != $pic_url) {
	echo "Picture URL updated.<br>";
}

//echo "DEBUG: UPDATE $dbtables[profile] SET skill='$skill',alignment='$alignment',story='$story',pic_url='$pic_url' WHERE player_id='$playerinfo[player_id]'";
$res2 = $db->Execute("UPDATE $dbtables[profile] SET skill='$skill',alignment='$alignment',story='$story',pic_url='$pic_url' WHERE player_id='$playerinfo[player_id]'");

//-------------------------------------------------------------------------------------------------
echo "<br>";
TEXT_GOTOMAIN();

include("footer.php");

?>
