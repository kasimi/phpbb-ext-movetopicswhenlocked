<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2018 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\migrations;

class v1_1_0 extends \phpbb\db\migration\migration
{
	/**
	 * @return array
	 */
	public static function depends_on()
	{
		return ['\kasimi\movetopicswhenlocked\migrations\v1_0_5'];
	}

	/**
	 * @return array
	 */
	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'forums' => [
					'move_topics_when_locked_auto' => ['TINT:1', 0],
				],
			],
		];
	}

	/**
	 * @return array
	 */
	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'forums'	=> [
					'move_topics_when_locked_auto',
				],
			],
		];
	}
}
