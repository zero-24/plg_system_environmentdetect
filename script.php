<?php
/**
 * EnvironmentDetect Plugin
 *
 * @copyright  Copyright (C) 2017 Tobias Zulauf All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

 defined('_JEXEC') or die; 

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  1.0
 */
class PlgSystemEnvironmentDetectInstallerScript extends JInstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '3.7';
		$this->minimumPhp    = '7.0';

		$this->deleteFiles = array(
			'/plugins/system/environmentdetect/language/en-GB/en-GB.plg_system_environmentdetect.sys.ini',
			'/plugins/system/environmentdetect/language/de-DE/de-DE.plg_system_environmentdetect.sys.ini',
		);
	}
}
