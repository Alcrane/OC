<?php
//-- src/OC/PlatformBundle/Entity/AdvertRepository.php

namespace OC\PlatformBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * AdvertRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AdvertRepository extends EntityRepository
{
    /*-------------------------------------
     * public function myFindAll()
     */
    
    public function myFindAll()
    {
        //-- Methode 1: en Passant par EntityManager
        $queryBuilder = $this->_em->createQueryBuilder
                ->select('a')
                ->from($this->_entityName, 'a');
        // Dans un repository, $this->_entityName est 
        // le namespace de l'entité gérée
        // Ici, il vaut donc OC\PlatformBundle\Entity\Advert
        
        //-- Methode 2: En passant par le raccourci A faire
        $queryBuilder = $this->crreateQueryBuilder('a');
        
        // On n'ajoute pas de critère ou tri particulier, la construction
        // de notre requête est finie

        // On récupère la Query à partir du QueryBuilder
        $query = $queryBuilder->getQuery();
        
        //-- Recup le resultat à partir de la Query
        $result = $query->getResult();
        
        //-- Retourne le resultat
        //--return $result;
        
        //-- Methode raccourci
        return $this
                ->createQueryBuider('a')
                ->getQuery()
                ->getResult();
        
    }
    
    /*-------------------------------------
     * public function myFindOne($id)
     */
    
    public function myFindOne($id)
    {
        $qb = $this->createQueryBuilder('a');
        
        $qb
                ->where('a.id = :id')
                ->setParameter('id', $id);
        
        return $qb
                ->getQuery()
                ->getResult()
        ;
    }
    
     /*-------------------------------------
     * public function findByAuthorAndDate($author, $year)
     */
    public function findByAuthorAndDate($author, $year)
    {
        $qb = $this->createQueryBuilder('a');
        
        $qb
                ->where('a.author = :author')
                    ->setParameter('author', $author)
                ->andWhere('a.date < :year')
                    ->setParameter('year' , $year)
                ->orderBy('a.date', 'DESC')
                ;
        
        return $qb
                ->getQuery()
                ->getResult();
    }
    
    /*------------------------------------------------
     * 
     */
    public function whereCurrentYear(QueryBuilder $qd)
    {
        $qb
                ->andWhere('a.date BETWEEN :start AND :end')
                    ->setParameter('start', new \DateTime(date('Y').'-01-01'))
                    ->setParameter('end', new \DateTime(date('Y').'-12-31'))
                ;
        
    }
    
    /*----------------------------------------------------
     * public function myFind()
     */
    
    public function myFind()
    {
        $qb = $this->createQueryBuilder('a');

        // On peut ajouter ce qu'on veut avant
        $qb
                ->where('a.author = :author')
                ->setParameter('author', 'Marine')
        ;

        // On applique notre condition sur le QueryBuilder
        $this->whereCurrentYear($qb);

        // On peut ajouter ce qu'on veut après
        $qb->orderBy('a.date', 'DESC');

        return $qb
                        ->getQuery()
                        ->getResult()
        ;
    }
    
    /*----------------------------------------------------
     * public function getAdvertWithApplications
     */
    
    public function getAdvertWithApplications()
    {
        $qb = $this
                ->createQueryBuilder('a')
                ->leftJoin('a.application', 'app')
                ->addSelect('app');
        
        return $qb
                ->getQuery()
                ->getResult();
        
    }
    
    /*-----------------------------------------------------
     * public function getAdvertWithCategories(array $categoryNames)
     */
    public function getAdvertWithCategories(array $categoryNames)
    {
        $qb = $this->createQueryBuiler('a');
        
        //-- Creation de la jointure avec les category
        $qb 
            ->Join('a.category', 'c')
            ->addSelect('c');
        
        //-- rajout des conditions
        $qb
                ->where($qb->expr->in('c.name',$categoryNames));
        
        //-- renvoie le resultat
        return $qb
                ->getQuery()
                ->getResult();
    }
    
    /*-----------------------------------------------------
     * public function getApplicationsWithAdvert
     */
    
    public function getApplicationsWithAdvert($limit)
    {
        $qb = $this->createQueryBuilder('a');

        // On fait une jointure avec l'entité Advert avec pour alias « adv »
        $qb
                ->join('a.advert', 'adv')
                ->addSelect('adv')
        ;

        // Puis on ne retourne que $limit résultats
        $qb->setMaxResults($limit);

        // Enfin, on retourne le résultat
        return $qb
                        ->getQuery()
                        ->getResult()
        ;
    }
    
    /*-----------------------------------------------------
     * public function getAdverts 
     */
    public function getAdverts ($page, $nbPerPage) {
        $query = $this->createQueryBuilder ('a')
                ->leftJoin ('a.image', 'i')
                ->addSelect ('i')
                ->leftJoin ('a.categories', 'c')
                ->addSelect ('c')
                ->orderBy ('a.date', 'DESC')
                ->getQuery ()
        ;

        $query
                // On définit l'annonce à partir de laquelle commencer la liste
                ->setFirstResult (($page - 1) * $nbPerPage)
                // Ainsi que le nombre d'annonce à afficher sur une page
                ->setMaxResults ($nbPerPage)
        ;

        // Enfin, on retourne l'objet Paginator correspondant à la requête construite
        // (n'oubliez pas le use correspondant en début de fichier)
        return new Paginator ($query, true);
    }
    
    /*-------------------------------------------------------------------
     * public function getPublishedQueryBuilder () 
     */
    public function getPublishedQueryBuilder () {
        return $this
                        ->createQueryBuilder ('a')
                        ->where ('a.published = :published')
                        ->setParameter ('published', true)
        ;
    }

}//-- fin class
