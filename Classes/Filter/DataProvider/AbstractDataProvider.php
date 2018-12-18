<?php

namespace Ps\Play\Filter\DataProvider;

abstract class AbstractDataProvider {

	/**
	 * @param array $data
	 * @param array $properties
	 * @return array $data
	 */
	abstract protected function provide($data, $properties);
}