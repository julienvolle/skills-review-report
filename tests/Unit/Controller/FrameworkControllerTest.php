<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\FrameworkController;
use App\Entity\Framework;
use App\Service\FrameworkService;
use App\Tests\CustomTestCase;
use Exception;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @group unit
 */
class FrameworkControllerTest extends CustomTestCase
{
    private ?FrameworkController $controller = null;

    public function setUp(): void
    {
        $this->setProphecies([
            FlashBagInterface::class,
            FrameworkService::class,
        ]);

        $this->controller = new FrameworkController(
            $this->getReveal(FrameworkService::class),
            $this->getMockBuilder(TranslatorInterface::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(ValidatorInterface::class)->disableOriginalConstructor()->getMock()
        );

        $security = $this->getMockBuilder(Security::class)->disableOriginalConstructor()->getMock();
        $security->method('isGranted')->willReturn(true);

        $session = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $session->method('getFlashBag')->willReturn($this->getReveal(FlashBagInterface::class));

        $requestStack = $this->getMockBuilder(RequestStack::class)->disableOriginalConstructor()->getMock();
        $requestStack->method('getSession')->willReturn($session);

        $file = $this->getMockBuilder(FormInterface::class)->disableOriginalConstructor()->getMock();
        $file->method('getData')->willReturn(
            $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock()
        );

        $form = $this->getMockBuilder(FormInterface::class)->disableOriginalConstructor()->getMock();
        $form->method('handleRequest')->willReturn($form);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn(new Framework());
        $form->method('createView')->willReturn('htmlForm');
        $form->method('get')->with('file')->willReturn($file);

        $formFactory = $this->getMockBuilder(FormFactoryInterface::class)->disableOriginalConstructor()->getMock();
        $formFactory->method('create')->willReturn($form);

        $twig = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $twig->method('render')->willReturn('html');

        $router = $this->getMockBuilder(Router::class)->disableOriginalConstructor()->getMock();
        $router->method('generate')->willReturn('url');

        $mocks = [
            'security.authorization_checker' => $security,
            'form.factory'                   => $formFactory,
            'request_stack'                  => $requestStack,
            'twig'                           => $twig,
            'router'                         => $router,
        ];

        $container = $this->getMockBuilder(ContainerInterface::class)->disableOriginalConstructor()->getMock();
        $container->method('has')->willReturn(true);
        $container->method('get')->with($this->anything())->will(
            $this->returnCallback(function ($serviceName) use ($mocks) {
                return $mocks[$serviceName];
            })
        );

        $this->controller->setContainer($container);
    }

    public function tearDown(): void
    {
        unset($this->controller);

        parent::tearDown();
    }

    /** @dataProvider providerTestErrorFlashMessage */
    public function testErrorFlashMessage(string $method, array $arguments, string $methodFailed): void
    {
        $this->getProphecy(FrameworkService::class)
            ->$methodFailed(Argument::any())
            ->shouldBeCalledOnce()
            ->willThrow(new Exception('error_message'));

        $this->getProphecy(FlashBagInterface::class)
            ->add(
                Argument::exact('danger'),
                Argument::exact('error_message')
            )
            ->shouldBeCalledOnce();

        $this->controller->$method(...$arguments);
    }

    public function providerTestErrorFlashMessage(): iterable
    {
        yield 'create' => ['create', [new Request()],                  'save'];
        yield 'update' => ['update', [new Request(), new Framework()], 'save'];
        yield 'delete' => ['delete', [new Framework()],              'remove'];
        yield 'import' => ['import', [new Request()],    'handleUploadedFile'];
        yield 'export' => ['export', [new Framework()],              'export'];
    }
}
