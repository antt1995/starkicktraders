<?php 
include("config.php");

if(empty($lang))
  $lang=$default_lang;

$found = 0;
if(!empty($newlang))
{
  if(!preg_match("/^[\w]+$/", $lang)) 
  {
     $lang = $default_lang;

  }
  foreach($avail_lang as $key => $value)
  {
    if($newlang == $value[file])
    {
      $lang=$newlang;
      //SetCookie("lang",$lang,time()+(3600*24),$gamepath,$gamedomain);
      $found = 1;
      break;
    }
  }

  if($found == 0)
    $lang = $default_lang;

  $lang = $lang . ".inc";
}

include("languages/$lang");

$title="StarKick Traders Login";

include("header.php");
?>
<CENTER>
<form action="login2.php" method="post">
<input type=hidden name=kk value=<? echo date("U");?>>
<?php
if ($browser=="treo" || $browser == "up") {
?>
<div align="center"><img src="images/startitle.gif" width="145" height="18"></div>
    <TABLE CELLPADDING="0" width="100%">
      <TR> 
        <TD align="center"> 
          <? echo $l_login_email; ?>
          <input type="hidden" name="kk" value="<? echo date("U"); ?>">
          <br>
          <INPUT TYPE="TEXT" NAME="email" SIZE="20" MAXLENGTH="40" VALUE="<?php echo "$username" ?>">
        </TD>
      </TR>
      <TR> 
        <TD align="center"> 
          <? echo $l_login_pw;?>
          <br>
          <INPUT TYPE="text" NAME="pass" SIZE="20" MAXLENGTH="20" VALUE="<?php echo "$password" ?>">
        </TD>
      </TR>
      <TR> 
        <TD align="center"> 
          <input type="SUBMIT" value="<? echo $l_login_title;?>" name="SUBMIT">
        </TD>
      </TR>
      <TR>
        <TD align="center">
<p>&nbsp;</p>
          </TD>
      </TR>
      </TABLE>
    <?php
} else {
?>
    <TABLE CELLPADDING="0">
      <TR> 
        <TD align="right" rowspan="4"><font face="Geneva, Arial, Helvetica, san-serif"><img src="images/starkick.jpg" width="110" height="140"> 
          </font></TD>
        <TD align="center"> <font face="Geneva, Arial, Helvetica, san-serif">
          <? echo $l_login_email; ?>
          <input type="hidden" name="kk" value="<? echo date("U"); ?>">
          <br>
          <INPUT TYPE="TEXT" NAME="email" SIZE="20" MAXLENGTH="40" VALUE="<?php echo "$username" ?>">
          </font></TD>
      </TR>
      <TR> 
        <TD align="center"> <font face="Geneva, Arial, Helvetica, san-serif">
          <? echo $l_login_pw;?>
          <br>
          <INPUT TYPE="password" NAME="pass" SIZE="20" MAXLENGTH="20" VALUE="<?php echo "$password" ?>">
          </font></TD>
      </TR>
      <TR> 
        <TD align="center"> <font face="Geneva, Arial, Helvetica, san-serif">
          <input type="SUBMIT" value="<? echo $l_login_title;?>" name="SUBMIT">
          </font></TD>
      </TR>
      <TR> 
        <TD align="center"><font face="Geneva, Arial, Helvetica, san-serif"><b><a href="intro.html">New 
          Player?</a></b></font></TD>
      </TR>
    </TABLE>
    <font face="Geneva, Arial, Helvetica, san-serif">
    <?php
	}
?>
    </font>
    <CENTER>
      <p><font face="Geneva, Arial, Helvetica, san-serif">Forgot your password? 
        Enter it blank and press login.<br>
        <a href="http://www.mpgames.com/skt2">Starkick Traders LE 2 is here!</a></font>
      <p><font face="Geneva, Arial, Helvetica, san-serif"><b>New Prototype Ships:</b></font>
      <p><font face="Geneva, Arial, Helvetica, san-serif"><b>Proto 1</b> [<a href="../images/proto1-small.htm" target="_blank">small</a>] 
        [<a href="../images/proto1-med.htm">med</a>] [<a href="../images/proto1-huge.htm">huge</a>]<br>
        <b>Proto 2</b> [<a href="../images/proto2-small.htm">small</a>] [<a href="../images/proto2-med.htm">med</a>] 
        [<a href="../images/proto2-huge.htm">huge</a>]</font>
      <p><font face="Geneva, Arial, Helvetica, san-serif">These ships are yet 
        to be assigned names and stats - <a href="http://www.mpgames.com/forums/viewtopic.php?t=357">check 
        out the forum thread</a> for info</font>
      <p><BR>
        <? echo $l_login_prbs;?>
        <A HREF="mailto:<?php echo "$admin_mail"?>"> 
        <? echo $l_login_emailus;?>
        </A> </p>
    </CENTER>
  </FORM>
  <p><br>
    <?php
if(!empty($link_forums))
  echo "<A HREF=\"$link_forums\" TARGET=\"_blank\">$l_forums</A> - ";
?>
    <A HREF="ranking.php"> 
    <? echo $l_rankings;?>
    </A> 
    <? echo " - "; ?>
    <A HREF="settings.php"> 
    <? echo $l_login_settings;?>
    </A> </p>
  <p>&nbsp;</p>
</CENTER>

<?php
include("footer.php");
?>
