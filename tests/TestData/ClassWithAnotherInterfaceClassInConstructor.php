<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle\TestData;

use Ser\DtoRequestBundle\Attributes\MapTo;

class ClassWithAnotherInterfaceClassInConstructor
{
    public function __construct(
        /**
         * @var (SomeClassInterface&ClassWithInterface)
         */
        #[MapTo(ClassWithInterface::class)]
        public SomeClassInterface $someClassProperty,
    ) {
    }
}
