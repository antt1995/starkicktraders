<?php
include("config.php");
include("languages/$lang");
$title="Who's Online?";
include("header.php");

connectdb();
bigtitle();
function insig($score) {
	global $l_insignia;
	
	$score_array = array('1000', '3000', '6000', '9000', '12000', '15000', '20000', '40000', '60000', '80000', '100000', '120000', '160000', '200000', '250000', '300000', '350000', '400000', '450000', '500000','1000000','2000000','4000000','8000000');
	
	for ( $i=0; $i<count($score_array); $i++)
	{           
		if ( $score < $score_array[$i])
		 {
		   $player_insignia = $l_insignia[$i];
		   break;    
		 }
	}
	
	if(!isset($player_insignia))
	  $player_insignia = end($l_insignia);
	return $player_insignia;
}

$res = $db->Execute("SELECT character_name,email,score from $dbtables[players] WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP($dbtables[players].last_login)) / 60 <= 5 and email NOT LIKE '%@furangee'");
if ($res->EOF) {
	echo "No one is online right now<br>";
} else {
	echo "<ol>";
	while (!$res->EOF) {
		$row = $res->fields;
		echo "<li>".insig($row[score])." <a href=ranking.php?detail=".urlencode($row[character_name]).">".$row[character_name]."</a></li>";
		$res->MoveNext();
	}
	echo "</ol>";
}
if(empty($username))
{
  TEXT_GOTOLOGIN();
}
else
{
  TEXT_GOTOMAIN();
}
include ("footer.php");
?>
