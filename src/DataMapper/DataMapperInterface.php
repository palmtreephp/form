<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Form;

interface DataMapperInterface
{
    /**
     * Maps data from the model to the relevant form fields.
     *
     * @param object $object
     */
    public function mapDataToForm($object, Form $form): void;

    /**
     * Maps submitted data on the form to the model.
     *
     * @param object $object
     */
    public function mapDataFromForm($object, array $data, Form $form): void;
}
