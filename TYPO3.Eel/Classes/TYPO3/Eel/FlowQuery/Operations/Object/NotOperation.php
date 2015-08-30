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
class NotOperation extends \TYPO3\Eel\FlowQuery\Operations\AbstractOperation
{

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
        $context = $flowQuery->getContext();
        $filterQuery = new FlowQuery($context);

        if ($this->isSimpleArgument($arguments[0])) {
            $output = $this->filterSimple($context, $arguments);
        } else {
            $output = $this->filterNode($context, $filterQuery, $arguments);
        }

        if (isset($output[0])) {
            $flowQuery->setContext($output);
        }
    }

    /**
     * @param NodeInterface $context The node to filter by
     * @return array The filtered node
     */
    protected function filterNode(NodeInterface $context, FlowQuery $filterQuery, array $arguments) {
        $filterQuery->pushOperation('filter', $arguments);
        $filteredContext = $filterQuery->get();
        $output = array_filter($context, function ($element) use ($filteredContext) {
            return !in_array($element, $filteredContext, TRUE);
        });
        return  array_values($output);
    }

    /**
     * @param array $context The value to filter by
     * @return array The filtered values
     */
    protected function filterSimple(array $context, $arguments) {
        $output = array_filter($context, function ($element) use ($arguments) {
            return !in_array($element, $arguments, TRUE);
        });
        return array_values($output);
    }

    /**
     * @param array $arguemnts
     * @return boolean TRUE if argument is a simple type (object, array, string, ...); i.e. everything which is NOT a class name
     */
    protected function isSimpleArgument($arguments) {
        if (is_array($arguments)) {
            foreach ($arguments as $type) {
                if ($type === 'object' || $type === 'array' || \TYPO3\Flow\Utility\TypeHandling::isLiteral($type)){
                    return false;
                }
            }
        } else {
            return $arguments === 'object' || $arguments === 'array' || \TYPO3\Flow\Utility\TypeHandling::isLiteral($arguments);
        }

        return true;
    }
}