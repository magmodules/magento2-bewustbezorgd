<?php
/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Thuiswinkel\BewustBezorgd\Model\Exception;

use Exception;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Phrase;

/**
 * Class WrongApiConfigurationException
 */
class WrongApiConfigurationException extends ConfigurationMismatchException
{
    /**
     * Constructor.
     *
     * @param Phrase|null $phrase
     * @param Exception|null $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Configuration mismatch detected.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
