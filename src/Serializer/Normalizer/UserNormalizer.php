<?php

// src/Serializer/Normalizer/FruitNormalizer.php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        // Convert the Fruit object to an array
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'email' => $object->getEmail()
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Only normalize Fruit objects
        return $data instanceof User;
    }
}
