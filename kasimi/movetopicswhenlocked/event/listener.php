<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2015 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\log\log_interface */
	protected $log;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
 	 * Constructor
	 *
	 * @param \phpbb\user							$user
	 * @param \phpbb\request\request_interface		$request
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\template\template				$template
	 * @param \phpbb\log\log_interface				$log
	 * @param string								$root_path
	 * @param string								$php_ext
	 */
	public function __construct(
		\phpbb\user $user,
		\phpbb\request\request_interface $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\template\template $template,
		\phpbb\log\log_interface $log,
		$root_path,
		$php_ext
	)
	{
		$this->user 		= $user;
		$this->request		= $request;
		$this->db			= $db;
		$this->template		= $template;
		$this->log			= $log;
		$this->root_path	= $root_path;
		$this->php_ext 		= $php_ext;
	}

	/**
	 * Register hooks
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_manage_forums_display_form'		=> 'acp_manage_forums_display_form',
			'core.acp_manage_forums_request_data'		=> 'acp_manage_forums_request_data',
			'core.modify_mcp_modules_display_option'	=> 'mcp_modify_mcp_modules_display_option',
			'core.mcp_lock_unlock_after'				=> 'mcp_lock_unlock_after',
		);
	}

	/**
	 * Event: core.acp_manage_forums_display_form
	 */
	public function acp_manage_forums_display_form($event)
	{
		$forum_data = $event['forum_data'];
		$this->template->assign_vars(array(
			'S_MOVE_TOPIC'					=> $forum_data['move_topic'],
			'S_MOVE_TOPICS_TO_OPTIONS'		=> make_forum_select($forum_data['move_topics_to'], $forum_data['parent_id']),
		));
	}

	/**
	 * Event: core.acp_manage_forums_request_data
	 */
	public function acp_manage_forums_request_data($event)
	{
		$event['forum_data'] = array_merge($event['forum_data'], array(
			'move_topic'		=> $this->request->variable('move_topic', 0),
			'move_topics_to'	=> $this->request->variable('move_topics_to', 0),
		));
	}

	/**
	 * Event: core.modify_mcp_modules_display_option
	 */
	public function mcp_modify_mcp_modules_display_option($event)
	{
		$this->user->add_lang_ext('kasimi/movetopicswhenlocked', 'info_acp_movetopicswhenlocked');
	}

	/**
	 * Event: core.mcp_lock_unlock_after
	 */
	public function mcp_lock_unlock_after($event)
	{
		if ($event['action'] == 'lock')
		{
			$topic_id = (int) current($topic_ids);
			$topic_ids = $event['ids'];
			$topic_data = $event['data'];
			$forum_id = (int) $topic_data[$topic_id]['forum_id'];
			$to_forum_id = (int) $topic_data[$topic_id]['move_topics_to'];
			$forum_data = phpbb_get_forum_data($to_forum_id);
			$to_forum_name = $forum_data[$to_forum_id]['forum_name'];

			$topics_moved = $topics_moved_unapproved = $topics_moved_softdeleted = 0;

			foreach ($topic_data as $topic_id => $topic_info)
			{
				if ($topic_info['topic_visibility'] == ITEM_APPROVED)
				{
					$topics_moved++;
				}
				else if ($topic_info['topic_visibility'] == ITEM_UNAPPROVED || $topic_info['topic_visibility'] == ITEM_REAPPROVE)
				{
					$topics_moved_unapproved++;
				}
				else if ($topic_info['topic_visibility'] == ITEM_DELETED)
				{
					$topics_moved_softdeleted++;
				}
			}

			$this->db->sql_transaction('begin');

			// Move topics, but do not resync yet
			if (!function_exists('move_topics'))
			{
				include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
			}
			move_topics($topic_ids, $to_forum_id, false);

			foreach ($topic_data as $topic_id => $row)
			{
				// We add the $to_forum_id twice, because 'forum_id' is updated
				// when the topic is moved again later.
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MOVED_LOCKED_TOPIC', false, array(
					'forum_id'		=> $to_forum_id,
					'topic_id'		=> $topic_id,
					$row['topic_title'],
					$row['forum_name'],
					$to_forum_name,
				));
			}
			unset($topic_data);

			$sync_sql = array();
			if ($topics_moved)
			{
				$sync_sql[$to_forum_id][] = 'forum_topics_approved = forum_topics_approved + ' . (int) $topics_moved;
				if ($topics_moved > 0)
				{
					$sync_sql[$forum_id][] = 'forum_topics_approved = forum_topics_approved - ' . (int) ($topics_moved);
				}
			}
			if ($topics_moved_unapproved)
			{
				$sync_sql[$to_forum_id][] = 'forum_topics_unapproved = forum_topics_unapproved + ' . (int) $topics_moved_unapproved;
				$sync_sql[$forum_id][] = 'forum_topics_unapproved = forum_topics_unapproved - ' . (int) $topics_moved_unapproved;
			}
			if ($topics_moved_softdeleted)
			{
				$sync_sql[$to_forum_id][] = 'forum_topics_softdeleted = forum_topics_softdeleted + ' . (int) $topics_moved_softdeleted;
				$sync_sql[$forum_id][] = 'forum_topics_softdeleted = forum_topics_softdeleted - ' . (int) $topics_moved_softdeleted;
			}

			foreach ($sync_sql as $forum_id_key => $array)
			{
				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . implode(', ', $array) . '
					WHERE forum_id = ' . $forum_id_key;
				$this->db->sql_query($sql);
			}

			$this->db->sql_transaction('commit');

			sync('forum', 'forum_id', array($forum_id, $to_forum_id));
		}
	}
}
