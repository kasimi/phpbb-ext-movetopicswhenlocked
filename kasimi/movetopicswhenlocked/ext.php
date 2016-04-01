<?php

/**
 *
 * @package phpBB Extension - Move Topics When Locked
 * @copyright (c) 2016 kasimi
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace kasimi\movetopicswhenlocked;

class ext extends \phpbb\extension\base
{
	/**
	 * Requires phpBB 3.1.7-RC1 due to the required event core.mcp_lock_unlock_after
	 *
	 * @return bool
	 * @access public
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');
		return phpbb_version_compare($config['version'], '3.1.7-RC1', '>=');
	}
}
