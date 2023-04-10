<?php

// src/Serializer/Normalizer/FruitNormalizer.php

namespace App\Serializer\Normalizer;

use App\Entity\Fruit;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class FruitNormalizer implements ContextAwareNormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        // Convert the Fruit object to an array
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'genus' => $object->getGenus(),
            'family' => $object->getFamily(),
            'order' => $object->getFruitOrder(),
            'nutritions' => json_decode($object->getNutritions()),
            'reveal' => false,
            'isFavorite'=>$object->isFavorite
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Only normalize Fruit objects
        return $data instanceof Fruit;
    }
}
