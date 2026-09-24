<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit\Csrf;

use Palmtree\Form\Csrf\CsrfValidatorInterface;
use Palmtree\Form\Exception\CsrfValidationFailedException;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class CsrfFailureTest extends TestCase
{
    public function testFailedCsrfValidationMarksFormSubmittedAndInvalid(): void
    {
        $form = $this->createForm();

        $form->submit(['name' => 'Bob']);

        $this->assertTrue($form->isSubmitted());
        $this->assertFalse($form->isValid());
        $this->assertSame(Form::CSRF_ERROR_MESSAGE, $form->getErrorMessage());
    }

    public function testSubmittedDataIsKeptForRedisplay(): void
    {
        $form = $this->createForm();

        $form->submit(['name' => 'Bob']);

        $this->assertSame('Bob', $form->get('name')->getData());

        $html = $form->render();

        $this->assertStringContainsString('value="Bob"', $html);
        $this->assertStringContainsString(Form::CSRF_ERROR_MESSAGE, $html);
        $this->assertStringNotContainsString('is-invalid', $html);
    }

    public function testBoundDataIsNotUpdated(): void
    {
        $data = new \ArrayObject(['name' => 'Alice']);

        $form = $this->createForm($data);

        $form->submit(['name' => 'Bob']);

        $this->assertSame('Alice', $data['name']);
    }

    public function testJsonResponseReportsCsrfFailure(): void
    {
        $form = $this->createForm();

        $form->submit(['name' => 'Bob']);

        $this->assertSame([
            'success' => false,
            'data' => [
                'message' => Form::CSRF_ERROR_MESSAGE,
                'errors' => [],
            ],
        ], JsonResponse::fromForm($form)->jsonSerialize());
    }

    public function testJsonResponseUsesDefaultErrorMessageWithoutFormError(): void
    {
        $form = (new FormBuilder('test'))->add('name', 'text')->getForm();

        $form->submit(['name' => '']);

        $this->assertSame(JsonResponse::ERROR_MESSAGE, JsonResponse::fromForm($form)->jsonSerialize()['data']['message']);
    }

    /**
     * @param \ArrayObject<string, mixed>|null $data
     */
    private function createForm(?\ArrayObject $data = null): Form
    {
        return (new FormBuilder('test', $data))
            ->setCsrfValidator(new class implements CsrfValidatorInterface {
                public function validate(): void
                {
                    throw new CsrfValidationFailedException('Invalid origin');
                }
            })
            ->add('name', 'text')
            ->getForm();
    }
}
