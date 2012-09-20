<?php
/***************************************************************************
 *
 *   NewPoints plugin (/inc/plugins/newpoints.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2009-2012 Pirata Nervo
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

if(!defined("IN_MYBB"))
	die("This file cannot be accessed directly.");

// Load NewPoints' settings whenever NewPoints plugin is executed
// Adds one additional query per page
// TODO: Perhaps use Plugin Library to modify the init.php file to load settings from both tables (MyBB's and NewPoints')
// OR: Go back to the old method and put the settings in the settings table but keep a copy in NewPoints' settings table
// but also add a page on ACP to run the check and fix any missing settings or perhaps do the check via task.
if (defined('IN_ADMINCP'))
{
	// Plugins get "require_once" on Plugins List and Plugins Check and we do not want to load our settings when our file is required by those
	if ($mybb->input['module'] != "config-plugins" && $GLOBALS['db']->table_exists("newpoints_settings"))
		newpoints_load_settings();
}
else
	newpoints_load_settings();

if(THIS_SCRIPT == 'showthread.php')
{
    global $templatelist;
    if(isset($templatelist))
    {
        $templatelist .= ',';
    }
    $templatelist .= 'newpoints_postbit,newpoints_donate_inline';
}
elseif(THIS_SCRIPT == 'member.php')
{
    global $templatelist;
    if(isset($templatelist))
    {
        $templatelist .= ',';
    }
    $templatelist .= 'newpoints_profile,newpoints_donate_inline';
}
	
define('NEWPOINTS_VERSION', '1.9.6');

// load plugins and do other stuff
if (defined('IN_ADMINCP'))
{
	define("NP_HOOKS", 1); // 1 means Admin
}
else {
	define("NP_HOOKS", 2); // 2 means outside ACP
}

// load hooks
require_once MYBB_ROOT."inc/plugins/newpoints/core/hooks.php";

if (defined('IN_ADMINCP'))
{
	global $db, $mybb;

	function newpoints_info()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		return newpoints_plugin_info();
	}

	function newpoints_install()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		newpoints_plugin_install();
	}

	function newpoints_is_installed()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		return newpoints_plugin_is_installed();
	}

	function newpoints_uninstall()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		newpoints_plugin_uninstall();
	}

	function newpoints_do_template_edits()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		newpoints_plugin_do_template_edits();
	}

	function newpoints_undo_template_edits()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		newpoints_plugin_undo_template_edits();
	}

	function newpoints_activate()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		newpoints_plugin_activate();
	}

	function newpoints_deactivate()
	{
		require_once MYBB_ROOT."inc/plugins/newpoints/core/plugin.php";
		newpoints_plugin_deactivate();
	}
}

/**************************************************************************************/
/****************** FUNCTIONS THAT CAN/SHOULD BE USED BY PLUGINS **********************/
/**************************************************************************************/

/**
 * Somewhat like htmlspecialchars_uni but for JavaScript strings
 * 
 * @param string: The string to be parsed
 * @return string: Javascript compatible string
 */
function newpoints_jsspecialchars($str)
{
	// Converts & -> &amp; allowing Unicode
	// Parses out HTML comments as the XHTML validator doesn't seem to like them
	$string = preg_replace(array("#\<\!--.*?--\>#", "#&(?!\#[0-9]+;)#"), array('','&amp;'), $str);
	return strtr($string, array("\n" => '\n', "\r" => '\r', '\\' => '\\\\', '"' => '\x22', "'" => '\x27', '<' => '&lt;', '>' => '&gt;'));
}

/*
 * Deletes templates from the database
 * 
 * @param string a list of templates seperated by ',' e.g. 'test','test_again','testing'
 * @param bool false if something went wrong
 *
*/
function newpoints_remove_templates($templates)
{
	global $db;
	
	if (!$templates)
		return false;
	
	return $db->delete_query('templates', "title IN (".$templates.")");
}

/*
 * Adds a new template
 * 
 * @param string the title of the template
 * @param string the contents of the template
 * @param integer the sid of the template
 * @param bool false if something went wrong
 *
*/
function newpoints_add_template($name, $contents, $sid = -1)
{
	global $db;
	
	if (!$name || !$contents)
		return false;
	
	$templatearray = array(
		"title" => $db->escape_string($name),
		"template" => $db->escape_string($contents),
		"sid" => intval($sid)
	);

	return $db->insert_query("templates", $templatearray);
}

/*
 * Deletes settings from the database
 * 
 * @param string a list of settings seperated by ',' e.g. 'test','test_again','testing'
 * @param bool false if something went wrong
 *
*/
function newpoints_remove_settings($settings)
{
	global $db;
	
	if (!$settings)
		return false;
	
	$db->delete_query('newpoints_settings', "name IN (".$settings.")");
	//$db->delete_query('settings', "name IN (".$settings.")");
	
	return true;
}

/*
 * Adds a new setting
 * 
 * @param string the name (unique identifier) of the setting
 * @param string the codename of plugin which owns the setting ('main' for main setting)
 * @param string the title of the setting
 * @param string the description of the setting
 * @param string the type of the setting ('text', 'textarea', etc...)
 * @param string the value of the setting
 *
*/
function newpoints_add_setting($name, $plugin, $title, $description, $type, $value = '', $disporder = 0)
{
	global $db;
	
	if ($name == '' || $plugin == '' || $title == '' || $description == '' || $type == '')
		return false;
	
	$setting = array(
		"name"			=> $db->escape_string($name),
		"plugin"		=> $db->escape_string($plugin),
		"title"			=> $db->escape_string($title),
		"description"	=> $db->escape_string($description),
		"type"			=> $db->escape_string($type),
		"value"			=> $db->escape_string($value),
		"disporder"		=> intval($disporder)
	);
	$db->insert_query("newpoints_settings", $setting);
	
	/*$setting = array(
		"name"			=> $db->escape_string($name),
		"title"			=> $db->escape_string($title),
		"description"	=> $db->escape_string($description),
		"optionscode"	=> $db->escape_string($type),
		"value"			=> $db->escape_string($value),
		"disporder"		=> intval($disporder),
		"gid"			=> ''
	);
	$db->insert_query("settings", $setting);*/
}

/*
 * Adds/Subtracts points to a user
 * 
 * @param integer the id of the user
 * @param float the number of points to add or subtract (if a negative value)
 * @param integer the forum income rate
 * @param integer the user group income rate
 * @param bool if the uid is a string in case we don't have the uid we can update the points field by searching for the user name
 * @param bool true if you want to run the query immediatly. Default is false which means the query will be run on shut down. Note that if the previous paremeter is set to true, the query is run immediatly
 * Note: some pages (by other plugins) do not run queries on shutdown so adding this to shutdown may not be good if you're not sure if it will run.
 *
*/
function newpoints_addpoints($uid, $points, $forumrate = 1, $grouprate = 1, $isstring = false, $immediate = false)
{
	global $db, $mybb, $userpoints;
	
	if ($points == 0 || ($uid <= 0 && !$isstring))
		return;
		
	if ($isstring === true)
		$immediate = true;
	
	// might work only for MySQL and MySQLi
	//$db->update_query("users", array('newpoints' => 'newpoints+('.floatval($points).')'), 'uid=\''.intval($uid).'\'', '', true);
	
	if ($isstring) // where username
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET newpoints=newpoints+'".floatval(round($points*$forumrate*$grouprate, intval($mybb->settings['newpoints_main_decimal'])))."' WHERE username='".$db->escape_string($uid)."'");
	else // where uid
	{
		// if immediate, run the query now otherwise add it to shutdown to avoid slow down
		if ($immediate)
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET newpoints=newpoints+'".floatval(round($points*$forumrate*$grouprate, intval($mybb->settings['newpoints_main_decimal'])))."' WHERE uid='".intval($uid)."'");
		else
		{
			$userpoints[intval($uid)] = floatval(round($points*$forumrate*$grouprate, intval($mybb->settings['newpoints_main_decimal'])));
			add_shutdown('newpoints_update_addpoints');
		}
	}
}

function newpoints_update_addpoints()
{
	global $cache, $userpoints, $db;
	if (!empty($userpoints))
	{
		foreach($userpoints as $uid => $amount)
		{
			$db->write_query('UPDATE `'.TABLE_PREFIX.'users` SET `newpoints` = `newpoints`+'.$amount.' WHERE `uid`=\''.$uid.'\'');
		}
		unset($userpoints);
	}
}

/*
 * Get rules of a certain group or forum
 * 
 * @param string the type of rule: 'forum' or 'group'
 * @param integer the id of the group or forum
 * @return bool false if something went wrong
 *
*/
function newpoints_getrules($type, $id)
{
	global $db, $cache;
	
	if (!$type || !$id)
		return false;
		
	if ($type == 'forum')
		$typeid = 'f';
	elseif ($type == 'group')
		$typeid = 'g';
	else
		return;
		
	$rule = array();
		
	$cachedrules = $cache->read("newpoints_rules");
	if ($cachedrules === false)
	{
		// Something's wrong so let's get rule from DB
		// To fix this issue, the administrator should edit a rule and save it (all rules are re-cached when one is added/edited)
		$query = $db->simple_select('newpoints_'.$type.'rules', '*', $typeid.'id=\''.intval($id).'\'');
		$rule = $db->fetch_array($query);
	}
	else
	{
		if (!empty($cachedrules))
		{
			// If the array is not empty then grab from cache
			$rule = $cachedrules[$type][$id];
		}
	}
	
	return $rule;
}

/*
 * Get all rules
 * 
 * @param string the type of rule: 'forum' or 'group'
 * @return array containing all rules
 *
*/
function newpoints_getallrules($type)
{
	global $db, $cache;
	
	if (!$type)
		return false;
		
	if ($type == 'forum')
		$typeid = 'f';
	elseif ($type == 'group')
		$typeid = 'g';
	else
		return;
		
	$rules = array();
		
	$cachedrules = $cache->read("newpoints_rules");
	if ($cachedrules === false)
	{
		// Something's wrong so let's get the rules from DB
		// To fix this issue, the administrator should edit a rule and save it (all rules are re-cached when one is added/edited)
		$query = $db->simple_select('newpoints_'.$type.'rules', '*');
		while ($rule = $db->fetch_array($query))
			$rules[$rule[$typeid.'id']] = $rule;
	}
	else
	{
		if (!empty($cachedrules[$type]))
		{
			// Not empty? Then grab the chosen rules
			foreach($cachedrules[$type] as $crule)
			{
				$rules[$crule[$typeid.'id']] = $crule;
			}
		}
	}
	
	return $rules;
}

/*
 * Rebuild the rules cache.
*/
function newpoints_rebuild_rules_cache(&$rules=array())
{
	global $db, $cache, $mybb;
	
	$rules = array();
	
	// Query forum rules
	$query = $db->simple_select("newpoints_forumrules");
	while($rule = $db->fetch_array($query))
	{
		$rules['forum'][$rule['fid']] = $rule;
	}
	$db->free_result($query);
	
	// Query group rules
	$query = $db->simple_select("newpoints_grouprules");
	while($rule = $db->fetch_array($query))
	{
		$rules['group'][$rule['gid']] = $rule;
	}
	$db->free_result($query);
	
	$cache->update('newpoints_rules', $rules);
}

/*
 * Formats points according to the settings
 * 
 * @param float the amount of points
 * @return string formated points
 *
*/
function newpoints_format_points($points)
{
	global $mybb;
	
	return $mybb->settings['newpoints_main_curprefix'].number_format($points, intval($mybb->settings['newpoints_main_decimal'])).$mybb->settings['newpoints_main_cursuffix'];
}

/**
 * Sends a PM to a user
 * 
 * @param array: The PM to be sent; should have 'subject', 'message', 'touid' and 'receivepms'
 * (receivepms is for admin override in case the user has disabled pm's)
 * @param int: from user id (0 if you want to use the uid of the person that sends it. -1 to use MyBB Engine
 * @return bool: true if PM sent
 */
function newpoints_send_pm($pm, $fromid = 0)
{
	global $lang, $mybb, $db;
	if($mybb->settings['enablepms'] == 0)
		return false;
		
	if (!is_array($pm))
		return false;
		
	if (!$pm['subject'] ||!$pm['message'] || !$pm['touid'] || !$pm['receivepms'])
		return false;
	
	$lang->load('messages');
	
	require_once MYBB_ROOT."inc/datahandlers/pm.php";
	
	$pmhandler = new PMDataHandler();
	
	$subject = $pm['subject'];
	$message = $pm['message'];
	$toid = $pm['touid'];
	
	if (is_array($toid))
		$recipients_to = $toid;
	else
		$recipients_to = array($toid);
		
	$recipients_bcc = array();
	
	if (intval($fromid) == 0)
		$fromid = intval($mybb->user['uid']);
	elseif (intval($fromid) < 0)
		$fromid = 0;
	
	$pm = array(
		"subject" => $subject,
		"message" => $message,
		"icon" => -1,
		"fromid" => $fromid,
		"toid" => $recipients_to,
		"bccid" => $recipients_bcc,
		"do" => '',
		"pmid" => ''
	);
	
	$pm['options'] = array(
		"signature" => 0,
		"disablesmilies" => 0,
		"savecopy" => 0,
		"readreceipt" => 0
	);
	
	$pm['saveasdraft'] = 0;
	$pmhandler->admin_override = 1;
	$pmhandler->set_data($pm);
	if($pmhandler->validate_pm())
	{
		$pmhandler->insert_pm();
	}
	else
	{
		return false;
	}
	
	return true;
}

/*
 * Get the user data of a user name
 * 
 * @param string the user name
 * @param string the fields to fetch
 * @return array the user data
 *
*/
function newpoints_getuser_byname($username, $fields = '*')
{
	global $db;
	
	if (!$username)
		return;
	
	$query = $db->simple_select('users', $fields, 'username=\''.$db->escape_string(trim($username)).'\'');
	return $db->fetch_array($query);
}

/*
 * Get the user group data of the gid
 * 
 * @param string the user name
 * @param string the fields to fetch
 * @return array the user data
 *
*/
function newpoints_get_usergroup($gid)
{
	global $db;
	
	if (!$gid)
		return;
	
	$query = $db->simple_select('usergroups', '*', 'gid=\''.intval($gid).'\'');
	return $db->fetch_array($query);
}


/**
 * Find and replace a string in a particular template in global templates set
 *
 * @param string The name of the template
 * @param string The regular expression to match in the template
 * @param string The replacement string
 * @return bolean true if matched template name, false if not.
 */

function newpoints_find_replace_templatesets($title, $find, $replace)
{
	global $db;

	$query = $db->write_query("
		SELECT template, tid FROM ".TABLE_PREFIX."templates WHERE title='$title' AND sid=-1
	");
	while($template = $db->fetch_array($query))
	{
		if($template['template']) // Custom template exists for this group
		{
			if(!preg_match($find, $template['template']))
			{
				return false;
			}
			$newtemplate = preg_replace($find, $replace, $template['template']);
			$template['template'] = $newtemplate;
			$update[] = $template;
		}
	}
	
	if(is_array($update))
	{
		foreach($update as $template)
		{
			$updatetemp = array("template" => $db->escape_string($template['template']), "dateline" => TIME_NOW);
			$db->update_query("templates", $updatetemp, "tid='".$template['tid']."'");
		}
	}
	return true;
}

/*
 * Create a new log entry
 * 
 * @param string action taken
 * @param string extra data
 * @param username of who's executed the action
 * @param uid of who's executed the action
 * @return bool false if something went wrong
 *
*/
function newpoints_log($action, $data = '', $username='', $uid=0)
{
	global $db, $mybb;
	
	if (!$action)
		return false;
		
	if ($username == '' || $uid == 0)
	{
		$username = $mybb->user['username'];
		$uid = $mybb->user['uid'];
	}
		
	$db->insert_query('newpoints_log', array('action' => $db->escape_string($action), 'data' => $db->escape_string($data), 'date' => TIME_NOW, 'uid' => intval($uid), 'username' => $db->escape_string($username)));
	
	return true;
}

/*
 * Removes all log entries by action
 * 
 * @param array action taken
 *
*/
function newpoints_remove_log($action)
{
	global $db, $mybb;
	
	if (empty($action) || !is_array($action))
		return false;
		
	foreach ($action as $act)
	{
		$db->delete_query('newpoints_log', 'action=\''.$act.'\'');
	}
}

function newpoints_load_plugins()
{
	global $cache, $plugins, $mybb, $theme, $db, $templates, $newpoints_plugins;
	
	$newpoints_plugins = '';
	
	// guests have 0 points
	if (!$mybb->user['uid'])
		$mybb->user['newpoints'] = 0;
	
	$pluginlist = $cache->read("newpoints_plugins");
	if(is_array($pluginlist['active']))
	{
		foreach($pluginlist['active'] as $plugin)
		{
			if($plugin != "" && file_exists(MYBB_ROOT."inc/plugins/newpoints/".$plugin.".php"))
			{
				require_once MYBB_ROOT."inc/plugins/newpoints/".$plugin.".php";
			}
		}
		
		$newpoints_plugins = $pluginlist;
	}
}

function newpoints_load_settings()
{
	global $mybb, $db, $cache;
	
	$settings = $cache->read("newpoints_settings");
	if ($settings !== false && !empty($settings))
	{
		foreach($settings as $name => $value)
		{
			$mybb->settings[$name] = $value;
		}
	}
	
	/* something is wrong so let's rebuild the cache data */
	if (empty($settings) || $settings === false)
	{
		$settings = array();
		newpoints_rebuild_settings_cache($settings);
	}
}

/*
 * Rebuild the settings cache.
*/
function newpoints_rebuild_settings_cache(&$settings=array())
{
	global $db, $cache, $mybb;
	
	$settings = array();
	
	$options = array(
		"order_by" => "title",
		"order_dir" => "ASC"
	);
	
	$query = $db->simple_select("newpoints_settings", "value, name", "", $options);
	while($setting = $db->fetch_array($query))
	{
		//$setting['value'] = str_replace("\"", "\\\"", $setting['value']);
		$settings[$setting['name']] = $setting['value'];
		$mybb->settings[$setting['name']] = $setting['value'];
	}
	$db->free_result($query);
	
	$cache->update('newpoints_settings', $settings);
}

function newpoints_lang_load($plugin)
{
	global $lang;
	if ($plugin == '')
		return;
		
	$lang->set_path(MYBB_ROOT."inc/plugins/newpoints/languages");
	$lang->load($plugin);
	$lang->set_path(MYBB_ROOT."inc/languages");
}

// Updates users' points by user group - used by group rules
function newpoints_update_users()
{
	global $cache, $userupdates, $db;
	
	if (!empty($userupdates))
	{
		foreach($userupdates as $gid => $amount)
		{
			$db->write_query('UPDATE `'.TABLE_PREFIX.'users` SET `newpoints` = `newpoints`+'.$amount.' WHERE `usergroup`='.$gid);
		}
		unset($userupdates);
	}
}

?>
