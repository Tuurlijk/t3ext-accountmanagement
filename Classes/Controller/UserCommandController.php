<?php
namespace MaxServ\Accountmanagement\Controller;

/**
 *  Copyright notice
 *
 *  ⓒ 2015 Michiel Roos <michiel@maxserv.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is free
 *  software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */
use TYPO3\CMS\Beuser\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class UserCommandController
 *
 * The following actions will be supported:
 * list
 * create
 * delete
 * activate
 * deactivate
 * show
 * setPassword
 *
 * @author Michiel Roos <michiel@maxserv.com>
 */
class UserCommandController extends CommandController {

	/**
	 * Backend user repository
	 *
	 * @var \TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository
	 * @inject
	 */
	protected $backendUserRepository;

	/**
	 * Persistence manager
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;

	/**
	 * Activate a user
	 *
	 * This command reactivates possibly expired accounts for the given user.
	 *
	 * If an authentication provider is specified, this command will look for
	 * an account with the given username related to the given provider. Still,
	 * this command will activate <b>all</b> accounts of a user, once such a
	 * user has been found.
	 *
	 * @param string $username The username of the user to be activated.
	 *
	 * @return void
	 */
	public function activateCommand($username) {
		/** @var BackendUser $user */
		$user = $this->getUserOrFail($username);
		$user->setIsDisabled(FALSE);
		$this->backendUserRepository->update($user);
		$this->persistenceManager->persistAll();
		$this->outputLine('Activated user "%s".', array($username));
	}

	/**
	 * Deactivate a user
	 *
	 * This command deactivates a user by flagging all of its accounts as
	 * expired.
	 *
	 * If an authentication provider is specified, this command will look for
	 * an account with the given username related to the given provider. Still,
	 * this command will deactivate <b>all</b> accounts of a user, once such a
	 * user has been found.
	 *
	 * @param string $username The username of the user to be deactivated.
	 *
	 * @return void
	 */
	public function deactivateCommand($username) {
		/** @var BackendUser $user */
		$user = $this->getUserOrFail($username);
		$user->setIsDisabled(TRUE);
		$this->backendUserRepository->update($user);
		$this->persistenceManager->persistAll();
		$this->outputLine('Deactivated user "%s".', array($username));
	}

	/**
	 * List backend users
	 *
	 * @return void
	 */
	public function listCommand() {
		$users = $this->backendUserRepository->findAll();
		$this->outputLine('Found ' . count($users) . ' users');
		/** @var BackendUser $user */
		foreach ($users as $user) {
			$this->outputLine($user->getUserName());
		}
	}

	/**
	 * Create a new user
	 *
	 * This command creates a new user which has access to the backend user
	 * interface.
	 *
	 * More specifically, this command will create a new user and a new account
	 * at the same time. The created account is, by default, a Neos backend
	 * account using the the "Typo3BackendProvider" for authentication. The
	 * given username will be used as an account identifier for that new
	 * account.
	 *
	 * If an authentication provider name is specified, the new account will be
	 * created for that provider instead.
	 *
	 * Roles for the new user can optionally be specified as a comma separated
	 * list. For all roles provided by Neos, the role namespace "TYPO3.Neos:"
	 * can be omitted.
	 *
	 * @param string $username The username of the user to be created, used as
	 *    an account identifier for the newly created account
	 * @param string $password Password of the user to be created
	 * @param string $firstName First name of the user to be created
	 * @param string $lastName Last name of the user to be created
	 * @param string $roles A comma separated list of roles to assign.
	 *    Examples: "Editor, Acme.Foo:Reviewer"
	 * @param string $authenticationProvider Name of the authentication
	 *    provider to use for the new account. Example: "Typo3BackendProvider"
	 *
	 * @return void
	 */
	public function createCommand($username, $password = '', $firstName = '', $lastName = '') {
		$user = $this->getUser($username);
		if ($user instanceof BackendUser) {
			$this->outputLine('The username "%s" is already in use', array($username));
			$this->quit(1);
		}

		try {
			$realName = ($lastName) ? $lastName . ', ' . $firstName : $firstName;
			$user = new BackendUser;
			$user->setUserName($username);
			$user->setRealName($realName);
			$this->backendUserRepository->add($user);
			$this->persistenceManager->persistAll();

			$this->outputLine('Created user "%s".', array($username));
		} catch (\Exception $exception) {
			$this->outputLine($exception->getMessage());
			$this->quit(1);
		}
	}


	/**
	 * Retrieves the given user
	 *
	 * @param string $username Username of the user to find
	 *
	 * @return BackendUser The user
	 */
	protected function getUser($username) {
		$user = FALSE;
		$users = $this->backendUserRepository->findByUserName($username);
		foreach ($users as $currentUser) {
			$user = $currentUser;
			break;
		}
		if (!$user instanceof BackendUser) {
			$user = FALSE;
		}

		return $user;
	}

	/**
	 * Retrieves the given user or fails by exiting with code 1 and a message
	 *
	 * @param string $username Username of the user to find
	 *
	 * @return BackendUser The user
	 * @throws Exception
	 */
	protected function getUserOrFail($username) {
		$user = NULL;
		$users = $this->backendUserRepository->findByUserName($username);
		foreach ($users as $currentUser) {
			$user = $currentUser;
			break;
		}
		if (!$user instanceof BackendUser) {
			$this->outputLine('The user "%s" does not exist.', array($username));
			$this->quit(1);
		}

		return $user;
	}
}
