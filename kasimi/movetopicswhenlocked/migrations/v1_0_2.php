<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2015 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\migrations;

class v1_0_2 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kasimi\movetopicswhenlocked\migrations\v1_0_1');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('kasimi.movetopicswhenlocked.version', '1.0.2')),
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'forums' => array(
					'move_topics_when_locked_solved' => array('TINT:1', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'forums'	=> array(
					'move_topics_when_locked_solved',
				),
			),
		);
	}
}
