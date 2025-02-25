<?php
// src/Repository/TeamMemberRepository.php
namespace App\Repository;

use App\Entity\TeamMember;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMember>
 */
class TeamMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMember::class);
    }

    /**
     * Finds TeamMembers by the Team name.
     *
     * @param string|null $teamName
     * @return TeamMember[]
     */
    public function findByTeamName(?string $teamName): array
    {
        $qb = $this->createQueryBuilder('tm')
            ->join('tm.team', 't')
            ->addSelect('t');

        if ($teamName) {
            $qb->andWhere('t.name = :teamName')
               ->setParameter('teamName', $teamName);
        }

        return $qb->getQuery()->getResult();
    }
}
