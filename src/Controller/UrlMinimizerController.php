<?php

namespace App\Controller;

use App\Repository\UrlMinimizerRepository;
use App\Service\UrlMinimizerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class UrlMinimizerController extends AbstractController
{
    private UrlMinimizerRepository $urlMinimizerRepository;

    public function __construct(UrlMinimizerRepository $urlMinimizerRepository)
    {
        $this->urlMinimizerRepository = $urlMinimizerRepository;
    }

    #[Route('/minimize')]
    public function minimizeUrl(Request $request, UrlMinimizerService $urlMinimizerService): Response
    {
        if ($request->isMethod('POST')) {
            $url = $request->request->get('url');
            $lifetime = $request->request->get('lifetime');

            $urlMinimizer = $urlMinimizerService->generateUrlMinimizer($url, $lifetime);

            $this->urlMinimizerRepository->save($urlMinimizer);

            $serverName = $request->getHttpHost();
            return $this->render('minimizer/success.html.twig', [
                'shortUrl' => "http://$serverName/redirect/{$urlMinimizer->getSlug()}",
                'infoUrl' => "http://$serverName/info/{$urlMinimizer->getSlug()}",
            ]);
        }

        return $this->render('minimizer/form.html.twig');
    }

    /**
     * @param                     $slug
     * @param UrlMinimizerService $urlMinimizerService
     *
     * @return RedirectResponse|Response
     */
    public function redirectToSlug($slug, UrlMinimizerService $urlMinimizerService): RedirectResponse|Response {
        $urlMinimizer = $this->urlMinimizerRepository->findOneBy(['slug' => $slug]);

        $message = '';
        if (!$urlMinimizer) {
            $message = "Посилання з ключем '$slug' не знайдено";
        }
        if (isset($urlMinimizer) && $urlMinimizer->isExpired()) {
            $message = "У посилання '$slug' минув термін";
        }
        if ($message !== '') {
            return $this->render('minimizer/redirect.html.twig', ['message' => $message]);
        }

        $urlMinimizerService->updateViewCount($urlMinimizer);

        return $this->redirect($urlMinimizer->getUrl());
    }

    /**
     * @param $slug
     *
     * @return Response
     */
    public function info($slug): Response {
        $urlMinimizer = $this->urlMinimizerRepository->findOneBy(['slug' => $slug]);

        if (is_null($urlMinimizer)) {
            throw new NotFoundHttpException('Iнформація не знайдена');
        }
        $createdAt = $urlMinimizer->getCreatedAt();
        $formattedDateCreatedAt = $createdAt->format('Y-m-d H:i:s');

        $expiryDate = $urlMinimizer->getExpiryDate();
        $formattedDateExpiryDate = $expiryDate->format('Y-m-d H:i:s');

        return $this->render('minimizer/info.html.twig', [
            'slug' => $urlMinimizer->getSlug(),
            'url' => $urlMinimizer->getUrl(),
            'expiryDate' => $formattedDateExpiryDate,
            'createdAt' => $formattedDateCreatedAt,
            'viewCount' => $urlMinimizer->getViewCount(),
        ]);
    }
}