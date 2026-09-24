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

        $uploadedFile = $this->getData();

        if ($uploadedFile === null || $uploadedFile->getErrorCode() === UploadedFile::UPLOAD_ERR_NO_FILE) {
            if (!$this->isRequired()) {
                return true;
            }

            $this->setErrorMessage(UploadedFile::ERROR_MESSAGES[UploadedFile::UPLOAD_ERR_NO_FILE]);

            return false;
        }

        if ($uploadedFile->getErrorCode() !== UploadedFile::UPLOAD_ERR_OK) {
            $this->setErrorMessage($uploadedFile->getErrorMessage());

            return false;
        }

        // Checked before any constraints run, as they may read the file at its temp path
        if (!$uploadedFile->isUploaded()) {
            $this->setErrorMessage('The file could not be uploaded');

            return false;
        }

        return parent::isValid();
    }

    protected function acceptsArrayData(): bool
    {
        return true;
    }

    public function getData(): ?UploadedFile
    {
        if ($this->normData === null && UploadedFile::isUploadedFileArray($this->data)) {
            $this->normData = new UploadedFile($this->data);
        }

        return $this->normData;
    }

    public function setData(array|string|int|float|bool|null $data): TypeInterface
    {
        $this->normData = null;

        return parent::setData($data);
    }

    public function clearData(): void
    {
        $this->normData = null;

        parent::clearData();
    }
}
