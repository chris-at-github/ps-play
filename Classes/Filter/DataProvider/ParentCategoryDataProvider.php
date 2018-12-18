<?php

namespace Ps\Play\Filter\DataProvider;

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Connection;

class ParentCategoryDataProvider extends AbstractDataProvider {

	/**
	 * @param array $data
	 * @param array $properties
	 * @return array $data
	 */
	public function provide($data, $properties) {
		$queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category')->createQueryBuilder();
		$statement = $queryBuilder->select('uid', 'title')
			->from('sys_category')
			->where(
				$queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($properties['uid'], \PDO::PARAM_INT))
			)
			->execute();

		while($row = $statement->fetch()) {
			DebuggerUtility::var_dump($row);
		}

		return $data;
	}
}