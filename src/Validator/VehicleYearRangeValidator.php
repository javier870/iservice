<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VehicleYearRangeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $min = 1900;
        $max = date('Y', strtotime('+1 year'));
        if (!$constraint instanceof VehicleYearRange) {
            throw new UnexpectedTypeException($constraint, VehicleYearRange::class);
        }

        if ($value < $min || $value > $max) {
            // the year must be between min and max
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ min }}', $min)
                ->setParameter('{{ max }}', $max)
                ->addViolation();
        }
    }
}