<?php
/***************************************************************************
 *
 *   NewPoints plugin (/inc/plugins/newpoints/core/hooks.php)
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

if(!defined("IN_MYBB") || !defined("NP_HOOKS"))
	die("This file cannot be accessed directly.");
	
if (NP_HOOKS == 1)
{
	$plugins->add_hook('admin_load', 'newpoints_admin_load_hook');
	$plugins->add_hook('admin_newpoints_menu', 'newpoints_admin_menu_hook');
	$plugins->add_hook('admin_newpoints_action_handler', 'newpoints_admin_action_handler_hook');
	$plugins->add_hook('admin_newpoints_permissions', 'newpoints_admin_permissions_hook');
	
	function newpoints_admin_load_hook()
	{
		global $plugins, $newpoints_plugins, $mybb;
		
		if (!$newpoints_plugins || !isset($newpoints_plugins))
		{
			newpoints_load_plugins();
		}
		
		// as plugins can't hook to admin_load, we must allow them to hook to newpoints_admin_load
		$plugins->run_hooks("newpoints_admin_load");
	}

	function newpoints_admin_menu_hook(&$sub_menu)
	{
		global $plugins, $newpoints_plugins;
		
		if (!$newpoints_plugins || !isset($newpoints_plugins))
		{
			newpoints_load_plugins();
		}
		
		// as plugins can't hook to admin_newpoints_menu, we must allow them to hook to newpoints_admin_newpoints_menu
		$sub_menu = $plugins->run_hooks("newpoints_admin_newpoints_menu", $sub_menu);
	}

	function newpoints_admin_action_handler_hook(&$actions)
	{
		global $plugins, $newpoints_plugins;
		
		if (!$newpoints_plugins || !isset($newpoints_plugins))
		{
			newpoints_load_plugins();
		}
		
		// as plugins can't hook to admin_newpoints_action_handler, we must allow them to hook to newpoints_newpoints_action_handler
		$actions = $plugins->run_hooks("newpoints_admin_newpoints_action_handler", $actions);
	}

	function newpoints_admin_permissions_hook(&$admin_permissions)
	{
		global $plugins, $newpoints_plugins;
		
		if (!$newpoints_plugins || !isset($newpoints_plugins))
		{
			newpoints_load_plugins();
		}
		
		// as plugins can't hook to admin_newpoints_permissions, we must allow them to hook to newpoints_newpoints_permissions
		$admin_permissions = $plugins->run_hooks("newpoints_admin_newpoints_permissions", $admin_permissions);
	}
}
// outside ACP hooks
elseif (NP_HOOKS == 2)
{
	$plugins->add_hook('global_start', 'newpoints_plugins_start');
	$plugins->add_hook('xmlhttp', 'newpoints_load_xmlhttp'); // we want to make sure plugins can use the xmlhttp file
	$plugins->add_hook('archive_start', 'newpoints_load_archive'); // we want to make sure plugins can use the archive
	
	// postbit
	$plugins->add_hook('postbit', 'newpoints_postbit', 50); // set priority to 50
	$plugins->add_hook('postbit_prev', 'newpoints_postbit', 50); // set priority to 50
	$plugins->add_hook('postbit_pm', 'newpoints_postbit', 50); // set priority to 50
	$plugins->add_hook('postbit_announcement', 'newpoints_postbit', 50); // set priority to 50 
	
	// member profile
	$plugins->add_hook("member_profile_end", "newpoints_profile");
	
	// per new post
	$plugins->add_hook('datahandler_post_insert_post', 'newpoints_newpost');
	// edit post
	$plugins->add_hook('datahandler_post_update', 'newpoints_editpost');
	$plugins->add_hook('xmlhttp', 'newpoints_editpost_xmlhttp');
	
	// delete post
	$plugins->add_hook('class_moderation_delete_post_start', 'newpoints_deletepost');
	// soft delete posts
	$plugins->add_hook('class_moderation_soft_delete_posts', 'newpoints_softdeleteposts');
	// restore soft deleted posts
	$plugins->add_hook('class_moderation_restore_posts', 'newpoints_restoreposts');
	
	// approve threads
	$plugins->add_hook('class_moderation_approve_threads', 'newpoints_approvethreads');
	// approve posts
	$plugins->add_hook('class_moderation_approve_posts', 'newpoints_approveposts');
	
	// unapprove threads
	$plugins->add_hook('class_moderation_unapprove_threads', 'newpoints_unapprovethreads');
	// unapprove posts
	$plugins->add_hook('class_moderation_unapprove_posts', 'newpoints_unapproveposts');
	
	// per new thread
	$plugins->add_hook('datahandler_post_insert_thread', 'newpoints_newthread');
	// delete thread
	$plugins->add_hook('class_moderation_delete_thread', 'newpoints_deletethread');
	// soft delete threads
	$plugins->add_hook('class_moderation_soft_delete_threads', 'newpoints_softdeletethreads');
	// restore soft deleted threads
	$plugins->add_hook('class_moderation_restore_threads', 'newpoints_restorethreads');
	
	// per new poll
	$plugins->add_hook('polls_do_newpoll_process', 'newpoints_newpoll');
	$plugins->add_hook('class_moderation_delete_poll', 'newpoints_deletepoll');
	
	// per new registration
	$plugins->add_hook("member_do_register_end", "newpoints_newreg");
	
	// per poll vote
	$plugins->add_hook('polls_vote_process', 'newpoints_pervote');
	
	// per pm sent
	$plugins->add_hook('private_do_send_end', 'newpoints_pmsent');
	
	// per thread rate
	$plugins->add_hook('ratethread_process', 'newpoints_perrate');
	
	// per page viewed and visit
	$plugins->add_hook('global_end', 'newpoints_perview');
	
	// minimum points to view
	$plugins->add_hook('forumdisplay_end', 'newpoints_blockview');
	$plugins->add_hook('showthread_start', 'newpoints_blockview');
	$plugins->add_hook('editpost_start', 'newpoints_blockview_edit');
	$plugins->add_hook('sendthread_do_sendtofriend_start', 'newpoints_blockview_send');
	$plugins->add_hook('sendthread_start', 'newpoints_blockview_send');
	$plugins->add_hook('archive_forum_start', 'newpoints_blockview_archive');
	$plugins->add_hook('archive_thread_start', 'newpoints_blockview_archive');
	$plugins->add_hook('printthread_end', 'newpoints_blockview_print');
	
	// minimum points to post
	$plugins->add_hook('newreply_start', 'newpoints_blockpost');
	$plugins->add_hook('newreply_do_newreply_start', 'newpoints_blockpost');
	$plugins->add_hook('newthread_start', 'newpoints_blockpost');
	$plugins->add_hook('newthread_do_newthread_start', 'newpoints_blockpost');
	
	// ****************** //
	// GENERAL HOOKS START //
	// ****************** //
	
	// Loads plugins when in archive and runs a new hook called 'newpoints_archive_start' that can be used by NewPoints plugins (instead of archive_start)
	function newpoints_load_archive()
	{
		global $plugins;
		
		newpoints_load_plugins();
		//newpoints_load_settings();
		
		// as plugins can't hook to archive_start, we must allow them to hook to newpoints_archive_start
		$plugins->run_hooks("newpoints_archive_start");
	}

	// Loads plugins from global_start and runs a new hook called 'newpoints_global_start' that can be used by NewPoints plugins (instead of global_start)
	// global_start can't be used by NP plugins
	function newpoints_plugins_start()
	{
		global $plugins, $mybb, $mypoints;
		
		newpoints_load_plugins();
		//newpoints_load_settings();
		
		if($mybb->user['uid'] > 0)
		{
			$mypoints = newpoints_format_points($mybb->user['newpoints']);
		}
		else
			$mypoints = 0;
		
		// as plugins can't hook to global_start, we must allow them to hook to global_start
		$plugins->run_hooks("newpoints_global_start");
	}

	// Loads plugins from global_start and runs a new hook called 'newpoints_global_start' that can be used by NewPoints plugins (instead of global_start)
	// global_start can't be used by NP plugins
	function newpoints_load_xmlhttp()
	{
		global $plugins;
		
		newpoints_load_plugins();
		//newpoints_load_settings();
		
		// as plugins can't hook to xmlhttp, we must allow them to hook to newpoints_xmlhttp
		$plugins->run_hooks("newpoints_xmlhttp");
	}
	
	// postbit
	function newpoints_postbit(&$post)
	{
		global $mybb, $db, $currency, $points, $templates, $donate, $lang, $uid;
		
		if($post['uid'] == 0)
		{
			$post['newpoints_postbit'] = '';
			return;
		}
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
		{
			$post['newpoints_postbit'] = '';
			return;
		}
		
		$lang->load("newpoints");
		
		$currency = $mybb->settings['newpoints_main_curname'];
		$points = newpoints_format_points($post['newpoints']);
		$uid = intval($post['uid']);
		
		if ($mybb->settings['newpoints_main_donationsenabled'] && $post['uid'] != $mybb->user['uid'] && $mybb->user['uid'] > 0)
			eval("\$donate = \"".$templates->get('newpoints_donate_inline')."\";");
		else
			$donate = '';
		
		eval("\$post['newpoints_postbit'] = \"".$templates->get('newpoints_postbit')."\";");
	}

	// member profile
	function newpoints_profile()
	{
		global $mybb, $db, $currency, $points, $templates, $memprofile, $newpoints_profile, $lang, $uid;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
		{
			$newpoints_profile = '';
			return;
		}
		
		$lang->load("newpoints");
		
		$currency = $mybb->settings['newpoints_main_curname'];
		$points = newpoints_format_points($memprofile['newpoints']);
		$uid = intval($memprofile['uid']);
		
		if ($mybb->settings['newpoints_main_donationsenabled'] && $memprofile['uid'] != $mybb->user['uid'] && $mybb->user['uid'] > 0)
			eval("\$donate = \"".$templates->get('newpoints_donate_inline')."\";");
		else
			$donate = '';
		
		eval("\$newpoints_profile = \"".$templates->get('newpoints_profile')."\";");
	}
	
	// ****************** //
	// INCOME HOOKS START //
	// ****************** //

	// new post
	function newpoints_newpost(&$data)
	{
		global $db, $mybb, $post, $thread;
		
		if ($mybb->input['action'] != "do_newreply" || $post['savedraft'])
			return;
		
		if($data->post_insert_data['visible'] != 1)
		{
			// If it's not visible, then we may have moderation (drafts are already considered above so it doesn't matter here)
			return;
		}
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpost'] == 0)
			return;
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $data->post_insert_data['fid']);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;

		// calculate points per character bonus
		// let's see if the number of characters in the post is greater than the minimum characters
		if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
			$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
		else
			$bonus = 0;
		
		// give points to the poster
		newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_newpost']+$bonus, $forumrules['rate'], $grouprules['rate']);
		
		if ($thread['uid'] != $mybb->user['uid'])
		{
			// we are not the thread started so give points to him/her
			if ($mybb->settings['newpoints_income_perreply'] != 0)
				newpoints_addpoints($thread['uid'], $mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
		}
	}

	function newpoints_approveposts($pids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpost'] == 0)
			return;
		
		if(!empty($pids))
		{
			foreach($pids as $pid)
			{
				$post = get_post((int)$pid);
				$thread = get_thread($post['tid']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// the post author != thread author?
				if ($thread['uid'] != $post['uid'])
				{
					// we are not the thread started so give points to them
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], $mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
				}
				
				// give points to the author of the post
				newpoints_addpoints($post['uid'], $mybb->settings['newpoints_income_newpost']+$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}
	
	function newpoints_unapproveposts($pids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpost'] == 0)
			return;
		
		if(!empty($pids))
		{
			foreach($pids as $pid)
			{
				$post = get_post((int)$pid);
				$thread = get_thread($post['tid']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// the post author != thread author?
				if ($thread['uid'] != $post['uid'])
				{
					// we are not the thread started so remove points from them
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], -$mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
				}
				
				// give points to the author of the post
				newpoints_addpoints($post['uid'], -$mybb->settings['newpoints_income_newpost']-$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}

	// edit post
	function newpoints_editpost(&$newpost)
	{
		global $db, $mybb, $thread;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_perchar'] == 0)
			return;
			
		if ($mybb->input['action'] != "do_editpost" || $mybb->input['editdraft'])
			return;
				
		$fid = intval($newpost->data['fid']);
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
			
		// get old message
		$post = get_post(intval($newpost->data['pid']));
		$oldcharcount = newpoints_count_characters($post['message']);
		$newcharcount = newpoints_count_characters($newpost->data['message']);

		// calculate points per character bonus
		// let's see if the number of characters in the post is greater than the minimum characters
		if ($newcharcount >= $mybb->settings['newpoints_income_minchar']) 
		{
			// if we have more characters now
			if ($newcharcount > $oldcharcount)
			{
				// calculate bonus based on difference of characters
				// bonus will be positive as the new message is longer than the old one
				$bonus = ($newcharcount - $oldcharcount) * $mybb->settings['newpoints_income_perchar'];
			}
			// otherwise if the message is shorter
			elseif ($newcharcount < $oldcharcount)
			{
				// calculate bonus based on difference of characters
				// bonus will be negative as the new message is shorter than the old one
				$bonus = ($newcharcount - $oldcharcount) * $mybb->settings['newpoints_income_perchar'];
			}
			// else if the length is the same, the bonus is 0
			elseif ($newcharcount == $oldcharcount)
			{
				$bonus = 0;
			}
		}
		elseif ($newcharcount >= $mybb->settings['newpoints_income_minchar'] && $oldcharcount >= $mybb->settings['newpoints_income_minchar']) 
		{
			// calculate bonus based on difference of characters
			// bonus will be negative as the new message is shorter than the minimum chars
			$bonus = ($newcharcount - $oldcharcount) * $mybb->settings['newpoints_income_perchar'];
		}
		
		// give points to the poster
		newpoints_addpoints($mybb->user['uid'], $bonus, $forumrules['rate'], $grouprules['rate'], false, true);
	}

	// edit post - counts less chars on edit because of \n\r being deleted
	function newpoints_editpost_xmlhttp()
	{
		global $db, $mybb, $thread, $lang, $charset;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_perchar'] == 0)
			return;
		
		if ($mybb->input['action'] != "edit_post")
			return;
		elseif ($mybb->input['action'] == "edit_post" && $mybb->input['do'] != 'update_post')
			return;
			
		if ($mybb->input['editdraft'])
			return;
			
		// Verify POST request
		if(!verify_post_check($mybb->input['my_post_key'], true))
		{
			xmlhttp_error($lang->invalid_post_code);
		}
			
		$post = get_post($mybb->input['pid']);
				
		$fid = intval($post['fid']);
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
			
		// get old message
		$oldcharcount = newpoints_count_characters($post['message']);
		
		$message = strval($_POST['value']);
		if(my_strtolower($charset) != "utf-8")
		{
			if(function_exists("iconv"))
			{
				$message = iconv($charset, "UTF-8//IGNORE", $message);
			}
			else if(function_exists("mb_convert_encoding"))
			{
				$message = @mb_convert_encoding($message, $charset, "UTF-8");
			}
			else if(my_strtolower($charset) == "iso-8859-1")
			{
				$message = utf8_decode($message);
			}
		}
		
		$newcharcount = newpoints_count_characters($message);
		
		// calculate points per character bonus
		// let's see if the number of characters in the post is greater than the minimum characters
		if ($newcharcount >= $mybb->settings['newpoints_income_minchar']) 
		{
			// if we have more characters now
			if ($newcharcount > $oldcharcount)
			{
				// calculate bonus based on difference of characters
				// bonus will be positive as the new message is longer than the old one
				$bonus = ($newcharcount - $oldcharcount) * $mybb->settings['newpoints_income_perchar'];
			}
			// otherwise if the message is shorter
			elseif ($newcharcount < $oldcharcount)
			{
				// calculate bonus based on difference of characters
				// bonus will be positive as the new message is longer than the old one
				$bonus = ($newcharcount - $oldcharcount) * $mybb->settings['newpoints_income_perchar'];
			}
			// else if the length is the same, the bonus is 0
			elseif ($newcharcount == $oldcharcount)
			{
				$bonus = 0;
			}
		}
		else
		{
			// calculate bonus based on difference of characters
			// bonus will be negative as the new message is shorter than the minimum chars
			$bonus = ($newcharcount - $oldcharcount) * $mybb->settings['newpoints_income_perchar'];
		}
		
		// give points to the poster
		newpoints_addpoints($mybb->user['uid'], $bonus, $forumrules['rate'], $grouprules['rate'], false, true);
	}


	// delete post
	function newpoints_deletepost($pid)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpost'] == 0)
			return;
			
		$post = get_post((int)$pid);
		// It's currently soft deleted, so we do nothing as we already subtracted points when doing that
		// If it's not visible (unapproved) we also don't take out any money
		if($post['visible'] == -1 || $post['visible'] == 0)
			return;
		
		$thread = get_thread($post['tid']);
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be removed so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be removed so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;

		// calculate points per character bonus
		// let's see if the number of characters in the post is greater than the minimum characters
		if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
			$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
		else
			$bonus = 0;
		
		if ($thread['uid'] != $post['uid'])
		{
			// we are not the thread started so remove points from him/her
			if ($mybb->settings['newpoints_income_perreply'] != 0)
				newpoints_addpoints($thread['uid'], -$mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
		}
		
		// remove points from the poster
		newpoints_addpoints($post['uid'], -$mybb->settings['newpoints_income_newpost']-$bonus, $forumrules['rate'], $grouprules['rate']);
	}

	// soft delete posts
	function newpoints_softdeleteposts($pids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpost'] == 0)
			return;
		
		if(!empty($pids))
		{
			foreach($pids as $pid)
			{
				$post = get_post((int)$pid);
				$thread = get_thread($post['tid']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// the post author != thread author?
				if ($thread['uid'] != $post['uid'])
				{
					// we are not the thread started so remove points from him/her
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], -$mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
				}
				
				// remove points from the poster
				newpoints_addpoints($post['uid'], -$mybb->settings['newpoints_income_newpost']-$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}
	
	// restore posts
	function newpoints_restoreposts($pids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpost'] == 0)
			return;
		
		if(!empty($pids))
		{
			foreach($pids as $pid)
			{
				$post = get_post((int)$pid);
				$thread = get_thread($post['tid']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// the post author != thread author?
				if ($thread['uid'] != $post['uid'])
				{
					// we are not the thread started so give points to them
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], $mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
				}
				
				// give points to the author of the post
				newpoints_addpoints($post['uid'], $mybb->settings['newpoints_income_newpost']+$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}

	// new thread
	function newpoints_newthread(&$that)
	{
		global $db, $mybb, $fid, $thread;
		
		if ($mybb->input['action'] != "do_newthread" || $mybb->input['savedraft'])
			return;
		
		if($that->thread_insert_data['visible'] != 1)
		{
			// If it's not visible, then we may have moderation (drafts are already considered above so it doesn't matter here)
			return;
		}
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newthread'] == 0)
			return;
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;

		// calculate points per character bonus
		// let's see if the number of characters in the thread is greater than the minimum characters
		if (($charcount = newpoints_count_characters($mybb->input['message'])) >= $mybb->settings['newpoints_income_minchar']) 
			$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
		else
			$bonus = 0;
		
		// give points to the author of the new thread
		newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_newthread']+$bonus, $forumrules['rate'], $grouprules['rate']);
	}

	function newpoints_approvethreads($tids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newthread'] == 0)
			return;
		
		if(!empty($tids))
		{
			foreach($tids as $tid)
			{
				$thread = get_thread($tid);
				$post = get_post((int)$thread['firstpost']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// add points to the poster
				newpoints_addpoints($post['uid'], $mybb->settings['newpoints_income_newthread']+$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}
	
	function newpoints_unapprovethreads($tids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newthread'] == 0)
			return;
		
		if(!empty($tids))
		{
			foreach($tids as $tid)
			{
				$thread = get_thread($tid);
				$post = get_post((int)$thread['firstpost']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// add points to the poster
				newpoints_addpoints($post['uid'], -$mybb->settings['newpoints_income_newthread']-$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}

	// delete thread
	function newpoints_deletethread($tid)
	{
		global $db, $mybb;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newthread'] == 0)
			return;
		
		// even though the thread was deleted it was previously cached so we can use get_thread
		$thread = get_thread((int)$tid);
		$fid = $thread['fid'];
		
		// It's currently soft deleted, so we do nothing as we already subtracted points when doing that
		// If it's not visible (unapproved) we also don't take out any money
		if($thread['visible'] == -1 || $thread['visible'] == 0)
			return;
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be removed so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be removed so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
		
		// get post of the thread
		$post = get_post($thread['firstpost']);

		// calculate points per character bonus
		// let's see if the number of characters in the thread is greater than the minimum characters
		if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
			$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
		else
			$bonus = 0;
		
		if ($thread['poll'] != 0)
		{
			// if this thread has a poll, remove points from the author of the thread
			newpoints_addpoints($thread['uid'], -$mybb->settings['newpoints_income_newpoll'], $forumrules['rate'], $grouprules['rate']);
		}
		
		$q = $db->simple_select('posts', 'COUNT(*) as total_replies', 'uid!='.(int)$thread['uid'].' AND tid='.(int)$thead['tid']);
		$thread['replies'] = (int)$db->fetch_field($q, 'total_replies');
		newpoints_addpoints($thread['uid'], -($thread['replies']*$mybb->settings['newpoints_income_perreply']), $forumrules['rate'], $grouprules['rate']);
		
		// take out points from the author of the thread
		newpoints_addpoints($thread['uid'], -$mybb->settings['newpoints_income_newthread']-$bonus, $forumrules['rate'], $grouprules['rate']);
	}
	
	// soft delete threads
	function newpoints_softdeletethreads($tids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newthread'] == 0)
			return;
		
		if(!empty($tids))
		{
			foreach($tids as $tid)
			{
				$thread = get_thread($tid);
				$post = get_post((int)$thread['firstpost']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// the post author != thread author?
				if ($thread['uid'] != $post['uid'])
				{
					// we are not the thread started so remove points from him/her
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], -$mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
				}
				
				// remove points from the poster
				newpoints_addpoints($post['uid'], -$mybb->settings['newpoints_income_newthread']-$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}
	
	// restore threads
	function newpoints_restorethreads($tids)
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newthread'] == 0)
			return;
		
		if(!empty($tids))
		{
			foreach($tids as $tid)
			{
				$thread = get_thread($tid);
				$post = get_post((int)$thread['firstpost']);
				
				// check forum rules
				$forumrules = newpoints_getrules('forum', $fid);
				if (!$forumrules)
					$forumrules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the forum rate is 0, nothing is going to be removed so let's just leave the function
				if ($forumrules['rate'] == 0)
					return;
				
				// check group rules - primary group check
				$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
				if (!$grouprules)
					$grouprules['rate'] = 1; // no rule set so default income rate is 1
				
				// if the group rate is 0, nothing is going to be removed so let's just leave the function
				if ($grouprules['rate'] == 0)
					return;

				// calculate points per character bonus
				// let's see if the number of characters in the post is greater than the minimum characters
				if (($charcount = newpoints_count_characters($post['message'])) >= $mybb->settings['newpoints_income_minchar']) 
					$bonus = $charcount * $mybb->settings['newpoints_income_perchar'];
				else
					$bonus = 0;
				
				// the post author != thread author?
				if ($thread['uid'] != $post['uid'])
				{
					// we are not the thread started so give points to them
					if ($mybb->settings['newpoints_income_perreply'] != 0)
						newpoints_addpoints($thread['uid'], $mybb->settings['newpoints_income_perreply'], $forumrules['rate'], $grouprules['rate']);
				}
				
				// give points to the author of the post
				newpoints_addpoints($post['uid'], $mybb->settings['newpoints_income_newthread']+$bonus, $forumrules['rate'], $grouprules['rate']);
			}
		}
	}

	// new poll
	function newpoints_newpoll()
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpoll'] == 0)
			return;
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
		
		// give points to the author of the new poll
		newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_newpoll'], $forumrules['rate'], $grouprules['rate']);
	}


	// delete poll
	function newpoints_deletepoll($pid)
	{
		global $db, $mybb;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_newpoll'] == 0)
			return;
			
		$query = $db->simple_select("polls", "*", "pid = '{$pid}'");
		$poll = $db->fetch_array($query);
		
		$fid = $poll['fid'];
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
		
		// remove points from the author by deleting the poll
		newpoints_addpoints($poll['uid'], -$mybb->settings['newpoints_income_newpoll'], $forumrules['rate'], $grouprules['rate']);
	}

	// new registration
	function newpoints_newreg()
	{
		global $db, $mybb, $user_info;
		
		// give points to our new user
		if ($mybb->settings['newpoints_income_newreg'] != 0)
			newpoints_addpoints(trim($mybb->input['username']), $mybb->settings['newpoints_income_newreg'], 1, 1, true);
			
		if ($mybb->settings['newpoints_income_referral'] != 0)
		{
			// Grab the referred user's points
			$query = $db->simple_select('users', 'uid,newpoints', 'username=\''.my_strtolower($db->escape_string(trim($mybb->input['referrername']))).'\'');
			$user = $db->fetch_array($query);
			if(empty($user))
				return;
			
			// check group rules - primary group check
			$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
			if (!$grouprules)
				$grouprules['rate'] = 1; // no rule set so default income rate is 1
			
			// if the group rate is 0, nothing is going to be added so let's just leave the function
			if ($grouprules['rate'] == 0)
				return;
			
			newpoints_addpoints($user['uid'], $mybb->settings['newpoints_income_referral'], 1, $grouprules['rate']);
		}
	}

	// new poll vote
	function newpoints_pervote()
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_pervote'] == 0)
			return;
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
		
		// give points to us as we're voting in a poll
		newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_pervote'], $forumrules['rate'], $grouprules['rate']);
	}

	// send a pm
	function newpoints_pmsent()
	{
		global $pmhandler, $pminfo, $db, $mybb;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_pmsent'] == 0)
			return;
		
		if(isset($pminfo['draftsaved']))
			return;
			
		if($mybb->user['uid'] == $pmhandler->data['toid'])
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
		
		// give points to the author of the PM
		newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_pmsent'], 1, $grouprules['rate']);
	}

	// per rate
	function newpoints_perrate()
	{
		global $db, $mybb, $fid;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		if ($mybb->settings['newpoints_income_perrate'] == 0)
			return;
		
		// check forum rules
		$forumrules = newpoints_getrules('forum', $fid);
		if (!$forumrules)
			$forumrules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the forum rate is 0, nothing is going to be added so let's just leave the function
		if ($forumrules['rate'] == 0)
			return;
		
		// check group rules - primary group check
		$grouprules = newpoints_getrules('group', $mybb->user['usergroup']);
		if (!$grouprules)
			$grouprules['rate'] = 1; // no rule set so default income rate is 1
		
		// if the group rate is 0, nothing is going to be added so let's just leave the function
		if ($grouprules['rate'] == 0)
			return;
		
		// give points us, as we're rating a thread
		newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_perrate'], $forumrules['rate'], $grouprules['rate']);
	}

	// page view / visit
	function newpoints_perview()
	{
		global $db, $mybb, $cache, $groupscache, $userupdates;
		
		if (!$mybb->user['uid'])
			return;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
			
		// check group rules - primary group check
		$grouprules = newpoints_getallrules('group');
		if (empty($grouprules))
			return;
			
		if ($mybb->settings['newpoints_income_pageview'] != 0)
		{
			newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_pageview'], 1, $grouprules[$mybb->user['usergroup']]['rate']);
		}
		
		if ($mybb->settings['newpoints_income_visit'] != 0)
		{
			if((TIME_NOW - $mybb->user['lastactive']) > 900)
				newpoints_addpoints($mybb->user['uid'], $mybb->settings['newpoints_income_visit'], 1, $grouprules[$mybb->user['usergroup']]['rate']);
		}
		
		foreach($grouprules as $gid => $rule)
		{
			if ($rule['pointsearn'] == 0 || $rule['period'] == 0 || $rule['lastpay']>(TIME_NOW - $rule['period']))
				continue;

			$amount = floatval($rule['pointsearn']);

			$userupdates[$gid] = $amount;
			// update rule with last payment
			$db->update_query('newpoints_grouprules', array('lastpay' => TIME_NOW), 'rid=\''.(int)$rule['rid'].'\'');
			
			// Re-cache rules (lastpay must be updated)
			newpoints_rebuild_rules_cache();
					
			if($mybb->user['usergroup'] == $gid)
				$mybb->user['newpoints'] += $amount;
			
			if(!empty($userupdates))
			{
				// run updates to users on shut down
				add_shutdown('newpoints_update_users');
			}
		}
	}
	
	// ****************** //
	// FORUM RULES HOOKS START //
	// ****************** //
	
	function newpoints_blockview()
	{
		global $mybb, $lang, $fid;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
		
		if (THIS_SCRIPT == 'forumdisplay.php')
			$fid = intval($mybb->input['fid']);

		$forumrules = newpoints_getrules('forum', $fid);
		if ($forumrules['pointsview'] > $mybb->user['newpoints'])
		{
			$lang->load("newpoints");
			error($lang->sprintf($lang->newpoints_not_enough_points, newpoints_format_points($forumrules['pointsview'])));
		}
	}

	function newpoints_blockview_edit()
	{
		global $mybb, $lang;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
		
		$pid = intval($mybb->input['pid']);
		$post = get_post($pid);
		if (!$post)
			return;

		$fid = $post['fid'];

		$forumrules = newpoints_getrules('forum', $fid);
		if ($forumrules['pointsview'] > $mybb->user['newpoints'])
		{
			$lang->load("newpoints");
			error($lang->sprintf($lang->newpoints_not_enough_points, newpoints_format_points($forumrules['pointsview'])));
		}
	}

	function newpoints_blockview_send()
	{
		global $mybb, $lang, $fid;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;

		$forumrules = newpoints_getrules('forum', $fid);
		if ($forumrules['pointsview'] > $mybb->user['newpoints'])
		{
			$lang->load("newpoints");
			error($lang->sprintf($lang->newpoints_not_enough_points, newpoints_format_points($forumrules['pointsview'])));
		}
	}

	function newpoints_blockview_archive()
	{
		global $mybb, $lang, $forum;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;
		
		$fid = intval($forum['fid']);

		$forumrules = newpoints_getrules('forum', $fid);
		if ($forumrules['pointsview'] > $mybb->user['newpoints'])
		{
			$lang->load("newpoints");
			error($lang->sprintf($lang->newpoints_not_enough_points, newpoints_format_points($forumrules['pointsview'])));
		}
	}

	function newpoints_blockview_print()
	{
		global $mybb, $lang, $fid;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;

		$forumrules = newpoints_getrules('forum', $fid);
		if ($forumrules['pointsview'] > $mybb->user['newpoints'])
		{
			$lang->load("newpoints");
			error($lang->sprintf($lang->newpoints_not_enough_points, newpoints_format_points($forumrules['pointsview'])));
		}
	}
	
	function newpoints_blockpost()
	{
		global $mybb, $lang, $fid;
		
		if ($mybb->settings['newpoints_main_enabled'] != 1)
			return;

		$forumrules = newpoints_getrules('forum', $fid);
		if ($forumrules['pointspost'] > $mybb->user['newpoints'])
		{
			$lang->load("newpoints");
			error($lang->sprintf($lang->newpoints_not_enough_points, newpoints_format_points($forumrules['pointspost'])));
		}
	}
}

?>
