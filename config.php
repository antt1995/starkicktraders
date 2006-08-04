<?
include("config_local.php");
include("$ADOdbpath" . "/adodb.inc.php");

/* Main scheduler variables (game flow control)
-----------------------------------------------*/

/*
  Set this to how often (in minutes) you are running
  the scheduler script.
*/
$sched_ticks = 2;

/* All following vars are in minutes.
   These are TRUE minutes, no matter to what interval
   you're running the scheduler script! The scheduler
   will auto-adjust, possibly running many of the same
   events in a single call.
*/
$sched_turns = 2;    //New turns rate (also includes towing, furangee)
$sched_ports = 2;    //How often port production occurs
$sched_planets = 2;  //How often planet production occurs
$sched_IGB = 2;      //How often IGB interests are added
$sched_ranking = 10; //How often rankings will be generated
$sched_news = 2;    //How often news is generated
$sched_degrade = 6;  //How often sector fighters degrade when unsupported by a planet
$sched_apocalypse = 15;
$doomsday_value = 200000000; // number of colonists a planet needs before being affected by the apocalypse
$sched_mooring = 2; // Check for mooring fee payments every n minutes

/* Scheduler config end */

/* GUI colors (temporary until we have something nicer) */

$color_header = "#005000";
$color_line1 = "#003000";
$color_line2 = "#004000";
/*
$color_header = "#663399";
$color_line1 = "#300030";
$color_line2 = "#333399";
*/
/* Localization (regional) settings */
$local_number_dec_point = ".";
$local_number_thousands_sep = ", ";
$language = "english";

/* game variables */
$fed_dock_max = 20000;
$ip = getenv("REMOTE_ADDR");
$mine_hullsize = 8; //Minimum size hull has to be to hit mines 
$ewd_maxhullsize = 15; //Max hull size before EWD degrades
$sector_max = 5000;
$link_max=10;
$universe_size =12000;
$game_name = "Starkick Traders 5.0.3";
$fed_max_hull = 8;
$maxlen_password = 16;
$max_rank=50;				// Used on ranking page but not used right now.
$rating_combat_factor=.8;    //ammount of rating gained from combat
$server_closed=false;        //true = block logins but not new account creation
$account_creation_closed=false;    //true = block new account creation
$force_subscription=false; // true = block access from PC's unless subscribed
$max_team_members = 5;

/* newbie niceness variables */
$newbie_nice = "YES";
$newbie_extra_nice = "YES";
$newbie_hull = "8";
$newbie_engines = "8";
$newbie_power = "8";
$newbie_computer = "8";
$newbie_sensors = "8";
$newbie_armour = "8";
$newbie_shields = "8";
$newbie_beams = "8";
$newbie_torp_launchers = "8";
$newbie_cloak = "8";

/* specify which special features are allowed */
$allow_fullscan = true;                // full long range scan
$allow_navcomp = true;                 // navigation computer
$allow_ibank = true;                  // Intergalactic Bank (IGB)
$allow_genesis_destroy = true;         // Genesis torps can destroy planets

// iBank Config - Intergalactic Banking
// Trying to keep ibank constants unique by prefixing with $ibank_
// Please EDIT the following variables to your liking.

$ibank_interest = 0.0003;			// Interest rate for account funds NOTE: this is calculated every system update!
$ibank_paymentfee = 0.05; 		// Paymentfee
$ibank_loaninterest = 0.0010;		// Loan interest (good idea to put double what you get on a planet)
$ibank_loanfactor = 0.10;			// One-time loan fee
$ibank_loanlimit = 0.25;		// Maximum loan allowed, percent of net worth
$limit_IGB = FALSE;				// Provide only basic banking

// Information displayed on the 'Manage Own Account' section
$ibank_ownaccount_info = "Interest rate is " . $ibank_interest * 100 . "%<BR>Loan rate is " .
$ibank_loaninterest * 100 . "%<P>If you have loans make sure you have enough credits deposited each turn " .
  "to pay the interest and mortgage, otherwise it will be deducted from your ships acccount at <FONT COLOR=RED>" .
  "twice the current Loan rate (" . $ibank_loaninterest * 100 * 2 .")%</FONT>.";

// end of iBank config

// default planet production percentages
$default_prod_ore      = 20.0;
$default_prod_organics = 20.0;
$default_prod_goods    = 20.0;
$default_prod_energy   = 20.0;
$default_prod_fighters = 10.0;
$default_prod_torp     = 10.0;

/* port pricing variables */
$ore_price = 13.8;
$ore_delta = 2;
$ore_rate = 75000;
$ore_prate = 0.33333;
$ore_limit = 100000000;

$organics_price = 6;
$organics_delta = 1;
$organics_rate = 5000;
$organics_prate = 0.5;
$organics_limit = 100000000;

$goods_price = 19;
$goods_delta = 3;
$goods_rate = 75000;
$goods_prate = 0.25;
$goods_limit = 100000000;

$energy_price = 4;
$energy_delta = 1;
$energy_rate = 75000;
$energy_prate = 0.5;
$energy_limit = 200000000;

$inventory_factor = 1;
$upgrade_cost = 1000;
$upgrade_factor = 2;
$level_factor = 1.5;

$dev_genesis_price = 12000000;
$dev_beacon_price = 100;
$dev_emerwarp_price = 1200000;
$dev_warpedit_price = 150000;
$dev_minedeflector_price = 12;
$dev_escapepod_price = 100000;
$dev_fuelscoop_price = 100000;
$dev_lssd_price = 9000000;

// Furangee related variables
$furangee_price = 0.5; // The discount that a Furangee special trader sells at compared to a Special Port
$furangeeMin = 0.6; // The lowest percentage of furangee that can exist in the universe before more are created

$fighter_price = 50;
$fighter_prate = .01;

$torpedo_price = 25;
$torpedo_prate = .025;
$torp_dmg_rate = 10;

$credits_prate = 0; // No credits made by colonists in this game

$armour_price = 5;
$basedefense = 1;  // Additional factor added to tech levels by having a base on your planet. All your base are belong to us.

$colonist_price = 5;
$colonist_production_rate = .005;
$colonist_reproduction_rate = 0.0005;
$colonist_limit = 250000000;
$organics_consumption = 0.00;
$starvation_death_rate = 0.01;

$interest_rate = 1.0005;

$base_ore = 10000;
$base_goods = 10000;
$base_organics = 10000;
$base_credits = 10000000;
$base_modifier = 1;

$start_fighters = 10;
$start_armour = 10;
$start_credits = 1000;
$start_energy = 1;
$start_turns = 1500;

$max_turns = 3000;
$max_emerwarp = 10;

$fullscan_cost = 1;
$scan_error_factor=20;

$max_planets_sector = 5;
$max_traderoutes_player = 80;

$min_bases_to_own = 3;

$default_lang = 'english';

$avail_lang[0][file] = 'english';
$avail_lang[0][name] = 'English';
$avail_lang[1][file] = 'german';
$avail_lang[1][name] = 'Deutsch';
$avail_lang[2][file] = 'french';
$avail_lang[2][name] = 'Français';
$avail_lang[3][file] = 'romanian';
$avail_lang[3][name] = 'Romanian';
$avail_lang[4][file] = 'czech';
$avail_lang[4][name] = 'Cesky';

$IGB_min_turns = $start_turns; //Turns a player has to play before ship transfers are allowed 0=disable
$IGB_svalue = 0.15; //Max amount of sender's value allowed for ship transfers 0=disable
$IGB_trate = 1440; //Time (in minutes) before two similar transfers are allowed for ship transfers.0=disable
$IGB_lrate = 1440; //Time (in minutes) players have to repay a loan
$IGB_crate = 1442;
$IGB_tconsolidate = 10; //Cost in turns for consolidate : 1/$IGB_consolidate
$corp_planet_transfers = 0; //If transferring credits to/from corp planets is allowed. 1=enable
$min_value_capture = 0; //Percantage of planet's value a ship must be worth to be able to capture it. 0=disable
$defence_degrade_rate = 0.05;
$energy_per_fighter = 0.10;
$bounty_maxvalue = 0.15; //Max amount a player can place as bounty - good idea to make it the same as $IGB_svalue. 0=disable
$bounty_ratio = 0.10; // ratio of players networth before attacking results in a bounty. 0=disable
$bounty_minturns = 500; // Minimum number of turns a target must have had before attacking them may not get you a bounty. 0=disable
$display_password = false; // If true, will display password on signup screen.
$space_plague_kills = 0.20; // Percentage of colonists killed by space plague
$sched_type = 0; // 0 = Cron based, 1 = player triggered.
$max_credits_without_base = $base_credits; // Max amount of credits allowed on a planet without a base
$sofa_on = true;
$ksm_allowed = false;  // Original true
// Dry dock fees
$mooringFee = 200; // 100 credits per tick

include("global_funcs.php");
?>
