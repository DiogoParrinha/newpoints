<?php
/***************************************************************************
 *
 *   NewPoints plugin (/admin/modules/newpoints/stats.php)
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

$lang->load('newpoints');

$plugins->run_hooks("newpoints_admin_stats_begin");

$page->add_breadcrumb_item($lang->newpoints_stats, 'index.php?module=newpoints-stats');
			
$page->output_header($lang->newpoints_stats);
	
$sub_tabs['newpoints_stats'] = array(
	'title'			=> $lang->newpoints_stats,
	'link'			=> 'index.php?module=newpoints-stats',
	'description'	=> $lang->newpoints_stats_description
);

$page->output_nav_tabs($sub_tabs, 'newpoints_stats');
if (!$mybb->input['action']) // view stats
{
	$fields = array('uid', 'username', 'newpoints');
	
	$plugins->run_hooks("newpoints_admin_stats_noaction_start");
	
	// table
	$table = new Table;
	$table->construct_header($lang->newpoints_stats_user, array('width' => '50%'));
	$table->construct_header($lang->newpoints_stats_points, array('width' => '50%', 'class' => 'align_center'));

	$query = $db->simple_select('users', implode(',', $fields), '', array('order_by' => 'newpoints', 'order_dir' => 'DESC', 'limit' => intval($mybb->settings['newpoints_main_stats_richestusers'])));
	while($user = $db->fetch_array($query)) {
		$link = build_profile_link(htmlspecialchars_uni($user['username']), intval($user['uid']));
		$table->construct_cell($link);
		$table->construct_cell(newpoints_format_points($user['newpoints']), array('class' => 'align_center'));

		$table->construct_row();
	}
	
	if($table->num_rows() == 0)
	{
		$table->construct_cell($lang->newpoints_error_gathering, array('colspan' => 4));
		$table->construct_row();
	}
	
	$table->output($lang->newpoints_stats_richest_users);
	
	echo "<br />";
	
	// table
	$table = new Table;
	$table->construct_header($lang->newpoints_stats_to, array('width' => '30%'));
	$table->construct_header($lang->newpoints_stats_from, array('width' => '30%'));
	$table->construct_header($lang->newpoints_stats_amount, array('width' => '20%', 'class' => 'align_center'));
	$table->construct_header($lang->newpoints_stats_date, array('width' => '20%', 'class' => 'align_center'));

	$query = $db->simple_select('newpoints_log', '*', 'action=\'donation\'', array('order_by' => 'date', 'order_dir' => 'DESC', 'limit' => intval($mybb->settings['newpoints_main_stats_lastdonations'])));
	while($stats = $db->fetch_array($query)) {
		$data = explode('-', $stats['data']);
		
		$link = build_profile_link(htmlspecialchars_uni($data[0]), intval($data[1]));
		$table->construct_cell($link);
		
		$link = build_profile_link(htmlspecialchars_uni($stats['username']), intval($stats['uid']));
		$table->construct_cell($link);
		
		$table->construct_cell(newpoints_format_points($data[2]), array('class' => 'align_center'));
		$table->construct_cell(my_date($mybb->settings['dateformat'], intval($stats['date']), '', false).", ".my_date($mybb->settings['timeformat'], intval($stats['date'])), array('class' => 'align_center'));
		
		$table->construct_row();
	}
	
	if($table->num_rows() == 0)
	{
		$table->construct_cell($lang->newpoints_error_gathering, array('colspan' => 4));
		$table->construct_row();
	}
	
	$table->output($lang->newpoints_stats_lastdonations);
	
	$plugins->run_hooks("newpoints_admin_stats_noaction_end");
}

$plugins->run_hooks("newpoints_admin_stats_terminate");

$page->output_footer();

?>
