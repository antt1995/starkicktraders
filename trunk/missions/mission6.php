<?
/*************************************************************************
mission6.php - The 6th mission set up portion
Copyright (c)2003-2004 Ben Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
***************************************************************************/

if (preg_match("/mission/mission6.php/i", $PHP_SELF)) {
    echo "You can not access this file directly!";
    die();
}
// This is mission specific code used to setup the mission
// Mission Description
// Destroy 10 furangee. Put variables into mstatus list of generic variables
function extract_int ($str) {
 ereg ('[^0-9]*([0-9]+)[^0-9]*', $str, $regs);
 return (intval ($regs[1]));
}

$misres = $db->Execute("SELECT fks FROM $dbtables[kills] WHERE player_id=$playerinfo[player_id]");
if ($misres->EOF) {
	$var1 = 14;
} else {
	$killerinfo = $misres->fields;
	$var1 = extract_int($killerinfo[fks])+14; // # of Furangee killed to date + 10
}
// Let's put this into the database
$misres = $db->Execute("UPDATE $dbtables[mstatus] SET var1='$var1' WHERE player_id=$playerinfo[player_id] AND mission_id=$trigger[mission_id]");
?>
