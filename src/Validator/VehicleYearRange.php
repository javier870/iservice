<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class VehicleYearRange
 * @package App\Validator
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class VehicleYearRange extends Constraint
{
    public string $message = 'The vehicle year should be between {{ min }} and {{ max }}.';
}

