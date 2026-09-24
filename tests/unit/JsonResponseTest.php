<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class JsonResponseTest extends TestCase
{
    public function testValidFormResponds200(): void
    {
        $response = JsonResponse::fromForm($this->submitForm('Bob'), errorStatus: 422);

        $this->assertTrue($response->success);
        $this->assertSame(200, $response->status);
    }

    public function testInvalidFormUsesErrorStatus(): void
    {
        $this->assertSame(200, JsonResponse::fromForm($this->submitForm(''))->status);
        $this->assertSame(422, JsonResponse::fromForm($this->submitForm(''), errorStatus: 422)->status);
    }

    public function testToSymfonyResponse(): void
    {
        $response = JsonResponse::fromForm($this->submitForm(''), errorStatus: 422)->toSymfonyResponse();

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame([
            'success' => false,
            'data' => [
                'message' => JsonResponse::ERROR_MESSAGE,
                'errors' => ['name' => 'Please fill in this field'],
            ],
        ], json_decode((string)$response->getContent(), true));
    }

    private function submitForm(string $name): \Palmtree\Form\Form
    {
        $form = (new FormBuilder('test'))->add('name', 'text')->getForm();
        $form->submit(['name' => $name]);

        return $form;
    }
}
