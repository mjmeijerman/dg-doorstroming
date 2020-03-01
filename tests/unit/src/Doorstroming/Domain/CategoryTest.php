<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use PHPUnit_Framework_TestCase;

class CategoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itReturnsCompulsoryCategoriesFromYoungToOld()
    {
        $expectedResult = [
            Category::MINI,
            Category::VOORINSTAP,
            Category::INSTAP,
            Category::PUPIL1,
            Category::PUPIL2,
            Category::JEUGD1,
        ];

        $this->assertSame($expectedResult, Category::CompulsoryCategoriesFromYoungToOld());
    }

    /**
     * @test
     */
    public function itReturnsChoiceCategoriesFromYoungToOld()
    {
        $expectedResult = [
            Category::JEUGD2,
            Category::JUNIOR,
            Category::SENIOR,
        ];

        $this->assertSame($expectedResult, Category::ChoiceCategoriesFromYoungToOld());
    }

    /**
     * @test
     */
    public function itKnowIfACategoryIsCompulsory()
    {
        $this->assertTrue(Category::isCompulsory(Category::JEUGD1()));
        $this->assertFalse(Category::isCompulsory(Category::JEUGD2()));
    }
}
