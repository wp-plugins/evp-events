<?php
/**
 * Transforms "Authorization: Bearer 34adsadsa4d" to "34adsadsa4d"
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class BearerTokenTransformer
 */
class BearerTokenTransformer implements DataTransformerInterface
{
    const BEARER_REGEX = '/(?:Bearer\s*)(\w*)/';
    const BEARER_PREFIX = 'Authorization: Bearer ';

    /**
     * {@inheritdoc}
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function transform($value)
    {
        if (preg_match(self::BEARER_REGEX, $value, $matches)) {
            return $matches[1];
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        return self::BEARER_PREFIX .trim($value);
    }
}
