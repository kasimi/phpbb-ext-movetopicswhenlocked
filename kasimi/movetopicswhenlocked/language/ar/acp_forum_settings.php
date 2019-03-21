<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * Translated By : Bassel Taha Alhitary <http://alhitary.net>
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'MOVE_TOPICS_WHEN_LOCKED'					=> 'نقل المواضيع عند اغلاقها',
	'MOVE_TOPICS'								=> 'نقل المواضيع المُغلقة تلقائياً',

	'MOVE_TOPICS_SOLVED'						=> 'نقل المواضيع المُغلقة و التي تمت الإجابة عليها تلقائياً',
	'MOVE_TOPICS_SOLVED_EXTENSION'				=> '<a href="https://www.phpbb.com/customise/db/extension/topic_solved/">“تمت الإجابة على الموضوع”</a>',

	'MOVE_AUTO_LOCK'							=> 'نقل “الإغلاق التلقائي للمواضيع” تلقائياً',
	'MOVE_AUTO_LOCK_EXTENSION'					=> '<a href="https://www.phpbb.com/customise/db/extension/auto_lock_topics/">“الإغلاق التلقائي للمواضيع”</a>.',

	'MOVE_EXTENSION_VERSION'					=> 'أنت بحاجة إلى النسخة <strong>%1$s</strong> على الأقل للإضافة <strong>%2$s</strong>.',
	'MOVE_EXTENSION_ENABLED'					=> 'تم تفعيل الإضافة <strong>%s</strong>.',

	'MOVE_TOPICS_TO'							=> 'نقل المواضيع المُغلقة إلى',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS'			=> 'المنتديات الفرعية',
	'MOVE_TOPICS_APPLY_TO_SUBFORUMS_EXPLAIN'	=> 'عند اختيارك “نعم”, سيتم تطبيق الإعدادات أعلاه في هذا المنتدى وجميع منتدياته الفرعية (وجميع منتدياتهم الفرعية).',
]);
