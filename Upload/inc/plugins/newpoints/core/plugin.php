<?php
/***************************************************************************
 *
 *   NewPoints plugin (/inc/plugins/newpoints/core/plugin.php)
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

if(!defined("IN_MYBB"))
	die("This file cannot be accessed directly.");

function newpoints_plugin_info()
{
	return array(
		"name"			=> "NewPoints",
		"description"	=> "NewPoints is a complex but efficient points system for MyBB.",
		"website"		=> "http://www.consoleaddicted.com",
		"author"		=> "Diogo Parrinha",
		"authorsite"	=> "http://www.mybb-plugins.com",
		"version"		=> "2.1.2",
		"guid" 			=> "152e7f9f32fadb777d58fda000eb7a9e",
		"compatibility" => "18*"
	);
}

function newpoints_plugin_install()
{
	global $db, $mybb;

	$collation = $db->build_create_table_collation();

	// create tables
	if(!$db->table_exists("newpoints_settings"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_settings` (
		  `sid` int(10) UNSIGNED NOT NULL auto_increment,
		  `plugin` varchar(100) NOT NULL default '',
		  `name` varchar(100) NOT NULL default '',
		  `title` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `type` text NOT NULL,
		  `value` text NOT NULL,
		  `disporder` smallint(5) UNSIGNED NOT NULL default '0',
		  PRIMARY KEY  (`sid`)
			) ENGINE=MyISAM{$collation}");
	}

	if(!$db->table_exists("newpoints_log"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_log` (
		  `lid` bigint(30) UNSIGNED NOT NULL auto_increment,
		  `action` varchar(100) NOT NULL default '',
		  `data` text NOT NULL,
		  `date` bigint(30) UNSIGNED NOT NULL default '0',
		  `uid` bigint(30) UNSIGNED NOT NULL default '0',
		  `username` varchar(100) NOT NULL default '',
		  PRIMARY KEY  (`lid`)
			) ENGINE=MyISAM{$collation}");
	}

	if(!$db->table_exists("newpoints_forumrules"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_forumrules` (
		  `rid` bigint(30) UNSIGNED NOT NULL auto_increment,
		  `fid` int(10) UNSIGNED NOT NULL default '0',
		  `name` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `rate` float NOT NULL default '1',
		  `pointsview` DECIMAL(16,2) NOT NULL default '0',
		  `pointspost` DECIMAL(16,2) NOT NULL default '0',
		  PRIMARY KEY  (`rid`)
			) ENGINE=MyISAM{$collation}");
	}

	if(!$db->table_exists("newpoints_grouprules"))
    {
		$db->write_query("CREATE TABLE `".TABLE_PREFIX."newpoints_grouprules` (
		  `rid` bigint(30) UNSIGNED NOT NULL auto_increment,
		  `gid` int(10) UNSIGNED NOT NULL default '0',
		  `name` varchar(100) NOT NULL default '',
		  `description` text NOT NULL,
		  `rate` float NOT NULL default '1',
		  `pointsearn` DECIMAL(16,2) UNSIGNED NOT NULL default '0',
		  `period` bigint(30) UNSIGNED NOT NULL default '0',
		  `lastpay` bigint(30) UNSIGNED NOT NULL default '0',
		  PRIMARY KEY  (`rid`)
			) ENGINE=MyISAM{$collation}");
	}

	// add settings
	newpoints_add_setting('newpoints_main_enabled', 'main', 'Is NewPoints enabled?', 'Set to no if you want to disable NewPoints.', 'yesno', 1, 1);
	newpoints_add_setting('newpoints_main_curname', 'main', 'Currency Name', 'Enter a name for the currency.', 'text', 'Points', 2);
	newpoints_add_setting('newpoints_main_curprefix', 'main', 'Currency Prefix', 'Enter what you want to display before the number of points.', 'text', '', 3);
	newpoints_add_setting('newpoints_main_cursuffix', 'main', 'Currency Suffix', 'Enter what you want to display after the number of points.', 'text', '€', 4);
	newpoints_add_setting('newpoints_main_decimal', 'main', 'Decimal Places', 'Number of decimals to be used.', 'text', '2', 5);
	newpoints_add_setting('newpoints_main_statsvisible', 'main', 'Statistics visible to users?', 'Set to no if you do not want users to view the statistics.', 'yesno', 1, 6);
	newpoints_add_setting('newpoints_main_donationsenabled', 'main', 'Donations enabled?', 'Set to no if you want to disable donations.', 'yesno', 1, 7);
	newpoints_add_setting('newpoints_main_donationspm', 'main', 'Send a PM on donate?', 'Do you want it to automatically send a new private message to a user receiving a donation?', 'yesno', 1, 8);
	newpoints_add_setting('newpoints_main_stats_lastdonations', 'main', 'Last donations', 'Number of last donations to show.', 'text', 10, 9);
	newpoints_add_setting('newpoints_main_stats_richestusers', 'main', 'Richest Users', 'Number of richest users to show.', 'text', 10, 9);

	// income settings
	newpoints_add_setting('newpoints_income_newpost', 'income', 'New Post', 'Amount of points received on new post.', 'text', '10', 1);
	newpoints_add_setting('newpoints_income_newthread', 'income', 'New Thread', 'Amount of points received on new thread.', 'text', '20', 2);
	newpoints_add_setting('newpoints_income_newpoll', 'income', 'New Poll', 'Amount of points received on new poll.', 'text', '15', 3);
	newpoints_add_setting('newpoints_income_perchar', 'income', 'Per Character', 'Amount of points received per character (in new thread and new post).', 'text', '0.01', 4);
	newpoints_add_setting('newpoints_income_minchar', 'income', 'Minimum Characters', 'Minimum characters required in order to receive the amount of points per character.', 'text', '15', 5);
	newpoints_add_setting('newpoints_income_newreg', 'income', 'New Registration', 'Amount of points received by the user when registering.', 'text', '50', 6);
	newpoints_add_setting('newpoints_income_pervote', 'income', 'Per Poll Vote', 'Amount of points received by the user who votes.', 'text', '5', 7);
	newpoints_add_setting('newpoints_income_perreply', 'income', 'Per Reply', 'Amount of points received by the author of the thread, when someone replies to it.', 'text', '2', 8);
	newpoints_add_setting('newpoints_income_pmsent', 'income', 'Per PM Sent', 'Amount of points received everytime a user sends a private message.', 'text', '1', 9);
	newpoints_add_setting('newpoints_income_perrate', 'income', 'Per Rate', 'Amount of points received everytime a user rates a thread.', 'text', '0.05', 9);
	newpoints_add_setting('newpoints_income_pageview', 'income', 'Per Page View', 'Amount of points received everytime a user views a page.', 'text', '0', 10);
	newpoints_add_setting('newpoints_income_visit', 'income', 'Per Visit', 'Amount of points received everytime a user visits the forum. ("visits" = new MyBB session (expires after 15 minutes))', 'text', '0.1', 11);
	newpoints_add_setting('newpoints_income_referral', 'income', 'Per Referral', 'Amount of points received everytime a user is referred. (the referred user is who receives the points)', 'text', '5', 12);

	//rebuild_settings();

	newpoints_rebuild_settings_cache();
	newpoints_rebuild_rules_cache();

	// add points field
	if (!$db->field_exists('newpoints', 'users'))
		$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` ADD `newpoints` DECIMAL(16,2) NOT NULL DEFAULT '0';");

	// create task
	$new_task = array(
		"title" => "Backup NewPoints",
		"description" => "Creates a backup of NewPoints default tables and users\'s points.",
		"file" => "backupnewpoints",
		"minute" => '0',
		"hour" => '0',
		"day" => '*',
		"month" => '*',
		"weekday" => '0',
		"enabled" => '0',
		"logging" => '1'
	);

	$new_task['nextrun'] = 0; // once the task is enabled, it will generate a nextrun date
	$tid = $db->insert_query("tasks", $new_task);
}

function newpoints_plugin_is_installed()
{
	global $db;

	if($db->table_exists('newpoints_settings'))
		return true;
	else
		return false;
}

function newpoints_plugin_uninstall()
{
	global $db, $mybb, $cache, $plugins, $theme, $templates, $lang;

	// uninstall plugins
	$plugins_cache = $cache->read("newpoints_plugins");
	$active_plugins = $plugins_cache['active'];

	if (!empty($active_plugins))
	{
		foreach($active_plugins as $plugin)
		{
			// Ignore missing plugins
			if(!file_exists(MYBB_ROOT."inc/plugins/newpoints/".$plugin.".php"))
				continue;

			require_once MYBB_ROOT."inc/plugins/newpoints/".$plugin.".php";

			if(function_exists("{$plugin}_deactivate"))
			{
				call_user_func("{$plugin}_deactivate");
			}

			if(function_exists("{$plugin}_uninstall"))
			{
				call_user_func("{$plugin}_uninstall");
			}
		}
	}

	// delete plugins cache
	$db->delete_query('datacache', 'title=\'newpoints_plugins\'');

	if ($db->field_exists('newpoints', 'users'))
		$db->write_query("ALTER TABLE `".TABLE_PREFIX."users` DROP `newpoints`;");

	// delete default main settings
	newpoints_remove_settings("'newpoints_main_enabled','newpoints_main_curname','newpoints_main_curprefix','newpoints_main_cursuffix','newpoints_main_decimal','newpoints_main_statsvisible','newpoints_main_donationsenabled','newpoints_main_donationspm','newpoints_main_stats_lastdonations','newpoints_main_stats_richestusers'");

	// delete default income settings
	newpoints_remove_settings("'newpoints_income_newpost','newpoints_income_newthread','newpoints_income_newpoll','newpoints_income_perchar','newpoints_income_minchar','newpoints_income_newreg','newpoints_income_pervote','newpoints_income_perreply','newpoints_income_pmsent','newpoints_income_perrate','newpoints_income_pageview','newpoints_income_visit','newpoints_income_referral'");

	// drop tables
	if($db->table_exists('newpoints_settings'))
		$db->drop_table('newpoints_settings');

	if($db->table_exists('newpoints_log'))
		$db->drop_table('newpoints_log');

	if($db->table_exists('newpoints_forumrules'))
		$db->drop_table('newpoints_forumrules');

	if($db->table_exists('newpoints_grouprules'))
		$db->drop_table('newpoints_grouprules');

	//rebuild_settings();

	$db->delete_query('tasks', 'file=\'backupnewpoints\'');
}

function newpoints_plugin_do_template_edits()
{
	// do edits
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'user_details\']}').'#', '{$post[\'user_details\']}'.'{$post[\'newpoints_postbit\']}');
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'user_details\']}').'#', '{$post[\'user_details\']}'.'{$post[\'newpoints_postbit\']}');
	find_replace_templatesets("member_profile", '#'.preg_quote('{$warning_level}').'#', '{$warning_level}'.'{$newpoints_profile}');
}

function newpoints_plugin_undo_template_edits()
{
	// undo edits
	require_once MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", '#'.preg_quote('{$post[\'newpoints_postbit\']}').'#', '', 0);
	find_replace_templatesets("postbit", '#'.preg_quote('{$post[\'newpoints_postbit\']}').'#', '', 0);
	find_replace_templatesets("member_profile", '#'.preg_quote('{$newpoints_profile}').'#', '', 0);
}

function newpoints_plugin_activate()
{
	global $db, $lang;

	newpoints_add_template('newpoints_postbit', '<br /><span class="smalltext">{$currency}: <a href="{$mybb->settings[\'bburl\']}/newpoints.php">{$points}</a></span>{$donate}');
	newpoints_add_template('newpoints_profile', '<tr>
	<td class="trow2"><strong>{$currency}:</strong></td>
	<td class="trow2"><a href="{$mybb->settings[\'bburl\']}/newpoints.php">{$points}</a>{$donate}</td>
</tr>');

	newpoints_add_template('newpoints_donate_inline', ' <span class="smalltext">[<a href="{$mybb->settings[\'bburl\']}/newpoints.php?action=donate&amp;uid={$uid}">{$lang->newpoints_donate}</a>]</span>');

	newpoints_add_template('newpoints_donate', '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints} - {$lang->newpoints_donate}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
<td valign="top">
<form action="newpoints.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}" />
<input type="hidden" name="action" value="do_donate" />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->newpoints_donate}</strong></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_user}:</strong><br /><span class="smalltext">{$lang->newpoints_user_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="username" value="{$user[\'username\']}" class="textbox" id="username" /></td>
</tr>
<tr>
<td class="trow2" width="50%"><strong>{$lang->newpoints_amount}:</strong><br /><span class="smalltext">{$lang->newpoints_amount_desc}</span></td>
<td class="trow2" width="50%"><input type="text" name="amount" value="" class="textbox" size="20" /></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_reason}:</strong><br /><span class="smalltext">{$lang->newpoints_reason_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="reason" value="" class="textbox" size="20" /></td>
</tr>
<tr>
<td class="tfoot" width="100%" colspan="2" align="center"><input type="submit" name="submit" value="{$lang->newpoints_submit}" class="button" /></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
{$footer}
<link rel="stylesheet" href="{$mybb->asset_url}/jscripts/select2/select2.css">
<script type="text/javascript" src="{$mybb->asset_url}/jscripts/select2/select2.min.js?ver=185"></script>
<script type="text/javascript">
<!--
if(use_xmlhttprequest == "1")
{
	MyBB.select2();
	$("#username").select2({
		placeholder: "{$lang->newpoints_search_user}",
		minimumInputLength: 3,
		maximumSelectionSize: 3,
		multiple: false,
		width: 150,
		ajax: { // instead of writing the function to execute the request we use Select2\'s convenient helper
			url: "xmlhttp.php?action=get_users",
			dataType: \'json\',
			data: function (term, page) {
				return {
					query: term, // search term
				};
			},
			results: function (data, page) { // parse the results into the format expected by Select2.
				// since we are using custom formatting functions we do not need to alter remote JSON data
				return {results: data};
			}
		},
		initSelection: function(element, callback) {
			var value = $(element).val();
			if (value !== "") {
				callback({
					id: value,
					text: value
				});
			}
		},
       // Allow the user entered text to be selected as well
       createSearchChoice:function(term, data) {
			if ( $(data).filter( function() {
				return this.text.localeCompare(term)===0;
			}).length===0) {
				return {id:term, text:term};
			}
		},
	});

  	$(\'[for=username]\').click(function(){
		$("#username").select2(\'open\');
		return false;
	});
}
// -->
</script>
</body>
</html>');

	newpoints_add_template('newpoints_statistics', '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints} - {$lang->newpoints_statistics}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
    <tr>
        <td valign="top" width="180">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                <td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
                </tr>
                {$options}
            </table>
        </td>
        <td valign="top">
            <table width="100%" border="0" align="center">
                <tr>
                    <td valign="top" width="40%">
                        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                            <tr>
                                <td class="thead" colspan="2"><strong>{$lang->newpoints_richest_users}</strong></td>
                            </tr>
                            <tr>
                                <td class="tcat" width="50%"><strong>{$lang->newpoints_user}</strong></td>
                                <td class="tcat" width="50%" align="center"><strong>{$lang->newpoints_amount}</strong></td>
                            </tr>
                            {$richest_users}
                        </table>
                    </td>
                </tr>
            </table>
        </td>
        <td valign="top" width="60%">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead" colspan="4"><strong>{$lang->newpoints_last_donations}</strong></td>
                </tr>
                <tr>
                    <td class="tcat" width="30%"><strong>{$lang->newpoints_from}</strong></td>
                    <td class="tcat" width="30%"><strong>{$lang->newpoints_to}</strong></td>
                    <td class="tcat" width="20%" align="center"><strong>{$lang->newpoints_amount}</strong></td>
                    <td class="tcat" width="20%" align="center"><strong>{$lang->newpoints_date}</strong></td>
                </tr>
                {$last_donations}
            </table>
        </td>
    </tr>
</table>
{$footer}
</body>
</html>');

	newpoints_add_template('newpoints_statistics_richest_user', '
<tr>
<td class="{$bgcolor}" width="50%">{$user[\'username\']}</td>
<td class="{$bgcolor}" width="50%" align="center">{$user[\'newpoints\']}</td>
</tr>');

	newpoints_add_template('newpoints_statistics_donation', '
<tr>
<td class="{$bgcolor}" width="30%">{$donation[\'from\']}</td>
<td class="{$bgcolor}" width="30%">{$donation[\'to\']}</td>
<td class="{$bgcolor}" width="20%" align="center">{$donation[\'amount\']}</td>
<td class="{$bgcolor}" width="20%" align="center">{$donation[\'date\']}</td>
</tr>');

	newpoints_add_template('newpoints_no_results', '
<tr>
<td class="{$bgcolor}" width="100%" colspan="{$colspan}">{$no_results}</td>
</tr>');

	newpoints_add_template('newpoints_option', '
<tr>
<td class="{$bgcolor}" width="100%">{$option}</td>
</tr>');

	newpoints_add_template('newpoints_home', '
<html>
<head>
<title>{$mybb->settings[\'bbname\']} - {$lang->newpoints}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
<td valign="top">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints}</strong></td>
</tr>
<tr>
<td class="trow1">{$lang->newpoints_home_desc}<br />{$lang->newpoints_home_credits}</td>
</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>');

	newpoints_do_template_edits();

	//Change admin permissions
	change_admin_permission("newpoints", false, 1);
	change_admin_permission("newpoints", "plugins", 1);
	change_admin_permission("newpoints", "settings", 1);
	change_admin_permission("newpoints", "log", 1);
	change_admin_permission("newpoints", "maintenance", 1);
	change_admin_permission("newpoints", "forumrules", 1);
	change_admin_permission("newpoints", "grouprules", 1);
	change_admin_permission("newpoints", "stats", 1);
	change_admin_permission("newpoints", "upgrades", 1);
}

function newpoints_plugin_deactivate()
{
	global $db, $mybb;

	newpoints_remove_templates("'newpoints_postbit','newpoints_profile','newpoints_donate','newpoints_donate_inline','newpoints_statistics','newpoints_statistics_richest_user','newpoints_statistics_donation','newpoints_no_results','newpoints_option','newpoints_home'");

	newpoints_undo_template_edits();

	//Change admin permissions
	change_admin_permission("newpoints", false, -1);
	change_admin_permission("newpoints", "plugins", -1);
	change_admin_permission("newpoints", "settings", -1);
	change_admin_permission("newpoints", "log", -1);
	change_admin_permission("newpoints", "maintenance", -1);
	change_admin_permission("newpoints", "forumrules", -1);
	change_admin_permission("newpoints", "grouprules", -1);
	change_admin_permission("newpoints", "stats", -1);
	change_admin_permission("newpoints", "upgrades", -1);
}

?>
