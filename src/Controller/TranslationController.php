<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constant\TranslationConstant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route(path="/translation/{language}", name="switch_locale", methods={"GET"})
     */
    public function __invoke(Request $request, string $language): RedirectResponse
    {
        if (!\in_array($language, TranslationConstant::LANGUAGES)) {
            $this->addFlash('danger', $this->translator->trans('flash.switch_locale.failure', [], 'alerts'));
        } else {
            $request->getSession()->set('_locale', $language);
        }

        return new RedirectResponse($request->headers->get('referer', $this->generateUrl('home')));
    }
}
