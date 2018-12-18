<?php

namespace Ps\Play\Filter\DataProvider;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class ParentCategoryDataProvider extends AbstractDataProvider {

	/**
	 * @param array $data
	 * @param array $properties
	 * @return array $data
	 */
	public function provide($data, $properties) {
		$data['data'] = [
			78 => [
				'label' => 'Thema A'
			],
			63 => [
				'label' => 'Thema B'
			],
		];

		return $data;
	}
}