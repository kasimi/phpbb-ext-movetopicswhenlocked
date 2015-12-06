<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2015 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\migrations;

use \phpbb\db\migration\migration;

class v1_0_0 extends migration
{
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'forums' => array(
					'move_topic'		=> array('TINT:1', 0),
					'move_topics_to'	=> array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'forums'	=> array(
					'move_topic',
					'move_topics_to',
				),
			),
		);
	}
}
