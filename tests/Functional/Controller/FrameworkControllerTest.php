<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Constant\SecurityConstant;
use App\Constant\SerializerConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\Framework;
use App\Entity\User;
use App\Model\Export\FrameworkExport;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @group functional
 */
class FrameworkControllerTest extends CustomTestCase
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

        $this->client->request('GET', $this->router->generate('framework_create'));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $form['framework[name]'] = 'Test create framework';
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/framework\/update\/([a-z0-9\-]*)+$/i', $location);
    }

    /**
     * @depends testCreate
     */
    public function testUpdate(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'name' => 'Test create framework',
        ]);
        $this->client->request('GET', $this->router->generate('framework_update', [
            'framework_id' => $framework->getGuid(),
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();
        $form['framework[name]'] = 'Test update framework';
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

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

        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'name' => 'Test update framework',
        ]);
        $this->client->request('GET', $this->router->generate('framework_export', [
            'framework_id' => $framework->getGuid(),
        ]));
        self::assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('content-type'));

        $object = $this->serializer->deserialize(
            $response->getContent(),
            FrameworkExport::class,
            SerializerConstant::FORMAT_EXPORT
        );
        self::assertInstanceOf(FrameworkExport::class, $object);

        // Save file to test import
        file_put_contents(self::CACHE_DIR . '/export_framework.json', $response->getContent());
    }

    /**
     * @depends testExport
     */
    public function testDelete(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]));

        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'name' => 'Test update framework',
        ]);
        $this->client->request('GET', $this->router->generate('framework_delete', [
            'framework_id' => $framework->getGuid(),
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

        $this->client->request('GET', $this->router->generate('framework_import'));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Upload');
        $form = $buttonCrawlerNode->form();
        $form['import[file]']->upload(self::CACHE_DIR . '/export_framework.json');
        $form['import[delete]']->tick();
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/framework\/update\/([a-z0-9\-]*)+$/i', $location);

        $this->finalTearDown();
    }

    /**
     * Cannot run in static method self::tearDownAfterClass()
     */
    private function finalTearDown(): void
    {
        $framework = $this->entityManager->getRepository(Framework::class)->findOneBy([
            'name' => 'Test update framework',
        ]);
        $this->client->request('GET', $this->router->generate('framework_delete', [
            'framework_id' => $framework->getGuid(),
        ]));
    }
}
