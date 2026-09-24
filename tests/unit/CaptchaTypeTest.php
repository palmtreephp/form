<?php

declare(strict_types=1);

namespace Palmtree\Form\Test\unit;

use Palmtree\Form\Captcha\HoneypotCaptcha;
use Palmtree\Form\FormBuilder;
use PHPUnit\Framework\TestCase;

class CaptchaTypeTest extends TestCase
{
    public function testCaptchaIsNotMapped(): void
    {
        $form = (new FormBuilder('test'))->add('captcha', 'captcha', ['captcha' => new HoneypotCaptcha()])->getForm();

        $this->assertFalse($form->get('captcha')->isMapped());
    }

    public function testCaptchaWorksWithBoundObject(): void
    {
        $data = new \stdClass();
        $data->name = null;

        $form = (new FormBuilder('test', $data))
            ->add('name', 'text')
            ->add('captcha', 'captcha', ['captcha' => new HoneypotCaptcha()])
            ->getForm();

        $form->submit(['name' => 'Bob', 'captcha' => '']);

        $this->assertTrue($form->isValid());
        $this->assertSame('Bob', $data->name);
        $this->assertObjectNotHasProperty('captcha', $data);
    }

    public function testCaptchaWorksWithBoundArray(): void
    {
        $data = new \ArrayObject(['name' => null]);

        $form = (new FormBuilder('test', $data))
            ->add('name', 'text')
            ->add('captcha', 'captcha', ['captcha' => new HoneypotCaptcha()])
            ->getForm();

        $form->submit(['name' => 'Bob', 'captcha' => '']);

        $this->assertSame(['name' => 'Bob'], $data->getArrayCopy());
    }

    public function testFailedCaptchaDoesNotUpdateBoundData(): void
    {
        $data = new \stdClass();
        $data->name = 'Alice';

        $form = (new FormBuilder('test', $data))
            ->add('name', 'text')
            ->add('captcha', 'captcha', ['captcha' => new HoneypotCaptcha()])
            ->getForm();

        $form->submit(['name' => 'Bob', 'captcha' => 'spam']);

        $this->assertFalse($form->isValid());
        $this->assertSame('Alice', $data->name);
    }
}
