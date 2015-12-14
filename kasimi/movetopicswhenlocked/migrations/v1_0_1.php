<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2015 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked\migrations;

class v1_0_1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\kasimi\movetopicswhenlocked\migrations\v1_0_0');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('kasimi.movetopicswhenlocked.version', '1.0.1')),
		);
	}
}
