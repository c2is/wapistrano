<?php

namespace Wapistrano\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class RoleWithHostValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        var_dump($value);
        die();
        if ($value !== $this->context->getRoot()->get($constraint->hostName)->getData()) {


            return false;
        }

        return true;
    }


}