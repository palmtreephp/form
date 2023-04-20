<?php declare(strict_types=1);

use Palmtree\Form\FormBuilder;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$person               = new \Palmtree\Form\Examples\Fixtures\Person();
$person->name         = 'Person';
$person->emailAddress = 'person@example.org';
$person->setAge(42);

$builder = new FormBuilder([
    'key'             => 'data_binding_example',
    'method'          => 'POST',
    'html_validation' => false,
], $person);

$builder
    ->add('name', 'text')
    ->add('emailAddress', 'text')
    ->add('age', 'number')
;

$builder->add('send_message', 'submit');

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    var_export($person);
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => !empty($_GET['success']),
]);

echo $view;
