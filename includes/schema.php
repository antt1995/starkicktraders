<?
/*************************************************************************
schema.php - generates the tables used in the game database
Copyright (c)2003-2004 Ben Gibbs
Copyright (c)2000-2002 Ron Harwood

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
function create_schema($dbtables)
{
/*********************************************************
If you add/remove a table, don't forget to update the
table name variables in the global_func file.
*********************************************************/

global $maxlen_password;
//global $dbtables;
global $db;

function db_create_result()
{
global $db;

 if ($db)
 {
  echo "<td><font color=\"lime\">- created successfully.</font></td></tr><br>\n";
 }
 else
 {
  echo "<td><font color=\"red\">- failed to create. error code: $db.</font></td></tr><br>\n";
 }

}

// Delete all tables in the database
echo "<b>Dropping all tables </b><BR>";
foreach ($dbtables as $table => $tablename)
{
  echo "Dropping $table ";
//  $db->debug = true;
  $query = $db->Execute("DROP TABLE $tablename");
  if ($query)
  {
   echo "<td><font color=\"lime\">- dropped successfully.</font></td></tr><br>\n";
  }
  else
  {
   echo "<td><font color=\"red\">- failed to drop. error code: $query.</font></td></tr><br>\n";
  }
}
echo "<b>All tables have been successfully dropped.</b><p>";

// Create database schema
echo "<b>Creating tables </b><BR>";
echo "Creating table: links ";
$db->Execute("CREATE TABLE $dbtables[links] (" .
             "link_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "link_start int unsigned DEFAULT '0' NOT NULL," .
             "link_dest int unsigned DEFAULT '0' NOT NULL," .
             "PRIMARY KEY (link_id)," .
             "KEY link_start (link_start)," .
             "KEY link_dest (link_dest)" .
             ")");
db_create_result();

echo "Creating table: planets ";
$db->Execute("CREATE TABLE $dbtables[planets](" .
             "planet_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "sector_id int unsigned DEFAULT '0' NOT NULL," .
             "name tinytext," .
             "organics bigint(20) DEFAULT '0' NOT NULL," .
             "ore bigint(20) DEFAULT '0' NOT NULL," .
             "goods bigint(20) DEFAULT '0' NOT NULL," .
             "energy bigint(20) DEFAULT '0' NOT NULL," .
             "colonists bigint(20) DEFAULT '0' NOT NULL," .
             "credits bigint(20) DEFAULT '0' NOT NULL," .
             "fighters bigint(20) DEFAULT '0' NOT NULL," .
             "torps bigint(20) DEFAULT '0' NOT NULL," .
             "owner int unsigned DEFAULT '0' NOT NULL," .
             "corp int unsigned DEFAULT '0' NOT NULL," .
             "base enum('Y','N') DEFAULT 'N' NOT NULL," .
             "sells enum('Y','N') DEFAULT 'N' NOT NULL," .
             "prod_organics float(5,2) unsigned DEFAULT '20.0' NOT NULL," .
             "prod_ore float(5,2) unsigned DEFAULT '20.0' NOT NULL," .
             "prod_goods float(5,2) unsigned DEFAULT '20.0' NOT NULL," .
             "prod_energy float(5,2) unsigned DEFAULT '20.0' NOT NULL," .
             "prod_fighters float(5,2) unsigned DEFAULT '10.0' NOT NULL," .
             "prod_torp float(5,2) unsigned DEFAULT '10.0' NOT NULL," .
             "defeated enum('Y','N') DEFAULT 'N' NOT NULL," .
			 "tech_level int(11) NOT NULL default '0'," .
             "PRIMARY KEY (planet_id)," .
             "KEY owner (owner)," .
             "KEY corp (corp)" .
             ")");
db_create_result();

echo "Creating table: traderoutes ";
$db->Execute("CREATE TABLE $dbtables[traderoutes](
			  traderoute_id int(10) unsigned NOT NULL auto_increment,
			  source_id int(10) unsigned NOT NULL default '0',
			  dest_id int(10) unsigned NOT NULL default '0',
			  source_type enum('P','L','C','D','S') NOT NULL default 'P',
			  dest_type enum('P','L','C','D') NOT NULL default 'P',
			  move_type enum('R','W') NOT NULL default 'W',
			  owner int(10) unsigned NOT NULL default '0',
			  circuit enum('1','2') NOT NULL default '2',
			  PRIMARY KEY  (traderoute_id),
			  KEY owner (owner)" .
             ")");
db_create_result();

echo "Creating table: players ";
$db->Execute("CREATE TABLE $dbtables[players](" .
             "  player_id int(10) unsigned NOT NULL auto_increment,
			  character_name varchar(20) NOT NULL default '',
			  password varchar(16) NOT NULL default '',
			  email varchar(60) NOT NULL default '',
			  credits bigint(20) NOT NULL default '0',
			  sector int(10) unsigned NOT NULL default '0',
			  turns smallint(4) NOT NULL default '0',
			  on_planet enum('Y','N') NOT NULL default 'N',
			  turns_used int(10) unsigned NOT NULL default '0',
			  last_login datetime default NULL,
			  rating bigint(20) NOT NULL default '0',
			  score int(11) NOT NULL default '0',
			  team int(11) NOT NULL default '0',
			  team_invite int(11) NOT NULL default '0',
			  interface enum('N','O') NOT NULL default 'N',
			  ip_address tinytext NOT NULL,
			  planet_id int(10) unsigned NOT NULL default '0',
			  preset1 int(11) NOT NULL default '0',
			  preset2 int(11) NOT NULL default '0',
			  preset3 int(11) NOT NULL default '0',
			  trade_colonists enum('Y','N') NOT NULL default 'Y',
			  trade_fighters enum('Y','N') NOT NULL default 'N',
			  trade_torps enum('Y','N') NOT NULL default 'N',
			  trade_energy enum('Y','N','D') NOT NULL default 'Y',
			  cleared_defences tinytext,
			  lang varchar(30) NOT NULL default 'english',
			  alerts enum('Y','N') NOT NULL default 'Y',
			  alert2 enum('Y','N') NOT NULL default 'Y',
			  subscribed varchar(20) default NULL,
			  ore_price float NOT NULL default '1',
			  organics_price float NOT NULL default '1',
			  goods_price float NOT NULL default '1',
			  energy_price float NOT NULL default '1',
			  currentship smallint(6) NOT NULL default '1',
			  preset4 mediumint(9) NOT NULL default '0',
			  preset5 mediumint(9) NOT NULL default '0',
			  preset6 mediumint(9) NOT NULL default '0',
			  PRIMARY KEY  (email),
			  KEY email (email),
			  KEY sector (sector),
			  KEY on_planet (on_planet),
			  KEY team (team),
			  KEY ship_id (player_id)
			" .
             ")");
db_create_result();

echo "Creating table: universe ";
$db->Execute("CREATE TABLE $dbtables[universe](" .
             "sector_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "sector_name tinytext," .
             "zone_id int DEFAULT '0' NOT NULL," .
             "port_type enum('ore','organics','goods','energy','special','none') DEFAULT 'none' NOT NULL," .
             "port_organics bigint(20) DEFAULT '0' NOT NULL," .
             "port_ore bigint(20) DEFAULT '0' NOT NULL," .
             "port_goods bigint(20) DEFAULT '0' NOT NULL," .
             "port_energy bigint(20) DEFAULT '0' NOT NULL," .
             "KEY zone_id (zone_id)," .
             "KEY port_type (port_type)," .
             "beacon tinytext," .
             "x bigint(20) DEFAULT '0' NOT NULL," .
             "y bigint(20) DEFAULT '0' NOT NULL," .
             "z bigint(20) DEFAULT '0' NOT NULL," .
             "tech_level tinyint(4) NOT NULL default '0'," .
             "PRIMARY KEY (sector_id)" .
             ")");
db_create_result();

echo "Creating table: zones ";
$db->execute("CREATE TABLE $dbtables[zones](" .
             "zone_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "zone_name tinytext," .
             "owner int unsigned DEFAULT '0' NOT NULL," .
             "corp_zone enum('Y', 'N') DEFAULT 'N' NOT NULL," .
             "allow_beacon enum('Y','N','L') DEFAULT 'Y' NOT NULL," .
             "allow_attack enum('Y','N') DEFAULT 'Y' NOT NULL," .
             "allow_planetattack enum('Y','N') DEFAULT 'Y' NOT NULL," .
             "allow_warpedit enum('Y','N','L') DEFAULT 'Y' NOT NULL," .
             "allow_planet enum('Y','L','N') DEFAULT 'Y' NOT NULL," .
             "allow_trade enum('Y','L','N') DEFAULT 'Y' NOT NULL," .
             "allow_defenses enum('Y','L','N') DEFAULT 'Y' NOT NULL," .
             "max_hull int DEFAULT '0' NOT NULL," .
             "PRIMARY KEY(zone_id)," .
             "KEY zone_id(zone_id)" .
             ")");
db_create_result();

echo "Creating table: ibank_accounts ";
$db->Execute("CREATE TABLE $dbtables[ibank_accounts](" .
             "player_id int DEFAULT '0' NOT NULL," .
             "balance bigint(20) DEFAULT '0'," .
             "loan bigint(20)  DEFAULT '0'," .
             "loantime TIMESTAMP(14)," .
       "PRIMARY KEY(player_id)" .
             ")");
db_create_result();

echo "Creating table: ibank_statement ";
$db->Execute("CREATE TABLE $dbtables[ibank_statement](" .
			"player_id int(11) NOT NULL default '0'," .
  			"amount bigint(20) NOT NULL default '0'," .
  			"trans_type smallint(6) NOT NULL default '0'," .
  			"trans_time timestamp(14) NOT NULL" .
			") TYPE=MyISAM COMMENT='Stores bank transactions'");
db_create_result();

echo "Creating table: IGB_transfers ";
$db->Execute("CREATE TABLE $dbtables[IGB_transfers](" .
             "transfer_id int DEFAULT '0' NOT NULL auto_increment," .
             "source_id int DEFAULT '0' NOT NULL," .
             "dest_id int DEFAULT '0' NOT NULL," .
             "time TIMESTAMP(14)," .
             "PRIMARY KEY(transfer_id)" .
             ")");
db_create_result();

echo "Creating table: teams ";
$db->Execute("CREATE TABLE $dbtables[teams](" .
             "id int DEFAULT '0' NOT NULL," .
             "creator int DEFAULT '0'," .
             "team_name tinytext," .
             "description tinytext," .
             "number_of_members tinyint(3) DEFAULT '0' NOT NULL," .
             "PRIMARY KEY(id)" .
             ")");
db_create_result();

echo "Creating table: news ";
$db->Execute("CREATE TABLE $dbtables[news] (" .
             "news_id int(11) DEFAULT '0' NOT NULL auto_increment," .
             "headline varchar(100) NOT NULL," .
             "newstext text NOT NULL," .
             "user_id int(11)," .
             "date timestamp(8)," .
             "news_type varchar(10)," .
             "PRIMARY KEY (news_id)," .
             "KEY news_id (news_id)," .
             "UNIQUE news_id_2 (news_id)" .
             ")");
db_create_result();

echo "Creating table: internal messaging ";
$db->Execute("CREATE TABLE $dbtables[messages] (" .
             "ID int NOT NULL auto_increment," .
             "sender_id int NOT NULL default '0'," .
             "recp_id int NOT NULL default '0'," .
             "subject varchar(250) NOT NULL default ''," .
             "sent varchar(19) NULL," .
             "message longtext NOT NULL," .
             "notified enum('Y','N') NOT NULL default 'N'," .
             "PRIMARY KEY  (ID) " .
             ") TYPE=MyISAM");
db_create_result();

echo "Creating table: furangee ";
$db->Execute("CREATE TABLE $dbtables[furangee](" .
             "  furangee_id char(40) NOT NULL default '',
			  active enum('Y','N') NOT NULL default 'Y',
			  aggression smallint(5) NOT NULL default '0',
			  orders smallint(5) NOT NULL default '0',
			  prefer enum('ore','organics','goods','none') NOT NULL default 'none',
			  PRIMARY KEY  (furangee_id),
			  KEY furangee_id (furangee_id)" .
             ")");
db_create_result();

echo "Creating table: sector_defence ";
$db->Execute("CREATE TABLE $dbtables[sector_defence](" .
             "defence_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "player_id int DEFAULT '0' NOT NULL," .
             "sector_id int unsigned DEFAULT '0' NOT NULL," .
             "defence_type enum('M','F') DEFAULT 'M' NOT NULL," .
             "quantity bigint(20) DEFAULT '0' NOT NULL," .
             "fm_setting enum('attack','toll') DEFAULT 'toll' NOT NULL," .
             "PRIMARY KEY (defence_id)," .
             "KEY sector_id (sector_id)," .
             "KEY player_id (player_id)" .
             ")");
db_create_result();

echo "Creating table: scheduler ";
$db->Execute("CREATE TABLE $dbtables[scheduler](" .
             "sched_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "loop enum('Y','N') DEFAULT 'N' NOT NULL," .
             "ticks_left int unsigned DEFAULT '0' NOT NULL," .
             "ticks_full int unsigned DEFAULT '0' NOT NULL," .
             "spawn int unsigned DEFAULT '0' NOT NULL," .
             "file varchar(30) NOT NULL," .
             "extra_info varchar(50) NOT NULL," .
             "last_run BIGINT(20)," .
             "PRIMARY KEY (sched_id)" .
             ")");
db_create_result();

echo "Creating table: ip_bans ";
$db->Execute("CREATE TABLE $dbtables[ip_bans](" .
             "ban_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "ban_mask varchar(16) NOT NULL," .
             "PRIMARY KEY (ban_id)" .
             ")");
db_create_result();

echo "Creating table: logs ";
$db->Execute("CREATE TABLE $dbtables[logs](" .
             "  log_id int(10) unsigned NOT NULL auto_increment,
			  player_id int(11) NOT NULL default '0',
			  type mediumint(5) NOT NULL default '0',
			  time timestamp(14) NOT NULL,
			  data text,
			  furangee enum('Y','N') NOT NULL default 'N',
			  PRIMARY KEY  (log_id),
			  KEY idate (player_id,time)" .
             ")");
db_create_result();

echo "Creating table: bounty ";
$db->Execute("CREATE TABLE $dbtables[bounty] (" .
             "bounty_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "amount bigint(20) unsigned DEFAULT '0' NOT NULL," .
             "bounty_on int unsigned DEFAULT '0' NOT NULL," .
             "placed_by int unsigned DEFAULT '0' NOT NULL," .
             "PRIMARY KEY (bounty_id)," .
             "KEY bounty_on (bounty_on)," .
             "KEY placed_by (placed_by)" .
             ")");
db_create_result();

echo "Creating table: movement_log ";
$db->Execute("CREATE TABLE $dbtables[movement_log](" .
             "event_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
             "player_id int DEFAULT '0' NOT NULL," .
             "sector_id int DEFAULT '0'," .
             "time TIMESTAMP(14) ," .
             "PRIMARY KEY (event_id)," .
             "KEY player_id(player_id)," .
             "KEY sector_id (sector_id)" .
             ")");
db_create_result();
/*
echo "Creating table: planet_log ";
$db->Execute("CREATE TABLE $dbtables[planet_log](" .
              "planetlog_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
              "planet_id int DEFAULT '0' NOT NULL," .
              "player_id int DEFAULT '0' NOT NULL," .
              "owner_id int DEFAULT '0' NOT NULL," .
              "ip_address tinytext NOT NULL," .
              "action int DEFAULT '0' NOT NULL," .
              "time TIMESTAMP(14) ," .
              "PRIMARY KEY (planetlog_id)," .
              "KEY planet_id (planet_id)" .
              ")");
db_create_result();

echo "Creating table: ip_log ";
$db->Execute("CREATE TABLE $dbtables[ip_log](" .
              "log_id int unsigned DEFAULT '0' NOT NULL auto_increment," .
              "player_id int DEFAULT '0' NOT NULL," .
              "ip_address tinytext NOT NULL," .
              "time TIMESTAMP(14) ," .
              "PRIMARY KEY (log_id)," .
              "KEY ship_id (player_id)" .
              ")");
db_create_result();

echo "Creating planet/ip address index";
$db->Execute("ALTER table $dbtables[ip_log] ADD INDEX planet_id (ip_address(15))");
db_create_result();
*/
echo "Creating table: ship_types ";
$db->Execute("CREATE TABLE $dbtables[ship_types] (" .
             "type_id int unsigned DEFAULT '1' NOT NULL," .
             "name char(20)," .
             "image char(20)," .
             "description text," .
             "buyable enum('Y','N') DEFAULT 'Y' NOT NULL," .
             "cost_credits bigint(20) unsigned DEFAULT '0' NOT NULL," .
             "cost_ore bigint(20) unsigned DEFAULT '0' NOT NULL," .
             "cost_goods bigint(20) unsigned DEFAULT '0' NOT NULL," .
             "cost_energy bigint(20) unsigned DEFAULT '0' NOT NULL," .
             "cost_organics bigint(20) unsigned DEFAULT '0' NOT NULL," .
             "turnstobuild int unsigned DEFAULT '0' NOT NULL," .
             "minhull tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxhull tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "minengines tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxengines tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "minpower tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxpower tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "mincomputer tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxcomputer tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "minsensors tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxsensors tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "minbeams tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxbeams tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "mintorp_launchers tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxtorp_launchers tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "minshields tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxshields tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "minarmour tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxarmour tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "mincloak tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "maxcloak tinyint(3) unsigned DEFAULT '0' NOT NULL," .
			 "tech_level bigint(20) NOT NULL default '0'," .
             "PRIMARY KEY (type_id)" .
             ")");
db_create_result();

echo "Creating table: ships ";
$db->Execute("CREATE TABLE $dbtables[ships](" .
             "  ship_id int(10) unsigned NOT NULL auto_increment,
				  player_id int(11) NOT NULL default '0',
				  type smallint(6) NOT NULL default '1',
				  ship_name varchar(20) default NULL,
				  ship_destroyed enum('Y','N') NOT NULL default 'N',
				  hull tinyint(3) unsigned NOT NULL default '0',
				  engines tinyint(3) unsigned NOT NULL default '0',
				  power tinyint(3) unsigned NOT NULL default '0',
				  computer tinyint(3) unsigned NOT NULL default '0',
				  sensors tinyint(3) unsigned NOT NULL default '0',
				  beams tinyint(3) unsigned NOT NULL default '0',
				  torp_launchers tinyint(3) NOT NULL default '0',
				  torps bigint(20) NOT NULL default '0',
				  shields tinyint(3) unsigned NOT NULL default '0',
				  armour tinyint(3) unsigned NOT NULL default '0',
				  armour_pts bigint(20) NOT NULL default '0',
				  cloak tinyint(3) unsigned NOT NULL default '0',
				  sector int(10) unsigned NOT NULL default '0',
				  ship_ore bigint(20) NOT NULL default '0',
				  ship_organics bigint(20) NOT NULL default '0',
				  ship_goods bigint(20) NOT NULL default '0',
				  ship_energy bigint(20) NOT NULL default '0',
				  ship_colonists bigint(20) NOT NULL default '0',
				  ship_fighters bigint(20) NOT NULL default '0',
				  tow int(10) NOT NULL default '0',
				  on_planet enum('Y','N') NOT NULL default 'N',
				  dev_warpedit smallint(5) NOT NULL default '0',
				  dev_genesis smallint(5) NOT NULL default '0',
				  dev_beacon smallint(5) NOT NULL default '0',
				  dev_emerwarp smallint(5) NOT NULL default '0',
				  dev_escapepod enum('Y','N') NOT NULL default 'N',
				  dev_fuelscoop enum('Y','N') NOT NULL default 'N',
				  dev_minedeflector bigint(20) NOT NULL default '0',
				  planet_id int(10) unsigned NOT NULL default '0',
				  cleared_defences tinytext,
				  dev_lssd enum('Y','N') NOT NULL default 'N',
				  dev_sectorwmd enum('Y','N') NOT NULL default 'N',
				  fur_tech enum('Y','N') NOT NULL default 'N',
				  KEY sector (sector),
				  KEY ship_destroyed (ship_destroyed),
				  KEY on_planet (on_planet),
				  KEY ship_id (ship_id)
				" .
             ")");
db_create_result();
echo "Creating table: config ";
$db->Execute("CREATE TABLE $dbtables[config] (
  server_closed enum('Y','N') NOT NULL default 'N',
  account_creation_closed enum('Y','N') NOT NULL default 'N',
  force_subscription enum('Y','N') NOT NULL default 'N',
  closed_message varchar(255) NOT NULL default 'Server temporarily closed.',
  game_num mediumint(9) NOT NULL default '0',
  furangee_num mediumint(9) NOT NULL default '0',
  special_trader_num tinyint(4) NOT NULL default '0'
) COMMENT='Configuration Settings that can change mid-game';");
db_create_result();

echo "Creating table: profile ";
$db->Execute("CREATE TABLE $dbtables[profile] (
  player_id mediumint(9) NOT NULL default '0',
  skill enum('Novice','Intermediate','Expert','No Comment') NOT NULL default 'No Comment',
  alignment enum('Chaotic Evil','Evil','Neutral','Good','Chaotic Good','No Comment') NOT NULL default 'No Comment',
  story text,
  pic_url varchar(255) default NULL,
  PRIMARY KEY  (player_id)
);");
db_create_result();

echo "Creating table: missions";
$db->Execute("CREATE TABLE $dbtables[missions] (
  mission_id mediumint(9) NOT NULL default '0',
  state tinyint(4) NOT NULL default '0',
  nextstate tinyint(4) NOT NULL default '0',
  infoin text,
  infoout text,
  sql text NOT NULL,
  maxturns mediumint(9) NOT NULL default '0'
) TYPE=MyISAM COMMENT='Missions'");
db_create_result();

echo "Creating table: mission status";
$db->Execute("CREATE TABLE $dbtables[mstatus] (
  mission_id smallint(6) NOT NULL default '0',
  player_id mediumint(9) NOT NULL default '0',
  state smallint(6) NOT NULL default '0',
  timestamp timestamp(14) NOT NULL,
  completed enum('Y','N') NOT NULL default 'N',
  turns mediumint(9) NOT NULL default '0',
  message text NOT NULL,
  var1 varchar(255) NOT NULL default '',
  var2 varchar(255) NOT NULL default '',
  var3 varchar(255) NOT NULL default ''
) TYPE=MyISAM COMMENT='Mission status'");
db_create_result();

echo "Creating table: kills";
$db->Execute("CREATE TABLE $dbtables[kills] (
  player_id int(10) NOT NULL default '0',
  pks smallint(6) NOT NULL default '0',
  fks smallint(6) NOT NULL default '0',
  deaths smallint(6) NOT NULL default '0',
  PRIMARY KEY  (player_id)
) TYPE=MyISAM COMMENT='Player kills, furangee kills, deaths'");
db_create_result();

echo "Creating table: medals";
$db->Execute("CREATE TABLE $dbtables[medals] (
  medal_name varchar(40) NOT NULL default '',
  blurb varchar(255) NOT NULL default '',
  type_id tinyint(4) NOT NULL auto_increment,
  graphic varchar(25) NOT NULL default '',
  PRIMARY KEY  (type_id)
) TYPE=MyISAM COMMENT='List of medals that can be awarded'");
db_create_result();

echo "Creating table: browser";
$db->Execute("CREATE TABLE $dbtables[browser] (
  `player_id` mediumint(9) NOT NULL default '0',
  `browser` text NOT NULL,
  KEY `player_id` (`player_id`)
) TYPE=MyISAM");
db_create_result();

echo "Creating table: scan_log";
$db->Execute("CREATE TABLE $dbtables[scan_log] (
  `event_id` int(11) NOT NULL auto_increment,
  `player_id` mediumint(9) NOT NULL default '0',
  `sector_id` mediumint(9) NOT NULL default '0',
  `time` timestamp(14) NOT NULL,
  UNIQUE KEY `log_id` (`event_id`),
  KEY `player_id` (`player_id`)
) TYPE=MyISAM COMMENT='Stores scanned sectors for map function'");
db_create_result();

/*
echo "Creating table: email_log...";
$db->Execute("CREATE TABLE $dbtables[email_log](" .
             "log_id bigint(20) unsigned DEFAULT '0' NOT NULL auto_increment," .
             "sp_name varchar(50) NOT NULL," .
             "sp_IP tinytext NOT NULL," .
             "dp_name varchar(50) NOT NULL," .
             "e_subject varchar(250)," .
             "e_status enum('Y','N') DEFAULT 'N' NOT NULL," .
             "e_type tinyint(3) unsigned DEFAULT '0' NOT NULL," .
             "e_stamp char(20)," .
             "e_response varchar(250)," .
             "PRIMARY KEY (log_id)" .
             ")");
db_create_result();

echo "Creating table: routes_headers ";
$db->Execute("CREATE TABLE $dbtables[routes_headers](" .
             "route_id int DEFAULT '0' NOT NULL auto_increment," .
             "route_name text NOT NULL," .
             "sector_id int DEFAULT '0' NOT NULL," .
             "player_id int DEFAULT '0' NOT NULL," .
             "PRIMARY KEY (route_id)," .
             "KEY (player_id)," .
             "KEY (sector_id)" .
             ")");
db_create_result();

echo "Creating table: routes_steps ";
$db->Execute("CREATE TABLE $dbtables[routes_steps](" .
             "step_id int DEFAULT '0' NOT NULL auto_increment," .
             "route_id int DEFAULT '0' NOT NULL," .
             "step int DEFAULT '0' NOT NULL," .
             "action enum('move','trade','special','defense','planet') DEFAULT 'move' NOT NULL," .
             "arguments text NOT NULL," .
             "PRIMARY KEY (step_id)," .
             "KEY (route_id)" .
             ")");
db_create_result();
*/
//Finished
echo "<b>Database schema creation completed successfully.</b><BR>";

}

?>
