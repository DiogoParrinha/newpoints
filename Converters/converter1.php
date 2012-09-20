<?php

/***************************************************************************
 *
 *   Converter to convert MyPlaza Turbo to NewPoints (/converter1.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2009-2011 Pirata Nervo
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

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'converter1.php');

// Templates used by the converter
$template_home = '<html>
<head>
<title>NewPoints - Converter #1</title>
{$headerinclude}
</head>
<body>
{$header}
<form action="converter1.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}">
<input type="hidden" name="action" value="do_process1">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Convert MyPlaza Turbo to NewPoints</strong></td>
</tr>
<tr>
<td class="trow1" width="100%">This converter will convert the following data:<br /><ul><li>Points</li><li>Forum income rates to Forum rules</li><li>Group income rates to Group rules</li></ul><br />The converting process is divided into three processes to make sure it does not consume too much resources.<br />The first process converts MyPlaza Turbo points to NewPoints points.<br />Note: this might a slow process.</td>
</tr>
<tr>
<td class="tfoot" width="100%" colspan="2" align="center"><input type="submit" name="submit" value="Proceed"></td>
</tr>
</table>
</form>
{$footer}
</body>
</html>';

$template_process1 = '<html>
<head>
<title>NewPoints - Converter #1</title>
{$headerinclude}
</head>
<body>
{$header}
<form action="converter1.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}">
<input type="hidden" name="action" value="do_process2">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Convert MyPlaza Turbo to NewPoints</strong></td>
</tr>
<tr>
<td class="trow1" width="100%">{$count} users have been updated/converted.<br />The next process will convert forum income rates to forum rules.</td>
</tr>
<tr>
<td class="tfoot" width="100%" align="center"><input type="submit" name="submit" value="Proceed"></td>
</tr>
</table>
</form>
{$footer}
</body>
</html>';

$template_process2 = '<html>
<head>
<title>NewPoints - Converter #1</title>
{$headerinclude}
</head>
<body>
{$header}
<form action="converter1.php" method="POST">
<input type="hidden" name="postcode" value="{$mybb->post_code}">
<input type="hidden" name="action" value="do_process3">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Convert MyPlaza Turbo to NewPoints</strong></td>
</tr>
<tr>
<td class="trow1" width="100%">{$count} forums have been updated/converted.<br />The next process will convert group income rates to group rules.</td>
</tr>
<tr>
<td class="tfoot" width="100%" align="center"><input type="submit" name="submit" value="Proceed"></td>
</tr>
</table>
</form>
{$footer}
</body>
</html>';

$template_process3 = '<html>
<head>
<title>NewPoints - Converter #1</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Convert MyPlaza Turbo to NewPoints</strong></td>
</tr>
<tr>
<td class="trow1" width="100%">{$count} groups have been updated/converted.<br />Congratulations, you have successfully converted the data to the NewPoints data format.</td>
</tr>
</table>
{$footer}
</body>
</html>';

require_once "./global.php";

if (!$mybb->input['action'])
{
	eval("\$page = \"".str_replace("\\'", "'", addslashes($template_home))."\";");
	
	output_page($page);
}
elseif ($mybb->input['action'] == 'do_process1')
{
	verify_post_check($mybb->input['postcode']);
	
	$count = 0;
	
	$query = $db->simple_select('users', 'uid,'.MYPLAZA_MONEY_COLUMN, MYPLAZA_MONEY_COLUMN.'!=0');
	while ($user = $db->fetch_array($query))
	{
		$db->update_query('users', array('newpoints' => round(floatval($user[MYPLAZA_MONEY_COLUMN]), $mybb->settings['newpoints_main_decimal'])), 'uid=\''.$user['uid'].'\'');
		$count++;
	}
	
	eval("\$page = \"".str_replace("\\'", "'", addslashes($template_process1))."\";");
	
	output_page($page);
}
elseif ($mybb->input['action'] == 'do_process2')
{
	verify_post_check($mybb->input['postcode']);
	
	$count = 0;
	
	$query = $db->simple_select('forums', 'fid,name,myplaza_income_rate', 'myplaza_income_rate!=1');
	while ($forum = $db->fetch_array($query))
	{
		$db->insert_query('newpoints_forumrules', array('name' => $db->escape_string($forum['name']." Rules"), 'description' => $db->escape_string("Rule created by the converter."), 'rate' => floatval($forum['myplaza_income_rate']), 'pointsview' => 0, 'pointspost' => 0, 'fid' => intval($forum['fid'])));
		$count++;
	}
	
	eval("\$page = \"".str_replace("\\'", "'", addslashes($template_process2))."\";");
	
	output_page($page);
}
elseif ($mybb->input['action'] == 'do_process3')
{
	verify_post_check($mybb->input['postcode']);
	
	$count = 0;
	
	$query = $db->simple_select('usergroups', 'gid,title,myplaza_income_rate', 'myplaza_income_rate!=1');
	while ($group = $db->fetch_array($query))
	{
		$db->insert_query('newpoints_grouprules', array('name' => $db->escape_string($group['title']." Rule"), 'description' => $db->escape_string("Rule created by the converter."), 'rate' => floatval($group['myplaza_income_rate'])));
		$count++;
	}
	
	eval("\$page = \"".str_replace("\\'", "'", addslashes($template_process3))."\";");
	
	output_page($page);
}

exit;

?>