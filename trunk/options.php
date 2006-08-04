<?

include("config.php");
include("languages/$lang");
updatecookie();

$title="$l_opt_title";
include("header.php");

connectdb();

if(checklogin())
{
  die();
}

bigtitle();

//-------------------------------------------------------------------------------------------------

$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
$res2 = $db->Execute("SELECT * FROM $dbtables[profile] WHERE player_id='$playerinfo[player_id]'");
$profile = $res2->fields;
//-------------------------------------------------------------------------------------------------
?>
<FORM ACTION=option2.php METHOD=POST>
  <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2>
    <TR BGCOLOR="<?echo $color_header; ?>"> 
      <TD COLSPAN=2><B> 
        <? echo $l_opt_chpass; ?>
        </B></TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD> 
        <? echo $l_opt_curpass; ?>
      </TD>
      <TD> 
        <INPUT TYPE=PASSWORD NAME=oldpass SIZE=16 MAXLENGTH=16 VALUE="">
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line2; ?>"> 
      <TD> 
        <? echo $l_opt_newpass; ?>
      </TD>
      <TD> 
        <INPUT TYPE=PASSWORD NAME=newpass1 SIZE=16 MAXLENGTH=16 VALUE="">
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD> 
        <? echo $l_opt_newpagain; ?>
      </TD>
      <TD> 
        <INPUT TYPE=PASSWORD NAME=newpass2 SIZE=16 MAXLENGTH=16 VALUE="">
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_header; ?>"> 
      <TD COLSPAN=2><B>Email Alerts</B></TD>
    </TR>
    <? $alerts = ($playerinfo['alerts'] == 'Y') ? "CHECKED" : ""; ?>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD>Send email alerts?</TD>
      <TD> 
        <INPUT TYPE=CHECKBOX NAME=alerts VALUE=Y <? echo $alerts; ?>>
      </TD>
    </TR>
    <? $alert2 = ($playerinfo['alert2'] == 'Y') ? "CHECKED" : ""; ?>
	<TR BGCOLOR="<? echo $color_line2; ?>"> 
      <TD>Send email alerts for Furangee Trades?</TD>
      <TD> 
        <INPUT TYPE=CHECKBOX NAME=alert2 VALUE=Y <? echo $alert2; ?>>
      </TD>
    </TR>

    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD><b>Profile:</b></TD>
      <TD>&nbsp;</TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD>My skill level:</TD>
      <TD> 
        <select name="skill">
          <option value="No Comment" <? if ($profile[skill] == "No Comment") {echo "selected";}?>>No Comment</option>
          <option value="Novice" <? if ($profile[skill] == "Novice") {echo "selected";}?>>Novice</option>
          <option value="Intermediate" <? if ($profile[skill] == "Intermediate") {echo "selected";}?>>Intermediate</option>
          <option value="Expert" <? if ($profile[skill] == "Expert") {echo "selected";}?>>Expert</option>
        </select>
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD>My alignment:</TD>
      <TD> 
        <select name="alignment">
          <option value="No Comment" <? if ($profile[alignment] == "No Comment") {echo "selected";}?>>No Comment</option>
          <option value="Chaotic Evil" <? if ($profile[alignment] == "Chaotic Evil") {echo "selected";}?>>Chaotic Evil</option>
          <option value="Evil" <? if ($profile[alignment] == "Evil") {echo "selected";}?>>Evil</option>
          <option value="Neutral" <? if ($profile[alignment] == "Neutral") {echo "selected";}?>>Neutral</option>
          <option value="Good" <? if ($profile[alignment] == "Good") {echo "selected";}?>>Good</option>
          <option value="Chaotic Good" <? if ($profile[alignment] == "Chaotic Good") {echo "selected";}?>>Chaotic Good</option>
        </select>
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD colspan="2"> 
        <div align="center"><b>Background Story</b></div>
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD colspan="2">
        <div align="center">
          <textarea name="story" cols="60" rows="10" wrap="VIRTUAL"><? echo stripslashes($profile[story]);?></textarea>
        </div>
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD colspan="2"> 
        <div align="center"><b>Picture URL</b></div>
      </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>"> 
      <TD colspan="2">Provide a link to a picture of your character here. </TD>
    </TR>
    <TR BGCOLOR="<? echo $color_line1; ?>">
      <TD colspan="2"> 
        <div align="center">
          <input type="text" name="pic_url" maxlength="200" value="<? echo $profile[pic_url];?>" size="60">
        </div>
      </TD>
    </TR>
  </TABLE>
  <div align="left"><BR>
    <INPUT TYPE=SUBMIT value=<? echo $l_opt_save; ?>>
  </div>
</FORM>
<?
TEXT_GOTOMAIN();

include("footer.php");

?>

