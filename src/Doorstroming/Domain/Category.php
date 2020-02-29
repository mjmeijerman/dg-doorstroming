<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class Category
{
    const MINI       = 'Mini';
    const VOORINSTAP = 'Voorinstap';
    const INSTAP     = 'Instap';
    const PUPIL1     = 'Pupil 1';
    const PUPIL2     = 'Pupil 2';
    const JEUGD1     = 'Jeugd 1';
    const JEUGD2     = 'Jeugd 2';
    const JUNIOR     = 'Junior';
    const SENIOR     = 'Senior';

    private string $category;

    public static function MINI(): self
    {
        return self::fromString(self::MINI);
    }

    public static function VOORINSTAP(): self
    {
        return self::fromString(self::VOORINSTAP);
    }

    public static function INSTAP(): self
    {
        return self::fromString(self::INSTAP);
    }

    public static function PUPIL1(): self
    {
        return self::fromString(self::PUPIL1);
    }

    public static function PUPIL2(): self
    {
        return self::fromString(self::PUPIL2);
    }

    public static function JEUGD1(): self
    {
        return self::fromString(self::JEUGD1);
    }

    public static function JEUGD2(): self
    {
        return self::fromString(self::JEUGD2);
    }

    public static function JUNIOR(): self
    {
        return self::fromString(self::JUNIOR);
    }

    public static function SENIOR(): self
    {
        return self::fromString(self::SENIOR);
    }

    public static function fromString(string $category): self
    {
        Assertion::choice($category, self::allAsStringFromYoungToOld());

        $self           = new self();
        $self->category = $category;

        return $self;
    }

    public function toString(): string
    {
        return $this->category;
    }

    /**
     * @return self[]
     */
    public static function allFromYoungToOld(): array
    {
        return [
            self::MINI(),
            self::VOORINSTAP(),
            self::INSTAP(),
            self::PUPIL1(),
            self::PUPIL2(),
            self::JEUGD1(),
            self::JEUGD2(),
            self::JUNIOR(),
            self::SENIOR(),
        ];
    }

    /**
     * @return self[]
     */
    public static function allAsStringFromYoungToOld(): array
    {
        return [
            self::MINI,
            self::VOORINSTAP,
            self::INSTAP,
            self::PUPIL1,
            self::PUPIL2,
            self::JEUGD1,
            self::JEUGD2,
            self::JUNIOR,
            self::SENIOR,
        ];
    }

    /**
     * @return self[]
     */
    public static function CompulsoryCategoriesFromYoungToOld(): array
    {
        return [
            self::MINI,
            self::VOORINSTAP,
            self::INSTAP,
            self::PUPIL1,
            self::PUPIL2,
            self::JEUGD1,
        ];
    }

    /**
     * @return self[]
     */
    public static function ChoiceCategoriesFromYoungToOld(): array
    {
        return [
            self::JEUGD2,
            self::JUNIOR,
            self::SENIOR,
        ];
    }

    public static function isCompulsory(self $category): bool
    {
        return in_array($category->toString(), self::CompulsoryCategoriesFromYoungToOld());
    }

    public function equals(self $other): bool
    {
        return $other->toString() === $this->toString();
    }

    private function __construct()
    {
    }
}
