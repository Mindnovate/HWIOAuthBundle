<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Tests\Fixtures;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\State\State;
use HWI\Bundle\OAuthBundle\OAuth\StateInterface;
use Symfony\Component\HttpFoundation\Request;

class MyCustomProvider implements ResourceOwnerInterface
{
    public function getUserInformation(array $accessToken, array $extraParameters = [])
    {
        return new CustomUserResponse();
    }

    public function getAuthorizationUrl($redirectUri, array $extraParameters = [])
    {
        return '';
    }

    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = [])
    {
        return [];
    }

    public function isCsrfTokenValid($csrfToken)
    {
        return false;
    }

    public function getName()
    {
        return 'custom_provider';
    }

    public function getOption($name)
    {
    }

    public function handles(Request $request)
    {
        return false;
    }

    public function setName($name)
    {
    }

    public function addPaths(array $paths)
    {
    }

    public function refreshAccessToken($refreshToken, array $extraParameters = [])
    {
    }

    public function getState(): StateInterface
    {
        return new State(null);
    }

    public function addStateParameter(string $key, string $value): void
    {
    }

    public function storeState(StateInterface $state = null)
    {
    }
}
