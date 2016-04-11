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
	'MOVE_TOPICS_WHEN_LOCKED'					=> 'Verplaats de onderwerpen wanneer ze gesloten worden',
	'MOVE_TOPICS'								=> 'Verplaats gesloten onderwerpen automatisch',
	'MOVE_TOPICS_SOLVED'						=> 'Verplaats opgelost & gesloten onderwerpen automatisch',
	'MOVE_TOPICS_SOLVED_EXPLAIN'				=> 'Verplicht om de <a href="https://www.phpbb.com/customise/db/extension/topic_solved/">Onderwerp opgelost extensie van bpetty/tierra</a> te installeren.',
	'MOVE_TOPICS_TO'							=> 'Verplaats gesloten onderwerpen naar',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS'			=> 'Ken deze opties toe aan alle sub-fora',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS_EXPLAIN'	=> 'Als vastgezet op "ja", zullen de bovenstaande instellingen toegepast worden op dit forum en alle subfora (en hun subfora).',
));
