<?php
namespace Neos\RedirectHandler\DatabaseStorage\Domain\Repository;

/*
 * This file is part of the Neos.RedirectHandler.DatabaseStorage package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Neos\RedirectHandler\DatabaseStorage\Domain\Model\Redirection;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Persistence\Repository;

/**
 * Repository for redirection instances.
 * Note: You should not interact with this repository directly. Instead use the RedirectionService!
 *
 * @Flow\Scope("singleton")
 */
class RedirectionRepository extends Repository
{
    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $defaultOrderings = array(
        'sourceUriPath' => QueryInterface::ORDER_ASCENDING,
        'host' => QueryInterface::ORDER_ASCENDING
    );

    /**
     * @param string $sourceUriPath
     * @param string $host Host or host pattern
     * @return Redirection
     */
    public function findOneBySourceUriPathAndHost($sourceUriPath, $host = null)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('sourceUriPathHash', md5(trim($sourceUriPath, '/'))),
                $query->logicalOr(
                    $query->equals('host', $host),
                    $query->equals('host', null)
                )
            )
        );

        return $query->execute()->getFirst();
    }

    /**
     * @param string $targetUriPath
     * @param string $host Host or host pattern
     * @return Redirection
     */
    public function findOneByTargetUriPathAndHost($targetUriPath, $host = null)
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('targetUriPathHash', md5(trim($targetUriPath, '/'))),
                $query->logicalOr(
                    $query->equals('host', $host),
                    $query->equals('host', null)
                )
            )
        );

        return $query->execute()->getFirst();
    }

    /**
     * @param string $targetUriPath
     * @param string $host Host or host pattern
     * @return QueryInterface
     */
    public function findByTargetUriPathAndHost($targetUriPath, $host = null)
    {
        /** @var Query $query */
        $query = $this->entityManager->createQuery('SELECT r FROM Neos\RedirectHandler\DatabaseStorage\Domain\Model\Redirection r WHERE r.targetUriPathHash = :targetUriPathHash AND (r.host = :host OR r.host IS NULL)');
        $query->setParameter('targetUriPathHash', md5(trim($targetUriPath, '/')));
        $query->setParameter('host', $host);

        return $this->iterate($query->iterate());
    }

    /**
     * Finds all objects and return an IterableResult
     *
     * @param string $host Full qualified hostname or host pattern
     * @param callable $callback
     * @return \Generator<Redirection>
     */
    public function findAll($host = null, callable $callback = null)
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $query = $queryBuilder
            ->select('r')
            ->from($this->getEntityClassName(), 'r');

        if ($host !== null) {
            $query->andWhere('r.host = :host')
                ->setParameter('host', $host);
        }

        $query->orderBy('r.host', 'ASC');
        $query->addOrderBy('r.sourceUriPath', 'ASC');

        return $this->iterate($query->getQuery()->iterate(), $callback);
    }

    /**
     * Return a list of all host patterns
     *
     * @return array
     */
    public function findDistinctHosts()
    {
        /** @var Query $query */
        $query = $this->entityManager->createQuery('SELECT DISTINCT r.host FROM Neos\RedirectHandler\DatabaseStorage\Domain\Model\Redirection r');
        return array_filter(array_map(function($record) {
            return $record['host'];
        }, $query->getResult()));
    }

    /**
     * Iterator over an IterableResult and return a Generator
     *
     * @param IterableResult $iterator
     * @param callable $callback
     * @return \Generator<RedirectionDto>
     */
    protected function iterate(IterableResult $iterator, callable $callback = null)
    {
        $iteration = 0;
        foreach ($iterator as $object) {
            /** @var Redirection $object */
            $object = current($object);
            yield $object;
            if ($callback !== null) {
                call_user_func($callback, $iteration, $object);
            }
            ++$iteration;
        }
    }
}