<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\Form;
use Palmtree\Html\Element;

interface TypeInterface
{
    public function build(): void;

    public function setType(string $type): self;

    public function setName(string $name): self;

    public function setLabel(?string $label): self;

    public function getLabel(): ?string;

    public function getName(): string;

    public function isValid(): bool;

    public function getLabelElement(): ?Element;

    public function getElement(): Element;

    /**
     * @return array<int, Element>
     */
    public function getElements(): array;

    public function getHumanName(): string;

    public function getNameAttribute(): string;

    public function getPlaceHolderAttribute(): string;

    public function getData(): mixed;

    public function getNormData(): mixed;

    public function clearData(): void;

    public function setData(array|string|int|bool|null $data): self;

    public function mapData(): void;

    public function getErrorMessage(): ?string;

    public function setErrorMessage(string $errorMessage): self;

    public function setTag(string $tag): self;

    public function getTag(): string;

    public function getType(): string;

    public function setRequired(bool $required): self;

    public function isRequired(): bool;

    public function getForm(): Form;

    public function setForm(Form $form): self;

    public function getParent(): ?self;

    public function setParent(self $parent): self;

    public function addChild(self $child): self;

    public function add(string $name, string $class, array $options = []): self;

    /**
     * @return array<string|int, TypeInterface>
     */
    public function all(): array;

    public function get(string $name): ?self;

    public function setPosition(int $position): self;

    public function getPosition(): int;

    public function isUserInput(): bool;

    public function setUserInput(bool $userInput): self;

    public function addConstraint(ConstraintInterface ...$constraints): self;

    /**
     * @return array<int, ConstraintInterface>
     */
    public function getConstraints(): array;

    public function clearConstraints(): self;

    public function isMapped(): bool;
}
