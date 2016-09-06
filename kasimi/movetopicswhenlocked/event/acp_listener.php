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

class acp_listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/**
 	 * Constructor
	 *
	 * @param \phpbb\user							$user
	 * @param \phpbb\request\request_interface		$request
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\config\config					$config
	 */
	public function __construct(
		\phpbb\user							$user,
		\phpbb\request\request_interface	$request,
		\phpbb\db\driver\driver_interface	$db,
		\phpbb\template\template			$template,
		\phpbb\config\config				$config
	)
	{
		$this->user 	= $user;
		$this->request	= $request;
		$this->db		= $db;
		$this->template	= $template;
		$this->config	= $config;
	}

	/**
	 * Register hooks
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_manage_forums_display_form'	=> 'acp_manage_forums_display_form',
			'core.acp_manage_forums_request_data'	=> 'acp_manage_forums_request_data',
		);
	}

	/**
	 * Event: core.acp_manage_forums_display_form
	 *
	 * @param Event $event
	 */
	public function acp_manage_forums_display_form($event)
	{
		$this->user->add_lang_ext('kasimi/movetopicswhenlocked', 'acp_forum_settings');

		$is_edit = $event['action'] == 'edit';
		$forum_data = $event['forum_data'];

		$this->template->assign_vars(array(
			'MOVE_TOPICS_WHEN_LOCKED_VERSION'	=> $this->config['kasimi.movetopicswhenlocked.version'],
			'S_MOVE_TOPICS'						=> $is_edit ? $forum_data['move_topics_when_locked'] : false,
			'S_MOVE_TOPICS_SOLVED'				=> $is_edit ? $forum_data['move_topics_when_locked_solved'] : false,
			'S_MOVE_TOPICS_TO_OPTIONS'			=> make_forum_select($is_edit ? $forum_data['move_topics_when_locked_to'] : false, false, false, true),
		));
	}

	/**
	 * Event: core.acp_manage_forums_request_data
	 *
	 * @param Event $event
	 */
	public function acp_manage_forums_request_data($event)
	{
		$lock_options = array(
			'move_topics_when_locked'			=> $this->request->variable('move_topics_when_locked', 0),
			'move_topics_when_locked_solved'	=> $this->request->variable('move_topics_when_locked_solved', 0),
			'move_topics_when_locked_to'		=> $this->request->variable('move_topics_when_locked_to', 0),
		);

		$event['forum_data'] = array_merge($event['forum_data'], $lock_options);

		// Apply this forum's preferences to all sub-forums
		if ($event['action'] == 'edit' && $this->request->variable('move_topics_when_locked_subforums', 0))
		{
			$subforum_ids = $this->get_subforum_ids($event['forum_data']['forum_id']);

			if (!empty($subforum_ids))
			{
				$sql_ary = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $lock_options) . '
					WHERE ' . $this->db->sql_in_set('forum_id', $subforum_ids);
				$this->db->sql_query($sql_ary);
			}
		}
	}

	/**
	 * Returns an array containing all IDs of the forum's sub-forums (and their sub-forums)
	 *
	 * @param int $forum_id
	 * @return array
	 */
	protected function get_subforum_ids($forum_id)
	{
		$subforum_ids = array();

		foreach (get_forum_branch((int) $forum_id, 'children', 'descending', false) as $subforum)
		{
			$subforum_ids[] = (int) $subforum['forum_id'];
		}

		return $subforum_ids;
	}
}
