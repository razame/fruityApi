<?php

namespace App\Serializer\Normalizer;

use Doctrine\ORM\Query;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class QueryResultNormalizer implements ContextAwareNormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        // Get the result array from the query
        $result = $object->getResult();

        // Add any additional data to the result array
        $result['total'] = count($result);

        // Return the result array
        return $result;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Only normalize Query objects
        return $data instanceof Query;
    }
}
