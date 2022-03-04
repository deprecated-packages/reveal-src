<?php

declare(strict_types=1);

namespace Reveal\PHPStanRules\Tests\Rule\ValueObjectDestructRule\Source;

final class SomeValueObject
{
    public function __construct(
        private string $name,
        private string $surname
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    private function getFullname(): string
    {
        return $this->name . ' ' . $this->surname;
    }

    protected function getTitle(): string
    {
        return 'Phd';
    }
}
