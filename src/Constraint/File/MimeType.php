<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractConstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class MimeType extends AbstractConstraint implements ConstraintInterface
{
    private $mimeTypes = [];

    /**
     * @inheritDoc
     */
    public function validate($uploadedFile)
    {
        $mimeType = $this->getUploadedFileMimeType($uploadedFile);
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

    /**
     * @param array $mimeTypes
     *
     * @return MimeType
     */
    public function setMimeTypes(array $mimeTypes)
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }

    /**
     * @return array
     */
    public function getMimeTypes()
    {
        return $this->mimeTypes;
    }

    private function getUploadedFileMimeType($uploadedFile)
    {
        if (\extension_loaded('fileinfo')) {
            $finfo = new \finfo(\FILEINFO_MIME_TYPE);

            return $finfo->file($uploadedFile['tmp_name']);
        }

        if (\function_exists('mime_content_type')) {
            return mime_content_type($uploadedFile['tmp_name']);
        }

        return null;
    }
}
