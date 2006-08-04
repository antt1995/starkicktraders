<?
include("config.php");
updatecookie();

include("languages/$lang");
$title="Successful Donation";
include("header.php");

connectdb();

if(checklogin() == 1)
{
  die();
}
echo "<BR><BR>";
//-------------------------------------------------------------------------------------------------
$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;
if (strstr($playerinfo[subscribed],"payment")) {
	echo "Thank you for donating!";
} else {
	echo "<h2>Thank you for your support!</h2>";

}
$db->Execute("UPDATE $dbtables[players] SET subscribed='subscr_payment' WHERE player_id=$playerinfo[player_id]");

//-------------------------------------------------------------------------------------------------

echo "<BR><BR>";
TEXT_GOTOMAIN();

include("footer.php");

?>
