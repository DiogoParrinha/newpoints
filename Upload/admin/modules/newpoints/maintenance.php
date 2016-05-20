<?php
/***************************************************************************
 *
 *   NewPoints plugin (/admin/modules/newpoints/maintenance.php)
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

$plugins->run_hooks("newpoints_admin_maintenance_begin");

$page->add_breadcrumb_item($lang->newpoints_maintenance, 'index.php?module=newpoints-maintenance');
			
$page->output_header($lang->newpoints_maintenance);
	
$sub_tabs['newpoints_maintenance'] = array(
	'title'			=> $lang->newpoints_maintenance,
	'link'			=> 'index.php?module=newpoints-maintenance',
	'description'	=> $lang->newpoints_maintenance_description
);

$sub_tabs['newpoints_maintenance_edituser'] = array(
	'title'			=> $lang->newpoints_maintenance_edituser,
	'link'			=> 'index.php?module=newpoints-maintenance&amp;action=edituser',
	'description'	=> $lang->newpoints_maintenance_edituser_description
);

if (!$mybb->input['action']) // show page with various actions that can be taken
{
	$plugins->run_hooks("newpoints_admin_maintenance_start");
	
	$page->output_nav_tabs($sub_tabs, 'newpoints_maintenance');
	
	$form = new Form("index.php?module=newpoints-maintenance&amp;action=recount", "post", "newpoints");
	
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_recount);
	$form_container->output_row($lang->newpoints_recount_per_page, $lang->newpoints_recount_per_page_desc, $form->generate_text_box('per_page', 50, array('id' => 'per_page')), 'per_page');
	$form_container->end();

	$buttons = array();;
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	echo "<br />";
	
	$form = new Form("index.php?module=newpoints-maintenance&amp;action=reset", "post", "newpoints");
	
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_reset);
	$form_container->output_row($lang->newpoints_reset_per_page, $lang->newpoints_reset_per_page_desc, $form->generate_text_box('per_page', 500, array('id' => 'per_page')), 'per_page');
	$form_container->output_row($lang->newpoints_reset_points, $lang->newpoints_reset_points_desc, $form->generate_text_box('points', 0, array('id' => 'points')), 'points');
	$form_container->end();

	$buttons = array();;
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	echo "<br />";
	
	$form = new Form("index.php?module=newpoints-maintenance&amp;action=edituser", "post", "newpoints");
	
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_edituser);
	$form_container->output_row($lang->newpoints_edituser_uid, $lang->newpoints_edituser_uid_desc, $form->generate_text_box('uid', 0, array('id' => 'uid')), 'uid');
	$form_container->end();

	$buttons = array();
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	echo "<br />";
	
	$form = new Form("index.php?module=newpoints-maintenance&amp;action=reconstruct", "post", "newpoints");
	
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
		
	$form_container = new FormContainer($lang->newpoints_reconstruct);
	$form_container->output_row($lang->newpoints_reconstruct_title, $lang->newpoints_reconstruct_desc, '', 'reconstruct');
	$form_container->end();

	$buttons = array();;
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$form->output_submit_wrapper($buttons);
	$form->end();
	
	$plugins->run_hooks("newpoints_admin_maintenance_end");
}
elseif ($mybb->input['action'] == 'edituser')
{
	$plugins->run_hooks("newpoints_admin_maintenance_edituser_start");
	
	$page->output_nav_tabs($sub_tabs, 'newpoints_maintenance_edituser');
	
	if (!intval($mybb->input['uid']) || !($user = get_user(intval($mybb->input['uid']))))
	{
		flash_message($lang->newpoints_invalid_user, 'error');
		admin_redirect("index.php?module=newpoints-maintenance");
	}
	
	if($mybb->request_method == "post" && isset($mybb->input['do_change']))
	{
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-maintenance");
		}
		
		$updates =  array('newpoints' => floatval($mybb->input['points']));
		
		$plugins->run_hooks("newpoints_admin_maintenance_edituser_commit");
		
		$db->update_query('users', $updates, 'uid=\''.intval($mybb->input['uid']).'\'');
		
		flash_message($lang->newpoints_user_edited, 'success');
		admin_redirect("index.php?module=newpoints-maintenance");
	}
	
	$form = new Form("index.php?module=newpoints-maintenance&amp;action=edituser", "post", "newpoints");
	
	echo $form->generate_hidden_field("uid", intval($mybb->input['uid']));
	echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
	echo $form->generate_hidden_field("do_change", 1);
		
	$form_container = new FormContainer($lang->newpoints_edituser);
	$form_container->output_row($lang->newpoints_edituser_points, $lang->newpoints_edituser_points_desc, $form->generate_text_box('points', round($user['newpoints'], intval($mybb->settings['newpoints_main_decimal'])), array('id' => 'points')), 'points');
	$form_container->end();
	
	$plugins->run_hooks("newpoints_admin_maintenance_edituser_form");

	$buttons = array();;
	$buttons[] = $form->generate_submit_button($lang->newpoints_submit_button);
	$buttons[] = $form->generate_reset_button($lang->newpoints_reset_button);
	$form->output_submit_wrapper($buttons);
	
	$form->end();
	
	$plugins->run_hooks("newpoints_admin_maintenance_edituser_end");
}
elseif ($mybb->input['action'] == 'recount')
{
	$plugins->run_hooks("newpoints_admin_maintenance_recount_start");
	
	if($mybb->input['no']) // user clicked no
	{
		admin_redirect("index.php?module=newpoints-maintenance");
	}

	if($mybb->request_method == "post")
	{
		$mybb->input['per_page'] = intval($mybb->input['per_page']);
		
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'] || !$mybb->input['per_page'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-maintenance");
		}
		
		if (intval($mybb->input['start']) > 0)
			$start = intval($mybb->input['start']);
		else
			$start = 0;
			
		if (intval($mybb->input['per_page']) > 0)
			$per_page = intval($mybb->input['per_page']);
		else
			$per_page = 50;
			
		$query = $db->simple_select("users", "COUNT(*) as users");
		$total_users = $db->fetch_field($query, 'users');
		
		$allforumrules = newpoints_getallrules('forum');
			
		$query = $db->simple_select('users', 'uid,usergroup', '', array('order_by' => 'uid', 'order_dir' => 'ASC', 'limit' => "{$start}, {$per_page}"));
		while($user = $db->fetch_array($query)) {
			// recount points
			$points = 0;
			
			// group rules
			$grouprules = newpoints_getrules('group', $user['usergroup']);
			if (!$grouprules)
				$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
			// if the group rate is 0, nothing is going to be added so let's just skip this user
			if ($grouprules['rate'] == 0)
				continue;
				
			$firstposts = array(0);
				
			// threads and polls
			$totalthreads_query = $db->simple_select("threads", "firstpost,fid,poll", "uid='".$user['uid']."' AND visible=1");
			while ($thread = $db->fetch_array($totalthreads_query))
			{
				if ($mybb->settings['newpoints_income_newthread'] == 0)
					continue;
				
				if (!$allforumrules[$thread['fid']])
					$allforumrules[$thread['fid']]['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be added so let's just skip to the next post
				if ($allforumrules[$thread['fid']]['rate'] == 0)
					continue;
				
				// calculate points ber character bonus
				// let's see if the number of characters in the thread is greater than the minimum characters
				if (($charcount = my_strlen($mybb->input['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// give points to the author of the new thread
				$points += ($mybb->settings['newpoints_income_newthread']+$bonus)*$allforumrules[$thread['fid']]['rate'];
				
				if ($thread['poll'] != 0) // has a poll
				{
					$points += $mybb->settings['newpoints_income_newpoll']*$allforumrules[$thread['fid']]['rate'];
				}
				
				$firstposts[] = $thread['firstpost'];
			}
			
			// posts
			$totalposts_query = $db->simple_select("posts", "fid,message", "uid='".$user['uid']."' AND pid NOT IN(".implode(',', $firstposts).") AND visible=1");
			while ($post = $db->fetch_array($totalposts_query))
			{
				if ($mybb->settings['newpoints_income_newpost'] == 0)
					continue;
	
				if (!$allforumrules[$post['fid']])
					$allforumrules[$post['fid']]['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be added so let's just skip to the next post
				if ($allforumrules[$post['fid']]['rate'] == 0)
					continue;
					
				// calculate points ber character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = my_strlen($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// give points to the poster
				$points += ($mybb->settings['newpoints_income_newpost']+$bonus)*$allforumrules[$post['fid']]['rate'];
				
				$thread = get_thread($post['tid']);
				if ($thread['uid'] != $user['uid'])
				{
					// we are not the thread started so give points to him/her
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], $mybb->settings['newpoints_income_perreply'], $allforumrules[$post['fid']]['rate'], $grouprules['rate']);
				}
			}
			
			// poll votes
			if ($mybb->settings['newpoints_income_pervote'] != 0)
			{
				// just count the votes and don't get the poll and the thread (to calculate the correct income value  using the forum income rate but as it is a slow process, let's just not use forum rate here)
				$pollvotes_query = $db->simple_select("pollvotes", "COUNT(*) AS votes", "uid='".$user['uid']."'");
				$votes = $db->fetch_field($pollvotes_query, "votes");
				
				$points += $votes*$mybb->settings['newpoints_income_pervote'];
			}
			
			// private messages
			if ($mybb->settings['newpoints_income_pmsent'] != 0)
			{
				// count private messages this user has sent
				$pmssent_query = $db->simple_select("privatemessages", "COUNT(*) AS numpms", "fromid='".$user['uid']."' AND toid!='".$user['uid']."' AND receipt!='1'");
				$pmssent = $db->fetch_field($pmssent_query, "numpms");
				
				$points += $pmssent*$mybb->settings['newpoints_income_pmsent'];
			}
			
			$db->update_query('users', array('newpoints' => floatval($mybb->settings['newpoints_income_newreg'])+$points*$grouprules['rate']), 'uid=\''.$user['uid'].'\'');
		}
		
		if ($total_users > $start+intval($mybb->input['per_page']))
		{
			$form = new Form("index.php?module=newpoints-maintenance&amp;action=recount", "post", "newpoints");
			echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
			echo $form->generate_hidden_field("start", $start+intval($mybb->input['per_page']));
			echo $form->generate_hidden_field("per_page", intval($mybb->input['per_page']));
			echo "<div class=\"confirm_action\">\n";
			echo "<p>{$lang->newpoints_click_continue}</p>\n";
			echo "<br />\n";
			echo "<p class=\"buttons\">\n";
			echo $form->generate_submit_button($lang->newpoints_continue_button, array('class' => 'button_yes'));
			echo "</p>\n";
			echo "</div>\n";
			
			$form->end();
			
			$page->output_footer();

			exit;
		}
		
		log_admin_action($lang->newpoints_recount_done);
		
		flash_message($lang->newpoints_recounted, 'success');
		admin_redirect('index.php?module=newpoints-maintenance');
	}
	else
	{
		$mybb->input['per_page'] = intval($mybb->input['per_page']);
		$form = new Form("index.php?module=newpoints-maintenance&amp;action=recount&amp;per_page={$mybb->input['per_page']}&amp;my_post_key={$mybb->post_code}", 'post');
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->newpoints_recountconfirm}</p>\n";
		echo "<br />\n";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->yes, array('class' => 'button_yes'));
		echo $form->generate_submit_button($lang->no, array("name" => "no", 'class' => 'button_no'));
		echo "</p>\n";
		echo "</div>\n";
		$form->end();
	}
	
	$plugins->run_hooks("newpoints_admin_maintenance_recount_end");
}
elseif ($mybb->input['action'] == 'reset')
{
	$plugins->run_hooks("newpoints_admin_maintenance_reset_start");
	
	if($mybb->input['no']) // user clicked no
	{
		admin_redirect("index.php?module=newpoints-maintenance");
	}

	if($mybb->request_method == "post")
	{
		$mybb->input['per_page'] = intval($mybb->input['per_page']);
		
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'] || !$mybb->input['per_page'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-maintenance");
		}
		
		$points = floatval($mybb->input['points']);
		
		if (intval($mybb->input['start']) > 0)
			$start = intval($mybb->input['start']);
		else
			$start = 0;
			
		if (intval($mybb->input['per_page']) > 0)
			$per_page = intval($mybb->input['per_page']);
		else
			$per_page = 500;
			
		$query = $db->simple_select("users", "COUNT(*) as users");
		$total_users = $db->fetch_field($query, 'users');
			
		$query = $db->simple_select('users', 'uid', '', array('order_by' => 'uid', 'order_dir' => 'ASC', 'limit' => "{$start}, {$per_page}"));
		while($user = $db->fetch_array($query)) {
			// reset
			$db->update_query('users', array('newpoints' => $points), 'uid=\''.$user['uid'].'\'');
		}
		
		if ($total_users > $start+intval($mybb->input['per_page']))
		{
			$form = new Form("index.php?module=newpoints-maintenance&amp;action=reset&amp;my_post_key={$mybb->post_code}", "post", "newpoints");
			echo $form->generate_hidden_field("my_post_key", $mybb->post_code);
			echo $form->generate_hidden_field("start", $start+intval($mybb->input['per_page']));
			echo $form->generate_hidden_field("per_page", intval($mybb->input['per_page']));
			echo $form->generate_hidden_field("points", floatval($mybb->input['points']));
			echo "<div class=\"confirm_action\">\n";
			echo "<p>{$lang->newpoints_click_continue}</p>\n";
			echo "<br />\n";
			echo "<p class=\"buttons\">\n";
			echo $form->generate_submit_button($lang->newpoints_continue_button, array('class' => 'button_yes'));
			echo "</p>\n";
			echo "</div>\n";
			
			$form->end();
			
			$page->output_footer();

			exit;
		}
		
		log_admin_action($lang->newpoints_reset_done);
		
		flash_message($lang->newpoints_reset_action, 'success');
		admin_redirect('index.php?module=newpoints-maintenance');
	}
	else
	{
		$mybb->input['per_page'] = intval($mybb->input['per_page']);
		$form = new Form("index.php?module=newpoints-maintenance&amp;action=recount&amp;per_page={$mybb->input['per_page']}&amp;my_post_key={$mybb->post_code}", 'post');
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->newpoints_resetconfirm}</p>\n";
		echo "<br />\n";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->yes, array('class' => 'button_yes'));
		echo $form->generate_submit_button($lang->no, array("name" => "no", 'class' => 'button_no'));
		echo "</p>\n";
		echo "</div>\n";
		$form->end();
	}
	
	$plugins->run_hooks("newpoints_admin_maintenance_reset_start");
}
elseif ($mybb->input['action'] == 'reconstruct')
{
	$plugins->run_hooks("newpoints_admin_maintenance_reconstruct_start");
	
	if($mybb->input['no']) // user clicked no
	{
		admin_redirect("index.php?module=newpoints-maintenance");
	}

	if($mybb->request_method == "post")
	{		
		if(!isset($mybb->input['my_post_key']) || $mybb->post_code != $mybb->input['my_post_key'])
		{
			$mybb->request_method = "get";
			flash_message($lang->newpoints_error, 'error');
			admin_redirect("index.php?module=newpoints-maintenance");
		}
		
		newpoints_undo_template_edits();
		newpoints_do_template_edits();
		
		log_admin_action($lang->newpoints_reconstruct_done);
		
		flash_message($lang->newpoints_reconstructed, 'success');
		admin_redirect('index.php?module=newpoints-maintenance');
	}
	else
	{
		$mybb->input['per_page'] = intval($mybb->input['per_page']);
		$form = new Form("index.php?module=newpoints-maintenance&amp;action=reconstruct&amp;my_post_key={$mybb->post_code}", 'post');
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->newpoints_reconstructconfirm}</p>\n";
		echo "<br />\n";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->yes, array('class' => 'button_yes'));
		echo $form->generate_submit_button($lang->no, array("name" => "no", 'class' => 'button_no'));
		echo "</p>\n";
		echo "</div>\n";
		$form->end();
	}
	
	$plugins->run_hooks("newpoints_admin_maintenance_reconstruct_start");
}

$plugins->run_hooks("newpoints_admin_maintenance_terminate");

$page->output_footer();

?>
