<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Constant\SecurityConstant;
use App\Constant\SerializerConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Framework;
use App\Entity\Interview;
use App\Entity\User;
use App\Model\Export\InterviewExport;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @group functional
 */
class InterviewControllerTest extends CustomTestCase
{
    private ?KernelBrowser $client = null;
    private ?RouterInterface $router = null;
    private ?EntityManagerInterface $entityManager = null;
    private ?SerializerInterface $serializer = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = static::getContainer()->get('router');
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->serializer = static::getContainer()->get('serializer');
    }

    public function tearDown(): void
    {
        unset($this->client);
        unset($this->router);
        unset($this->serializer);

        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    public function testCreate(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $this->client->request('GET', $this->router->generate('interview_create', [
            'framework_id' => AppFixtures::GUIDS[Framework::class],
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $form['interview[title]'] = 'Test create interview';
        $form['interview[firstname]'] = 'John';
        $form['interview[lastname]'] = 'Doe';
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/interview\/update\/([a-z0-9\-]*)+$/i', $location);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#flashMessages p.text-success');
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'title' => 'Test create interview',
        ]);
        $this->client->request('GET', $this->router->generate('interview_update', [
            'interview_id' => $interview->getGuid(),
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $form['interview[title]'] = 'Test update interview';
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/interview\/update\/([a-z0-9\-]*)+$/i', $location);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#flashMessages p.text-success');
    }

    /**
     * @depends testUpdate
     */
    public function testExport(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'title' => 'Test update interview',
        ]);
        $this->client->request('GET', $this->router->generate('interview_export', [
            'interview_id' => $interview->getGuid(),
        ]));
        self::assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('content-type'));

        $object = $this->serializer->deserialize(
            $response->getContent(),
            InterviewExport::class,
            SerializerConstant::FORMAT_EXPORT
        );
        self::assertInstanceOf(InterviewExport::class, $object);

        // Save file to test import
        file_put_contents(self::CACHE_DIR . '/export_interview.json', $response->getContent());
    }

    /**
     * @depends testExport
     */
    public function testReport(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'title' => 'Test update interview',
        ]);
        $this->client->request('GET', $this->router->generate('interview_report', [
            'interview_id' => $interview->getGuid(),
        ]));
        self::assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        self::assertSelectorTextContains('.container h1', 'Test update interview');
        self::assertSelectorTextContains('.container h3', 'John DOE');
    }

    /**
     * @depends testReport
     */
    public function testDelete(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'title' => 'Test update interview',
        ]);
        $this->client->request('GET', $this->router->generate('interview_delete', [
            'interview_id' => $interview->getGuid(),
        ]));
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/$/i', $location);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#flashMessages p.text-success');
    }

    /**
     * @depends testDelete
     */
    public function testImport(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $this->client->request('GET', $this->router->generate('interview_import'));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();
        $form['import[file]']->upload(self::CACHE_DIR . '/export_interview.json');
        $form['import[delete]']->tick();
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/interview\/update\/([a-z0-9\-]*)+$/i', $location);

        $this->finalTearDown();
    }

    /**
     * Cannot run in static method self::tearDownAfterClass()
     */
    private function finalTearDown(): void
    {
        $interview = $this->entityManager->getRepository(Interview::class)->findOneBy([
            'title' => 'Test update interview',
        ]);
        $this->client->request('GET', $this->router->generate('interview_delete', [
            'interview_id' => $interview->getGuid(),
        ]));
    }
}
