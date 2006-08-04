<?
global $db,$dbtables;
connectdb();
$res = $db->Execute("SELECT COUNT(*) as loggedin from $dbtables[players] WHERE (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP($dbtables[players].last_login)) / 60 <= 5 and email NOT LIKE '%@furangee'");
$row = $res->fields;
$online = $row[loggedin];
?>
<br>
<center>
<?
if($online == 1)
{
   echo "<a href=online.php?kk=".date("U").">";
   echo $l_footer_one_player_on;
   echo "</a>";
}
else
{
echo "<a href=online.php?kk=".date("U").">";
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
      <td align=center valign="top"><a href="news.php"><b>Starkick Universe News</b></a></td>  <td valing valign="top"><a href=agreement.html>Terms of Use</a></td>
      <td valign="top"><a href="credits.php">Copyright</a></td>
   </tr>
  </table>
</body>
</html>
