<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\core;

class topic_mover
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\log\log_interface */
	protected $log;

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
	 * @param \phpbb\db\driver\driver_interface		$db
	 * @param \phpbb\log\log_interface				$log
	 * @param \phpbb\event\dispatcher_interface		$dispatcher
	 * @param string								$root_path
	 * @param string								$php_ext
	 */
	public function __construct(
		\phpbb\user							$user,
		\phpbb\db\driver\driver_interface	$db,
		\phpbb\log\log_interface			$log,
		\phpbb\event\dispatcher_interface	$dispatcher,
											$root_path,
											$php_ext
	)
	{
		$this->user 		= $user;
		$this->db			= $db;
		$this->log			= $log;
		$this->dispatcher	= $dispatcher;
		$this->root_path	= $root_path;
		$this->php_ext 		= $php_ext;
	}

	/**
	 * Moves topics to a new forum after they have been locked
	 *
	 * @param array $topic_data
	 * @param string $action
	 */
	public function move_topics($topic_data, $action)
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
		$vars = [
			'topic_data',
			'action',
			'is_enabled',
			'to_forum_id',
		];
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

		if (!function_exists('phpbb_get_forum_data'))
		{
			include($this->root_path . 'includes/functions_mcp.' . $this->php_ext);
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
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'LOG_MOVED_LOCKED_TOPIC', false, [
				'forum_id'		=> (int) $to_forum_id,
				'topic_id'		=> (int) $topic_id,
				$row['topic_title'],
				$row['forum_name'],
				$to_forum_data[$to_forum_id]['forum_name'],
			]);
		}

		$sync_sql = [];
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
				WHERE forum_id = ' . (int) $forum_id_key;
			$this->db->sql_query($sql);
		}

		$this->db->sql_transaction('commit');

		sync('forum', 'forum_id', [$forum_id, $to_forum_id]);

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
		$vars = [
			'topic_data',
			'action',
			'to_forum_id',
			'topics_moved',
			'topics_moved_unapproved',
			'topics_moved_softdeleted',
			'posts_moved',
			'posts_moved_unapproved',
			'posts_moved_softdeleted',
		];
		extract($this->dispatcher->trigger_event('kasimi.movetopicswhenlocked.move_topics_after', compact($vars)));

		unset($topic_data);
	}
}
