<?php

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\PasswordType;
use Palmtree\Form\Type\RepeatedType;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'simple_example',
    'method'          => 'POST',
    'html_validation' => false,
]);

$builder
    ->add('name', TextType::class, [
        'label' => 'Please enter your name',
    ])
    ->add('email_address', 'email')
    ->add('phone_number', 'tel', ['required' => false])
    ->add('message', 'textarea', ['required' => false])
    ->add('password', RepeatedType::class, [
        'repeatable_type' => PasswordType::class,
        'constraints'     => [
            new Palmtree\Form\Constraint\Length(['min' => 8]),
        ],
    ]);

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    redirect('?success=1');
}

$view = template('view.php', [
    'form'    => $form,
    'success' => (!empty($_GET['success'])),
]);

echo $view;
