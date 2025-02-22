<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HWI\Bundle\OAuthBundle\Security\Core\User;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * User provider for the ORM that loads users given a mapping between resource
 * owner names and the properties of the entities.
 *
 * @author Alexander <iam.asm89@gmail.com>
 *
 * @final since 1.4
 */
class EntityUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var array<string, string>
     */
    protected $properties = [
        'identifier' => 'id',
    ];

    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry    manager registry
     * @param string          $class       user entity class to load
     * @param array           $properties  Mapping of resource owners to properties
     * @param string          $managerName Optional name of the entity manager to use
     */
    public function __construct(ManagerRegistry $registry, $class, array $properties, $managerName = null)
    {
        $this->em = $registry->getManager($managerName);
        $this->class = $class;
        $this->properties = array_merge($this->properties, $properties);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->findUser(['username' => $identifier]);

        if (!$user) {
            $exception = new UserNotFoundException(sprintf("User '%s' not found.", $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUser(['username' => $username]);
        if (!$user) {
            $exception = new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
            $exception->setUsername($username);

            throw $exception;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();

        if (!isset($this->properties[$resourceOwnerName])) {
            throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
        }

        $username = $response->getUsername();
        if (null === $user = $this->findUser([$this->properties[$resourceOwnerName] => $username])) {
            $exception = new UsernameNotFoundException(sprintf("User '%s' not found.", $username));
            $exception->setUsername($username);

            throw $exception;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $identifier = $this->properties['identifier'];
        if (!$accessor->isReadable($user, $identifier) || !$this->supportsClass(\get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $userId = $accessor->getValue($user, $identifier);
        $username = $user->getUsername();

        if (null === $user = $this->findUser([$identifier => $userId])) {
            $exception = new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $userId));
            $exception->setUsername($username);

            throw $exception;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->class || is_subclass_of($class, $this->class);
    }

    /**
     * @return UserInterface|null
     */
    protected function findUser(array $criteria)
    {
        if (null === $this->repository) {
            $this->repository = $this->em->getRepository($this->class);
        }

        return $this->repository->findOneBy($criteria);
    }
}
