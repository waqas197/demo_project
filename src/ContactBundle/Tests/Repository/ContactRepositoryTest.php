<?php

namespace ContactBundle\Tests\Services;

use ContactBundle\Controller\ContactApiController;
use ContactBundle\Entity\Contact;
use ContactBundle\Repository\ContactRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class ContactRepositoryTest extends TestCase
{

    /**
     * @var MockObject | RegistryInterface
     */
    private $registry;

    /**
     * @var MockObject | ContainerInterface
     */
    private $container;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    private $em;

    public function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->createMock(RegistryInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->em->expects(static::once())
            ->method('getClassMetadata')
            ->willReturn($classMeta = $this->createMock(ClassMetadata::class));
        $this->createMock(ManagerRegistry::class);
        $this->registry->expects(static::once())
            ->method('getManagerForClass')
            ->willReturn($this->em);

        $this->contactRepository = new ContactRepository(
            $this->registry,
            $this->container
        );
    }

    public function testCreateOrUpdate(): void
    {
        $this->em->expects(static::once())
            ->method('persist')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)));
        $this->em->expects(static::once())
            ->method('flush')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)));

        static::assertTrue($this->contactRepository->createOrUpdate($contact));
    }

    public function testCreateOrUpdateWithException(): void
    {
        $this->em->expects(static::once())
            ->method('persist')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)));
        $this->em->expects(static::once())
            ->method('flush')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)))
            ->will($this->throwException(new \Exception()));

        static::assertFalse($this->contactRepository->createOrUpdate($contact));
    }

    public function testDelete(): void
    {
        $this->em->expects(static::once())
            ->method('remove')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)));
        $this->em->expects(static::once())
            ->method('flush')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)));

        static::assertTrue($this->contactRepository->delete($contact));
    }

    public function testDeleteWithException(): void
    {
        $this->em->expects(static::once())
            ->method('remove')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)));
        $this->em->expects(static::once())
            ->method('flush')
            ->with(static::equalTo($contact = $this->createMock(Contact::class)))
            ->will($this->throwException(new \Exception()));

        static::assertFalse($this->contactRepository->delete($contact));
    }

    public function testSearch(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);

        $this->em->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects(static::once())
            ->method('select')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects(static::once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects(static::exactly(3))
            ->method('andWhere')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects(static::exactly(3))
            ->method('setParameter')
            ->willReturn($mockQueryBuilder);

        $queryMock = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();

        $mockQueryBuilder
            ->method('getQuery')
            ->willReturn($queryMock);

        $queryMock
            ->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([]);

        static::assertEquals(
            [],
            $this->contactRepository->search([
                ContactApiController::NAME => 'name',
                ContactApiController::ADDRESS => 'address',
                ContactApiController::EMAIL => '11'
            ]));
    }


    public function testgetContacts(): void
    {
        $contact = new Contact();
        $contact->setFirstName('abc');
        $contact->setLastName('def');

        $contact1 = new Contact();
        $contact1->setFirstName('abc');
        $contact1->setLastName('def');

        $mockQueryBuilder = $this->createMock(QueryBuilder::class);

        $this->em->expects(static::once())
            ->method('createQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects(static::once())
            ->method('addSelect')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects(static::once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $queryMock = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();

        $mockQueryBuilder
            ->method('getQuery')
            ->willReturn($queryMock);

        $paginator = $this->createMock(Paginator::class);

        $this->container->expects(static::once())
            ->method('get')
            ->with('knp_paginator')
            ->willReturn($paginator);

        $paginator->expects(static::once())
            ->method('paginate')
            ->willReturn(
                [
                    $contact,
                    $contact1
                ]
            );

        static::assertEquals(
            [
                $contact,
                $contact1
            ],
            $this->contactRepository->getContacts(2, 4)
        );
    }

}
