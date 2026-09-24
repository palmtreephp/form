<?php

declare(strict_types=1);

namespace Palmtree\Form\Http;

use Palmtree\Form\Exception\NotSubmittedException;
use Palmtree\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

class JsonResponse implements \JsonSerializable
{
    public const SUCCESS_MESSAGE = 'Thanks!';
    public const ERROR_MESSAGE = 'Oops! Something went wrong there. Check the form for errors';

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(public array $data, public bool $success, public int $status = 200)
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

    /**
     * Sends the response and exits. Use toSymfonyResponse() to return the response from a framework controller instead.
     */
    public function send(): never
    {
        http_response_code($this->status);
        header('Content-Type: application/json');

        echo json_encode($this, flags: \JSON_THROW_ON_ERROR);

        exit;
    }

    public function toSymfonyResponse(): SymfonyJsonResponse
    {
        return new SymfonyJsonResponse($this->jsonSerialize(), $this->status);
    }

    /**
     * @param int $errorStatus HTTP status code for an invalid form, e.g. 422
     */
    public static function fromForm(Form $form, string $successMessage = self::SUCCESS_MESSAGE, string $errorMessage = self::ERROR_MESSAGE, int $errorStatus = 200): self
    {
        if (!$form->isSubmitted()) {
            throw new NotSubmittedException('Form must be submitted before calling ' . __METHOD__);
        }

        $success = $form->isValid();

        $data = [
            'message' => $success ? $successMessage : ($form->getErrorMessage() ?? $errorMessage),
        ];

        if (!$success) {
            $data['errors'] = $form->getErrors();
        }

        return new self($data, $success, $success ? 200 : $errorStatus);
    }
}
