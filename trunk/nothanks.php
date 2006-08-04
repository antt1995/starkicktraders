<?
include_once("config.php");
updatecookie();

include_once("languages/$lang");

$title="No Thanks";
include_once("header.php");

connectdb();

if(checklogin() == 1)
{
  die();
}

//-------------------------------------------------------------------------------------------------
$result     = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $result->fields;
?> 
<table width="240" border="0" cellspacing="0" cellpadding="2">
  <tr> 
    <td> 
      <div align="center">
        <p><img src="images/startitle.gif" width="145" height="18"><br>
          <img src="images/bigstinger.jpg" width="120" height="90"> </p>
        <p>Okay, thanks anyway - we won't bug you again (this game!)</p>
        <p>If you want to donate in the future please choose the donate link in 
          the main menu.</p>
        <p>&nbsp;</p>
      </div>
    </td>
  </tr>
</table>
  <p>
    

<?
//-------------------------------------------------------------------------------------------------

echo "<BR><BR>";
// Make them a sub anyway
$db->Execute("UPDATE $dbtables[players] SET subscribed='subscr_payment' WHERE player_id=$playerinfo[player_id]");

if($sectorinfo[port_type] == "special")
 {
 echo "<BR><BR>Click <A HREF=port.php>here</A> to return to the supply depot.";
 echo "<br>Or click <A HREF=shipyard.php>here</A> to return to the shipyard.";
}
if ($err == 2 ) {
	echo "$l_global_mlogin";
} else {
	TEXT_GOTOMAIN();
}
include("footer.php");

?>
