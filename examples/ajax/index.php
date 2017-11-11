<?php

use Palmtree\Form\Constraint\File\Extension;
use Palmtree\Form\Constraint\File\Size;
use Palmtree\Form\Constraint\Number;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'ajax_example',
    'action'          => 'index.php',
    'ajax'            => false,
    'html_validation' => false,
    'enc_type'        => 'multipart/form-data',
]);

$builder
    ->add('name', TextType::class, [
        'label' => 'Please enter your name',
    ])
    ->add('file', 'file', [
        'constraints' => [
            new Size([
                'max' => 1024 * 100,
            ]),
            new Extension([
                'extensions' => 'jpg',
            ]),
        ],
    ])
    ->add('email_address', 'email')
    ->add('phone_number', 'tel', ['required' => false])
    ->add('number', 'number', [
        'constraints' => [
            new Number([
                'min' => 5,
                'max' => 20,
            ]),
        ],
    ])
    ->add('message', 'textarea', ['required' => false])
    ->add('agree', 'checkbox', [
        'label'         => 'I agree to the terms and conditions',
        'error_message' => 'You must agree to our terms and conditions to continue',
    ])
    ->add('status', 'choice', [
        'expanded'      => true,
        'multiple'      => false,
        'error_message' => 'Please select one',
        'choices'       => [
            '1' => 'Yes',
            '2' => 'No',
        ],
    ])
    ->add('age', 'choice', [
        'error_message' => 'Please select your age group',
        'choices'       => [
            '18_to_24' => '18 to 24',
            '25_to_30' => '25 to 30',
            '30_to_25' => '30 to 35',
        ],
    ])
    ->add('interests', 'choice', [
        'required'      => false,
        'multiple'      => true,
        'error_message' => 'Please select your age group',
        'choices'       => [
            'Football',
            'Rugby',
            'Golf',
            'Cricket',
        ],
    ]);

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && Form::isAjaxRequest()) {
    if ($form->isValid()) {
        send_json([
            'message' => 'Thanks!',
        ]);
    } else {
        send_json_error([
            'errors' => $form->getErrors(),
        ]);
    }
}

$view = template('view.php', [
    'form'    => $form,
    'success' => (!empty($_GET['success'])),
]);

echo $view;
