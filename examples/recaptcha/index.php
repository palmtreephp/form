<?php declare(strict_types=1);

use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Http\JsonResponse;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'recaptcha',
    'html_validation' => false,
    'ajax'            => true,
]);

$builder
    ->add('name', TextType::class, [
        'label' => 'Please enter your name',
    ])
    ->add('recaptcha', 'captcha', [
        'captcha' => new GoogleRecaptcha('6LfOO5YUAAAAALKjc8OvDLW6WdKSxRVvQuIjEuFY', '6LfOO5YUAAAAAL5zQe0aZh2bMJq5-3sh7xKwzevR'),
    ])
;

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && Form::isAjaxRequest()) {
    $response = JsonResponse::fromForm($form);

    $response->send();
}

if ($form->isSubmitted() && $form->isValid()) {
    redirect('?success=1');
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => (!empty($_GET['success'])),
]);

echo $view;
