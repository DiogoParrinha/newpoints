<?php
/***************************************************************************
 *
 *   NewPoints plugin (/admin/modules/newpoints/module_meta.php)
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

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function newpoints_meta()
{
	global $page, $lang, $plugins;

	$sub_menu = array();
	$sub_menu['10'] = array("id" => "plugins", "title" => $lang->nav_plugins, "link" => "index.php?module=newpoints-plugins");
	$sub_menu['15'] = array("id" => "settings", "title" => $lang->nav_settings, "link" => "index.php?module=newpoints-settings");
	$sub_menu['20'] = array("id" => "log", "title" => $lang->nav_log, "link" => "index.php?module=newpoints-log");
	$sub_menu['25'] = array("id" => "maintenance", "title" => $lang->nav_maintenance, "link" => "index.php?module=newpoints-maintenance");
	$sub_menu['30'] = array("id" => "forumrules", "title" => $lang->nav_forumrules, "link" => "index.php?module=newpoints-forumrules");
	$sub_menu['35'] = array("id" => "grouprules", "title" => $lang->nav_grouprules, "link" => "index.php?module=newpoints-grouprules");
	$sub_menu['40'] = array("id" => "stats", "title" => $lang->nav_stats, "link" => "index.php?module=newpoints-stats");
	$sub_menu['45'] = array("id" => "upgrades", "title" => $lang->nav_upgrades, "link" => "index.php?module=newpoints-upgrades");
	
	$sub_menu = $plugins->run_hooks("admin_newpoints_menu", $sub_menu);
	
	$lang->load('newpoints');
	
	$page->add_menu_item($lang->newpoints, "newpoints", "index.php?module=newpoints", 60, $sub_menu);
	
	return true;
}

function newpoints_action_handler($action)
{
	global $page, $lang, $plugins;
	
	$page->active_module = "newpoints";
	
	$actions = array(
		'plugins' => array('active' => 'plugins', 'file' => 'plugins.php'),
		'settings' => array('active' => 'settings', 'file' => 'settings.php'),
		'log' => array('active' => 'log', 'file' => 'log.php'),
		'maintenance' => array('active' => 'maintenance', 'file' => 'maintenance.php'),
		'forumrules' => array('active' => 'forumrules', 'file' => 'forumrules.php'),
		'grouprules' => array('active' => 'grouprules', 'file' => 'grouprules.php'),
		'stats' => array('active' => 'stats', 'file' => 'stats.php'),
		'upgrades' => array('active' => 'upgrades', 'file' => 'upgrades.php'),
	);
	
	$actions = $plugins->run_hooks("admin_newpoints_action_handler", $actions);

	if(!isset($actions[$action]))
	{
		$page->active_action = "plugins";
		return "plugins.php";
	}
	else
	{
		$page->active_action = $actions[$action]['active'];
		return $actions[$action]['file'];
	}
}

function newpoints_admin_permissions()
{
	global $lang, $plugins;
	
	$admin_permissions = array(
		"newpoints"		=> $lang->can_manage_newpoints,
		"plugins"		=> $lang->can_manage_plugins,
		"settings"		=> $lang->can_manage_settings,
		"log"			=> $lang->can_manage_log,
		"maintenance"	=> $lang->can_manage_maintenance,
		"forumrules"	=> $lang->can_manage_forumrules,
		"grouprules"	=> $lang->can_manage_grouprules,
		"stats"			=> $lang->can_manage_stats,
		"upgrades"		=> $lang->can_manage_upgrades
	);
	
	$admin_permissions = $plugins->run_hooks("admin_newpoints_permissions", $admin_permissions);
	
	return array("name" => $lang->newpoints, "permissions" => $admin_permissions, "disporder" => 60);
}
?>
