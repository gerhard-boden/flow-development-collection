<?php
namespace TYPO3\Eel\FlowQuery\Operations\Object;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Eel".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\FlowQuery\FlowQuery;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;

/**
 * "not" operation working on generic objects. Remove elements from the set of
 * matched elements.
 * Accepts objects, arrays, traversable objects, FlowQuery and filters.
 */
class NotOperation extends \TYPO3\Eel\FlowQuery\Operations\AbstractOperation {

	/**
    	 * {@inheritdoc}
    	 *
    	 * @var string
    	 */
	static protected $shortName = 'not';

	/**
    	 * {@inheritdoc}
    	 *
    	 * @param \TYPO3\Eel\FlowQuery\FlowQuery $flowQuery the FlowQuery object
    	 * @param array $arguments the filter arguments
    	 * @return boolean
    	 */
	public function evaluate(FlowQuery $flowQuery, array $arguments) {
        		if (!isset($arguments[0]) || empty($arguments[0])) {
            			return;
		}
		$output = array();
		$context = $flowQuery->getContext();
		$filterQuery = new FlowQuery($context);

		/*
		 * @2do
		 * Diese Abfrage irgendwie umschreiben dass wir festellen k?nnen ob wir die Filter Operation einsetzen k?nnen
		 * oder ob wir simple Typen wie Arrays behaneln
		 */

		if ($arguments[0] instanceof NodeInterface) {
            			$filterQuery->pushOperation('filter', $arguments);
            			$filteredContext = $filterQuery->get();
            			$output = array_filter($context, function($element) use($filteredContext) {
                				return !in_array($element, $filteredContext, TRUE);
			});
            			$output = array_values($output);
            		} else {
            			$output = array_filter($context, function($element) use($arguments) {
                				return !in_array($element, $arguments, TRUE);
			});
            			$output = array_values($output);
            		}

		if (isset($output[0])) {
            			$flowQuery->setContext($output);
            		}
	}
}