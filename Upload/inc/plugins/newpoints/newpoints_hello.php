<?php
/***************************************************************************
 *
 *   NewPoints Hello plugin (/inc/plugins/newpoints/newpoints_hello.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2014 Pirata Nervo
 *   
 *   Website: http://www.mybb-plugins.com
 *
 *   A hello world plugin for NewPoints.
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

$plugins->add_hook("pre_output_page", "newpoints_hello_world");

function newpoints_hello_info()
{
	/**
	 * Array of information about the plugin.
	 * name: The name of the plugin
	 * description: Description of what the plugin does
	 * website: The website the plugin is maintained at (Optional)
	 * author: The name of the author of the plugin
	 * authorsite: The URL to the website of the author (Optional)
	 * version: The version number of the plugin
	 * guid: Unique ID issued by the MyBB Mods site for version checking
	 * compatibility: A CSV list of MyBB versions supported. Ex, "121,123", "12*". Wildcards supported.
	 */
	return array(
		"name"			=> "Hello World!",
		"description"	=> "A sample plugin that prints hello world depending on the settings.",
		"website"		=> "http://www.consoleworld.net",
		"author"		=> "Pirata Nervo",
		"authorsite"	=> "http://www.mybb-plugins.com",
		"version"		=> "1.0",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

/**
 * ADDITIONAL PLUGIN INSTALL/UNINSTALL ROUTINES
 *
 * _install():
 *   Called whenever a plugin is installed by clicking the "Install" button in the plugin manager.
 *   If no install routine exists, the install button is not shown and it assumed any work will be
 *   performed in the _activate() routine.
 *
 * function newpoints_hello_install()
 * {
 * }
 *
 * _is_installed():
 *   Called on the plugin management page to establish if a plugin is already installed or not.
 *   This should return TRUE if the plugin is installed (by checking tables, fields etc) or FALSE
 *   if the plugin is not installed.
 *
 * function newpoints_hello_is_installed()
 * {
 *		global $db;
 *		if($db->table_exists("newpoints_hello_world"))
 *  	{
 *  		return true;
 *		}
 *		return false;
 * }
 *
 * _uninstall():
 *    Called whenever a plugin is to be uninstalled. This should remove ALL traces of the plugin
 *    from the installation (tables etc). If it does not exist, uninstall button is not shown.
 *
 * function newpoints_hello_uninstall()
 * {
 * }
 *
 * _activate():
 *    Called whenever a plugin is activated via the Admin CP. This should essentially make a plugin
 *    "visible" by adding templates/template changes, language changes etc.
 *
 */
function newpoints_hello_activate()
{
	global $db, $mybb;
	// add settings
	// take a look at inc/plugins/newpoints.php to know exactly what each parameter means
	newpoints_add_setting('newpoints_hello_show', 'newpoints_hello', 'Show message', 'Set to yes if you want to show the hello message on every page.', 'yesno', 1, 1);
	rebuild_settings();
}
/*
 * _deactivate():
 *    Called whenever a plugin is deactivated. This should essentially "hide" the plugin from view
 *    by removing templates/template changes etc. It should not, however, remove any information
 *    such as tables, fields etc - that should be handled by an _uninstall routine. When a plugin is
 *    uninstalled, this routine will also be called before _uninstall() if the plugin is active.
 *
 */
function newpoints_hello_deactivate()
{
	global $db, $mybb;
	// delete settings
	// take a look at inc/plugins/newpoints.php to know exactly what each parameter means
	newpoints_remove_settings("'newpoints_hello_show'");
	rebuild_settings();
}


function newpoints_hello_world($page)
{
	global $mybb, $lang;
	if ($mybb->settings['newpoints_hello_show'] != 1)
		return;
	
	// load language files
	newpoints_lang_load('newpoints_hello');
		
	$page = str_replace("<!-- end: header -->", "<!-- end: header -->".$lang->newpoints_hello_message, $page);
	return $page;
}

?>