<?php

declare(strict_types=1);

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;
use Palmtree\Form\UploadedFile;

class MimeType extends AbstractConstraint implements ConstraintInterface
{
    /** @var array<int, string> */
    private array $mimeTypes = [];

    public function validate(mixed $input): bool
    {
        return $this->doValidate($input);
    }

    private function doValidate(UploadedFile $input): bool
    {
        $mimeType = $this->getUploadedFileMimeType($input);
        if ($mimeType === null) {
            $this->setErrorMessage('Could not determine the uploaded file\'s mime type');

            return false;
        }

        if (!\in_array($mimeType, $this->mimeTypes, true)) {
            $this->setErrorMessage("Invalid mime type '$mimeType'. Only the following are allowed: " . implode(', ', $this->mimeTypes));

            return false;
        }

        return true;
    }

    /** @param array<int, string> $mimeTypes */
    public function setMimeTypes(array $mimeTypes): self
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }

    /** @return array<int, string> */
    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
    }

    private function getUploadedFileMimeType(UploadedFile $uploadedFile): ?string
    {
        if (\extension_loaded('fileinfo')) {
            $finfo = new \finfo(\FILEINFO_MIME_TYPE);

            return $finfo->file($uploadedFile->getTempName()) ?: null;
        }

        if (\function_exists('mime_content_type')) {
            return mime_content_type($uploadedFile->getTempName()) ?: null;
        }

        return null;
    }
}
