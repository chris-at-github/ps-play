<?php

namespace Ps\Play\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
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
	 * Fix Parameter, die immer gelten und nicht durch den Anwender ueberschrieben werden koennen.
	 * Diese dienen ebenfalls DataProvidern um ihre Suchkriterien einzugrenzen z.B. Jahresauswahl von News
	 *
	 * @var array
	 */
	protected $fixedArguments = [];

	/**
	 * @param string $name
	 * @param \TYPO3\CMS\Extbase\Mvc\Web\Request $request
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObject
	 * @return void
	 */
	public function __construct($name, $request, $contentObject) {
		$this->name = $name;
		$this->request = $request;
		$this->contentObject = $contentObject;

		$this->initializeSettings();
		$this->initializeFixedArguments();
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
	 * @return void
	 */
	public function initializeFixedArguments() {
		if(isset($this->settings['filter'][$this->name]['fixed']) === true) {
			foreach($this->settings['filter'][$this->name]['fixed'] as $fixedName => $fixedValue) {
				$this->fixedArguments[$fixedName] = $fixedValue;

				if(strpos($fixedValue, ',') !== false) {
					$this->fixedArguments[$fixedName] = GeneralUtility::trimExplode(',', $fixedValue);
				}
			}
		}
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

			foreach($this->settings['filter'][$this->name]['items'] as $itemName => $itemProperties) {
				$return['items'][$itemName] = array_merge($itemProperties, [
					'name' => $itemName,
					'selected' => null
				]);
				
				// Eventuelle Parameter verarbeiten
				$arguments = $this->getArguments();
				if(isset($arguments[$itemName]) === true) {
					$return['items'][$itemName]['selected'] = $arguments[$itemName];
				}

				// DataProvider
				if(isset($itemProperties['dataProvider']) === true) {

					// Immer ein Data Eintrag zur Verfuegung stellen
					if(isset($itemProperties['dataProvider']['data']) === false) {
						$itemProperties['data'] = [];
					}

					foreach($itemProperties['dataProvider'] as $dataProviderFqcn => $dataProviderProperties) {
						$dataProvider = $this->getObjectManager()->get($dataProviderFqcn);
						$return['items'][$itemName] = $dataProvider->provide($return['items'][$itemName], $dataProviderProperties);
					}
				}
			}
		}

		return $return;
	}

	public function getArguments() {
		$arguments = [];

		if($this->request->hasArgument($this->getIdentifier()) === true) {
			$request = $this->request->getArgument($this->getIdentifier());

			foreach($this->settings['filter'][$this->name]['items'] as $itemName => $itemProperties) {
				if(isset($request[$itemName]) === true) {
					$arguments[$itemName] = $request[$itemName];
				}
			}

		// Verarbeite Default-Variablen
		} else {
			foreach($this->settings['filter'][$this->name]['items'] as $itemName => $itemProperties) {
				if(isset($itemProperties['default']) === true) {
					$arguments[$itemName] = $itemProperties['default'];

					if(strpos($arguments[$itemName], ',') !== false) {
						$arguments[$itemName] = GeneralUtility::trimExplode(',', $arguments[$itemName]);
					}
				}
			}
		}

		// Verarbeite fixierte Argumente (als letztes -> falls doppelte Keys vorhanden sind)
		$fixedArguments = $this->getFixedArguments();

		foreach($fixedArguments as $fixedName => $fixedValue) {
			$arguments[$fixedName] = $fixedValue;
		}

		return $arguments;
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

	/**
	 * @return array
	 */
	public function getFixedArguments() {
		return $this->fixedArguments;
	}

	/**
	 * @param array $fixedArguments
	 */
	public function setFixedArguments(array $fixedArguments) {
		$this->fixedArguments = $fixedArguments;
	}

	/**
	 * Setzt ein neues fixiertes Argument
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function addFixedArgument($name, $value) {
		$this->fixedArguments[$name] = $value;
	}
}