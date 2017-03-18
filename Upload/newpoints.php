<?php

/***************************************************************************
 *
 *   NewPoints plugin (/newpoints.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2014 Pirata Nervo
 *   
 *   Website: http://www.mybb-plugins.com
 *
 *   NewPoints plugin for MyBB - A complex but efficient points system for MyBB.
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'newpoints.php');
define("NP_DISABLE_GUESTS", 0);

// Templates used by NewPoints
$templatelist  = "newpoints_home,newpoints_donate,newpoints_statistics,newpoints_statistics_richest_user,newpoints_statistics_donation,newpoints_no_results,newpoints_option";

require_once "./global.php";

$plugins->run_hooks("newpoints_begin");

// Allow guests here? Some plugins may allow guest access and they may hook to newpoints_start
if (!$mybb->user['uid'] && NP_DISABLE_GUESTS == 1)
	error_no_permission();

// load language
$lang->load("newpoints");

$mybb->input['action'] = $mybb->get_input('action');

// build the menu

$menu = array();
// default menu options
$menu[0] = '<a href="'.$mybb->settings['bburl'].'/newpoints.php">'.$lang->newpoints_home.'</a>';
if ($mybb->settings['newpoints_main_statsvisible'] == 1)
	$menu[1] = '<a href="'.$mybb->settings['bburl'].'/newpoints.php?action=stats">'.$lang->newpoints_statistics.'</a>';
if ($mybb->settings['newpoints_main_donationsenabled'] == 1)
	$menu[2] = '<a href="'.$mybb->settings['bburl'].'/newpoints.php?action=donate">'.$lang->newpoints_donate.'</a>';
	
if ($mybb->input['action'] == '')
{
	$menu[0] = "&raquo; ".$menu[0];
}
elseif ($mybb->input['action'] == 'stats')
{
	$menu[1] = "&raquo; ".$menu[1];
}
elseif ($mybb->input['action'] == 'donate')
{
	$menu[2] = "&raquo; ".$menu[2];
}

$menu = $plugins->run_hooks("newpoints_default_menu", $menu);

$bgcolor = alt_trow();
$options = '';

foreach($menu as $option)
{
	$bgcolor = alt_trow();
	
	$plugins->run_hooks("newpoints_menu_build_option");
	eval("\$options .= \"".$templates->get('newpoints_option')."\";");
}

$plugins->run_hooks("newpoints_start");

// Block guests here
if (!$mybb->user['uid'])
	error_no_permission();

// no action = home
if (!$mybb->input['action'])
{
	$plugins->run_hooks("newpoints_home_start");
	
	$income_settings = '';
	
	// get income settings' titles, descriptions and its value
	$query = $db->simple_select('newpoints_settings', '*', 'plugin=\'income\'');
	while ($setting = $db->fetch_array($query))
	{
		if ($setting['name'] == 'newpoints_income_minchar')
			$value = $setting['value']." ".$lang->newpoints_chars;
		else
			$value = newpoints_format_points($setting['value']);

		$desc = 'newpoints_setting_'.$setting['name'].'_desc';
		$title = 'newpoints_setting_'.$setting['name'].'_title';

		$income_settings .= '<tr><td valign="middle" align="left"><span style="border-bottom: 1px dashed; cursor: help;" title="'."{$lang->{$desc}}".'">'."{$lang->{$title}}".'</span></td><td valign="middle" align="right">'.$value.'</td></tr>';
	}
	
	$plugins->run_hooks("newpoints_home_end", $income_settings);
	
	$lang->newpoints_home_desc = $lang->sprintf($lang->newpoints_home_desc, $income_settings);
	
	eval("\$page = \"".$templates->get('newpoints_home')."\";");
	
	output_page($page);
}

if ($mybb->input['action'] == 'stats')
{
	if ($mybb->settings['newpoints_main_statsvisible'] != 1)
		error($lang->newpoints_stats_disabled);
		
	$richest_users = '';
	$bgcolor = alt_trow();
	
	$fields = array('uid', 'username', 'newpoints', 'usergroup', 'displaygroup');
	
	$plugins->run_hooks("newpoints_stats_start");

	// get richest users
	$query = $db->simple_select('users', implode(',', $fields), '', array('order_by' => 'newpoints', 'order_dir' => 'DESC', 'limit' => intval($mybb->settings['newpoints_main_stats_richestusers'])));
	while($user = $db->fetch_array($query)) {
		$bgcolor = alt_trow();
		
		$user['username'] = build_profile_link(format_name(htmlspecialchars_uni($user['username']), $user['usergroup'], $user['displaygroup']), intval($user['uid']));
		$user['newpoints'] = newpoints_format_points($user['newpoints']);
		
		$plugins->run_hooks("newpoints_stats_richest_users");
		
		eval("\$richest_users .= \"".$templates->get('newpoints_statistics_richest_user')."\";");
	}
	
	if($richest_users == '')
	{
		$colspan = 2;
		$no_results = $lang->newpoints_noresults;
		eval("\$richest_users = \"".$templates->get('newpoints_no_results')."\";");
	}
	
	$plugins->run_hooks("newpoints_stats_middle");
	
	$last_donations = '';
	$bgcolor = alt_trow();
	
	// get latest donations
	$query = $db->query("
		SELECT l.*,u.usergroup,u.displaygroup
		FROM ".TABLE_PREFIX."newpoints_log l
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=l.uid)
		WHERE l.action='donation'
		ORDER BY l.date DESC
		LIMIT ".intval($mybb->settings['newpoints_main_stats_lastdonations'])."
	");
	while($donation = $db->fetch_array($query)) {
		$bgcolor = alt_trow();
		
		$data = explode('-', $donation['data']);
		
		$donation['to'] = build_profile_link(htmlspecialchars_uni($data[0]), intval($data[1]));
		$donation['from'] = build_profile_link(format_name(htmlspecialchars_uni($donation['username']), $donation['usergroup'], $donation['displaygroup']), intval($donation['uid']));
		
		$donation['amount'] = newpoints_format_points($data[2]);
		$donation['date'] = my_date($mybb->settings['dateformat'], intval($donation['date']), '', false).", ".my_date($mybb->settings['timeformat'], intval($donation['date']));
		
		$plugins->run_hooks("newpoints_stats_last_donations");
		
		eval("\$last_donations .= \"".$templates->get('newpoints_statistics_donation')."\";");
	}
	
	if($last_donations == '')
	{
		$colspan = 4;
		$no_results = $lang->newpoints_noresults;
		eval("\$last_donations = \"".$templates->get('newpoints_no_results')."\";");
	}
	
	eval("\$page = \"".$templates->get('newpoints_statistics')."\";");
	
	$plugins->run_hooks("newpoints_stats_end");
	
	output_page($page);
}
elseif ($mybb->input['action'] == 'donate')
{
	if ($mybb->settings['newpoints_main_donationsenabled'] != 1)
		error($lang->newpoints_donations_disabled);
	
	$plugins->run_hooks("newpoints_donate_start");
	
	// make sure wen're trying to send a donation to ourselves
	$uid = intval($mybb->input['uid']);
	$user = get_user($uid);
	if ($user['username'] != "")
		$user['username'] = htmlspecialchars_uni($user['username']);
	else
		$user['username'] = '';
		
	if ($uid == $mybb->user['uid'] || $user['username'] == $mybb->user['username'])
		error($lang->newpoints_cant_donate_self);
	
	eval("\$page = \"".$templates->get('newpoints_donate')."\";");
	
	$plugins->run_hooks("newpoints_donate_end");
	
	output_page($page);
}
elseif ($mybb->input['action'] == 'do_donate')
{
	verify_post_check($mybb->input['postcode']);

	if ($mybb->settings['newpoints_main_donationsenabled'] != 1)
		error($lang->newpoints_donations_disabled);
	
	$plugins->run_hooks("newpoints_do_donate_start");
	
	if($mybb->user['usergroup'] != 4)
	{
		$q = $db->simple_select('newpoints_log', 'COUNT(*) as donations', 'action=\'donation\' AND date>'.(TIME_NOW-15*60*60).' AND uid='.(int)$mybb->user['uid']);
		$totaldonations = (int)$db->fetch_field($q, 'donations');
		if($totaldonations >= MAX_DONATIONS_CONTROL)
		{
			error($lang->sprintf($lang->newpoints_max_donations_control, $totaldonations));
		}
	}
	
	// make sure we're not trying to send a donation to ourselves
	$username = trim($mybb->input['username']);
	if (my_strtolower($username) == my_strtolower($mybb->user['username']))
		error($lang->newpoints_cant_donate_self);
	
	$amount = round(floatval($mybb->input['amount']), (int)$mybb->settings['newpoints_main_decimal']);
	
	// do we have enough points?
	if ($amount <= 0 || $amount > $mybb->user['newpoints'])
		error($lang->newpoints_invalid_amount);
		
	// make sure we're sending points to a valid user
	$touser = newpoints_getuser_byname($username, 'uid,username');
	if (!$touser)
	{
		error($lang->newpoints_invalid_user);
	}
	
	// remove points from us
	newpoints_addpoints($mybb->user['uid'], -($amount));

	// give points to user
	newpoints_addpoints($username, $amount, 1, 1, true);
	
	// send pm to the user if the "Send PM on donate" setting is set to Yes
	if ($mybb->settings['newpoints_main_donationspm'] != 0)
	{
		if ($mybb->input['reason'] != '')
			newpoints_send_pm(array('subject' => $lang->newpoints_donate_subject, 'message' => $lang->sprintf($lang->newpoints_donate_message_reason, newpoints_format_points($amount), htmlspecialchars_uni($mybb->input['reason'])), 'receivepms' => 1, 'touid' => $touser['uid']));
		else
			newpoints_send_pm(array('subject' => $lang->newpoints_donate_subject, 'message' => $lang->sprintf($lang->newpoints_donate_message, newpoints_format_points($amount)), 'receivepms' => 1, 'touid' => $touser['uid']));
	}
	
	// log donation
	newpoints_log('donation', $lang->sprintf($lang->newpoints_donate_log, $touser['username'], $touser['uid'], $amount));
	
	$plugins->run_hooks("newpoints_do_donate_end");
	
	redirect($mybb->settings['bburl']."/newpoints.php", $lang->sprintf($lang->newpoints_donated, newpoints_format_points($amount)));
}

$plugins->run_hooks("newpoints_terminate");

exit;

?>