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

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

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
	 * @param \phpbb\config\config					$config
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param string								$root_path
	 * @param string								$php_ext
	 */
	public function __construct(
		\phpbb\user $user,
		\phpbb\request\request_interface $request,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\template\template $template,
		\phpbb\log\log_interface $log,
		\phpbb\config\config $config,
		\phpbb\event\dispatcher_interface $dispatcher,
		$root_path,
		$php_ext
	)
	{
		$this->user 		= $user;
		$this->request		= $request;
		$this->db			= $db;
		$this->template		= $template;
		$this->log			= $log;
		$this->config		= $config;
		$this->dispatcher	= $dispatcher;
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
			'tierra.topicsolved.mark_solved_after'		=> 'topic_solved_after',
		);
	}

	/**
	 * Event: core.acp_manage_forums_display_form
	 */
	public function acp_manage_forums_display_form($event)
	{
		$this->user->add_lang_ext('kasimi/movetopicswhenlocked', 'acp_forum_settings');

		$forum_data = $event['forum_data'];
		$this->template->assign_vars(array(
			'MOVE_TOPICS_WHEN_LOCKED_VERSION'	=> $this->config['kasimi.movetopicswhenlocked.version'],
			'S_MOVE_TOPICS'						=> $forum_data['move_topics_when_locked'],
			'S_MOVE_TOPICS_SOLVED'				=> $forum_data['move_topics_when_locked_solved'],
			'S_MOVE_TOPICS_TO_OPTIONS'			=> make_forum_select($forum_data['move_topics_when_locked_to'], false, false, true),
		));
	}

	/**
	 * Event: core.acp_manage_forums_request_data
	 */
	public function acp_manage_forums_request_data($event)
	{
		$move_topics = $this->request->variable('move_topics_when_locked', 0);
		$move_solved_topics = $this->request->variable('move_topics_when_locked_solved', 0);
		$move_topics_to = $this->request->variable('move_topics_when_locked_to', 0);

		$event['forum_data'] = array_merge($event['forum_data'], array(
			'move_topics_when_locked'			=> $move_topics,
			'move_topics_when_locked_solved'	=> $move_solved_topics,
			'move_topics_when_locked_to'		=> $move_topics_to,
		));

		// Apply this forum's preferences to all sub-forums
		if ($this->request->variable('move_topics_when_locked_subforums', 0))
		{
			$subforum_ids = array();
			foreach (get_forum_branch($event['forum_data']['forum_id'], 'children', 'descending', false) as $subforum)
			{
				$subforum_ids[] = (int) $subforum['forum_id'];
			}

			if (!empty($subforum_ids))
			{
				$this->db->sql_transaction('begin');

				foreach ($subforum_ids as $subforum_id)
				{
					$sql_ary = 'UPDATE ' . FORUMS_TABLE . '
						SET move_topics_when_locked = ' . (int) $move_topics . ',
							move_topics_when_locked_solved = ' . (int) $move_solved_topics . ',
							move_topics_when_locked_to = ' . (int) $move_topics_to . '
						WHERE forum_id = ' . (int) $subforum_id;
					$this->db->sql_query($sql_ary);
				}

				$this->db->sql_transaction('commit');
			}
		}
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
			$this->move_topics($event['data'], 'move_topics_when_locked');
		}
	}

	/**
	 * Event: tierra.topicsolved.mark_solved_after
	 */
	public function topic_solved_after($event)
	{
		if ($event['column_data']['topic_status'] == ITEM_LOCKED)
		{
			if (!function_exists('phpbb_get_topic_data'))
			{
				include($this->root_path . 'includes/functions_mcp.' . $this->php_ext);
			}

			$topic_id = $event['topic_data']['topic_id'];
			$topic_data = phpbb_get_topic_data(array($topic_id));
			$this->move_topics($topic_data, 'move_topics_when_locked_solved');
		}
	}

	/**
	 * Moves topics to a new forum after they have been locked
	 *
	 * @param	array	$topic_data
	 * @param	string	$action
	 */
	protected function move_topics($topic_data, $action)
	{
		$first_topic_data = reset($topic_data);
		$is_enabled = (int) $first_topic_data[$action];
		$to_forum_id = (int) $first_topic_data['move_topics_when_locked_to'];

		/**
		 * This event allows you to perform additional actions before locked topics are moved.
		 *
		 * @event kasimi.movetopicswhenlocked.move_topics_before
		 * @var	array	topic_data		Array with general topic data
		 * @var	string	action			Who triggered the forums to be moved, one of move_topics_when_locked|move_topics_when_locked_solved
		 * @var int		is_enabled		Whether or not the forum's ACP settings specify the topics to be moved
		 * @var int		to_forum_id		The destination forum
		 * @since 1.0.2
		 */
		$vars = array(
			'topic_data',
			'action',
			'is_enabled',
			'to_forum_id',
		);
		extract($this->dispatcher->trigger_event('kasimi.movetopicswhenlocked.move_topics_before', compact($vars)));

		// Forum settings are set to not move the topics
		if (!$is_enabled || !$to_forum_id)
		{
			return;
		}

		$forum_id = (int) $first_topic_data['forum_id'];

		// The topics are already in the destination forum
		if ($forum_id == $to_forum_id)
		{
			return;
		}

		$to_forum_data = phpbb_get_forum_data($to_forum_id, 'f_post');

		// The destination forum does not exist
		if (empty($to_forum_data))
		{
			return;
		}

		// The following code is taken from the mcp_move_topic() function in /includes/mpc/mcp_main.php
		$topics_moved = $topics_moved_unapproved = $topics_moved_softdeleted = 0;
		$posts_moved = $posts_moved_unapproved = $posts_moved_softdeleted = 0;

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

			$posts_moved += $topic_info['topic_posts_approved'];
			$posts_moved_unapproved += $topic_info['topic_posts_unapproved'];
			$posts_moved_softdeleted += $topic_info['topic_posts_softdeleted'];
		}

		$this->db->sql_transaction('begin');

		if (!function_exists('move_topics'))
		{
			include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		}

		// Move topics, but do not resync yet
		move_topics(array_keys($topic_data), $to_forum_id, false);

		foreach ($topic_data as $topic_id => $row)
		{
			// We add the $to_forum_id twice, because 'forum_id' is updated
			// when the topic is moved again later.
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MOVED_LOCKED_TOPIC', false, array(
				'forum_id'		=> (int) $to_forum_id,
				'topic_id'		=> (int) $topic_id,
				$row['topic_title'],
				$row['forum_name'],
				$to_forum_data[$to_forum_id]['forum_name'],
			));
		}
		unset($topic_data);

		$sync_sql = array();
		if ($posts_moved)
		{
			$sync_sql[$to_forum_id][] = 'forum_posts_approved = forum_posts_approved + ' . (int) $posts_moved;
			$sync_sql[$forum_id][] = 'forum_posts_approved = forum_posts_approved - ' . (int) $posts_moved;
		}
		if ($posts_moved_unapproved)
		{
			$sync_sql[$to_forum_id][] = 'forum_posts_unapproved = forum_posts_unapproved + ' . (int) $posts_moved_unapproved;
			$sync_sql[$forum_id][] = 'forum_posts_unapproved = forum_posts_unapproved - ' . (int) $posts_moved_unapproved;
		}
		if ($posts_moved_softdeleted)
		{
			$sync_sql[$to_forum_id][] = 'forum_posts_softdeleted = forum_posts_softdeleted + ' . (int) $posts_moved_softdeleted;
			$sync_sql[$forum_id][] = 'forum_posts_softdeleted = forum_posts_softdeleted - ' . (int) $posts_moved_softdeleted;
		}

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

		/**
		 * This event allows you to perform additional actions after locked topics have been moved.
		 *
		 * @event kasimi.movetopicswhenlocked.move_topics_after
		 * @var	array	topic_data					Array with general topic data
		 * @var string	action						Who triggered the forums to be moved, one of move_topics_when_locked|move_topics_when_locked_solved
		 * @var int		to_forum_id					The destination forum
		 * @var int		topics_moved				Number of moved topics
		 * @var int		topics_moved_unapproved		Number of moved unapproved topics
		 * @var int		topics_moved_softdeleted	Number of moved soft-deleted topics
		 * @var int		posts_moved					Number of moved posts
		 * @var int		posts_moved_unapproved		Number of moved unapproved posts
		 * @var int		posts_moved_softdeleted		Number of moved soft-deleted posts
		 * @since 1.0.2
		 */
		$vars = array(
			'topic_data',
			'action',
			'to_forum_id',
			'topics_moved',
			'topics_moved_unapproved',
			'topics_moved_softdeleted',
			'posts_moved',
			'posts_moved_unapproved',
			'posts_moved_softdeleted',
		);
		extract($this->dispatcher->trigger_event('kasimi.movetopicswhenlocked.move_topics_after', compact($vars)));
	}
}
