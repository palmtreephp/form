<?php

namespace Palmtree\Form\Constraint\File;

use Palmtree\Form\Constraint\AbstractContstraint;
use Palmtree\Form\Constraint\ConstraintInterface;

class Size extends AbstractContstraint implements ConstraintInterface
{
    protected $min;
    protected $max;

    /**
     * @inheritDoc
     */
    public function validate($uploadedFile)
    {
        $size = $uploadedFile['size'];
        $min  = $this->getMin();
        $max  = $this->getMax();

        if (!is_null($min) && $size < $min) {
            $this->setErrorMessage(sprintf('File size must be greater than %d bytes', $max));

            return false;
        }

        if (!is_null($max) && $size > $max) {
            $this->setErrorMessage(sprintf('File size must be less than %d bytes', $max));

            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param mixed $max
     *
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @param mixed $min
     *
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }
}
