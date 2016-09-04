<?php

namespace Trejjam\Acl;

use Nette;
use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Trejjam;

class Authenticator implements Nette\Security\IAuthenticator
{
	/**
	 * @var Entity\User\UserRepository
	 */
	private $userRepository;

	/**
	 * Authenticator constructor.
	 *
	 * @param Entity\User\UserRepository $userRepository
	 */
	function __construct(Trejjam\Acl\Entity\User\UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 *
	 * @param array $credentials
	 *
	 * @return IIdentity
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$user = $this->userRepository->getByUsername($username);

		if (is_null($user->getPassword())) {
			throw new Trejjam\Acl\Entity\User\NotDefinedPasswordException($username);
		}
		else if ( !Nette\Security\Passwords::verify($password, $user->getPassword())) {
			throw new Trejjam\Acl\Entity\User\InvalidCredentialsException($username);
		}
		else if (Nette\Security\Passwords::needsRehash($user->getPassword())) {
			$this->userRepository->changePassword($user, $password);
		}

		return $user;
	}
}
