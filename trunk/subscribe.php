<?
include_once("config.php");
updatecookie();

include_once("languages/$lang");

$title="Donate";
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
        Starkick Traders is free to play but we really need donations to pay for the server. 
        If you would like to donate some cash via PayPal please click below: 
		
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="ben@mpgames.com">
<input type="hidden" name="item_name" value="SKT LE Support">
<input type="hidden" name="item_number" value="sktle001">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://www.mpgames.com/skt/success.php">
<input type="hidden" name="cancel_return" value="http://www.mpgames.com/skt/nothanks.php">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="tax" value="0">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
</form>
        <p>&nbsp;</p>
        <p><a href="nothanks.php">I do not want to donate at this time</a> (Please reconsider, every bit counts!)</p>
      </div>
    </td>
  </tr>
</table>
  <p>
    

<?
//-------------------------------------------------------------------------------------------------

echo "<BR><BR>";


if($sectorinfo[port_type] == "special")
 {
 echo "<BR><BR>Click <A HREF=port.php>here</A> to return to the supply depot.";
}
if ($err == 2 ) {
	echo "$l_global_mlogin";
} else {
	TEXT_GOTOMAIN();
}
include("footer.php");

?>
