<?php declare(strict_types=1);

use Palmtree\Form\Captcha\GoogleRecaptcha;
use Palmtree\Form\Constraint\Number;
use Palmtree\Form\Form;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Http\JsonResponse;
use Palmtree\Form\Type\TextType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'ajax_example',
    'action'          => 'index.php',
    'ajax'            => true,
    'html_validation' => false,
    'enc_type'        => 'multipart/form-data',
]);

$builder
    ->add('name', TextType::class, [
        'label' => 'Please enter your name',
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
    ->add('interests', 'choice', [
        'required'      => false,
        'multiple'      => true,
        'error_message' => 'Please select your age group',
        'choices'       => [
            'Sport' => [
                'football' => 'Football',
                'rugby'    => 'Rugby',
                'golf'     => 'Golf',
                'cricket'  => 'Cricket',
            ],
        ],
    ])
    ->add('recaptcha', 'captcha', [
        'captcha' => new GoogleRecaptcha('6LfOO5YUAAAAALKjc8OvDLW6WdKSxRVvQuIjEuFY', '6LfOO5YUAAAAAL5zQe0aZh2bMJq5-3sh7xKwzevR'),
    ])
;

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && Form::isAjaxRequest()) {
    $response = JsonResponse::fromForm($form, successMessage: 'Thanks for your enquiry. We will be in touch shortly');

    $response->send();
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => (!empty($_GET['success'])),
]);

echo $view;
