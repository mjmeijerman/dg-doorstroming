<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class CategoryLevelSpecificDoorstromingListsCollection
{
    /**
     * @var CategoryLevelSpecificDoorstromingLists[]
     */
    private array $categoryLevelSpecificDoorstromingLists;

    public static function create(array $categoryLevelSpecificDoorstromingLists): self
    {
        Assertion::allIsInstanceOf(
            $categoryLevelSpecificDoorstromingLists,
            CategoryLevelSpecificDoorstromingLists::class
        );

        $self                                         = new self();
        $self->categoryLevelSpecificDoorstromingLists = $categoryLevelSpecificDoorstromingLists;

        return $self;
    }

    public function toArray(): array
    {
        return $this->categoryLevelSpecificDoorstromingLists;
    }
}
