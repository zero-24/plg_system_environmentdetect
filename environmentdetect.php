<?php
/**
 * EnvironmentDetect Plugin
 *
 * @copyright  Copyright (C) 2017 Tobias Zulauf All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or Later
 */

defined('_JEXEC') or die;

jimport('joomla.environment.browser');

/**
 * Plugin class for Environment Detection
 *
 * @since  1.0
 */
class PlgSystemEnvironmentDetect extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Holds the current supported platforms by the project plus a user friendly name
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $supportedPlatforms = array(
		'win' => 'Microsoft Windows',
		'mac' => 'Mac OS / OS X',
	);

	/**
	 * Holds the current supported browsers by the project plus a user friendly name
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $supportedBrowsers = array(
		'opera'   => 'Opera',
		'edge'    => 'Microsoft Edge',
		'chrome'  => 'Google Chrome',
		'msie'    => 'Microsoft Internet Explorer',
		'mozilla' => 'Mozilla Firefox',
		'safari'  => 'Apple Safari',
	);

	/**
	 * Match the supported platform to the URLs
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $platformToUrl = array(
		'win' => 'windows',
		'mac' => 'apple-mac-os',
	);

	/**
	 * Match the supported browser to the URLs
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $browserToUrl = array(
		'opera'   => 'opera',
		'edge'    => 'microsoft-edge',
		'chrome'  => 'google-chrome',
		'msie'    => 'internet-explorer-11',
		'mozilla' => 'mozilla-firefox',
		'safari'  => 'safari',
	);

	/**
	 * Listener for the `onAfterRoute` event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onAfterRoute()
	{
		$url    = JUri::getInstance()->toString();
		$detect = explode('/', $url);

		// Load language.
		$this->loadLanguage();

		if (end($detect) !== 'detect.html')
		{
			return;
		}

		$environment = $this->getEnvironmentInfos();

		// When we can not detect a supported platform just tell that on move to the homepage
		if ($environment['platform'] === 'unknown')
		{
			$this->app->enqueueMessage(JText::_('PLG_SYSTEM_ENVIRONMENTDETECT_CANT_DETECT_SYSTEM'), 'message');
			$this->app->redirect('index.php');

			return;
		}

		// Craft the redirect url and a user friendly message
		$redirect            = str_replace('detect.html', $this->constructRedirect($environment), $url);
		$userFriendlyMessage = $this->getUserFriendlyMessage($environment);

		// Show the message and redirect
		$this->app->enqueueMessage($userFriendlyMessage, 'message');
		$this->app->redirect($redirect . '.html');
	}

	/**
	 * Method that create a UserFriendly message based on the envoirment infomations
	 *
	 * @param   array  The array with the envoirment infos
	 *
	 * @return  mixed  The redirect URL or false when the browser was not detected
	 *
	 * @since   1.0
	 */
	private function getUserFriendlyMessage($environment)
	{
		if ($environment['browser'] === 'unknown')
		{
			// We have just detected a supported platform
			return JText::sprintf(
				'PLG_SYSTEM_ENVIRONMENTDETECT_DETECTED_JUST_PLATFORM',
				$this->getUserFriendlyPlatform($environment['platform'])
			);
		}

		// We have detected a supported platform and browser
		return JText::sprintf(
			'PLG_SYSTEM_ENVIRONMENTDETECT_DETECTED_PLATFORM_AND_BROWSER',
			$this->getUserFriendlyPlatform($environment['platform']),
			$this->getUserFriendlyBrowser($environment['browser'])
		);
	}

	/**
	 * Gets the user friendly name for the detected Platform
	 *
	 * @return  string  User friendly name for the detected Platform
	 *
	 * @since   1.0
	 */
	private function getUserFriendlyPlatform($platform)
	{
		return $this->supportedPlatforms[$platform];
	}

	/**
	 * Gets the user friendly name for the detected Browser
	 *
	 * @return  string  User friendly name for the detected Browser
	 *
	 * @since   1.0
	 */
	private function getUserFriendlyBrowser($browser)
	{
		return $this->supportedBrowsers[$browser];
	}

	/**
	 * Method that construct the redirect URL
	 *
	 * @param  array  The array with the envoirment infos
	 *
	 * @return  mixed  The redirect URL or false when the browser was not detected
	 *
	 * @since   1.0
	 */
	private function constructRedirect($environment)
	{
		if ($environment['browser'] === 'unknown')
		{
			// We have just detected a supported platform
			return $this->matchPlatformToUrl($environment['platform']);
		}

		// We have detected a supported platform and browser
		return $this->matchPlatformToUrl($environment['platform']) . '/' . $this->matchBrowserToUrl($environment['browser']);
	}

	/**
	 * Matches the detected Browser to the correct URL
	 *
	 * @return  string  The string for that Browser in the URL
	 *
	 * @since   1.0
	 */
	private function matchBrowserToUrl($browser)
	{
		return $this->browserToUrl[$browser];
	}

	/**
	 * Matches the detected Platform to the correct URL
	 *
	 * @return  string  The string for that Platform in the URL
	 *
	 * @since   1.0
	 */
	private function matchPlatformToUrl($platform)
	{
		return $this->platformToUrl[$platform];
	}

	/**
	 * Method to get the Environment Information based
	 * on Joomla's Joomla\CMS\Environment\Browser class
	 *
	 * @return  array  An array with `platform` and `browser`
	 *
	 * @since   1.0
	 */
	private function getEnvironmentInfos()
	{
		// Get a instance of Joomla\CMS\Environment\Browser
		$browser = JBrowser::getInstance();

		// Get the values form the useragent string
		$detectedPlatform = $browser->getPlatform();
		$detectedBrowser  = $browser->getBrowser();

		// Prepare the return values
		$environmentInfos = array(
			'platform' => 'unknown',
			'browser'  => 'unknown',
		);

		// Set the platform value when we support it.
		if (array_key_exists($detectedPlatform, $this->supportedPlatforms))
		{
			$environmentInfos['platform'] = $detectedPlatform;
		}

		// Set the browser value when we support it.
		if (array_key_exists($detectedBrowser, $this->supportedBrowsers))
		{
			$environmentInfos['browser'] = $detectedBrowser;
		}

		// Return the results
		return $environmentInfos;
	}
}
