<?php
/***************************************************************************
 *
 *   NewPoints plugin (/inc/tasks/backupnewpoints.php)
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

function task_backupnewpoints($task)
{
	global $mybb, $db, $lang, $cache, $plugins;
	
	$lang->load("newpoints");
	
	$plugins->run_hooks("newpoints_task_backup");
	
	backupnewpoints_backupdb();
	
	add_task_log($task, $lang->newpoints_task_ran);
}

// a modified copy of task_backupdb() from backupdb.php
function backupnewpoints_backupdb()
{
	global $db, $config, $lang, $plugins;
	static $contents;

	@set_time_limit(0);
	
	if(!defined('MYBB_ADMIN_DIR'))
	{
		if(!isset($config['admin_dir']))
		{
			$config['admin_dir'] = "admin";
		}
	
		define('MYBB_ADMIN_DIR', MYBB_ROOT.$config['admin_dir'].'/');
	}
	
	// Check if folder is writable, before allowing submission
	if(!is_writable(MYBB_ADMIN_DIR."/backups/backupnewpoints"))
	{
		return false;
	}
	else
	{
		$db->set_table_prefix('');
		
		$file = MYBB_ADMIN_DIR.'/backups/backupnewpoints/backup_'.substr(md5($mybb->user['uid'].TIME_NOW), 0, 10).random_str(54);
		
		if(function_exists('gzopen'))
		{
			$fp = gzopen($file.'.sql.gz', 'w9');
		}
		else
		{
			$fp = fopen($file.'.sql', 'w');
		}
		
		// backup default tables and newpoints field from users table
		$tables = array(TABLE_PREFIX.'newpoints_log', TABLE_PREFIX.'newpoints_settings', TABLE_PREFIX.'newpoints_forumrules', TABLE_PREFIX.'newpoints_grouprules', TABLE_PREFIX.'users', TABLE_PREFIX.'datacache');
		$backup_fields = array('newpoints');
		
		$backup_fields = $plugins->run_hooks("newpoints_task_backup_tables", $backup_fields);
	
		$time = date('dS F Y \a\t H:i', TIME_NOW);
		$header = "-- MyBB Database Backup\n-- Generated: {$time}\n-- -------------------------------------\n\n";
		$contents = $header;
		foreach($tables as $table)
		{
			$plugins->run_hooks("newpoints_task_backup_table");
			if ($table == TABLE_PREFIX.'users')
			{
				backupnewpoints_clear_overflow($fp, $contents);
				
				$query = $db->simple_select($table, 'uid,'.implode(',', $backup_fields));
				while($row = $db->fetch_array($query))
				{
					$update = '';
					
					foreach ($backup_fields as $field)
					{
						$update .= "UPDATE `".TABLE_PREFIX."users` SET `{$field}` = '".$row[$field]."' WHERE `uid` = '".$row['uid']."';\n";
					}

					$contents .= $update;
					backupnewpoints_clear_overflow($fp, $contents);
				}
			}
			elseif ($table == TABLE_PREFIX.'datacache')
			{
				backupnewpoints_clear_overflow($fp, $contents);
				
				$query = $db->simple_select($table, 'cache', 'title=\'newpoints_plugins\'', array('limit' => 1));
				$row = $db->fetch_array($query);

				$contents .= "UPDATE `".TABLE_PREFIX."datacache` SET `cache` = '".$row['cache']."' WHERE `title` = 'newpoints_plugins';\n";
				backupnewpoints_clear_overflow($fp, $contents);
				
				$query = $db->simple_select($table, 'cache', 'title=\'newpoints_rules\'', array('limit' => 1));
				$row = $db->fetch_array($query);

				$contents .= "UPDATE `".TABLE_PREFIX."datacache` SET `cache` = '".$row['cache']."' WHERE `title` = 'newpoints_rules';\n";
				backupnewpoints_clear_overflow($fp, $contents);
				
				$query = $db->simple_select($table, 'cache', 'title=\'newpoints_settings\'', array('limit' => 1));
				$row = $db->fetch_array($query);

				$contents .= "UPDATE `".TABLE_PREFIX."datacache` SET `cache` = '".$row['cache']."' WHERE `title` = 'newpoints_settings';\n";
				backupnewpoints_clear_overflow($fp, $contents);
			}
			else {
				$field_list = array();
				$fields_array = $db->show_fields_from($table);
				foreach($fields_array as $field)
				{
					$field_list[] = $field['Field'];
				}
				
				$fields = implode(",", $field_list);
		
				/*$structure = $db->show_create_table($table).";\n";
				$contents .= $structure;*/
				backupnewpoints_clear_overflow($fp, $contents);
				
				if ($table == TABLE_PREFIX.'datacache')
					$where = "title='newpoints_plugins'";
				else
					$where = '';
				
				$query = $db->simple_select($table, '*', $where);
				while($row = $db->fetch_array($query))
				{
					$insert = "INSERT INTO {$table} ($fields) VALUES (";
					$comma = '';
					foreach($field_list as $field)
					{
						if(!isset($row[$field]) || trim($row[$field]) == "")
						{
							$insert .= $comma."''";
						}
						else
						{
							$insert .= $comma."'".$db->escape_string($row[$field])."'";
						}
						$comma = ',';
					}
					$insert .= ");\n";
					$contents .= $insert;
					backupnewpoints_clear_overflow($fp, $contents);
				}
			}
		}
		
		$db->set_table_prefix(TABLE_PREFIX);
		
		if(function_exists('gzopen'))
		{
			gzwrite($fp, $contents);
			gzclose($fp);
		}
		else
		{
			fwrite($fp, $contents);
			fclose($fp);
		}
		
		return true;
	}
}

// Allows us to refresh cache to prevent over flowing
function backupnewpoints_clear_overflow($fp, &$contents) 
{
	global $mybb;
	
	if(function_exists('gzopen')) 
	{
		gzwrite($fp, $contents);
	} 
	else 
	{
		fwrite($fp, $contents);
	}
		
	$contents = '';	
}

?>
