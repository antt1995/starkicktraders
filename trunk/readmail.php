<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_readm_title;
include("header.php");

bigtitle();

connectdb();

if(checklogin())
{
  die();
}

$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
$db->Execute("UPDATE $dbtables[messages] SET notified='Y' WHERE recp_id='".$playerinfo[player_id]."'");

if ($action=="delete")
{
 $db->Execute("DELETE FROM $dbtables[messages] WHERE ID='".$ID."' AND recp_id='".$playerinfo[player_id]."'");
?>
<FONT COLOR="#FF0000" Size="5"><B><Center><? echo $l_readm_delete; ?></Center></B></FONT><BR>
<?
}
if ($action=="deleteall")
{
	if ($confirm != "yes") {
		?>
		<FONT COLOR="#FF0000" Size="5"><B><Center>Delete All Messages: Are you sure?<br>
		<a href="readmail.php?action=deleteall&confirm=yes&kk=<? echo date("U") ?>">Yes</a><br>
		<a href="readmail.php?kk=<? echo date("U") ?>">No</a><br>
		</Center></B></FONT><BR>
		<?
	} else {
		?>
		<FONT COLOR="#FF0000" Size="5"><B><Center>All Messages Deleted<br>
		</Center></B></FONT><BR>
		<?
		 $db->Execute("DELETE FROM $dbtables[messages] WHERE recp_id='".$playerinfo[player_id]."'");
	}
}
if (empty($from)) {
	$from =0;
} else {
	$start=$from;
}
$res = $db->Execute("SELECT * FROM $dbtables[messages] WHERE recp_id='".$playerinfo[player_id]."' ORDER BY sent DESC LIMIT $from,5");
 if ($res->EOF)
 {
  echo "$l_readm_nomessage<br><br>";
 }
 else
 {
 	if ($action !="deleteall") {
		?>
		Click <a href="readmail.php?action=deleteall&kk=<? echo date("U"); ?>">here</a> to delete all messages.<br>
		<?
	}
TEXT_GOTOMAIN();
$cur_D = date("Y-m-d");
$cur_T = date("H:i:s");
$line_counter = true;
while(!$res->EOF)
  {
   $from++;
   $msg = $res->fields;
   $result = $db->Execute("SELECT * FROM $dbtables[players] WHERE player_id='".$msg[sender_id]."'");
   $sender = $result->fields;
   $result = $db->Execute ("SELECT * FROM $dbtables[ships] WHERE player_id=$sender[player_id] AND ship_id=$sender[currentship]");
   $shipinfo=$result->fields;

?>

<table width=100%>
  <TR BGCOLOR="<? echo $color_line1; ?>"> 
    <TD><b>From: 
      <? 
if ($playerinfo[player_id]==1 && $sender[subscribed]=="subscr_payment") {
	echo "[Subscriber #".$sender[player_id]."] ";
}

echo $sender[character_name]; ?>
      - 
      <? echo $l_readm_captn ?>
      <? echo $shipinfo[ship_name] ?>
      </b></td>
  </TR>
  <TR BGCOLOR="<?
if ($line_counter)
{
 echo $color_line2;
 $line_counter = false;
}
else
{
 echo $color_line1;
 $line_counter = true;
}
?>"> 
    <TD VALIGN=TOP><b>Subject:</b> <b> 
      <? echo $msg[subject]; ?>
      </b></TD>
	</tr>
	<tr>
    <TD> <b>Date:</b> 
      <? echo "<font size=-1>$msg[sent]</font>" ?>
    </TD>
  </TR>
  <TR BGCOLOR="<?
if ($line_counter)
{
 echo $color_line2;
 $line_counter = false;
}
else
{
 echo $color_line1;
 $line_counter = true;
}
?>"> 
    <TD> 
      <hr>
      <? echo nl2br($msg[message]); ?>
      <hr>
        <a href="readmail.php?action=delete&ID=<? echo $msg[ID]; ?>"> 
        <? echo $l_readm_del ?>
        </a>&nbsp;&nbsp;<a href="mailto2.php?kk=<? echo date("U"); ?>&name=<? echo urlencode($sender[character_name]); ?>&subject=<? echo urlencode($msg[subject]) ?>"> 
        <? echo $l_readm_repl ?>
        </a><br>
     <hr>
    </TD>
  </TR>
  </TABLE>
  <?
    $res->MoveNext();
  }
  if ($start > 0) {
  	echo "<a href=readmail.php?from=0&kk=".date("U").">Latest Messages</a><br>"; 
  	echo "<a href=readmail.php?from=".($start-5)."&kk=".date("U")."><< Previous 5 messages</a>   ";
  }
  echo "<a href=readmail.php?from=$from&kk=".date("U").">Next 5 messages >></a><br><br>";
 }

TEXT_GOTOMAIN();

include("footer.php");
?>
