<?php

namespace App\Controller;

use App\Entity\UrlMinimizer;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlMinimizerController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/minimize')]
    public function minimizeUrl(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $url = $request->request->get('url');
            $lifetime = $request->request->get('lifetime');

            $entityManager = $this->entityManager;

            $urlMinimizer = new UrlMinimizer();
            $urlMinimizer->setUrl($url);

            $shortCode = $urlMinimizer->generateShortCode();
            $urlMinimizer->setSlug($shortCode);
            $urlMinimizer->setExpiryDate($urlMinimizer->getDateTimeFromHours($lifetime));
            $currentDateTime = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $urlMinimizer->setCreatedAt($currentDateTime);
            $urlMinimizer->setViewCount(0);
            $entityManager->persist($urlMinimizer);
            $entityManager->flush();

            $serverName = $request->getHttpHost();
            return $this->render('minimizer/success.html.twig', [
                'shortUrl' => "http://$serverName/redirect/$shortCode",
            ]);
        }

        return $this->render('minimizer/form.html.twig');
    }

    /**
     * @param $slug
     *
     * @return RedirectResponse|Response
     */
    public function redirectToSlug($slug): RedirectResponse|Response {
        $urlMinimizer = $this->entityManager->getRepository(UrlMinimizer::class)->findOneBy(['slug' => $slug]);

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

        $entityManager = $this->entityManager;
        $urlMinimizer->setViewCount($urlMinimizer->getViewCount() + 1);
        $entityManager->persist($urlMinimizer);
        $entityManager->flush();

        return $this->redirect($urlMinimizer->getUrl());
    }
}