<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'MOVE_TOPICS_WHEN_LOCKED'					=> 'Move Topics When Locked',
	'MOVE_TOPICS'								=> 'Move locked topics automatically',
	'MOVE_TOPICS_SOLVED'						=> 'Move solved & locked topics automatically',
	'MOVE_TOPICS_SOLVED_EXPLAIN'				=> 'Requires the <a href="https://www.phpbb.com/customise/db/extension/topic_solved/">Topics Solved extension by bpetty/tierra</a> to be installed.',
	'MOVE_TOPICS_TO'							=> 'Move locked topics to',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS'			=> 'Apply these options to all sub-forums',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS_EXPLAIN'	=> 'If set to “Yes”, the above preferences are applied to this forum and all sub-forums (and their sub-forums).',
));
