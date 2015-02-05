<?php
/**
 * Cascades DiscountType saving process
 *
 * @author Valentinas Bartusevičius <v.bartusevicius@evp.lt>
 */

namespace Evp\Bundle\TicketAdminBundle\Service\Menu\Action\EditCascador;

use Evp\Bundle\TicketAdminBundle\Service\Menu\Action\ActionAbstract;
use Evp\Bundle\TicketBundle\Entity\Discount;
use Evp\Bundle\TicketBundle\Entity\DiscountType;
use Goodby\CSV\Import\Protocol\InterpreterInterface;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\LexerConfig;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class DiscountTypeCascador
 */
class DiscountTypeCascador extends ActionAbstract implements CascadorInterface, InterpreterInterface
{
    /**
     * @var array
     */
    static protected $keys = array(
        'token',
        'value',
    );

    /**
     * @var DiscountType
     */
    private $discountType;

    /**
     * Cascades the specific relations
     *
     * @param DiscountType $obj
     */
    public function cascade($obj)
    {
        $this->discountType = $obj;

        if ($obj->getUploadedFile() !== null) {
            $this->processUploadedFile($obj->getUploadedFile());
        }

        return;
    }

    /**
     * @param UploadedFile $file
     */
    private function processUploadedFile(UploadedFile $file)
    {
        $config = new LexerConfig();
        $lexer = new Lexer($config);

        $lexer->parse($file->getPathname(), $this);

        $this->entityManager->flush();
    }

    /**
     * @param $line
     *
     * @return void
     */
    public function interpret($line)
    {
        $line = array_combine(self::$keys, $line);

        $discount = new Discount();
        $discount
            ->setToken(strtoupper($line['token']))
            ->setValue($line['value'])
            ->setDiscountType($this->discountType)
            ->setName($this->discountType->getName())
        ;

        $this->entityManager->persist($discount);
    }
}
