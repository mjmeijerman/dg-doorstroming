<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class Level
{
    const N1   = 'N1';
    const N2   = 'N2';
    const N3   = 'N3';
    const N4   = 'N4';
    const D1   = 'D1';
    const D2   = 'D2';
    const D3   = 'D3';
    const D4   = 'D4';
    const R1   = 'R1';
    const R2   = 'R2';
    const R3   = 'R3';
    const ERE  = 'Eredivisie';
    const DIV1 = 'Divisie 1';
    const DIV2 = 'Divisie 2';
    const DIV3 = 'Divisie 3';
    const DIV4 = 'Divisie 4';
    const DIV5 = 'Divisie 5';
    const DIV6 = 'Divisie 6';

    private string $level;

    public static function N1(): self
    {
        return self::fromString(self::N1);
    }

    public static function N2(): self
    {
        return self::fromString(self::N2);
    }

    public static function N3(): self
    {
        return self::fromString(self::N3);
    }

    public static function N4(): self
    {
        return self::fromString(self::N4);
    }

    public static function D1(): self
    {
        return self::fromString(self::D1);
    }

    public static function D2(): self
    {
        return self::fromString(self::D2);
    }

    public static function D3(): self
    {
        return self::fromString(self::D3);
    }

    public static function D4(): self
    {
        return self::fromString(self::D4);
    }

    public static function R1(): self
    {
        return self::fromString(self::R1);
    }

    public static function R2(): self
    {
        return self::fromString(self::R2);
    }

    public static function R3(): self
    {
        return self::fromString(self::R3);
    }

    public static function ERE(): self
    {
        return self::fromString(self::ERE);
    }

    public static function DIV1(): self
    {
        return self::fromString(self::DIV1);
    }

    public static function DIV2(): self
    {
        return self::fromString(self::DIV2);
    }

    public static function DIV3(): self
    {
        return self::fromString(self::DIV3);
    }

    public static function DIV4(): self
    {
        return self::fromString(self::DIV4);
    }

    public static function DIV5(): self
    {
        return self::fromString(self::DIV5);
    }

    public static function DIV6(): self
    {
        return self::fromString(self::DIV6);
    }

    public static function fromString(string $level): self
    {
        Assertion::choice($level, self::allAsStringFromHighToLow());

        $self        = new self();
        $self->level = $level;

        return $self;
    }

    public function toString(): string
    {
        return $this->level;
    }

    public function compare(Level $other): int
    {
        if ($this->equals($other)) {
            return 0;
        }

        foreach (self::allFromHighToLow() as $level) {
            if ($this->equals($level)) {
                return -1;
            }

            if ($other->equals($level)) {
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
    public static function allFromHighToLow(): array
    {
        return [
            self::N1(),
            self::N2(),
            self::N3(),
            self::N4(),
            self::D1(),
            self::D2(),
            self::D3(),
            self::D4(),
            self::R1(),
            self::R2(),
            self::R3(),
            self::ERE(),
            self::DIV1(),
            self::DIV2(),
            self::DIV3(),
            self::DIV4(),
            self::DIV5(),
            self::DIV6(),
        ];
    }

    /**
     * @return string[]
     */
    public static function allAsStringFromHighToLow(): array
    {
        return [
            self::N1,
            self::N2,
            self::N3,
            self::N4,
            self::D1,
            self::D2,
            self::D3,
            self::D4,
            self::R1,
            self::R2,
            self::R3,
            self::ERE,
            self::DIV1,
            self::DIV2,
            self::DIV3,
            self::DIV4,
            self::DIV5,
            self::DIV6,
        ];
    }

    /**
     * @return string[]
     */
    public static function nationalLevels(): array
    {
        return [
            self::N1,
            self::N2,
            self::N3,
            self::N4,
            self::ERE,
            self::DIV1,
            self::DIV2,
            self::DIV3,
        ];
    }

    public function isNationalLevel(): bool
    {
        return in_array($this->toString(), self::nationalLevels());
    }

    /**
     * @param Category $category
     *
     * @return string[]
     */
    public static function getAvailableLevelsForCategoryAsString(Category $category): array
    {
        switch ($category->toString()) {
            case Category::MINI:
            case Category::VOORINSTAP:
            case Category::INSTAP:
            case Category::PUPIL1:
            case Category::PUPIL2:
                return [
                    self::N1,
                    self::N2,
                    self::N3,
                    self::D1,
                    self::D2,
                    self::D3,
                    self::D4,
                ];
            case Category::JEUGD1:
                return [
                    self::N1,
                    self::N2,
                    self::N3,
                    self::N4,
                    self::D1,
                    self::D2,
                    self::D3,
                    self::D4,
                ];
            case Category::JEUGD2:
            case Category::JUNIOR:
            case Category::SENIOR:
                return [
                    self::ERE,
                    self::DIV1,
                    self::DIV2,
                    self::DIV3,
                    self::DIV4,
                    self::DIV5,
                    self::DIV6,
                ];
            default:
                throw new \LogicException(sprintf('No levels found for category "%s"', $category->toString()));
        }
    }

    public function equals(self $other): bool
    {
        return $other->toString() === $this->toString();
    }

    private function __construct()
    {
    }
}
