<?php declare(strict_types=1);

use Palmtree\Form\FormBuilder;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$person               = new \Palmtree\Form\Test\Fixtures\Person();
$person->name         = 'Person';
$person->emailAddress = 'person@example.org';
$person->setAge(42);
$person->setSignup(true);
$person->interests[] = 'football';
$person->pets[]      = 'cat';

$builder = new FormBuilder([
    'key'             => 'data_binding_example',
    'method'          => 'POST',
    'html_validation' => false,
], $person);

$builder
    ->add('name', 'text')
    ->add('emailAddress', 'text')
    ->add('age', 'number')
    ->add('signup', 'checkbox', [
        'required' => false,
    ])
    ->add('favouriteConsole', 'choice', [
        'placeholder' => 'Select a console',
        'choices'     => [
            'PlayStation' => 'PlayStation',
            'Xbox'        => 'Xbox',
            'Switch'      => 'Switch',
        ],
    ])
    ->add('interests', 'choice', [
        'multiple' => true,
        'expanded' => true,
        'choices'  => [
            'football' => 'Football',
            'gaming'   => 'Gaming',
            'music'    => 'Music',
        ],
    ])
    ->add('pets', 'collection', [
        'entry_type' => 'text',
    ])
;

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    echo '<pre>';
    var_export($person);
    echo '</pre>';
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => !empty($_GET['success']),
]);

echo $view;
