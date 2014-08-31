<?php
/***************************************************************************
 *
 *   NewPoints plugin (/admin/modules/newpoints/grouprules.php)
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

$plugins->run_hooks("newpoints_admin_grouprules_begin");

$page->add_breadcrumb_item($lang->newpoints_grouprules, 'index.php?module=newpoints-grouprules');
			
$page->output_header($lang->newpoints_grouprules);
	
$sub_tabs['newpoints_grouprules'] = array(
	'title'			=> $lang->newpoints_grouprules,
	'link'			=> 'index.php?module=newpoints-grouprules',
	'description'	=> $lang->newpoints_grouprules_description
);

$sub_tabs['newpoints_grouprules_add'] = array(
	'title'			=> $lang->newpoints_grouprules_add,
	'link'			=> 'index.php?module=newpoints-grouprules&amp;action=add',
	'description'	=> $lang->newpoints_grouprules_add_description
);

$sub_tabs['newpoints_grouprules_edit'] = array(
	'title'			=> $lang->newpoints_grouprules_edit,
	'link'			=> 'index.php?module=newpoints-grouprules&amp;action=edit',
	'description'	=> $lang->newpoints_grouprules_edit_description
);

if (!$mybb->input['action']) // view grouprules
{
	$page->output_nav_tabs($sub_tabs, 'newpoints_grouprules');
	
	$plugins->run_hooks("newpoints_admin_grouprules_noaction_start");
	
	echo "<p class=\"notice\">{$lang->newpoints_grouprules_notice}</p>";
	
	// table
	$table = new Table;
	$table->construct_header($lang->newpoints_grouprules_name, array('width' => '50%'));
	$table->construct_header($lang->newpoints_grouprules_title, array('width' => '30%'));
	$table->construct_header($lang->newpoints_grouprules_options, array('width' => '20%', 'class' => 'align_center'));

	$query = $db->simple_select('newpoints_grouprules', '*', '', array('order_by' => 'rid', 'order_dir' => 'ASC'));
	while($rule = $db->fetch_array($query)) {
		$table->construct_cell(htmlspecialchars_uni($rule['name'])."<br /><small>".htmlspecialchars_uni($rule['description'])."</small>");
		
		$group = newpoints_get_usergroup($rule['gid']);
		
		$table->construct_cell(htmlspecialchars_uni($group['title']));
		$table->construct_cell("<a href=\"index.php?module=newpoints-grouprules&amp;action=delete_rule&amp;rid={$rule['rid']}\" target=\"_self\">{$lang->newpoints_delete}</a> - <a href=\"index.php?module=newpoints-grouprules&amp;action=edit&amp;rid={$rule['rid']}\" target=\"_self\">{$lang->newpoints_edit}</a>", array('class' => 'align_center')); // delete button

		$table->construct_row();
	}
	
	if($table->num_rows() == 0)
	{
		$table->construct_cell($lang->newpoints_grouprules_none, array('colspan' => 3));
		$table->construct_row();
	}
	
	$table->output($lang->newpoints_grouprules_rules);
	
	$plugins->run_hooks("newpoints_admin_grouprules_noaction_end");
}
elseif ($mybb->input['action'] == 'add')
{
	$plugins->run_hooks("newpoints_admin_grouprules_add_start");
	
	$page->output_nav_tabs($sub_tabs, 'newpoints_grouprules_add');
	
	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-grouprules");
		}
		
		if (!$mybb->input['name'] || !$mybb->input['group'])
		{
			flash_message($lang->newpoints_missing_fields, 'error');
			admin_redirect("index.php?module=newpoints-grouprules");
		}
		
		$insert_query = array(
			'name' => $db->escape_string($mybb->input['name']),
			'description' => $db->escape_string($mybb->input['description']),
			'rate' => floatval($mybb->input['rate']),
			'gid' => intval($mybb->input['group']),
			'pointsearn' => floatval($mybb->input['pointsearn']),
			'period' => intval($mybb->input['period'])
		);
		
		$insert_query = $plugins->run_hooks("newpoints_admin_grouprules_add_insert", $insert_query);
		
		$db->insert_query('newpoints_grouprules', $insert_query);
		
		// Rebuild rules cache
		$array = array();
		newpoints_rebuild_rules_cache($array);
		
		flash_message($lang->newpoints_grouprules_added, 'success');
		admin_redirect("index.php?module=newpoints-grouprules");
	}
	
	$options[0] = $lang->newpoints_select_group;
	$query = $db->simple_select("usergroups", "gid, title", "", array('order_by' => 'title'));
	while($usergroup = $db->fetch_array($query))
	{
		$options[$usergroup['gid']] = $usergroup['title'];
	}
	
	$form = new Form("index.php?module=newpoints-grouprules&amp;action=add", "post", "newpoints");

	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_grouprules_addrule);
	$form_container->output_row($lang->newpoints_grouprules_name."<em>*</em>", $lang->newpoints_grouprules_name_desc, $form->generate_text_box('name', '', array('id' => 'name')), 'name');
	$form_container->output_row($lang->newpoints_grouprules_desc, $lang->newpoints_grouprules_desc_desc, $form->generate_text_box('description', '', array('id' => 'description')), 'description');
	$form_container->output_row($lang->newpoints_grouprules_rate."<em>*</em>", $lang->newpoints_grouprules_rate_desc, $form->generate_text_box('rate', '1', array('id' => 'rate')), 'rate');
	$form_container->output_row($lang->newpoints_grouprules_pointsearn, $lang->newpoints_grouprules_pointsearn_desc, $form->generate_text_box('pointsearn', '0', array('id' => 'pointsearn')), 'pointsearn');
	$form_container->output_row($lang->newpoints_grouprules_period, $lang->newpoints_grouprules_period_desc, $form->generate_text_box('period', '0', array('id' => 'period')), 'period');
	$form_container->output_row($lang->newpoints_grouprules_group."<em>*</em>", $lang->newpoints_grouprules_group_desc, $form->generate_select_box('group', $options, 0, array('id' => 'group')), 'group');
	
	$form_container = $plugins->run_hooks("newpoints_admin_grouprules_add", $form_container);
	
	$form_container->end();

	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
elseif ($mybb->input['action'] == 'edit')
{
	$plugins->run_hooks("newpoints_admin_grouprules_edit_start");
	
	$page->output_nav_tabs($sub_tabs, 'newpoints_grouprules_edit');
	
	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-grouprules");
		}
		
		if (!$mybb->input['name'] || !$mybb->input['group'])
		{
			flash_message($lang->newpoints_missing_fields, 'error');
			admin_redirect("index.php?module=newpoints-grouprules");
		}
		
		$update_query = array(
			'name' => $db->escape_string($mybb->input['name']),
			'description' => $db->escape_string($mybb->input['description']),
			'rate' => floatval($mybb->input['rate']),
			'gid' => intval($mybb->input['group']),
			'pointsearn' => floatval($mybb->input['pointsearn']),
			'period' => intval($mybb->input['period'])
		);
		
		$update_query = $plugins->run_hooks("newpoints_admin_grouprules_edit_update", $update_query);
		
		$db->update_query('newpoints_grouprules', $update_query, 'rid='.intval($mybb->input['rid']));
		
		// Rebuild rules cache
		$array = array();
		newpoints_rebuild_rules_cache();
		
		flash_message($lang->newpoints_grouprules_edited, 'success');
		admin_redirect("index.php?module=newpoints-grouprules");
	}
	
	$query = $db->simple_select('newpoints_grouprules', '*', 'rid=\''.intval($mybb->input['rid']).'\'');
	$rule = $db->fetch_array($query);
	if (!$rule)
	{
		flash_message($lang->newpoints_grouprules_invalid, 'error');
		admin_redirect("index.php?module=newpoints-grouprules");
	}
	
	$options[0] = $lang->newpoints_select_group;
	$query = $db->simple_select("usergroups", "gid, title", "", array('order_by' => 'title'));
	while($usergroup = $db->fetch_array($query))
	{
		$options[$usergroup['gid']] = $usergroup['title'];
	}
	
	$form = new Form("index.php?module=newpoints-grouprules&amp;action=edit", "post", "newpoints");

	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
	echo $form->generate_hidden_field("rid", $rule['rid']);
		
	$form_container = new FormContainer($lang->newpoints_grouprules_editrule);
	$form_container->output_row($lang->newpoints_grouprules_name."<em>*</em>", $lang->newpoints_grouprules_name_desc, $form->generate_text_box('name', htmlspecialchars_uni($rule['name']), array('id' => 'name')), 'name');
	$form_container->output_row($lang->newpoints_grouprules_desc, $lang->newpoints_grouprules_desc_desc, $form->generate_text_box('description', htmlspecialchars_uni($rule['description']), array('id' => 'description')), 'description');
	$form_container->output_row($lang->newpoints_grouprules_rate."<em>*</em>", $lang->newpoints_grouprules_rate_desc, $form->generate_text_box('rate', floatval($rule['rate']), array('id' => 'rate')), 'rate');
	$form_container->output_row($lang->newpoints_grouprules_pointsearn, $lang->newpoints_grouprules_pointsearn_desc, $form->generate_text_box('pointsearn', floatval($rule['pointsearn']), array('id' => 'pointsearn')), 'pointsearn');
	$form_container->output_row($lang->newpoints_grouprules_period, $lang->newpoints_grouprules_period_desc, $form->generate_text_box('period', intval($rule['period']), array('id' => 'period')), 'period');
	$form_container->output_row($lang->newpoints_grouprules_group."<em>*</em>", $lang->newpoints_grouprules_group_desc, $form->generate_select_box('group', $options, intval($rule['gid']), array('id' => 'group')), 'group');
	
	$form_container = $plugins->run_hooks("newpoints_admin_grouprules_edit", $form_container);
	
	$form_container->end();

	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
}
elseif ($mybb->input['action'] == 'delete_rule')
{
	if($mybb->input['no']) // user clicked no
	{
		admin_redirect("index.php?module=newpoints-grouprules");
	}

	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-grouprules");
		}
		
		if (!$db->fetch_field($db->simple_select('newpoints_grouprules', 'name', 'rid='.intval($mybb->input['rid']), array('limit' => 1)), 'name'))
		{
			flash_message($lang->newpoints_grouprules_invalid, 'error');
			admin_redirect('index.php?module=newpoints-grouprules');
		}
		else {																				 
			$db->delete_query('newpoints_grouprules', 'rid='.intval($mybb->input['rid']));
			
			// Rebuild rules cache
			$array = array();
			newpoints_rebuild_rules_cache();
			
			flash_message($lang->newpoints_grouprules_deleted, 'success');
			admin_redirect('index.php?module=newpoints-grouprules');
		}
	}
	else
	{
		$mybb->input['rid'] = intval($mybb->input['rid']);
		$form = new Form("index.php?module=newpoints-grouprules&amp;action=delete_rule&amp;rid={$mybb->input['rid']}&amp;my_post_key={$mybb->post_code}", 'post');
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->newpoints_grouprules_deleteconfirm}</p>\n";
		echo "<br />\n";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->yes, array('class' => 'button_yes'));
		echo $form->generate_submit_button($lang->no, array("name" => "no", 'class' => 'button_no'));
		echo "</p>\n";
		echo "</div>\n";
		$form->end();
	}
}

$plugins->run_hooks("newpoints_admin_grouprules_terminate");

$page->output_footer();

?>
