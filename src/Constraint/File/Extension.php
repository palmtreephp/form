<?php declare(strict_types=1);

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\UploadedFile;

class Extension extends AbstractConstraint implements ConstraintInterface
{
    /** @var array<int, string> */
    private $extensions = [];

    public function validate($input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(UploadedFile $input): bool
    {
        $extension = pathinfo($input->getName(), \PATHINFO_EXTENSION);

        if (!\in_array($extension, $this->extensions, true)) {
            $this->setErrorMessage('Only the following file extensions are allowed: ' . implode(', ', $this->extensions));

            return false;
        }

        return true;
    }

    /**
     * @param array<int, string> $extensions
     */
    public function setExtensions(array $extensions): self
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
