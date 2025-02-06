<?php

declare(strict_types=1);

namespace Palmtree\Form\Type;

use Palmtree\Form\UploadedFile;
use Palmtree\Html\Element;

class FileType extends AbstractType
{
    protected string $type = 'file';
    private ?UploadedFile $normData = null;

    public function getElement(): Element
    {
        $element = parent::getElement();

        unset($element->attributes['value']);

        return $element;
    }

    public function isValid(): bool
    {
        if (!$this->form->isSubmitted()) {
            return true;
        }

        if (!$uploadedFile = $this->getData()) {
            return true;
        }

        if (!$this->isRequired() && $uploadedFile->getErrorCode() === UploadedFile::UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (!parent::isValid()) {
            return false;
        }

        if ($uploadedFile->getErrorCode() !== UploadedFile::UPLOAD_ERR_OK) {
            $this->setErrorMessage($uploadedFile->getErrorMessage());

            return false;
        }

        return true;
    }

    public function getData(): ?UploadedFile
    {
        if ($this->normData === null && $this->data !== null) {
            $this->normData = new UploadedFile($this->data);
        }

        return $this->normData;
    }
}
