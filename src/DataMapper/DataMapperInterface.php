<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Form;

interface DataMapperInterface
{
    /**
     * Maps data from the model to the relevant form fields.
     *
     * @param object|array $data
     */
    public function mapDataToForm($data, Form $form): void;

    /**
     * Maps submitted data on the form to the model.
     *
     * @param object|array $data
     */
    public function mapDataFromForm($data, array $formData, Form $form): void;
}
