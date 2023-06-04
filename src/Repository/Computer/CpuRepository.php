<?php

declare(strict_types=1);

namespace App\Repository\Computer;

use App\Repository\GenericRepository;
use App\Exception\RepositoryException;
use App\Model\Computer\Cpu;
use PDO;
use PDOException;
use App\Util\ORM;

class CpuRepository extends GenericRepository {

    /**
     * Insert a cpu
     * @param Cpu $cpu  The cpu to insert
     * @throws RepositoryException  If the insert fails
     */
    public function insert(Cpu $cpu): void {

        $query =
            "INSERT INTO cpu 
            (ModelName,Speed) VALUES 
            (:ModelName,:Speed);";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam("ModelName", $cpu->ModelName, PDO::PARAM_STR);
        $stmt->bindParam("Speed", $cpu->Speed, PDO::PARAM_STR);

        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RepositoryException("Error while inserting the cpu with name: {" . $cpu->ModelName . "}");
        }
    }

    /**
     * Select cpu by id
     * @param int $CpuID    The cpu id to select
     * @return ?Cpu     The selected cpu, null if not found
     */
    public function selectById(int $CpuID): ?Cpu {
        $query = "SELECT * FROM cpu WHERE CpuID = :CpuID";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam("CpuID", $CpuID, PDO::PARAM_INT);
        $stmt->execute();
        $cpu = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cpu) {
            return ORM::getNewInstance(Cpu::class, $cpu);
        }
        return null;
    }

    /**
     * Select cpu by name
     * @param string $ModelName     The cpu name to select
     * @return ?Cpu     The cpu selected, null if not found
     */
    public function selectByName(string $ModelName): ?Cpu {
        $query = "SELECT * FROM cpu WHERE ModelName = :ModelName";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam("ModelName", $ModelName, PDO::PARAM_STR);
        $stmt->execute();
        $cpu = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($cpu) {
            return ORM::getNewInstance(Cpu::class, $cpu);
        }
        return null;
    }

    /**
     * Select cpu by key
     * @param string $key     The key to search
     * @return array     The cpus selected
     */
    public function selectByKey(string $key): array {
        $query = "SELECT * FROM cpu WHERE ModelName LIKE :key OR Speed LIKE :key";

        $key = '%' . $key . '%';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam("key", $key, PDO::PARAM_STR);
        $stmt->execute();
        $arr_cpu = $stmt->fetchAll(PDO::FETCH_CLASS);

        return $arr_cpu;
    }

    /**
     * Select all cpus
     * @return ?array   The cpus selected, null if no result
     */
    public function selectAll(): ?array {
        $query = "SELECT * FROM cpu";

        $stmt = $this->pdo->query($query);

        $arr_cpu = $stmt->fetchAll(PDO::FETCH_CLASS);

        return $arr_cpu;
    }

    /**
     * Update a cpu
     * @param Cpu $c    The cpu to update
     * @throws RepositoryException If the update fails
     */
    public function update(Cpu $c): void {
        $query =
            "UPDATE cpu 
            SET ModelName = :ModelName,
            Speed = :Speed
            WHERE CpuID = :CpuID;";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam("ModelName", $c->ModelName, PDO::PARAM_STR);
        $stmt->bindParam("Speed", $c->Speed, PDO::PARAM_STR);
        $stmt->bindParam("CpuID", $c->CpuID, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RepositoryException("Error while updating the cpu with id: {" . $c->CpuID . "}");
        }
    }

    /**
     * Delete a cpu by id
     * @param int $CpuID    The cpu id to delete
     * @throws RepositoryException  If the delete fails
     */
    public function delete(int $CpuID): void {
        $query =
            "DELETE FROM cpu  
            WHERE CpuID = :CpuID;";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam("CpuID", $CpuID, PDO::PARAM_INT);
        try {
            $stmt->execute();
        } catch (PDOException $e) {
            throw new RepositoryException("Error while deleting the cpu with id: {" . $CpuID . "}");
        }
    }
}
