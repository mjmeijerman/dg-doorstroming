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

    public function compare(Category $other): int
    {
        if ($this->equals($other)) {
            return 0;
        }

        foreach (self::allFromYoungToOld() as $category) {
            if ($this->equals($category)) {
                return -1;
            }

            if ($other->equals($category)) {
                return 1;
            }
        }

        throw new \LogicException(
            sprintf('Found no result while comparing category "%s" with "%s"', $this->toString(), $other->toString())
        );
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

    public function guess(string $supposedToBeACategory): self
    {
        $hayStack = strtolower($supposedToBeACategory);
        if (strpos($hayStack, 'mini') !== false) {
            return self::MINI();
        }

        if (strpos($hayStack, 'instap') !== false) {
            if (strpos($hayStack, 'voor') !== false) {
                return self::VOORINSTAP();
            }

            return self::INSTAP();
        }

        if (strpos($hayStack, 'pupil1') !== false || strpos($hayStack, 'pupil-1') !== false || strpos($hayStack, 'pupil 1') !== false) {
            return self::PUPIL1();
        }

        if (strpos($hayStack, 'pupil2') !== false || strpos($hayStack, 'pupil-2') !== false || strpos($hayStack, 'pupil 2') !== false) {
            return self::PUPIL2();
        }

        if (strpos($hayStack, 'jeugd1') !== false || strpos($hayStack, 'jeugd-1') !== false || strpos($hayStack, 'jeugd 1') !== false) {
            return self::JEUGD1();
        }

        if (strpos($hayStack, 'jeugd2') !== false || strpos($hayStack, 'jeugd-2') !== false || strpos($hayStack, 'jeugd 2') !== false) {
            return self::JEUGD2();
        }

        if (strpos($hayStack, 'junior') !== false) {
            return self::JUNIOR();
        }

        if (strpos($hayStack, 'senior') !== false) {
            return self::SENIOR();
        }

        throw new \LogicException(sprintf('Could not guess the category from input "%s"', $supposedToBeACategory));
    }

    private function __construct()
    {
    }
}
