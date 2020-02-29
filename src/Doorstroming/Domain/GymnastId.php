<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use phpDocumentor\Reflection\Types\Integer;

final class GymnastId
{
    private int $id;

    public static function fromInteger(Int $id): self
    {
        $self = new self();
        $self->id = $id;

        return $self;
    }

    public function toInteger(): int
    {
        return $this->id;
    }

    public function equals(GymnastId $other): bool
    {
        return $other->toInteger() === $this->id;
    }

    private function __construct()
    {
    }
}
