<?php
/**
 *
 * Move Topics When Locked. An extension for the phpBB Forum Software package.
 * French translation by Galixte (http://www.galixte.com)
 *
 * @copyright (c) 2018 kasimi <https://kasimi.net>
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 *
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, [
	'MOVE_TOPICS_WHEN_LOCKED'					=> 'Déplacer les sujets lorsqu’ils sont vérrouillés',
	'MOVE_TOPICS'								=> 'Déplacer automatiquement les sujet verrouillés',

	'MOVE_TOPICS_SOLVED'						=> 'Déplacer automatiquement les sujets résolus et verrouillés',
	'MOVE_TOPICS_SOLVED_EXTENSION'				=> '« <a href="https://www.phpbb.com/customise/db/extension/topic_solved/">Topic Solved</a> » est nécessaire et ',

	'MOVE_AUTO_LOCK'							=> 'Déplacer automatiquement les sujets qui ont été vérrouillés  automatiquement',
	'MOVE_AUTO_LOCK_EXTENSION'					=> '« <a href="https://www.phpbb.com/customise/db/extension/auto_lock_topics/">Auto-lock Topics</a> » est nécessaire et ',

	'MOVE_EXTENSION_VERSION'					=> 'À minima la version <strong>%1$s</strong> de l’extension <strong>%2$s</strong> est nécessaire.',
	'MOVE_EXTENSION_ENABLED'					=> 'L’extension « <strong>%s</strong> » est activée.',

	'MOVE_TOPICS_TO'							=> 'Déplacer les sujets verrouillés vers',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS'			=> 'Appliquer maintenant ces options à tous les sous-forums',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS_EXPLAIN'	=> 'Permet d’appliquer les préférences ci-dessus à ce forum et à tous ses sous-forums (et leurs sous-forums).',
]);
