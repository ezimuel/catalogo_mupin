<?php

declare(strict_types=1);

namespace App\Service\Computer;

use App\Exception\ServiceException;
use App\Model\Computer\Os;
use App\Repository\Computer\OsRepository;

class OsService {

    public OsRepository $osRepository;

    public function __construct(OsRepository $osRepository) {
        $this->osRepository = $osRepository;
    }

    /**
     * Insert os
     * @param Os $os The os to insert
     * @throws ServiceException If the os name is already used
     * @throws RepositoryException If the insert fails
     */
    public function insert(Os $os): void {
        $osFetch = $this->osRepository->selectByName($os->Name);
        if ($osFetch)
            throw new ServiceException("Os name already used!");

        $this->osRepository->insert($os);
    }

    /**
     * Select os by id
     * @param int $id The id to select
     * @return Os The os selected
     * @throws ServiceException If not found
     */
    public function selectById(int $id): Os {
        $os = $this->osRepository->selectById($id);
        if (is_null($os)) {
            throw new ServiceException("Os not found");
        }

        return $os;
    }

    /**
     * Select os by name
     * @param string $name The name to select
     * @return Os The os selected
     * @throws ServiceException If not found
     */
    public function selectByName(string $name): Os {
        $os = $this->osRepository->selectByName($name);
        if (is_null($os)) {
            throw new ServiceException("Os not found");
        }

        return $os;
    }

    /**
     * Select os by key
     * @param string $key The key to search
     * @return array The oss selected
     */
    public function selectByKey(string $key): array {
        return $this->osRepository->selectByKey($key);
    }

    /**
     * Select all
     * @return array All the oss
     */
    public function selectAll(): array {
        return $this->osRepository->selectAll();
    }

    /**
     * Update a os
     * @param Os $os The os to update
     * @throws ServiceException If not found
     * @throws RepositoryException If the update fails
     */
    public function update(Os $os): void {
        $o = $this->osRepository->selectById($os->OsID);
        if (is_null($o)) {
            throw new ServiceException("Os not found!");
        }

        $this->osRepository->update($os);
    }

    /**
     * Delete an os
     * @param int $id The id to delete
     * @throws ServiceException If not found
     * @throws RepositoryException If the delete fails
     */
    public function delete(int $id): void {
        $os = $this->osRepository->selectById($id);
        if (is_null($os)) {
            throw new ServiceException("Os not found!");
        }

        $this->osRepository->delete($id);
    }
}
