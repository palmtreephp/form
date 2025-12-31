<?php declare(strict_types=1);

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

\Symfony\Component\HttpFoundation\Request::setTrustedHosts(['localhost']);

$builder = new FormBuilder([
    'key'             => 'simple_example',
    'method'          => 'POST',
    'html_validation' => false,
]);

$builder->enableCsrf();

$builder
    ->add('name', TextType::class, [
        'label' => 'Please enter your name',
    ])
    ->add('email_address', 'email', [
        'help' => 'We will never share your email address with anyone.'
    ]);

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    redirect('?success=1');
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => !empty($_GET['success']),
]);

echo $view;
