<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private UserPasswordHasherInterface $encoder;
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        UserPasswordHasherInterface $encoder,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/login/{_locale}", name="security_login")
     */
    public function login(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('danger', $error->getMessage());
        }

        return $this->render('page/security/login.html.twig', [
            'last_username' => $this->authenticationUtils->getLastUsername(),
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout(): void
    {
        // @codeCoverageIgnoreStart
        throw new LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * @Route("/register/{_locale}", name="security_register")
     */
    public function register(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles([$form->get('role')->getData()]);
            $user->setPassword($this->encoder->hashPassword($user, $form->get('plainPassword')->getData()));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', $this->translator->trans('flash.user.registered', [], 'alerts'));

            return $this->redirectToRoute('security_login');
        }

        return $this->render('page/security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
