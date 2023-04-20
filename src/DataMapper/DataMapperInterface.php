<?php

declare(strict_types=1);

namespace Palmtree\Form\DataMapper;

use Palmtree\Form\Form;

interface DataMapperInterface
{
    public function mapDataToForm(Form $form): void;

    public function mapDataFromForm(array $data, Form $form): void;
}
