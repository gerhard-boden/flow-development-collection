<?php
namespace TYPO3\Eel\Tests\Unit\FlowQuery\Operations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Eel".             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Eel\FlowQuery\Operations\Object\NotOperation;

/**
 * NotOperation test
 */
class NotOperationTest extends \TYPO3\Flow\Tests\UnitTestCase {

    public function NotExamples() {
                return array(
                        'no argument' => array(array('a', 'b', 'c'), array(), array('a', 'b', 'c')),
                        'empty array' => array(array(), array('[instanceof B]'), array()),
                        'argumentIsFilter' => array(array('a', 'b', 'c', 'd'), array('b'), array('a', 'c', 'd')),
                    );
    }

    /**
         * @test
         * @dataProvider NotExamples
         */
    public function evaluateSetsTheCorrectPartOfTheContextArray($value, $arguments, $expected) {
                $flowQuery = new \TYPO3\Eel\FlowQuery\FlowQuery($value);
        
                $operation = new NotOperation();
                $operation->evaluate($flowQuery, $arguments);
        
                $this->assertEquals($expected, $flowQuery->getContext());
            }

}