<?
include("config.php");
updatecookie();

include("languages/$lang");
$title=$l_planet_title;
include("header.php");

connectdb();

if(checklogin())
{
  die();
}
include("metaplanet.php");
?>
