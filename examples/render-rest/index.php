<?php

declare(strict_types=1);

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\EmailType;
use Palmtree\Form\Type\TelType;
use Palmtree\Form\Type\TextareaType;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$builder = (new FormBuilder('grid_example'))
    ->add('first_name', TextType::class, [
    ])
    ->add('last_name', TextType::class, [
    ])
    ->add('email_address', EmailType::class, [
    ])
    ->add('telephone_number', TelType::class, [
    ])
    ->add('address', TextareaType::class, [
    ])
;

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    redirect('?success=1');
}

$view = template('view.phtml', [
    'form' => $form,
    'success' => !empty($_GET['success']),
]);

echo $view;
