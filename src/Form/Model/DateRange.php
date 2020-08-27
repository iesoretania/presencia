<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class DateRange
{
    /**
     * @Assert\LessThanOrEqual("today")
     * @var \DateTime
     */
    public $firstDate;

    /**
     * @Assert\GreaterThanOrEqual(propertyPath="firstDate")
     * @Assert\LessThanOrEqual("today")
     * @var \DateTime
     */
    public $lastDate;
}
