<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\OAuth\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @final since 1.4
 */
class HttpTransportException extends AuthenticationException
{
    private $ownerName;

    public function __construct($message, $ownerName, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->ownerName = $ownerName;
    }

    public function getOwnerName()
    {
        return $this->ownerName;
    }
}
