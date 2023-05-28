<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

use Ser\DtoRequestBundle\Attributes\MapTo;

class ClassWithAnotherInterfaceClass
{
    /**
     * @var (SomeClassInterface&ClassWithInterface)
     */
    #[MapTo(ClassWithInterface::class)]
    public SomeClassInterface $someClassProperty;
}
