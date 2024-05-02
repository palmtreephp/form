<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Form;

interface DataMapperInterface
{
    /**
     * Maps data from the model to the relevant form fields.
     *
     * @param object|array<string, mixed> $data
     */
    public function mapDataToForm(object|array $data, Form $form): void;

    /**
     * Maps submitted data on the form to the model.
     *
     * @param object|array<string, mixed> $data
     * @param array<string, mixed>        $formData
     */
    public function mapDataFromForm(object|array $data, array $formData, Form $form): void;
}
