<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class mcp_listener implements EventSubscriberInterface
{
	/** @var \kasimi\movetopicswhenlocked\core\topic_mover */
	protected $topic_mover;

	/** @var \phpbb\user */
	protected $user;

	/**
 	 * Constructor
	 *
	 * @param \kasimi\movetopicswhenlocked\core\topic_mover	$topic_mover
	 * @param \phpbb\user									$user
	 */
	public function __construct(
		\kasimi\movetopicswhenlocked\core\topic_mover	$topic_mover,
		\phpbb\user										$user
	)
	{
		$this->topic_mover	= $topic_mover;
		$this->user 		= $user;
	}

	/**
	 * Register hooks
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.modify_mcp_modules_display_option'	=> 'mcp_modify_mcp_modules_display_option',
			'core.mcp_lock_unlock_after'				=> 'mcp_lock_unlock_after',
		);
	}

	/**
	 * Event: core.modify_mcp_modules_display_option
	 *
	 * @param Event $event
	 */
	public function mcp_modify_mcp_modules_display_option($event)
	{
		// Add language for MCP log entries
		$this->user->add_lang_ext('kasimi/movetopicswhenlocked', 'info_acp_movetopicswhenlocked');
	}

	/**
	 * Event: core.mcp_lock_unlock_after
	 *
	 * @param Event $event
	 */
	public function mcp_lock_unlock_after($event)
	{
		if ($event['action'] == 'lock')
		{
			$this->topic_mover->move_topics($event['data'], 'move_topics_when_locked');
		}
	}
}
