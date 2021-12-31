<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Constant\SecurityConstant;
use App\Constant\TranslationConstant;
use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Tests\CustomTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @group functional
 */
class TranslationControllerTest extends CustomTestCase
{
    private ?KernelBrowser $client = null;
    private ?RouterInterface $router = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = static::getContainer()->get('router');

        $user = static::getContainer()->get('doctrine')->getManager()->getRepository(User::class)->findOneBy([
            'guid' => AppFixtures::GUIDS[User::class][SecurityConstant::ROLE_USER],
        ]);
        $this->client->loginUser($user);
    }

    public function tearDown(): void
    {
        unset($this->client);
        unset($this->router);

        parent::tearDown();
    }

    /** @dataProvider providerTestSwitchLocale */
    public function testSwitchLocale(string $locale): void
    {
        $uri = $this->router->generate('switch_locale', ['language' => $locale]);
        $this->client->request(Request::METHOD_GET, $uri);
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/$/i', $location); // Redirect to home by default

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#translation');
        self::assertSelectorTextContains('#translation span', strtoupper($locale));
        foreach (TranslationConstant::LANGUAGES as $language) {
            if ($language !== $locale) {
                self::assertSelectorTextContains('#translation a', strtoupper($language));
            }
        }
    }

    public function providerTestSwitchLocale(): iterable
    {
        foreach (TranslationConstant::LANGUAGES as $language) {
            yield $language => [$language];
        }
    }

    public function testInvalidLocale(): void
    {
        $uri = $this->router->generate('switch_locale', ['language' => '__']);
        $this->client->request(Request::METHOD_GET, $uri);
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertMatchesRegularExpression('/\/$/i', $location); // Redirect to home by default

        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('#flashMessages p.text-danger');
    }

    public function testRedirectToReferer(): void
    {
        $referer = 'previous_location_uri';
        $uri = $this->router->generate('switch_locale', ['language' => TranslationConstant::DEFAULT_LOCALE]);
        $this->client->request(Request::METHOD_GET, $uri, [], [], ['HTTP_REFERER' => $referer]);
        self::assertResponseRedirects();

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $location = $response->headers->get('location');
        self::assertSame($referer, $location); // Redirect to referer
    }
}
