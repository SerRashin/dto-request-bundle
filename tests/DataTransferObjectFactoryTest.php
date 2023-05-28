<?php

declare(strict_types=1);

namespace Ser\DtoRequestBundle;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Ser\DtoRequestBundle\TestData\ClassWithAnotherClassInConstructor;
use Ser\DtoRequestBundle\TestData\ClassWithAnotherClassInConstructorAsSimpleArgument;
use Ser\DtoRequestBundle\TestData\ClassWithAnotherClassThatContainsArrayMapper;
use Ser\DtoRequestBundle\TestData\ClassWithAnotherEmptyClassField;
use Ser\DtoRequestBundle\TestData\ClassWithAnotherInterfaceClass;
use Ser\DtoRequestBundle\TestData\ClassWithAnotherInterfaceClassInConstructor;
use Ser\DtoRequestBundle\TestData\ClassWithArrayField;
use Ser\DtoRequestBundle\TestData\ClassWithArrayFieldInConstructor;
use Ser\DtoRequestBundle\TestData\ClassWithClassWithParameters;
use Ser\DtoRequestBundle\TestData\ClassWithDateTimeInterfaceMapper;
use Ser\DtoRequestBundle\TestData\ClassWithDateTimeProperty;
use Ser\DtoRequestBundle\TestData\ClassWithInterface;
use Ser\DtoRequestBundle\TestData\ClassWithNullableScalarTypes;
use Ser\DtoRequestBundle\TestData\ClassWithReadonlyNullableScalarTypesInConstructor;
use Ser\DtoRequestBundle\TestData\ClassWithReadonlyScalarTypesInConstructor;
use Ser\DtoRequestBundle\TestData\ClassWithScalarTypesInConstructor;
use Ser\DtoRequestBundle\TestData\ClassWithStringField;
use Ser\DtoRequestBundle\TestData\EmptyClass;
use Ser\DtoRequestBundle\TestData\SomeClassInterface;

class DataTransferObjectFactoryTest extends TestCase
{
    private DataTransferObjectFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new DataTransferObjectFactory();
    }

    public function testCreateIfClassNotHasProperties(): void
    {
        $result = $this->factory->create([], EmptyClass::class);

        $this->assertInstanceOf(EmptyClass::class, $result);
        $this->assertEquals([], json_decode(json_encode($result), true));
    }

    public function testCreateIfClassHasStringFieldAndDataEmpty(): void
    {
        $result = $this->factory->create([], ClassWithStringField::class);

        $this->assertInstanceOf(ClassWithStringField::class, $result);
        $this->assertEquals('', $result->someField);
    }

    public function testCreateIfClassHasStringFieldAndDataNotEmpty(): void
    {
        $requestData = [
            'someField' => 'some data'
        ];

        $result = $this->factory->create($requestData, ClassWithStringField::class);

        $this->assertInstanceOf(ClassWithStringField::class, $result);
        $this->assertEquals($requestData['someField'], $result->someField);
    }

    public function testCreateIfClassHasStringFieldAndDataSetToNull(): void
    {
        $requestData = [
            'someField' => null
        ];

        $result = $this->factory->create($requestData, ClassWithStringField::class);

        $this->assertInstanceOf(ClassWithStringField::class, $result);
        $this->assertEquals($requestData['someField'], $result->someField);
    }

    public function testCreateIfClassHasArrayFieldAndDataEmpty(): void
    {
        $result = $this->factory->create([], ClassWithArrayField::class);

        $this->assertInstanceOf(ClassWithArrayField::class, $result);
        $this->assertEquals([], $result->arrayField);
    }

    public function testCreateIfClassHasArrayFieldAndDataNotEmpty(): void
    {
        $requestData = [
            'arrayField' => ['some data']
        ];

        $result = $this->factory->create($requestData, ClassWithArrayField::class);

        $this->assertInstanceOf(ClassWithArrayField::class, $result);
        $this->assertEquals($requestData['arrayField'], $result->arrayField);
    }

    public function testCreateIfClassHasClassFieldAndDataEmpty(): void
    {
        $result = $this->factory->create([], ClassWithAnotherEmptyClassField::class);

        $this->assertInstanceOf(ClassWithAnotherEmptyClassField::class, $result);
        $this->assertInstanceOf(EmptyClass::class, $result->someClass);
    }

    public function testCreateIfClassHasClassFieldAndDataNotEmpty(): void
    {
        $requestData = [
            'someClass' => 'someString',
        ];

        $result = $this->factory->create($requestData, ClassWithAnotherEmptyClassField::class);

        $this->assertInstanceOf(ClassWithAnotherEmptyClassField::class, $result);
        $this->assertInstanceOf(EmptyClass::class, $result->someClass);
    }

    public function testCreateIfClassHasClassFieldAndDataSetToNull(): void
    {
        $requestData = [
            'someClass' => null,
        ];

        $result = $this->factory->create($requestData, ClassWithAnotherEmptyClassField::class);

        $this->assertInstanceOf(ClassWithAnotherEmptyClassField::class, $result);
        $this->assertInstanceOf(EmptyClass::class, $result->someClass);
    }

    public function testInitializeNestedClassWithProperties(): void
    {
        $requestData = [
            'classField' => [
                'someField' => 'some string'
            ],
        ];

        $result = $this->factory->create($requestData, ClassWithClassWithParameters::class);

        $this->assertInstanceOf(ClassWithClassWithParameters::class, $result);
        $this->assertInstanceOf(ClassWithStringField::class, $result->classField);
        $this->assertEquals($requestData['classField']['someField'], $result->classField->someField);
    }

    public function testInitializeNestedClassWithPropertiesAndNullableProperty(): void
    {
        $requestData = [
            'classField' => [
                'someField' => null
            ],
        ];

        $result = $this->factory->create($requestData, ClassWithClassWithParameters::class);

        $this->assertInstanceOf(ClassWithClassWithParameters::class, $result);
        $this->assertInstanceOf(ClassWithStringField::class, $result->classField);
        $this->assertEquals($requestData['classField']['someField'], $result->classField->someField);
    }

    public function testInitializeClassWithNullableScalarTypesWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithNullableScalarTypes::class);

        $this->assertInstanceOf(ClassWithNullableScalarTypes::class, $result);
        $this->assertEquals(null, $result->boolField);
        $this->assertEquals(null, $result->intField);
        $this->assertEquals(null, $result->stringField);
        $this->assertEquals(null, $result->floatField);
    }

    public function testInitializeClassWithNullableScalarTypesWithData(): void
    {
        $requestData = [
            'boolField' => true,
            'intField' => 921,
            'stringField' => 'some string',
            'floatField' => 123.123,
        ];

        $result = $this->factory->create($requestData, ClassWithNullableScalarTypes::class);

        $this->assertInstanceOf(ClassWithNullableScalarTypes::class, $result);
        $this->assertSame($requestData['boolField'], $result->boolField);
        $this->assertSame($requestData['intField'], $result->intField);
        $this->assertSame($requestData['stringField'], $result->stringField);
        $this->assertSame($requestData['floatField'], $result->floatField);
    }

    public function testInitializeClassWithScalarTypesInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithScalarTypesInConstructor::class);

        $this->assertInstanceOf(ClassWithScalarTypesInConstructor::class, $result);
        $this->assertSame(false, $result->boolField);
        $this->assertSame(0, $result->intField);
        $this->assertSame('', $result->stringField);
        $this->assertSame(0.0, $result->floatField);
    }

    public function testInitializeClassWithScalarTypesInConstructorWithData(): void
    {
        $requestData = [
            'boolField' => true,
            'intField' => 921,
            'stringField' => 'some string',
            'floatField' => 123.123,
        ];

        $result = $this->factory->create($requestData, ClassWithScalarTypesInConstructor::class);

        $this->assertInstanceOf(ClassWithScalarTypesInConstructor::class, $result);
        $this->assertSame($requestData['boolField'], $result->boolField);
        $this->assertSame($requestData['intField'], $result->intField);
        $this->assertSame($requestData['stringField'], $result->stringField);
        $this->assertSame($requestData['floatField'], $result->floatField);
    }

    public function testInitializeClassWithReadonlyScalarTypesInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithReadonlyScalarTypesInConstructor::class);

        $this->assertInstanceOf(ClassWithReadonlyScalarTypesInConstructor::class, $result);
        $this->assertSame(false, $result->boolField);
        $this->assertSame(0, $result->intField);
        $this->assertSame('', $result->stringField);
        $this->assertSame(0.0, $result->floatField);
    }

    public function testInitializeClassWithReadonlyScalarTypesInConstructorWithData(): void
    {
        $requestData = [
            'boolField' => true,
            'intField' => 921,
            'stringField' => 'some string',
            'floatField' => 123.123,
        ];

        $result = $this->factory->create($requestData, ClassWithReadonlyScalarTypesInConstructor::class);

        $this->assertInstanceOf(ClassWithReadonlyScalarTypesInConstructor::class, $result);
        $this->assertSame($requestData['boolField'], $result->boolField);
        $this->assertSame($requestData['intField'], $result->intField);
        $this->assertSame($requestData['stringField'], $result->stringField);
        $this->assertSame($requestData['floatField'], $result->floatField);
    }

    public function testInitializeClassWithReadonlyNullableScalarTypesInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithReadonlyNullableScalarTypesInConstructor::class);

        $this->assertInstanceOf(ClassWithReadonlyNullableScalarTypesInConstructor::class, $result);
        $this->assertSame(null, $result->boolField);
        $this->assertSame(null, $result->intField);
        $this->assertSame(null, $result->stringField);
        $this->assertSame(null, $result->floatField);
    }

    public function testInitializeClassWithReadonlyNullableScalarTypesInConstructorWithData(): void
    {
        $requestData = [
            'boolField' => true,
            'intField' => 921,
            'stringField' => 'some string',
            'floatField' => 123.123,
        ];

        $result = $this->factory->create($requestData, ClassWithReadonlyNullableScalarTypesInConstructor::class);

        $this->assertInstanceOf(ClassWithReadonlyNullableScalarTypesInConstructor::class, $result);
        $this->assertSame($requestData['boolField'], $result->boolField);
        $this->assertSame($requestData['intField'], $result->intField);
        $this->assertSame($requestData['stringField'], $result->stringField);
        $this->assertSame($requestData['floatField'], $result->floatField);
    }

    public function testInitializeClassWithAnotherClassInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithAnotherClassInConstructor::class);

        $this->assertInstanceOf(ClassWithAnotherClassInConstructor::class, $result);
        $this->assertInstanceOf(ClassWithStringField::class, $result->stringFieldClass);
        $this->assertSame('', $result->stringFieldClass->someField);
    }

    public function testInitializeClassWithAnotherClassInConstructorWithData(): void
    {
        $requestData = [
            'stringFieldClass' => [
                'someField' => 'some field value'
            ]
        ];

        $result = $this->factory->create($requestData, ClassWithAnotherClassInConstructor::class);

        $this->assertInstanceOf(ClassWithAnotherClassInConstructor::class, $result);
        $this->assertInstanceOf(ClassWithStringField::class, $result->stringFieldClass);
        $this->assertSame($requestData['stringFieldClass']['someField'], $result->stringFieldClass->someField);
    }

    public function testInitializeClassWithArrayFieldInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithArrayFieldInConstructor::class);

        $this->assertInstanceOf(ClassWithArrayFieldInConstructor::class, $result);
        $this->assertSame([], $result->arrayField);
    }

    public function testInitializeClassWithArrayFieldInConstructorWithData(): void
    {
        $requestData = [
            'arrayField' => [
                true,
                'two',
                5.35
            ]
        ];

        $result = $this->factory->create($requestData, ClassWithArrayFieldInConstructor::class);

        $this->assertInstanceOf(ClassWithArrayFieldInConstructor::class, $result);
        $this->assertSame($requestData['arrayField'], $result->arrayField);
    }

    public function testInitializeClassWithDateTimePropertyWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithDateTimeProperty::class);

        $this->assertInstanceOf(ClassWithDateTimeProperty::class, $result);
        $this->assertSame((new DateTime())->format('Y-m-d H:i'), $result->dateTime->format('Y-m-d H:i'));
    }

    public function testInitializeClassWithDateTimePropertyWithData(): void
    {
        $requestData = [
            'dateTime' => '2001-01-01 12:01',
        ];
        $result = $this->factory->create($requestData, ClassWithDateTimeProperty::class);

        $this->assertInstanceOf(ClassWithDateTimeProperty::class, $result);
        $this->assertSame($requestData['dateTime'], $result->dateTime->format('Y-m-d H:i'));
    }

    public function testInitializeClassWithAnotherClassInConstructorAsSimpleArgumentWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithAnotherClassInConstructorAsSimpleArgument::class);

        $this->assertInstanceOf(ClassWithAnotherClassInConstructorAsSimpleArgument::class, $result);
        $this->assertInstanceOf(ClassWithStringField::class, $result->getClass());
        $this->assertSame('', $result->getClass()->someField);
    }

    public function testInitializeClassWithAnotherClassInConstructorAsSimpleArgumentWithData(): void
    {
        $requestData = [
            'classParameter' => [
                'someField' => 'some field value'
            ]
        ];

        $result = $this->factory->create($requestData, ClassWithAnotherClassInConstructorAsSimpleArgument::class);

        $this->assertInstanceOf(ClassWithAnotherClassInConstructorAsSimpleArgument::class, $result);
        $this->assertInstanceOf(ClassWithStringField::class, $result->getClass());
        $this->assertSame($requestData['classParameter']['someField'], $result->getClass()->someField);
    }

    public function testInitializeClassWithAnotherClassThatContainsArrayMapperWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithAnotherClassThatContainsArrayMapper::class);

        $this->assertInstanceOf(ClassWithAnotherClassThatContainsArrayMapper::class, $result);
        $this->assertEquals([], $result->array);
    }

    public function testInitializeClassWithAnotherClassThatContainsArrayMapperWithData(): void
    {
        $requestData = [
            'array' => [
                [
                    'someField' => 'some field value'
                ],
                [
                    'someField' => 'some other value'
                ]
            ]
        ];

        $result = $this->factory->create($requestData, ClassWithAnotherClassThatContainsArrayMapper::class);

        $this->assertInstanceOf(ClassWithAnotherClassThatContainsArrayMapper::class, $result);
        $this->assertEquals($requestData['array'], json_decode(json_encode($result->array), true));
    }

    public function testInitializeClassWithAnotherClassThatContainsArrayMapperInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithAnotherClassThatContainsArrayMapper::class);

        $this->assertInstanceOf(ClassWithAnotherClassThatContainsArrayMapper::class, $result);
        $this->assertEquals([], $result->array);
    }

    public function testInitializeClassWithAnotherClassThatContainsArrayMapperInConstructorWithData(): void
    {
        $requestData = [
            'array' => [
                [
                    'someField' => 'some field value'
                ],
                [
                    'someField' => 'some other value'
                ]
            ]
        ];

        $result = $this->factory->create($requestData, ClassWithAnotherClassThatContainsArrayMapper::class);

        $this->assertInstanceOf(ClassWithAnotherClassThatContainsArrayMapper::class, $result);
        $this->assertEquals($requestData['array'], json_decode(json_encode($result->array), true));
    }

    public function testInitializeClassWithAnotherInterfaceClassWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithAnotherInterfaceClass::class);

        $this->assertInstanceOf(ClassWithAnotherInterfaceClass::class, $result);
        $this->assertInstanceOf(ClassWithInterface::class, $result->someClassProperty);
        $this->assertInstanceOf(SomeClassInterface::class, $result->someClassProperty);
        $this->assertEquals('', $result->someClassProperty->someField);
    }

    public function testInitializeClassWithAnotherInterfaceClassWithData(): void
    {
        $requestData = [
            'someClassProperty' => [
                'someField' => 'some value'
            ]
        ];
        $result = $this->factory->create($requestData, ClassWithAnotherInterfaceClass::class);

        $this->assertInstanceOf(ClassWithAnotherInterfaceClass::class, $result);
        $this->assertInstanceOf(ClassWithInterface::class, $result->someClassProperty);
        $this->assertInstanceOf(SomeClassInterface::class, $result->someClassProperty);
        $this->assertEquals($requestData['someClassProperty']['someField'], $result->someClassProperty->someField);
    }

    public function testInitializeClassWithAnotherInterfaceClassInConstructorWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithAnotherInterfaceClassInConstructor::class);

        $this->assertInstanceOf(ClassWithAnotherInterfaceClassInConstructor::class, $result);
        $this->assertInstanceOf(ClassWithInterface::class, $result->someClassProperty);
        $this->assertInstanceOf(SomeClassInterface::class, $result->someClassProperty);
        $this->assertEquals('', $result->someClassProperty->someField);
    }

    public function testInitializeClassWithAnotherInterfaceClassInConstructorWithData(): void
    {
        $requestData = [
            'someClassProperty' => [
                'someField' => 'some value'
            ]
        ];
        $result = $this->factory->create($requestData, ClassWithAnotherInterfaceClassInConstructor::class);

        $this->assertInstanceOf(ClassWithAnotherInterfaceClassInConstructor::class, $result);
        $this->assertInstanceOf(ClassWithInterface::class, $result->someClassProperty);
        $this->assertInstanceOf(SomeClassInterface::class, $result->someClassProperty);
        $this->assertEquals($requestData['someClassProperty']['someField'], $result->someClassProperty->someField);
    }


    public function testInitializeClassWithDateTimeInterfaceMapperWithoutData(): void
    {
        $result = $this->factory->create([], ClassWithDateTimeInterfaceMapper::class);

        $this->assertInstanceOf(ClassWithDateTimeInterfaceMapper::class, $result);
        $this->assertInstanceOf(DateTimeInterface::class, $result->dateTime);
        $this->assertSame((new DateTime())->format('Y-m-d H:i'), $result->dateTime->format('Y-m-d H:i'));
    }

    public function testInitializeClassWithDateTimeInterfaceMapperWithData(): void
    {
        $requestData = [
            'dateTime' => '2001-01-01 12:01',
        ];

        $result = $this->factory->create($requestData, ClassWithDateTimeInterfaceMapper::class);

        $this->assertInstanceOf(ClassWithDateTimeInterfaceMapper::class, $result);
        $this->assertInstanceOf(DateTimeInterface::class, $result->dateTime);
        $this->assertSame($requestData['dateTime'], $result->dateTime->format('Y-m-d H:i'));
    }
}
