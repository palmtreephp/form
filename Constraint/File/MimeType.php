<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractContstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class MimeType extends AbstractContstraint implements ConstraintInterface
{
    protected $mimeTypes = [];

    /**
     * @inheritDoc
     */
    public function validate($file)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->file($file);

        if (!in_array($mimeType, $this->getMimeTypes())) {
            $this->setErrorMessage(
                sprintf('File must have one of the following mime types: %s', implode(',', $this->getMimeTypes()))
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
    public function setMimeTypes($mimeTypes)
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

}
