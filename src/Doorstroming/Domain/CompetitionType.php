<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Assert\Assertion;

final class CompetitionType
{
    const NATIONAL = 'national';
    const DISTRICT = 'district';

    private string $competitionType;

    public static function NATIONAL(): self
    {
        return self::fromString(self::NATIONAL);
    }

    public static function DISTRICT(): self
    {
        return self::fromString(self::DISTRICT);
    }

    /**
     * @return string[]
     */
    public static function allAsString(): array
    {
        return [
            self::NATIONAL,
            self::DISTRICT,
        ];
    }

    public static function fromString(string $competitionType): self
    {
        Assertion::choice($competitionType, self::allAsString());
        $self                  = new self();
        $self->competitionType = $competitionType;

        return $self;
    }

    public function toString(): string
    {
        return $this->competitionType;
    }

    public function equals(CompetitionType $other): bool
    {
        return $this->toString() === $other->toString();
    }

    private function __construct()
    {
    }
}
