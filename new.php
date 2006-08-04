<?
include("config.php");
include("languages/$lang");

$title=$l_new_title;
include("header.php");

  bigtitle();
?> 
<form action="new2.php" method="post">
 <center>
    <table  width="" border="0" cellspacing="0" cellpadding="4">
      <tr> 
        <td> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          <? echo $l_login_email;?>
          </font></td>
        <td> 
          <input type="text" name="username" size="20" maxlength="40" value="">
        </td>
      </tr>
      <tr> 
        <td> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          <? echo $l_new_shipname; ?>
          : </font></td>
        <td> 
          <input type="text" name="shipname" size="20" maxlength="20" value="">
        </td>
      </tr>
      <tr> 
        <td> <font face="Verdana, Arial, Helvetica, sans-serif" size="2"> 
          <? echo $l_new_pname;?>
          : </font></td>
        <td> 
          <input type="text" name="character" size="20" maxlength="20" value="">
        </td>
      </tr>
      <tr> 
        <td><font face="Verdana, Arial, Helvetica, sans-serif" size="2">I have 
          read and agreed to the<br>
          <a href="agreement.html">StarKick Traders Usage agreement</a>:</font></td>
        <td> 
          <input type="checkbox" name="checkbox" value="yes" checked>
        </td>
      </tr>
    </table>
    <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2">We highly recommend 
      you use your <b>@tmail.com</b> email address so you can receive game event 
      alerts immendiately on your Sidekick.<br>
      (Alerts can be switched off in the Options menu)</font><br>
      <br>
      <input type="submit" value="<? echo $l_submit;?>">
      <input type="reset" value="<? echo $l_reset;?>">
      <br>
    </p>
    <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>SKT is free 
      to play for ships with up to level 8 techs.<br>
      </b></font><br>
      <font face="Verdana, Arial, Helvetica, sans-serif" size="2">Get a subscription 
      to StarKick Traders though and enjoy the following: </font> </p>
    <ul>
      <li><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Access 
        to level 9 and above upgrades!</b></font></li>
      <li><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Buy bigger 
        and better ships!</font></li>
      <li><font face="Verdana, Arial, Helvetica, sans-serif" size="2"><b>Free 
        cloning when you die!</b></font></li>
    </ul>
    <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Subscriptions 
      are handled by PayPal and are secure. </font> 
    <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2">Cost: $3.99 
      for four weeks or $9.99 for twelve weeks!</font>
    <p><font face="Verdana, Arial, Helvetica, sans-serif" size="2">See Main Menu 
      in the game for more details!</font>
    <p><br>
      <? echo $l_new_info;?>
      <br>
    </p>
    </center>
</form>

<? include("footer.php"); ?>
