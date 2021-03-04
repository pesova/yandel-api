<?php

namespace App\Contracts;

interface BankServiceInterface 
{
    /**
     * List Banks
     * 
     * @param string|null $nuban
     * 
     * @return array
     */
    public function listBanks(): ?object;

    /**
     * Updates the list of Nigerian banks
     * 
     * @return object
     */
    public function updateBankList(): object;

    /**
     * Find a Banks
     * 
     * @param string $bankCode
     * 
     * @return object
     */
    public function findBank(string $bankCode = null): ?object;

    /**
     * saves a new bank account for user
     * 
     * @param array $params
     * 
     * @return object
     * 
     * @throws BankUserServiceException
     */
    public function addBankAccount( array $params );

    /**
     * Delete bank account
     * 
     * @param array $params
     * 
     * @return object|null
     */
    public function deleteBankAccount( int $bank_id, array $params ): ?object;

    /**
     * returns a list of banks belonging to a user
     * 
     * @return null|object
     */
    public function listBankAccounts(): ?object;

    /**
     * finds a bank account by id
     * 
     * @param string $bank_id
     * 
     * @return object
     */
    public function findBankAccount( int $bank_id ): ?object;

    /**
     * checks if a user has a bank account
     * 
     * @param bool $return - determines if model or boolean is returned
     * 
     * @return bool|collection
     */
    public function userHasBank( bool $return = false ): bool;
}