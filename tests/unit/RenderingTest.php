<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\CollectionType;
use PHPUnit\Framework\TestCase;

class RenderingTest extends TestCase
{
    private const string PAYLOAD = '"><script>alert(1)</script>';

    public function testSubmittedValuesAreEscaped(): void
    {
        $form = (new FormBuilder('test'))
            ->add('name', 'text')
            ->add('message', 'textarea')
            ->add('email', 'email')
            ->getForm();

        $form->submit([
            'name' => self::PAYLOAD,
            'message' => '</textarea><img src=x onerror=alert(2)>',
            'email' => 'not-an-email',
        ]);

        $this->assertFalse($form->isValid());

        $document = self::loadHtml($form->render());

        $this->assertSame(0, $document->getElementsByTagName('script')->length);
        $this->assertSame(0, $document->getElementsByTagName('img')->length);
        $this->assertSame(self::PAYLOAD, self::getElementById($document, 'form_test-name')->getAttribute('value'));
        $this->assertSame('</textarea><img src=x onerror=alert(2)>', self::getElementById($document, 'form_test-message')->textContent);
    }

    public function testLabelsAndHelpAreEscaped(): void
    {
        $form = (new FormBuilder('test'))
            ->add('name', 'text', [
                'label' => '<b>Name</b>',
                'help' => '<i>Help</i>',
            ])
            ->getForm();

        $html = $form->render();

        $this->assertStringContainsString('&lt;b&gt;Name&lt;/b&gt;', $html);
        $this->assertStringContainsString('&lt;i&gt;Help&lt;/i&gt;', $html);
    }

    public function testTextareaRendersFalsyScalarData(): void
    {
        $form = (new FormBuilder('test'))->add('message', 'textarea')->getForm();

        $form->submit(['message' => '0']);

        $document = self::loadHtml($form->render());

        $this->assertSame('0', self::getElementById($document, 'form_test-message')->textContent);
    }

    public function testInvalidElementDataAttributeIsHtml(): void
    {
        $form = (new FormBuilder('test'))->add('name', 'text')->getForm();

        $document = self::loadHtml($form->render());

        $this->assertSame(
            $form->createInvalidElement()->render(),
            self::getElementById($document, 'form_test')->getAttribute('data-invalid_element'),
        );
    }

    public function testCollectionDataAttributesAreDecodable(): void
    {
        $form = (new FormBuilder('test'))
            ->add('names', CollectionType::class, [
                'entry_type' => 'text',
                'add_label' => 'Add <name>',
            ])
            ->getForm();

        $document = self::loadHtml($form->render());
        $collection = self::getElementById($document, 'form_test-names');

        $this->assertStringStartsWith('<div class="palmtree-form-collection-entry">', $collection->getAttribute('data-prototype'));
        $this->assertSame(['addLabel' => 'Add <name>'], json_decode($collection->getAttribute('data-palmtree-form-collection'), true, flags: \JSON_THROW_ON_ERROR));
    }

    /**
     * @requires extension curl
     */
    public function testCaptchaConfigDataAttributeIsJson(): void
    {
        $form = (new FormBuilder('test'))
            ->add('captcha', 'captcha', ['captcha' => new GoogleRecaptcha('site"key', 'secret')])
            ->getForm();

        $document = self::loadHtml($form->render());
        $placeholder = self::getElementById($document, 'form_test-captcha_placeholder');

        $config = json_decode($placeholder->getAttribute('data-palmtree-form-captcha'), true, flags: \JSON_THROW_ON_ERROR);

        $this->assertIsArray($config);
        $this->assertSame('site"key', $config['siteKey']);
    }

    private static function loadHtml(string $html): \DOMDocument
    {
        $document = new \DOMDocument();
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>', \LIBXML_NOERROR);

        return $document;
    }

    private static function getElementById(\DOMDocument $document, string $id): \DOMElement
    {
        $element = (new \DOMXPath($document))->query("//*[@id='$id']")?->item(0);

        self::assertInstanceOf(\DOMElement::class, $element, "Element with id '$id' not found");

        return $element;
    }
}
