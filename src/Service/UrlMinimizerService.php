<?php

namespace App\Service;

use App\Entity\UrlMinimizer;
use App\Repository\UrlMinimizerRepository;
use DateTimeImmutable;
use DateTimeZone;

class UrlMinimizerService
{
    private UrlMinimizerRepository $urlMinimizerRepository;

    public function __construct(UrlMinimizerRepository $urlMinimizerRepository)
    {
        $this->urlMinimizerRepository = $urlMinimizerRepository;
    }

    public function generateUrlMinimizer($url, $lifetime): UrlMinimizer
    {
        $urlMinimizer = new UrlMinimizer();
        $urlMinimizer->setUrl($url);

        $shortCode = $urlMinimizer->generateShortCode();
        $urlMinimizer->setSlug($shortCode);
        $urlMinimizer->setExpiryDate($urlMinimizer->getDateTimeFromHours($lifetime));
        $currentDateTime = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $urlMinimizer->setCreatedAt($currentDateTime);
        $urlMinimizer->setViewCount(0);

        return $urlMinimizer;
    }

    public function updateViewCount(UrlMinimizer $urlMinimizer): void
    {
        $urlMinimizer->setViewCount($urlMinimizer->getViewCount() + 1);
        $this->urlMinimizerRepository->save($urlMinimizer);
    }
}
