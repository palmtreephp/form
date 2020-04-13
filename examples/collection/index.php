<?php

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\CollectionType;
use Palmtree\Form\Type\PersonType;
use Palmtree\Form\Type\SubmitType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/PersonType.php';

$builder = new FormBuilder([
    'key'             => 'simple_example',
    'method'          => 'POST',
    'html_validation' => false,
]);

$builder
    ->add('people', CollectionType::class, [
        'entry_type' => PersonType::class,
    ]);

$builder->add('send_message', SubmitType::class);

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    redirect('?success=1');
}

$view = template('view.php', [
    'form'    => $form,
    'success' => !empty($_GET['success']),
]);

echo $view;
