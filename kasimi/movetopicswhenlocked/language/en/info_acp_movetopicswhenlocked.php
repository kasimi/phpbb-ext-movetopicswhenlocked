<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2015 kasimi
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
	'MOVE_TOPICS'						=> 'Move Topics',
	'MOVE_TOPICS_WHEN_LOCKED'			=> 'Move closed Topics',
	'MOVE_TOPICS_WHEN_LOCKED_EXPLAIN'	=> 'Do you want closed topics to be moved automatically?',
	'MOVE_TOPICS_TO'					=> 'Move closed topics to',
	'MOVE_TOPICS_TO_EXPLAIN'			=> 'Where do you want closed topics to be moved?',
	'LOG_MOVED_LOCKED_TOPIC'			=> '<strong>Locked topic \'%s\' automatically moved</strong><br />&raquo; from %s<br />&raquo; to %s',
));
