<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ServiceException;
use App\Model\Response\UserResponse;
use App\Repository\UserRepository;
use App\Model\User;

class UserService {

    public UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * Insert user
     * @param User $u   The user to insert
     * @throws RepositoryException  If the insert fails
     * @throws ServiceException     If the email is already user
     */
    public function insert(User $u): void {
        if ($this->userRepository->selectById($u->Email) != null)
            throw new ServiceException("Email already used!");

        $this->userRepository->insert($u);
    }

    /**
     * Select by id
     * @param string $Email The email to select
     * @return UserResponse     The user selected
     * @throws ServiceException     If no user is found
     */
    public function selectById(string $email): UserResponse {
        $user = $this->userRepository->selectById($email);
        if (is_null($user)) throw new ServiceException("User not found");

        return $user;
    }

    /**
     * Select by credentials
     * @param string $Email The email to select
     * @param string $Password The password to select
     * @return UserResponse     The user selected
     * @throws ServiceException     If no user is found
     */
    public function selectByCredentials(string $Email, string $Password): UserResponse {
        $user = $this->userRepository->selectByCredentials($Email, $Password);
        if (is_null($user)) throw new ServiceException("Wrong credentials");

        return $user;
    }

    /**
     * Select all Users
     * @return array All the users
     * @throws ServiceException If no results
     */
    public function selectAll(): array {

        $users = $this->userRepository->selectAll();

        if ($users) {
            return $users;
        }

        throw new ServiceException("No results");
    }

    /**
     * Update a user
     * @param User $u The user to update
     * @throws ServiceException If the user is not found
     * @throws RepositoryException If the update fails
     */
    public function update(User $u): void {
        $user = $this->userRepository->selectById($u->Email);
        if (is_null($user)) {
            throw new ServiceException("User not found!");
        }

        $this->userRepository->update($u);
    }

    /**
     * Delete a User by email
     * @param string $email The email to delete
     * @throws ServiceException If the user is not found
     * @throws RepositoryException If the delete fails
     */
    public function delete(string $email): void {
        $user = $this->userRepository->selectById($email);
        if (is_null($user)) {
            throw new ServiceException("User not found!");
        }

        $this->userRepository->delete($email);
    }
}
