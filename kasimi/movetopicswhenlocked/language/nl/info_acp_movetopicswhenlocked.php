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
	'MOVE_TOPICS'						=> 'Verplaats gesloten onderwerpen automatisch',
	'MOVE_TOPICS_TO'					=> 'Verplaats gesloten onderwerpen naar',
	'LOG_MOVED_LOCKED_TOPIC'			=> '<strong>Gesloten onderwerp “%s” is automatisch verplaatst</strong><br />&raquo; van %s<br />&raquo; naar %s',
));
