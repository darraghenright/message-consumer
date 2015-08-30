<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TradeController
 *
 * @author Darragh Enright <darraghenright@gmail.com>
 */
class TradeController extends Controller
{
    /**
     * messageAction
     *
     * Consume Trade Message requests:
     *
     * * Validate Content-Type
     * * Parse JSON
     * * Validate JSON structure and data
     * * Persist record
     *
     * Outcomes:
     *
     * * `400 Bad Request` for invalid Content-Type
     * * `400 Bad Request` for JSON parse error
     * * `422 Unprocessable Entity` for invalid JSON structure and/or data
     * * `503 Service Unavailable` for data persistence errors
     * * `201 Created` for successful consumption and persistence
     *
     * @Method("POST")
     * @Route("/trade/message/")
     */
    public function messageAction(Request $request)
    {
        if ('json' !== $request->getContentType()) {
            return new JsonResponse([
                'message' => 'Content-Type must be application/json',
            ], 400);
        }

        // parse json

        // validate json structure

        // persist!

        return new JsonResponse(['data' => '1']);
    }
}
