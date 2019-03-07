<?php

use Palmtree\Form\Constraint\File\Extension;
use Palmtree\Form\Constraint\File\MimeType;
use Palmtree\Form\Constraint\File\Size;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'fileupload_example',
    'action'          => 'index.php',
    'ajax'            => false,
    'html_validation' => false,
    'enc_type'        => 'multipart/form-data',
]);

$builder->add('file', 'file', [
    'constraints' => [
        new Size([
            'max' => 1024 * 100,
        ]),
        new Extension([
            'extensions' => ['jpg', 'gif'],
        ]),
        new MimeType([
            'mime_types' => ['image/jpeg', 'image/gif'],
        ]),
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
    'success' => (! empty($_GET['success'])),
]);

echo $view;
