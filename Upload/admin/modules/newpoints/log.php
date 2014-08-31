<?php
/***************************************************************************
 *
 *   NewPoints plugin (/admin/modules/newpoints/log.php)
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

$plugins->run_hooks("newpoints_admin_log_begin");

if (!$mybb->input['action']) // view logs
{
	$page->add_breadcrumb_item($lang->newpoints_log, 'index.php?module=newpoints-log');
			
	$page->output_header($lang->newpoints_log);
		
	$sub_tabs['newpoints_log'] = array(
		'title'			=> $lang->newpoints_log,
		'link'			=> 'index.php?module=newpoints-log',
		'description'	=> $lang->newpoints_log_description
	);
	
	$page->output_nav_tabs($sub_tabs, 'newpoints_log');
	
	$per_page = 10;
	if($mybb->input['page'] && $mybb->input['page'] > 1)
	{
		$mybb->input['page'] = intval($mybb->input['page']);
		$start = ($mybb->input['page']*$per_page)-$per_page;
	}
	else
	{
		$mybb->input['page'] = 1;
		$start = 0;
	}
	
	$sql = '';
	
	$filter_msg = '';
	
	// Process "username" search
	if(isset($mybb->input['username']) && $mybb->input['username'] != '')
	{
		$query = $db->simple_select('users', 'uid', 'username=\''.$db->escape_string(trim($mybb->input['username'])).'\'');
		$uid = $db->fetch_field($query, 'uid');
		if($uid <= 0)
		{
			flash_message($lang->newpoints_invalid_username, 'error');
			admin_redirect("index.php?module=newpoints-log");
		}
			
		$sql .= 'uid='.(int)$uid;
		
		$url_filters .= '&amp;username='.urlencode(htmlspecialchars_uni($mybb->input['username']));
		
		$filter_msg .= $lang->newpoints_username.': '.htmlspecialchars_uni($mybb->input['username']);
	}

	// Process "fields" search
	$selected = array();
	if(isset($mybb->input['fields']) && is_array($mybb->input['fields']) && !empty($mybb->input['fields']))
	{
		$or = '';
		$close = '';
		
		if($sql != '')
		{
			$sql .= ' AND (';
			$close = ')';
		}
		
		foreach($mybb->input['fields'] as $field)
		{
			$field = htmlspecialchars_uni($field);
			$sql .= $or.'action=\''.$field.'\'';
			if($or == '')
				$or = ' OR ';
				
			$selected[$field] = $field;
		
			if(!isset($selected[$field]))
				$selected[$field] = $field;
				
			if($filter_msg != '')
				$filter_msg .= '<br />'.$field;
				
			$url_filters .= '&amp;fields[]='.$field;
		}

		$sql .= $close;
	}
	
	if($filter_msg != '')
		echo "<p class=\"notice\">".$lang->sprintf($lang->newpoints_filter, $filter_msg)."</p><br />";
	
	echo "<p class=\"notice\">{$lang->newpoints_log_notice}</p>";
	
	$query = $db->simple_select("newpoints_log", "COUNT(lid) as log_entries", $sql);
	$total_rows = $db->fetch_field($query, "log_entries");
	if ($total_rows > $per_page)
		echo "<br />".draw_admin_pagination($mybb->input['page'], $per_page, $total_rows, "index.php?module=newpoints-log&amp;page={page}".$url_filters);
	
	// table
	$table = new Table;
	$table->construct_header($lang->newpoints_log_action, array('width' => '15%'));
	$table->construct_header($lang->newpoints_log_data, array('width' => '30%'));
	$table->construct_header($lang->newpoints_log_user, array('width' => '20%'));
	$table->construct_header($lang->newpoints_log_date, array('width' => '20%', 'class' => 'align_center'));
	$table->construct_header($lang->newpoints_log_options, array('width' => '15%', 'class' => 'align_center'));

	$fields = array();
	$query = $db->simple_select('newpoints_log', '*', $sql, array('order_by' => 'date', 'order_dir' => 'DESC', 'limit' => "{$start}, {$per_page}"));
	while($log = $db->fetch_array($query))
	{
		$table->construct_cell(htmlspecialchars_uni($log['action']));
		$table->construct_cell(htmlspecialchars_uni($log['data']));
		$link = build_profile_link(htmlspecialchars_uni($log['username']), intval($log['uid']));
		$table->construct_cell($link);
		$table->construct_cell(my_date($mybb->settings['dateformat'], intval($log['date']), '', false).", ".my_date($mybb->settings['timeformat'], intval($log['date'])), array('class' => 'align_center'));
		$table->construct_cell("<a href=\"index.php?module=newpoints-log&amp;action=delete_log&amp;lid={$log['lid']}&amp;my_post_key={$mybb->post_code}\" target=\"_self\">{$lang->newpoints_delete}</a>", array('class' => 'align_center')); // delete button
		
		$table->construct_row();
	}
	
	if($table->num_rows() == 0)
	{
		$table->construct_cell($lang->newpoints_no_log_entries, array('colspan' => 5));
		$table->construct_row();
	}
	
	$table->output($lang->newpoints_log_entries);
	
	echo "<br />";
	
	// Get all actions
	$fields = array();
	$q = $db->query("SELECT action FROM `".TABLE_PREFIX."newpoints_log` GROUP BY action");
	while($action = $db->fetch_field($q, 'action'))
		$fields[htmlspecialchars_uni($action)] = htmlspecialchars_uni($action);
	
	$form = new Form("index.php?module=newpoints-log", "post", "newpoints");
	
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_log_filter);
	$form_container->output_row($lang->newpoints_filter_username, $lang->newpoints_filter_username_desc, $form->generate_text_box('username', htmlspecialchars_uni($mybb->input['username']), array('id' => 'username')), 'username');
	$form_container->output_row($lang->newpoints_filter_actions, $lang->newpoints_filter_actions_desc, $form->generate_select_box('fields[]', $fields, $selected, array('id' => 'fields', 'multiple' => true)), 'fields');
	$form_container->end();

	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	echo "<br />";
	
	$form = new Form("index.php?module=newpoints-log&amp;action=prune", "post", "newpoints");
	
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_log_prune);
	$form_container->output_row($lang->newpoints_older_than, $lang->newpoints_older_than_desc, $form->generate_text_box('days', 30, array('id' => 'days')), 'days');
	$form_container->end();

	$buttons = array();;
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
elseif ($mybb->input['action'] == 'delete_log')
{
	if($mybb->input['no']) // user clicked no
	{
		admin_redirect("index.php?module=newpoints-log");
	}

	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-log");
		}
		
		if (!$db->fetch_field($db->simple_select('newpoints_log', 'action', 'lid='.intval($mybb->input['lid']), array('limit' => 1)), 'action'))
		{
			flash_message($lang->newpoints_log_invalid, 'error');
			admin_redirect('index.php?module=newpoints-log');
		}
		else {																				 
			$db->delete_query('newpoints_log', 'lid='.intval($mybb->input['lid']));
			flash_message($lang->newpoints_log_deleted, 'success');
			admin_redirect('index.php?module=newpoints-log');
		}
	}
	else
	{
		$page->add_breadcrumb_item($lang->newpoints_log, 'index.php?module=newpoints-log');
			
		$page->output_header($lang->newpoints_log);
		
		$mybb->input['lid'] = intval($mybb->input['lid']);
		$form = new Form("index.php?module=newpoints-log&amp;action=delete_log&amp;lid={$mybb->input['lid']}&amp;my_post_key={$mybb->post_code}", 'post');
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->newpoints_log_deleteconfirm}</p>\n";
		echo "<br />\n";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->yes, array('class' => 'button_yes'));
		echo $form->generate_submit_button($lang->no, array("name" => "no", 'class' => 'button_no'));
		echo "</p>\n";
		echo "</div>\n";
		$form->end();
	}
}
elseif ($mybb->input['action'] == 'prune')
{
	if($mybb->input['no']) // user clicked no
	{
		admin_redirect("index.php?module=newpoints-log");
	}

	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-log");
		}
		
		$db->delete_query('newpoints_log', 'date < '.(TIME_NOW - intval($mybb->input['days'])*60*60*24));
		flash_message($lang->newpoints_log_pruned, 'success');
		admin_redirect('index.php?module=newpoints-log');
	}
	else
	{
		$page->add_breadcrumb_item($lang->newpoints_log, 'index.php?module=newpoints-log');
			
		$page->output_header($lang->newpoints_log);
		
		$mybb->input['days'] = intval($mybb->input['days']);
		$form = new Form("index.php?module=newpoints-log&amp;action=prune&amp;days={$mybb->input['days']}&amp;my_post_key={$mybb->post_code}", 'post');
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->newpoints_log_pruneconfirm}</p>\n";
		echo "<br />\n";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->yes, array('class' => 'button_yes'));
		echo $form->generate_submit_button($lang->no, array("name" => "no", 'class' => 'button_no'));
		echo "</p>\n";
		echo "</div>\n";
		$form->end();
	}
}

$plugins->run_hooks("newpoints_admin_log_terminate");

$page->output_footer();

?>
