<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

use DateTime;
use DateTimeInterface;
use Ser\DtoRequestBundle\Attributes\MapTo;

class ClassWithDateTimeInterfaceMapper
{
    #[MapTo(DateTime::class)]
    public DateTimeInterface $dateTime;
}
