<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Constant\SecurityConstant;
use App\Constant\TranslationConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Tests\CustomTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @group functional
 */
class SecurityControllerTest extends CustomTestCase
{
    private ?KernelBrowser $client = null;
    private ?RouterInterface $router = null;
    private ?EntityManagerInterface $entityManager = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = static::getContainer()->get('router');
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function tearDown(): void
    {
        unset($this->client);
        unset($this->router);

        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    /** @dataProvider providerTestLogin */
    public function testLoginSuccess(string $username, string $password): void
    {
        $this->client->request('GET', $this->router->generate('security_login', [
            '_locale' => TranslationConstant::DEFAULT_LOCALE,
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Submit');
        $form = $buttonCrawlerNode->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/$/i', $location);
    }

    public function providerTestLogin(): iterable
    {
        foreach (AppFixtures::CREDENTIALS as $role => $credentials) {
            yield $role => [$credentials['username'], $credentials['password']];
        }
    }

    public function testLoginFailure(): void
    {
        $this->client->request('GET', $this->router->generate('security_login', [
            '_locale' => TranslationConstant::DEFAULT_LOCALE,
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Submit');
        $form = $buttonCrawlerNode->form();
        $form['_username'] = 'unknown@example.com';
        $form['_password'] = '______';
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        $locales = implode('|', TranslationConstant::LANGUAGES);
        self::assertMatchesRegularExpression('/\/login\/(' . $locales . ')$/i', $location);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#flashMessages p.text-danger');
    }

    public function testAlreadyAuthenticated(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_USER],
        ]));

        $this->client->request('GET', $this->router->generate('security_login', [
            '_locale' => TranslationConstant::DEFAULT_LOCALE,
        ]));
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/$/i', $location);
    }

    public function testLogout(): void
    {
        $this->client->loginUser($this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_USER],
        ]));

        $this->client->request('GET', $this->router->generate('security_logout'));
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        $locales = implode('|', TranslationConstant::LANGUAGES);
        self::assertMatchesRegularExpression('/\/login\/(' . $locales . ')$/i', $location);
    }

    public function testRegister(): void
    {
        $this->client->request('GET', $this->router->generate('security_register', [
            '_locale' => TranslationConstant::DEFAULT_LOCALE,
        ]));
        self::assertResponseIsSuccessful();

        $crawler = $this->client->getCrawler();
        $buttonCrawlerNode = $crawler->selectButton('Submit');
        $form = $buttonCrawlerNode->form();
        $form['user[email]']         = 'sample@domain.com';
        $form['user[plainPassword]'] = 'sample';
        $form['user[role]']          = SecurityConstant::ROLE_USER;
        $this->client->submit($form);

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        $locales = implode('|', TranslationConstant::LANGUAGES);
        self::assertMatchesRegularExpression('/\/login\/(' . $locales . ')$/i', $location);

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#flashMessages p.text-success');
    }
}
