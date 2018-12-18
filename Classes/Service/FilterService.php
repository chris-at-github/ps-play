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
	 * Filtereintrag (wird als Argument an den Konstruktor uebergeben)
	 *
	 * @var string $name
	 */
	protected $name;

	/**
	 * @var \TYPO3\CMS\Extbase\Mvc\Web\Request $request
	 */
	protected $request;

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @param string $name
	 * @param \TYPO3\CMS\Extbase\Mvc\Web\Request $request
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
	 * @return void
	 */
	public function __construct($name, $request, $contentObject) {
		$this->name = $name;
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
	 * @return array
	 */
	public function get() {
		$return = [];

		if(isset($this->settings['filter'][$this->name]) === true) {

			// FieldNamePrefix
			// @see: https://docs.typo3.org/typo3cms/ExtbaseGuide/Fluid/ViewHelper/Form.html#fieldnameprefix
			$return['namespace'] = $this->settings['filter'][$this->name]['namespace'];

			// Gruppierung aller Filter unter einem Identifier
			$return['identifier'] = $this->getIdentifier();

			// Items
			$return['items'] = [];

			foreach($this->settings['filter'][$this->name]['items'] as $itemKey => $itemProperties) {
				$return['items'][$itemKey] = array_merge($itemProperties, [
					'name' => $itemKey
				]);

				// DataProvider
				if(isset($itemProperties['dataProvider']) === true) {

					// Immer ein Data Eintrag zur Verfuegung stellen
					if(isset($itemProperties['dataProvider']['data']) === false) {
						$itemProperties['data'] = [];
					}

					foreach($itemProperties['dataProvider'] as $dataProviderFqcn => $dataProviderProperties) {
						$dataProvider = $this->getObjectManager()->get($dataProviderFqcn);
						$return['items'][$itemKey] = $dataProvider->provide($return['items'][$itemKey], $dataProviderProperties);
					}
				}
			}
		}

		return $return;
	}

	public function getArguments() {
		$return = [];

		if($this->request->hasArgument($this->getIdentifier()) === true) {
			$arguments = $this->request->getArgument($this->getIdentifier());

			foreach($this->settings['filter'][$this->name]['items'] as $itemKey => $itemProperties) {
				if(isset($arguments[$itemKey]) === true) {
					$return[$itemKey] = $arguments[$itemKey];
				}
			}
		}

		return $return;
	}

	/**
	 * Erzeugt einen eindeutigen Identifier fuer die Formularelemente, falls mehrere Filter auf einer Seite vorhanden sind
	 *
	 * @return string
	 */
	public function getIdentifier() {

		// String Prefix muss vorhanden sein -> reiner Zahlenwert wirft Exception nach dem Absenden
		// @see: https://wiki.typo3.org/Exception/CMS/1210858767
		return md5($this->contentObject->data['uid']);
	}
}