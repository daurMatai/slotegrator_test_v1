<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ProductRequestValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate(array $data, string $context): ConstraintViolationListInterface
    {
        $constraints = $this->getConstraints($context);
        return $this->validator->validate($data, new Assert\Collection($constraints));
    }

    private function getConstraints(string $context): array
    {
        $baseConstraints = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255]),
            ],
            'price' => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'float']),
                new Assert\GreaterThan(0),
            ],
            'photo' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255]),
                new Assert\Url(),
            ],
            'description' => [
                new Assert\Optional([
                    new Assert\Length(['max' => 1000]),
                ]),
            ],
        ];

        if ($context === 'update') {
            foreach ($baseConstraints as $field => $constraints) {
                $baseConstraints[$field] = [new Assert\Optional($constraints)];
            }
        }

        return $baseConstraints;
    }
}