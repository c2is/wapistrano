<?php

namespace Wapistrano\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RoleWithHost extends Constraint
{
    public $message = 'No host defined';
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}