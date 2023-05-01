<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Form;

interface DataMapperInterface
{
    /**
     * Maps data from the model to the relevant form fields.
     */
    public function mapDataToForm(Form $form): void;

    /**
     * Maps submitted data on the form to the model.
     */
    public function mapDataFromForm(array $data, Form $form): void;
}
