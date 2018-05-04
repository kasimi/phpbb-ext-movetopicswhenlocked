<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\event;

use phpbb\db\driver\driver_interface;
use phpbb\event\data;
use phpbb\extension\manager;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class acp_listener implements EventSubscriberInterface
{
	/** @var language */
	protected $lang;

	/** @var request_interface */
	protected $request;

	/** @var driver_interface */
	protected $db;

	/** @var template */
	protected $template;

	/** @var manager */
	protected $extension_manager;

	/** @var array */
	protected $compatible_extensions = [
		'TOPICS_SOLVED' => [
			'name'			=> 'tierra/topicsolved',
			'min_version'	=> '2.2.0',
			'row_key'		=> 'move_topics_when_locked_solved',
		],
		'AUTO_LOCK' => [
			'name'			=> 'alfredoramos/autolocktopics',
			'min_version'	=> '1.1.0',
			'row_key'		=> 'move_topics_when_locked_auto',
		],
	];

	/**
	 * @param language			$lang
	 * @param request_interface	$request
	 * @param driver_interface	$db
	 * @param template			$template
	 * @param manager			$extension_manager
	 */
	public function __construct(
		language $lang,
		request_interface $request,
		driver_interface $db,
		template $template,
		manager $extension_manager
	)
	{
		$this->lang 				= $lang;
		$this->request				= $request;
		$this->db					= $db;
		$this->template				= $template;
		$this->extension_manager	= $extension_manager;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.acp_manage_forums_display_form'	=> 'acp_manage_forums_display_form',
			'core.acp_manage_forums_request_data'	=> 'acp_manage_forums_request_data',
		];
	}

	/**
	 * @param data $event
	 */
	public function acp_manage_forums_display_form($event)
	{
		$this->lang->add_lang('acp_forum_settings', 'kasimi/movetopicswhenlocked');

		$is_edit = $event['action'] == 'edit';
		$forum_data = $event['forum_data'];

		$template_vars = [
			'S_MOVE_TOPICS'				=> $is_edit ? $forum_data['move_topics_when_locked'] : false,
			'S_MOVE_TOPICS_TO_OPTIONS'	=> make_forum_select($is_edit ? $forum_data['move_topics_when_locked_to'] : false, false, false, true),
		];

		foreach ($this->compatible_extensions as $ext_key => $ext)
		{
			$full_name = $this->lang->lang('MOVE_' . $ext_key . '_EXTENSION');

			if ($this->extension_manager->is_enabled($ext['name']))
			{
				$metadata = $this->extension_manager->create_extension_metadata_manager($ext['name'])->get_metadata();
				$is_valid_version = phpbb_version_compare($metadata['version'], $ext['min_version'], '>=');

				$template_vars = array_merge($template_vars, [
					'S_MOVE_' . $ext_key			=> $is_edit ? $forum_data[$ext['row_key']] : false,
					'MOVE_' . $ext_key . '_ENABLED'	=> $is_valid_version ? $this->lang->lang('MOVE_EXTENSION_ENABLED', $full_name) : false,
					'MOVE_' . $ext_key . '_VERSION'	=> $is_valid_version ? false : $this->lang->lang('MOVE_EXTENSION_VERSION', $ext['min_version'], $full_name),
				]);
			}
			else
			{
				$template_vars = array_merge($template_vars, [
					'MOVE_' . $ext_key . '_DISABLED' => $this->lang->lang('EXTENSION_DISABLED', $full_name),
				]);
			}
		}

		$this->template->assign_vars($template_vars);
	}

	/**
	 * @param data $event
	 */
	public function acp_manage_forums_request_data($event)
	{
		$lock_options = [
			'move_topics_when_locked'			=> $this->request->variable('move_topics_when_locked', 0),
			'move_topics_when_locked_solved'	=> $this->request->variable('move_topics_when_locked_solved', 0),
			'move_topics_when_locked_auto'		=> $this->request->variable('move_topics_when_locked_auto', 0),
			'move_topics_when_locked_to'		=> $this->request->variable('move_topics_when_locked_to', 0),
		];

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
		$subforum_ids = [];

		foreach (get_forum_branch((int) $forum_id, 'children', 'descending', false) as $subforum)
		{
			$subforum_ids[] = (int) $subforum['forum_id'];
		}

		return $subforum_ids;
	}
}
