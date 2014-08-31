<?php
/***************************************************************************
 *
 *   NewPoints plugin (/inc/plugins/upgrades/upgrade11.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2014 Pirata Nervo
 *   
 *   Website: http://www.mybb-plugins.com
 *
 *   Upgrade file to upgrade NewPoints 1.0 to NewPoints 1.1
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
	
if (!defined('IN_ADMINCP'))
	die("This file must be accessed from the Administrator Panel.");

function upgrade11_info()
{
	return array('new_version' => '1.1',
				 'name' => 'Upgrade to 1.1',
				 'description' => 'Upgrade NewPoints 1.0 to NewPoints 1.1'
				 );
}

// upgrade function
function upgrade11_run()
{
	newpoints_add_setting('newpoints_income_referral', 'income', 'Per Referral', 'Amount of points received everytime a user is referred. (the referred user is who receives the points)', 'text', '5', 12);
	change_admin_permission("newpoints", "upgrades", 1);
	
	newpoints_remove_templates("'newpoints_donate'");
	
	newpoints_add_template('newpoints_donate', '
<html>
<head>
<title>{$lang->newpoints} - {$lang->newpoints_donate}</title>
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
<input type="hidden" name="postcode" value="{$mybb->post_code}">
<input type="hidden" name="action" value="do_donate">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->newpoints_donate}</strong></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_user}:</strong><br /><span class="smalltext">{$lang->newpoints_user_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="username" value="{$user[\'username\']}" class="textbox"></td>
</tr>
<tr>
<td class="trow2" width="50%"><strong>{$lang->newpoints_amount}:</strong><br /><span class="smalltext">{$lang->newpoints_amount_desc}</span></td>
<td class="trow2" width="50%"><input type="text" name="amount" value="" class="textbox"></td>
</tr>
<tr>
<td class="trow1" width="50%"><strong>{$lang->newpoints_reason}:</strong><br /><span class="smalltext">{$lang->newpoints_reason_desc}</span></td>
<td class="trow1" width="50%"><input type="text" name="reason" value="" class="textbox"></td>
</tr>
<tr>
<td class="tfoot" width="100%" colspan="2" align="center"><input type="submit" name="submit" value="{$lang->newpoints_submit}"></td>
</tr>
</table>
</form>
</td>
</tr>
</table>
{$footer}
</body>
</html>');
}

?>
