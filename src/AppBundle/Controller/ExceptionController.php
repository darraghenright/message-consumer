<?php

namespace AppBundle\Controller;

use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * ExceptionController
 *
 * Override standard exceptions
 * in production env with basic
 * json response codes.
 *
 * @see app/config/services.yml
 */
class ExceptionController extends BaseExceptionController
{
    /**
     * {@inheritdoc}
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        if ($request->attributes->get('showException', $this->debug)) {
            return parent::showAction($request, $exception, $logger);
        }

        return new JsonResponse([
            'message' => sprintf('Status code: %d', $exception->getStatusCode())
        ], $exception->getStatusCode());
    }
}
