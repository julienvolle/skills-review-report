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
class HomeControllerTest extends CustomTestCase
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

    public function testRedirectToLogin(): void
    {
        $this->client->request('GET', $this->router->generate('home'));
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        $locales = implode('|', TranslationConstant::LANGUAGES);
        self::assertMatchesRegularExpression('/\/login\/(' . $locales . ')$/i', $location);
    }

    public function testUserView(): void
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_USER],
        ]);

        $this->client->loginUser($user);
        $this->client->request('GET', $this->router->generate('home'));

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('#translation');
        self::assertSelectorExists('#session');
        self::assertSelectorExists('#profileModal');
        self::assertSelectorExists('#navbar');
        self::assertSelectorExists('#navbar > #logo');

        self::assertSelectorNotExists('#navbar > #buttonFrameworks');
        self::assertSelectorNotExists('#frameworkModal');
        self::assertSelectorNotExists('#buttonCreateFramework');
        self::assertSelectorNotExists('#buttonUpdateFramework');
        self::assertSelectorNotExists('#buttonImportFramework');

        self::assertSelectorNotExists('#navbar > #buttonInterviews');
        self::assertSelectorNotExists('#interviewModal');
        self::assertSelectorNotExists('#buttonCreateInterview');
        self::assertSelectorNotExists('#buttonUpdateInterview');
        self::assertSelectorNotExists('#buttonImportInterview');

        self::assertSelectorExists('#navbar > #buttonReports');
        self::assertSelectorExists('#reportModal');
    }

    public function testAdminView(): void
    {
        $admin = $this->entityManager->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_ADMIN],
        ]);

        $this->client->loginUser($admin);
        $this->client->request('GET', $this->router->generate('home'));

        self::assertResponseIsSuccessful();

        self::assertSelectorExists('#translation');
        self::assertSelectorExists('#session');
        self::assertSelectorExists('#profileModal');
        self::assertSelectorExists('#navbar');
        self::assertSelectorExists('#navbar > #logo');

        self::assertSelectorExists('#navbar > #buttonFrameworks');
        self::assertSelectorExists('#frameworkModal');
        self::assertSelectorExists('#buttonCreateFramework');
        self::assertSelectorExists('#buttonUpdateFramework');
        self::assertSelectorExists('#buttonImportFramework');

        self::assertSelectorExists('#navbar > #buttonInterviews');
        self::assertSelectorExists('#interviewModal');
        self::assertSelectorExists('#buttonCreateInterview');
        self::assertSelectorExists('#buttonUpdateInterview');
        self::assertSelectorExists('#buttonImportInterview');

        self::assertSelectorNotExists('#navbar > #buttonReports');
        self::assertSelectorNotExists('#reportModal');
    }
}
