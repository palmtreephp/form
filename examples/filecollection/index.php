<?php declare(strict_types=1);

use Palmtree\Form\Constraint as Constraint;
use Palmtree\Form\FormBuilder;
use Palmtree\Form\Type\CollectionType;
use Palmtree\Form\Type\FileType;
use Palmtree\Form\Type\SubmitType;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../.bootstrap.php';

$builder = new FormBuilder([
    'key'             => 'file_collection_example',
    'method'          => 'POST',
    'html_validation' => false,
]);

$builder
    ->add('files', CollectionType::class, [
        'label'         => 'Files must be a maximum of 5MB each. You may upload images, PDFs, Word documents, Spreadsheets and Powerpoint presentations',
        'required'      => false,
        'min_entries'   => 1,
        'max_entries'   => 5,
        'entry_type'    => FileType::class,
        'entry_options' => [
            'required'    => false,
            'constraints' => [
                new Constraint\File\Size([
                    'max' => 1024 * 1024 * 5,
                ]),
                new Constraint\File\Extension([
                    'extensions' => [
                        'jpg',
                        'jpeg',
                        'png',
                        'pdf',
                        'doc',
                        'docx',
                        'xls',
                        'xlsx',
                        'ppt',
                        'pptx',
                    ],
                ]),
            ],
        ],
    ]);

$builder->add('submit', SubmitType::class);

$form = $builder->getForm();

$form->handleRequest();

if ($form->isSubmitted() && $form->isValid()) {
    /** @var \Palmtree\Form\UploadedFile[] $data */
    $data = $form->get('files')->getData();
    redirect('?success=1');
}

$view = template('view.phtml', [
    'form'    => $form,
    'success' => !empty($_GET['success']),
]);

echo $view;
