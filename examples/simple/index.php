<?php declare(strict_types=1);

use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

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
    ->add('preferences', 'choice', [
        'expanded'      => true,
        'multiple'      => true,
        'error_message' => 'Please select preferences',
        'choices'       => [
            '1' => 'Yes',
            '2' => 'No',
            '3' => 'Maybe',
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
    ->add('password', 'repeated', [
        'repeatable_type' => 'password',
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
    'success' => !empty($_GET['success']),
]);

echo $view;
