<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class CategoryLevelCombinationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itRepresentsACategoryLevelCombination()
    {
        $categoryLevelCombination = CategoryLevelCombination::create(
            Category::JEUGD1(),
            Level::N3()
        );

        $this->assertTrue($categoryLevelCombination->category()->equals(Category::JEUGD1()));
        $this->assertTrue($categoryLevelCombination->level()->equals(Level::N3()));
    }
}
