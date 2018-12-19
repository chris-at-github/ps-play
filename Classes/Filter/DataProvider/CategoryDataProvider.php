<?php

namespace Ps\Play\Filter\DataProvider;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class CategoryDataProvider extends AbstractDataProvider {

	/**
	 * @param array $data
	 * @param array $properties
	 * @return array $data
	 */
	public function provide($data, $properties) {
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category')->createQueryBuilder();
		$query = $queryBuilder->select('uid', 'title')
			->from('sys_category');

		if(isset($properties['parent']) === true) {
			$queryBuilder->where(
				$queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($properties['parent'], \PDO::PARAM_INT))
			);
		}

		$statement = $query->execute();

		// Zum Verarbeiten muss der Selected Wert immer ein Array sein (kann durch Default ein String sein)
		if(gettype($data['selected']) !== 'array') {
			$data['selected'] = [$data['selected']];
		}

		while($row = $statement->fetch()) {
			$data['data'][$row['uid']] = [
				'label' => $row['title'],
				'selected' => false
			];

			if(in_array($row['uid'], $data['selected']) === true) {
				$data['data'][$row['uid']]['selected'] = true;
			}
		}

		return $data;
	}
}