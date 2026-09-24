<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\UploadedFile;

class Size extends AbstractConstraint implements ConstraintInterface
{
    private int $minBytes = 1;
    private ?int $maxBytes = null;

    public function validate(mixed $input): bool
    {
        return $input instanceof UploadedFile && $this->doValidate($input);
    }

    private function doValidate(UploadedFile $input): bool
    {
        $size = $input->getSize();

        if ($size < $this->minBytes) {
            $this->setErrorMessage("File size must be at least $this->minBytes bytes");

            return false;
        }

        if ($this->maxBytes !== null && $size > $this->maxBytes) {
            $this->setErrorMessage("File size must not exceed $this->maxBytes bytes");

            return false;
        }

        return true;
    }

    public function setMinBytes(int $minBytes): self
    {
        $this->minBytes = $minBytes;

        return $this;
    }

    public function getMinBytes(): int
    {
        return $this->minBytes;
    }

    public function setMaxBytes(?int $maxBytes): self
    {
        $this->maxBytes = $maxBytes;

        return $this;
    }

    public function getMaxBytes(): ?int
    {
        return $this->maxBytes;
    }

    /**
     * Alias of setMinBytes() so the constraint can be configured with a 'min' option.
     */
    public function setMin(int $minBytes): self
    {
        return $this->setMinBytes($minBytes);
    }

    /**
     * Alias of setMaxBytes() so the constraint can be configured with a 'max' option.
     */
    public function setMax(?int $maxBytes): self
    {
        return $this->setMaxBytes($maxBytes);
    }
}
