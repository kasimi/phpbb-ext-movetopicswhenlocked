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
	'MOVE_TOPICS'						=> 'Mover los temas cerrados automáticamente',
	'MOVE_TOPICS_TO'					=> 'Mover temas cerrados a',
	'LOG_MOVED_LOCKED_TOPIC'			=> '<strong>El tema cerrado “%s” ha sido movido automáticamente</strong><br />&raquo; de %s<br />&raquo; a %s',
));
