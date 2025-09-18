<?php

declare(strict_types=1);

namespace Palmtree\Form\Http;

use Palmtree\Form\Exception\NotSubmittedException;
use Palmtree\Form\Form;

class JsonResponse implements \JsonSerializable
{
    public const SUCCESS_MESSAGE = 'Thanks!';
    public const ERROR_MESSAGE = 'Oops! Something went wrong there. Check the form for errors';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(public array $data, public bool $success)
    {
    }

    /**
     * @return array{success: bool, data: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'data' => $this->data,
        ];
    }

    public function send(): never
    {
        header('Content-Type: application/json');

        echo json_encode($this, flags: \JSON_THROW_ON_ERROR);

        exit;
    }

    public static function fromForm(Form $form, string $successMessage = self::SUCCESS_MESSAGE, string $errorMessage = self::ERROR_MESSAGE): self
    {
        if (!$form->isSubmitted()) {
            throw new NotSubmittedException('Form must be submitted before calling ' . __METHOD__);
        }

        $success = $form->isValid();

        $data = [
            'message' => $success ? $successMessage : $errorMessage,
        ];

        if (!$success) {
            $data['errors'] = $form->getErrors();
        }

        return new self(
            $data,
            $success,
        );
    }
}
