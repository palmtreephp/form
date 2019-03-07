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
        if (!\in_array($this->getUploadedFileMimeType($uploadedFile), $this->getMimeTypes())) {
            $this->setErrorMessage(
                "Invalid mime type '$mimeType'. Only the following are allowed: " . \implode(', ', $this->getMimeTypes())
            );

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
        if (\class_exists('finfo') && \defined('FILEINFO_MIME_TYPE')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);

            return $finfo->file($uploadedFile['tmp_name']);
        }

        return \mime_content_type($uploadedFile['tmp_name']);
    }
}
