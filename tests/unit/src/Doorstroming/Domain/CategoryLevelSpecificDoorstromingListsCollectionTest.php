<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class CategoryLevelSpecificDoorstromingListsCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itRepresentsACategoryLevelSpecificDoorstromingListsCollection()
    {
        $list1 = CategoryLevelSpecificDoorstromingLists::create(
            CategoryLevelCombination::create(Category::JEUGD1(), Level::N3()),
            [],
            12
        );
        $instance = CategoryLevelSpecificDoorstromingListsCollection::create(
            [$list1]
        );

        $this->assertSame([$list1], $instance->toArray());
    }
}
