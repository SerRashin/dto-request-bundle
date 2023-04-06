

Map request on your DTO object with zero configuration.
This library very simple and fast.

## Install

```shell
composer require ser/dto-request-bundle
```

# Supports
Library supports all PHP8.2 features.
You can use readonly class and properties, parameters in constructer and etc. See samples.

## Usage

1. Create a DTO
```php 
class RegistrationData
{
    public string $firstName;
    public string $lastName;

    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
}
```
2. Use your DTO in a Controller e.g.:
```php 
<?php
   declare(strict_types=1);
   
   namespace App\Controller\User;
   
   use Symfony\Component\HttpFoundation\JsonResponse;
   use App\Dto\RegistrationData;
   
   class RegistrationController
   {
        public function __invoke(RegistrationData $dto): JsonResponse
        {
            return new JsonResponse($dto);
        }
   }
```

# Mappers
You can use mappers for mapping collections and Classes to properties.

Examples:
1. Collections mapping.
```php
<?php

declare(strict_types=1);

namespace App\Dto;

use Ser\DTORequestBundle\Attributes\MapToArrayOf;

class Address
{
    public readonly string $city;
    public readonly string $country;
}

class RegistrationData
{
    public string $firstName;
    public string $lastName;

    #[MapToArrayOf(Address::class)]
    public array $addresses;

    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
}
```

1. Map class to interface or mixed/object value. 
```php
<?php

declare(strict_types=1);

namespace App\Dto;

use Ser\DTORequestBundle\Attributes\MapTo;
use DateTimeInterface;

class AddressInterface {}

class Address implements AddressInterface
{
    public readonly string $city;
    public readonly string $country;
}

class RegistrationData
{
    public string $firstName;
    public string $lastName;

    #[MapTo(Address::class)]
    public AddressInterface $addresses;
    
    #[MapTo(DateTime::class)]
    public DateTimeInterface $birthday;

    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
}
```

# Validation
For validation you can use SymfonyValidator.

1. Create DTO with constraint:
```php
<?php

declare(strict_types=1);

namespace App\Dto;

use App\Constraint\UniqueEmail;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationData
{
    #[Assert\Length(min: 3, max: 75)]
    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\Length(min: 3, max: 75)]
    #[Assert\NotBlank]
    public string $lastName;

    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        #[UniqueEmail] // this is custom constraint
        public readonly string $email,
        #[Assert\NotBlank]
        public readonly string $password
    ) {
    }
}
```
2. Execute validation in you service or controller.
2.1. Validation in service (Best variant)
```php
<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Service\ErrorInterface;
use App\Service\ResponseFactoryInterface;
use App\Dto\RegistrationData;
use App\Service\RegistrationService;
use Symfony\Component\HttpFoundation\Response;

final class RegistrationController
{
    public function __construct(
        private readonly RegistrationService $registrationService,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function __invoke(RegistrationData $registrationData): Response
    {
        $result = $this->registrationService->register($registrationData);

        if ($result instanceof ErrorInterface) {
            return $this->responseFactory->createResponse(ErrorView::create($result), 400);
        }
        
        return $this->responseFactory->createResponse(UserView::create($result), 201);
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use App\Service\ErrorInterface;
use App\Dto\RegistrationData;
use App\Model\UserInterface;
use App\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function register(RegistrationData $registrationData): UserInterface|ErrorInterface
    {
        $violations = $this->validator->validate($registrationData);
        
        if ($violations->count() !== 0) {
            return new Error($validationError);
        }
        
        $user = new User($registrationData->email, $registrationData->password);

        // save user to DB

        return $user;
    }
```

## Validation for nested DTO.
> For validation nested DTO use constraint `cascade` for class that contains dto. Sample:
```php
<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class Address
{
    #[Assert\Length(min: 3, max: 75)]
    #[Assert\NotBlank]
    public readonly string $city;
    
    #[Assert\Length(min: 3, max: 75)]
    #[Assert\NotBlank]
    public readonly string $country;
}

#[Assert\Cascade]
class UserData
{
    #[Assert\Length(min: 3, max: 75)]
    #[Assert\NotBlank]
    public string $firstName;

    #[Assert\Length(min: 3, max: 75)]
    #[Assert\NotBlank]
    public string $lastName;
    
    #[Assert\NotNull]
    public ?Address $address;
}
```
