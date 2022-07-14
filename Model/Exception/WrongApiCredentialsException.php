<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Thuiswinkel\BewustBezorgd\Model\Exception;

use Exception;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Phrase;

/**
 * Class WrongApiConfigurationException
 */
class WrongApiCredentialsException extends ConfigurationMismatchException
{
    /**
     * Constructor.
     *
     * @param Phrase|null $phrase
     * @param Exception|null $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, Exception $cause = null, $code = 401)
    {
        if ($phrase === null) {
            $phrase = new Phrase('ShopID does not exist or credentials are invalid.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
