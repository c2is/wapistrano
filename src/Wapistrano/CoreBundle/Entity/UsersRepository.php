<?php
namespace Wapistrano\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UsersRepository
 *
 * Provider for Users Entity
 */
class UsersRepository extends EntityRepository implements UserProviderInterface
{
    /**
     * @param string $username
     *
     * @return mixed|UserInterface
     */
    public function loadUserByUsername($username)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT c FROM WapistranoCoreBundle:Users c WHERE c.login = :username')
            ->setParameter('username', $username)
            ->getOneOrNullResult();
    }

    /**
     * @param UserInterface $user
     *
     * @return object|UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->findOneBy(array('email' => $user->getUsername()));
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return ($class == 'Wapistrano\\CoreBundle\\Entity\\Users');
    }
}