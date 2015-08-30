<?php

namespace AppBundle\Controller;

use AppBundle\Entity\TradeMessage;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        if (!$this->isValidContentType($request)) {
            return new JsonResponse([
                'message' => 'Content-Type must be application/json',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $data = $this->parseJsonRequest($request);
        } catch (RuntimeException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $message = new TradeMessage();

        $message->fromArray($data);
        $message->transformData();

        $errors = $this->get('validator')->validate($message);

        if (0 !== $errors->count()) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['message' => 'Trade Message was created'], 201);
    }

    /**
     * isValidContentType
     *
     * Ensure that the request Content-Type
     * is `application/json`.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return boolean
     */
    private function isValidContentType(Request $request)
    {
        return 'json' === $request->getContentType();
    }

    /**
     * parseJsonRequest
     *
     * Parse JSON data into its native PHP
     * representation as an associative array.
     *
     * If the format of the JSON string received
     * a `\RuntimeException` containting an error.
     *
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @throws \RuntimeException
     * @return array
     */
    private function parseJsonRequest(Request $request)
    {
        $data = [];
        $json = $request->getContent();

        if (!empty($json)) {
            $data = json_decode($json, true);
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException(
                sprintf('JSON parse error (%s)', json_last_error_msg())
            );
        }

        return $data;
    }
}
