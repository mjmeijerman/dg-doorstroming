<?php

declare(strict_types=1);

namespace Mark\Doorstroming\Domain;

use Ramsey\Uuid\Uuid as RamseyUuid;

abstract class Uuid
{
    /**
     * @var string
     */
    private $value;

    /**
     * @return static
     */
    public static function generate()
    {
        return new static(RamseyUuid::uuid4()->toString());
    }

    public function equals($other): bool
    {
        return $this == $other;
    }

    /**
     * @param string $string
     *
     * @return static
     */
    public static function fromString(string $string)
    {
        return new static($string);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function __construct(string $uuid)
    {
        if (!RamseyUuid::isValid($uuid)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid uuid string "%s" of class "%s"', $uuid, get_class($this)), 400
            );
        }

        $this->value = strtoupper($uuid);
    }
}
