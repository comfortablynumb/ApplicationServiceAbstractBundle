<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\ValidationErrorsFormatter;

use Symfony\Component\Validator\ConstraintViolationList;

interface ValidationErrorsFormatterInterface
{
    public function format($object, ConstraintViolationList $errorList, $formatForFieldName = null);
}
