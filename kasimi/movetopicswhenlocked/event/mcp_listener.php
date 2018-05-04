<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\event;

use kasimi\movetopicswhenlocked\core\topic_mover;
use phpbb\event\data;
use phpbb\language\language;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class mcp_listener implements EventSubscriberInterface
{
	/** @var topic_mover */
	protected $topic_mover;

	/** @var language */
	protected $lang;

	/**
	 * @param topic_mover	$topic_mover
	 * @param language		$lang
	 */
	public function __construct(
		topic_mover $topic_mover,
		language $lang
	)
	{
		$this->topic_mover	= $topic_mover;
		$this->lang 		= $lang;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.modify_mcp_modules_display_option'	=> 'mcp_modify_mcp_modules_display_option',
			'core.mcp_lock_unlock_after'				=> 'mcp_lock_unlock_after',
		];
	}

	/**
	 *
	 */
	public function mcp_modify_mcp_modules_display_option()
	{
		// Add language for MCP log entries
		$this->lang->add_lang('info_acp_movetopicswhenlocked', 'kasimi/movetopicswhenlocked');
	}

	/**
	 * @param data $event
	 */
	public function mcp_lock_unlock_after($event)
	{
		if ($event['action'] == 'lock')
		{
			$this->topic_mover->move_topics($event['data'], 'move_topics_when_locked');
		}
	}
}
