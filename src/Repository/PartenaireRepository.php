<?php

namespace App\Repository;

use App\Entity\Partenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partenaire>
 */
class PartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partenaire::class);
    }

    public function add(Partenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Partenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne la liste des partenaires, avec filtre optionnel sur actif,
     * triés par ordre croissant puis par nom croissant.
     *
     * @param bool|null $actif
     * @return Partenaire[]
     */
    public function findFiltered(?bool $actif = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.ordre', 'ASC')
            ->addOrderBy('p.nom', 'ASC');

        if ($actif !== null) {
            $qb->andWhere('p.actif = :actif')
                ->setParameter('actif', $actif);
        }

        return $qb->getQuery()->getResult();
    }
}
