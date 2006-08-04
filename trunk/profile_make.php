<?
include("config.php");
$title="profile maker";
include("header.php");

### Connect to the database.

connectdb();

### Print Title on Page.

bigtitle();
$res = $db->Execute("SELECT player_id FROM $dbtables[players] WHERE email NOT LIKE '%furangee' ORDER BY `player_id` ASC");
while (!$res->EOF) {
	$row = $res->fields;
	echo "INSERT INTO $dbtables[profile] SET player_id=$row[player_id]<br>";
	$db->Execute("INSERT INTO $dbtables[profile] SET player_id=$row[player_id]");
	$res->MoveNext();
}
echo "done"
?>
