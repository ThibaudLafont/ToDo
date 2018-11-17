<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findDoneTasks() {
        $statement = "
            SELECT t
            FROM App:Task t
            WHERE t.doneAt IS NOT null
        ";

        // Execute the query and store results
        $tasks = $this->getEntityManager()
            ->createQuery($statement)
            ->execute();

        // Return result
        return $tasks;
    }

    public function findToDoTasks() {
        $statement = "
            SELECT t
            FROM App:Task t
            WHERE t.doneAt IS null
        ";

        // Execute the query and store results
        $tasks = $this->getEntityManager()
            ->createQuery($statement)
            ->execute();

        // Return result
        return $tasks;
    }
}
