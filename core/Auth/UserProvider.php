<?php

namespace Fusion\Core\Auth;

/**
 * User Provider Interface
 */
interface UserProvider
{
    /**
     * Retrieve user by ID
     */
    public function findById(int $id);

    /**
     * Retrieve user by credentials
     */
    public function findByCredentials(array $credentials);

    /**
     * Validate user credentials
     */
    public function validateCredentials($user, array $credentials): bool;
}
