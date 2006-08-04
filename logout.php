<?


include("config.php");
include("languages/$lang");
$title = "Logout";

setcookie("username","",0);
setcookie("password","",0);

include("header.php");

connectdb();

$result = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;

$current_score = gen_score($playerinfo[player_id]);
playerlog($playerinfo[player_id], LOG_LOGOUT, $ip);

bigtitle();
echo "$l_logout_score ".NUMBER($current_score).".<BR>";
$l_logout_text=str_replace("[name]",$username,$l_logout_text);
echo $l_logout_text;

include("footer.php");

?>
