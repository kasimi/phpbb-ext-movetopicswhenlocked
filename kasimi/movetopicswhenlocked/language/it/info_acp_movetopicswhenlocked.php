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
	'MOVE_TOPICS'						=> 'Sposta argomenti bloccati', // I suppressed 'automatically' for brevity sake
	'MOVE_TOPICS_TO'					=> 'Sposta argomenti bloccati in',
	'LOG_MOVED_LOCKED_TOPIC'			=> '<strong>L’argomento bloccato “%s” è stato automaticamente spostato</strong><br />&raquo; da %s<br />&raquo; a %s',
));
