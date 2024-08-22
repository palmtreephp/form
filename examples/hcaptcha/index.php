<?php declare(strict_types=1);

use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\Captcha\HCaptcha;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'hcaptcha',
    'html_validation' => false,
]);

$builder
    ->add('name', TextType::class, [
        'label' => 'Please enter your name',
    ])
    ->add('hcaptcha', 'captcha', [
        'captcha' => new HCaptcha('6b1ef180-ed78-4948-ae66-258e0bfe4ecc', 'ES_c1935238614149509f69db166c2f970d'),
    ]);

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    redirect('?success=1');
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => (!empty($_GET['success'])),
]);

echo $view;
