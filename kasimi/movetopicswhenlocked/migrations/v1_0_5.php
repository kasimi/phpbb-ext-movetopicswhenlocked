<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2017 kasimi - https://kasimi.net
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\migrations;

class v1_0_5 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kasimi\movetopicswhenlocked\migrations\v1_0_4');
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('kasimi.movetopicswhenlocked.version')),
		);
	}
}
