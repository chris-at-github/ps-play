<?php

namespace Ps\Play\Filter\DataProvider;

abstract class AbstractDataProvider {

	/**
	 * Instanz des Filter Service
	 *
	 * @var \Ps\Play\Service\FilterService $filter
	 */
	protected $filter;

	/**
	 * @param array $data
	 * @param array $properties
	 * @return array $data
	 */
	abstract protected function provide($data, $properties);

	/**
	 * @return \Ps\Play\Service\FilterService
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @param \Ps\Play\Service\FilterService $filter
	 */
	public function setFilter(\Ps\Play\Service\FilterService $filter) {
		$this->filter = $filter;
	}
}