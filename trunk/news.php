<?
include("config.php");
include("includes/newsservices.php");

include("languages/$lang");
$title=$l_news_title;
include("header.php");

connectdb();

//Check to see if the date was passed in the query string
if ($startdate == '')
{
    //The date wasn't supplied so use today's date
    $startdate = date("Y/m/d");
}

$previousday = getpreviousday($startdate);
$nextday = getnextday($startdate);
?>
 
<table border="0" cellspacing="2" cellpadding="2">
  <tr> 
    <td><img src="images/bnnhead.gif" width="312" height="123" alt="SUN"></td><td align=right>
<script type="text/javascript"><!--
google_ad_client = "pub-2030062931737666";
google_ad_width = 234;
google_ad_height = 60;
google_ad_format = "234x60_as";
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
</td>
  </tr>
  <tr> 
    <td colspan=2 bgcolor="#00001A"> 
      <div align="center"><?php echo $l_news_info?><br>
        <b><?php echo $l_news_for ?> <?php echo $startdate?></b>
      </div>
    </td>
  </tr>
      <td bgcolor="#00001A" align="left"><a href="news.php?startdate=<?php echo $previousday ?>"><?php echo $l_news_prev ?></a></td>
	  <td bgcolor="#00001A" align="right"><a href="news.php?startdate=<?php echo $nextday ?>"><?php echo $l_news_next ?></a></td>
  </tr>
  <?php


//Select news for date range
$res = $db->Execute("SELECT * from $dbtables[news] where date = '$startdate' order by news_id desc");

//Check to see if there was any news to be shown
if($res->EOF)
{

    //No news
    echo "<tr><td colspan=2 bgcolor=\"#00001A\" align=\"center\">$l_news_flash</td></tr><tr><td colspan=2 bgcolor=\"#00001A\" align=\"center\">$l_news_none</td></tr></table><p align=left>";

    //Display link to the main page
    TEXT_GOTOMAIN();
    die();
}

while (!$res->EOF) {
  $row = $res->fields;
?>
  <tr> 
    <td colspan=2 bgcolor="#000033" align="center"><font size="3"><b> 
      <?php echo $row[headline]?>
      </b></font> </td>
  </tr>
  <tr>
    <td colspan=2 bgcolor="#000033" align="center">
      <?php echo $row[newstext]?>
    </td>
  </tr>
  <tr><td colspan=2>&nbsp;</td></tr>
  <?php
  $res->MoveNext();
}
?>
</table>
<p align=left>
<?php
if(empty($username))
{
  TEXT_GOTOLOGIN();
}
else
{
  TEXT_GOTOMAIN();
}
?>
