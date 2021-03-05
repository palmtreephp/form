<?php declare(strict_types=1);

use Palmtree\Form\Constraint\File as FileConstraint;
use Palmtree\Form\FormBuilder;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'fileupload_example',
    'action'          => 'index.php',
    'ajax'            => false,
    'html_validation' => false,
]);

$builder->add('file', 'file', [
    'constraints' => [
        new FileConstraint\Size([
            'max' => 1024 * 100,
        ]),
        new FileConstraint\Extension([
            'extensions' => ['jpg', 'gif', 'png'],
        ]),
        new FileConstraint\MimeType([
            'mime_types' => ['image/jpeg', 'image/gif', 'image/png'],
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
    'success' => (!empty($_GET['success'])),
]);

echo $view;
