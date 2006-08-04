<?php
include("config.php");
include("languages/$lang");

updatecookie();

connectdb();

$title="Refer A Friend";
include("header.php");

$flag = checklogin();
if($flag)
{
	 die();
}

//-------------------------------------------------------------------------------------------------


$res = $db->Execute("SELECT * FROM $dbtables[players] WHERE email='$username'");
$playerinfo = $res->fields;
?>
<h1>Refer a Friend </h1>
<p>Everyone likes spending time with their friends; and battling them or getting 
  them to help crush the weak makes it fun to do. So invite your friends to come 
  hang out with you in Starkick Traders!<br>
  <b>Enter your friends' email addresses</b> <br>
  (You may refer as many friends as you like.) </p>
<form name="form1" method="post" action="tell2.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr> 
      <td width="17%"> 
        <div align="right">email for friend #1:</div>
      </td>
      <td width="83%"> 
        <input type="text" name="email1">
      </td>
    </tr>
    <tr> 
      <td width="17%"> 
        <div align="right">email for friend #2:</div>
      </td>
      <td width="83%"> 
        <input type="text" name="email2">
      </td>
    </tr>
    <tr> 
      <td width="17%"> 
        <div align="right">email for friend #3:</div>
      </td>
      <td width="83%"> 
        <input type="text" name="email3">
      </td>
    </tr>
    <tr> 
      <td width="17%"> 
        <div align="right">email for friend #4:</div>
      </td>
      <td width="83%"> 
        <input type="text" name="email4">
      </td>
    </tr>
    <tr> 
      <td width="17%"> 
        <div align="right">email for friend #5:</div>
      </td>
      <td width="83%"> 
        <input type="text" name="email5">
      </td>
    </tr>
    <tr> 
      <td width="17%"> 
        <div align="right"></div>
      </td>
      <td width="83%">this appears in the subject line of your email</td>
    </tr>
    <tr> 
      <td width="17%"> 
        <div align="right">Enter your name:</div>
      </td>
      <td width="83%"> 
        <input type="text" name="name">
      </td>
    </tr>
    <tr>
      <td width="17%">&nbsp;</td>
      <td width="83%">
        <input type="submit" name="Submit" value="Submit">
      </td>
    </tr>
  </table>
  <p>&nbsp; </p>
  </form>
<?
TEXT_GOTOMAIN();
include("footer.php");

?>
