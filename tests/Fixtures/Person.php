<?php

namespace Palmtree\Form\Test\Fixtures;

use Palmtree\Form\DataMapper\DataMapperInterface;
use Palmtree\Form\DataMapper\ObjectDataMapper;

class Person
{
    /** @var string */
    public $name;
    /** @var string */
    public $emailAddress;
    /** @var int */
    private $age = 0;
    /** @var bool */
    private $signup = false;
    /** @var string */
    private $favouriteConsole;
    /** @var array */
    public $interests = [];
    /** @var array */
    public $pets = [];

    public function setAge($age): void
    {
        $this->age = (int)$age;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setSignup($signup): void
    {
        $this->signup = (bool)$signup;
    }

    public function isSignup(): bool
    {
        return $this->signup;
    }

    /**
     * @param string $favouriteConsole
     */
    public function setFavouriteConsole(string $favouriteConsole): void
    {
        $this->favouriteConsole = $favouriteConsole;
    }

    /**
     * @return string
     */
    public function getFavouriteConsole(): ?string
    {
        return $this->favouriteConsole;
    }
}
