<?php

namespace Palmtree\Form;

class UploadedFile
{
    /** @var string[] */
    public const ERROR_MESSAGES = [
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    ];

    /** @var string */
    private $name;
    /** @var string|null */
    private $type;
    /** @var int */
    private $size;
    /** @var string */
    private $tempName;
    /** @var int */
    private $errorCode;

    public function __construct(array $uploadedFile)
    {
        $this->name      = $uploadedFile['name'];
        $this->type      = $uploadedFile['type'] ?? null;
        $this->size      = (int)$uploadedFile['size'];
        $this->tempName  = $uploadedFile['tmp_name'];
        $this->errorCode = (int)$uploadedFile['error'];
    }

    /**
     * Returns the original name of the file on the client machine.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the client provided mime type of the file. Do NOT trust this.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Returns the size, in bytes, of the uploaded file.
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the temporary filename of the file in which the uploaded file was stored on the server.
     */
    public function getTempName(): string
    {
        return $this->tempName;
    }

    /**
     *  Returns the error code associated with this file upload. An error code of 0
     *  indicates that the upload was successful.
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * Returns a human readable error message based on the error code.
     */
    public function getErrorMessage(): ?string
    {
        return self::ERROR_MESSAGES[$this->errorCode] ?? null;
    }
}
