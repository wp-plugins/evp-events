<?php
/**
 * Transforms "+65 minutes" to "01:05" (H:i)
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class RelativeTimeToTimeStringTransformer
 */
class RelativeTimeToTimeStringTransformer implements DataTransformerInterface
{
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
        $date = new \DateTime($value);
        $now = new \DateTime;
        $diff = $date->diff($now);

        $transformed = \DateTime::createFromFormat('H:i', $diff->format('%H:%I'));
        return $transformed;
    }

    /**
     * {@inheritdoc}
     *
     * By convention, reverseTransform() should return NULL if an empty string
     * is passed.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException When the transformation fails.
     */
    public function reverseTransform($value)
    {
        $now = new \DateTime('1970-01-01 00:00:00');
        $diff = $value->getTimestamp() - $now->getTimestamp();
        $diff /= 60;
        return '+' .$diff .' minutes';
    }
}
