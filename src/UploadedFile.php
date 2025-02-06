<?php

declare(strict_types=1);

namespace Palmtree\Form;

/**
 * @phpstan-type UploadedFileArray array{name: string, type?: string, size: numeric-string, tmp_name: string, error: numeric-string}
 */
class UploadedFile
{
    final public const UPLOAD_ERR_OK = 0;
    final public const UPLOAD_ERR_INI_SIZE = 1;
    final public const UPLOAD_ERR_FORM_SIZE = 2;
    final public const UPLOAD_ERR_PARTIAL = 3;
    final public const UPLOAD_ERR_NO_FILE = 4;
    final public const UPLOAD_ERR_NO_TMP_DIR = 6;
    final public const UPLOAD_ERR_CANT_WRITE = 7;
    final public const UPLOAD_ERR_EXTENSION = 8;

    /** @var array<int, string> */
    final public const ERROR_MESSAGES = [
        self::UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        self::UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        self::UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
        self::UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        self::UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        self::UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        self::UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];

    private string $name;
    private ?string $type;
    private int $size;
    private string $tempName;
    private int $errorCode;

    /**
     * @param UploadedFileArray $uploadedFile
     */
    public function __construct(array $uploadedFile)
    {
        $this->name = $uploadedFile['name'];
        $this->type = $uploadedFile['type'] ?? null;
        $this->size = (int)$uploadedFile['size'];
        $this->tempName = $uploadedFile['tmp_name'];
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
     * Returns a human-readable error message based on the error code.
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGES[$this->errorCode] ?? 'An error occurred';
    }
}
