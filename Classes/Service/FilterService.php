<?php

namespace Ps\Play\Service;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class FilterService {

	/**
	 * objectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * Plugin TypoScript Settings
	 *
	 * @var array $settings
	 */
	protected $settings;

	/**
	 * @var \TYPO3\CMS\Extbase\Mvc\Web\Request $request
	 */
	protected $request;

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @param \TYPO3\CMS\Extbase\Mvc\Web\Request $request
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
	 * @return void
	 */
	public function __construct($request, $contentObject) {
		$this->initializeSettings();
		$this->request = $request;
		$this->contentObject = $contentObject;
	}

	/**
	 * return an instance of objectManager
	 *
	 * @return \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	public function getObjectManager() {
		if(($this->objectManager instanceof \TYPO3\CMS\Extbase\Object\ObjectManager) === false) {
			$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
		}

		return $this->objectManager;
	}

	/**
	 * liefert die TypoScript Plugin Einstellungen
	 *
	 * @return array
	 */
	public function initializeSettings() {
		if(isset($this->settings) === false) {
			$this->settings = $this->getObjectManager()->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class)->getConfiguration(
				\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'Play', 'Filter'
			);
		}

		return $this->settings;
	}

	/**
	 * @param string $name
	 * @return array
	 */
	public function get($name) {
		$return = [];

		if(isset($this->settings['filter'][$name]) === true) {

			// FieldNamePrefix
			// @see: https://docs.typo3.org/typo3cms/ExtbaseGuide/Fluid/ViewHelper/Form.html#fieldnameprefix
			$return['namespace'] = $this->settings['filter'][$name]['namespace'];

			// Items
			$return['items'] = [];

			foreach($this->settings['filter'][$name]['items'] as $itemKey => $itemProperties) {
				$return['items'][$itemKey] = array_merge($itemProperties, [
					'name' => $itemKey
				]);
			}
		}

		return $return;
	}
}