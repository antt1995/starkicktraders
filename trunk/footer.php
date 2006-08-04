<?
global $db,$dbtables,$l_footer_players_on_1,$online,$l_footer_players_on_2;
connectdb();
// Update the last login info (online)
$db->Execute("UPDATE $dbtables[players] SET last_login=NOW() WHERE player_id=$playerinfo[player_id]");
$res = $db->Execute("SELECT COUNT(*) as loggedin from $dbtables[players] WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP($dbtables[players].last_login)) / 60 <= 5 and email NOT LIKE '%@furangee'");
$row = $res->fields;
$online = $row[loggedin];
?>
<center>
<br>
<?
if($online == 1)
{
   echo "<a href=online.php$ck>";
   echo $l_footer_one_player_on;
   echo "</a>";
}
else
{
echo "<a href=online.php$ck>";
echo $l_footer_players_on_1;
echo " ";
echo $online;
echo " ";
echo $l_footer_players_on_2;
echo "</a>";
}
?>
</center>
<p>
  <table width=100% border=0 cellspacing=1 cellpadding=0>
   <tr>
      <td align=center valign="top" width=33%><a href="news.php"><b>Starkick Universe News</b></a></td>
	  <td align=center valign="top"  width=33%><a href=agreement.html>Terms of Use</a></td>
      <td align=center valign="top"  width=33%><a href="credits.php">Copyright</a></td>
   </tr>
  </table>
    <center>
<script type="text/javascript"><!--
google_ad_client = "pub-2030062931737666";
google_ad_width = 728;
google_ad_height = 90;
google_ad_format = "728x90_as";
google_ad_channel ="3730355459";
google_color_border = "333333";
google_color_bg = "000000";
google_color_link = "00FF00";
google_color_url = "999999";
google_color_text = "CCCCCC";
//--></script>
<script type="text/javascript"
  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</center>
</body>
</html>
