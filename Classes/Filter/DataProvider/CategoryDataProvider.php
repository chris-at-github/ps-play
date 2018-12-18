<?php

namespace Ps\Play\Filter\DataProvider;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

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

		while($row = $statement->fetch()) {
			$data['data'][$row['uid']] = [
				'label' => $row['title']
			];
		}

		return $data;
	}
}