<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\event;

use phpbb\event\data;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	/** @var \kasimi\movetopicswhenlocked\core\topic_mover */
	protected $topic_mover;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
 	 * Constructor
	 *
	 * @param \kasimi\movetopicswhenlocked\core\topic_mover	$topic_mover
	 * @param \phpbb\user									$user
	 * @param \phpbb\auth\auth								$auth
	 * @param \phpbb\request\request_interface				$request
	 * @param string										$root_path
	 * @param string										$php_ext
	 */
	public function __construct(
		\kasimi\movetopicswhenlocked\core\topic_mover	$topic_mover,
		\phpbb\user										$user,
		\phpbb\auth\auth								$auth,
		\phpbb\request\request_interface				$request,
														$root_path,
														$php_ext
	)
	{
		$this->topic_mover	= $topic_mover;
		$this->user 		= $user;
		$this->auth			= $auth;
		$this->request		= $request;
		$this->root_path	= $root_path;
		$this->php_ext 		= $php_ext;
	}

	/**
	 * Register hooks
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.posting_modify_submit_post_after'	=> 'posting_modify_submit_post_after',
			'tierra.topicsolved.mark_solved_after'	=> 'topic_solved_after',
		];
	}

	/**
	 * Event: core.posting_modify_submit_post_after
	 *
	 * @param data $event
	 */
	public function posting_modify_submit_post_after($event)
	{
		$post_data = $event['post_data'];

		if ($post_data['topic_status'] == ITEM_UNLOCKED && $this->request->is_set_post('lock_topic'))
		{
			if (($this->auth->acl_get('m_lock', $event['forum_id']) || ($this->auth->acl_get('f_user_lock', $event['forum_id']) && $this->user->data['is_registered'] && !empty($post_data['topic_poster']) && $this->user->data['user_id'] == $post_data['topic_poster'] && $post_data['topic_status'] == ITEM_UNLOCKED)) ? true : false)
			{
				$topic_data = [$event['post_data']['topic_id'] => $event['post_data']];
				$this->topic_mover->move_topics($topic_data, 'move_topics_when_locked');
			}
		}
	}

	/**
	 * Event: tierra.topicsolved.mark_solved_after
	 *
	 * @param data $event
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
			$topic_data = phpbb_get_topic_data([$topic_id]);
			$this->topic_mover->move_topics($topic_data, 'move_topics_when_locked_solved');
		}
	}
}
