<?php

namespace AppBundle\Controller;

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
     * @Route("/trade/message/")
     */
    public function messageAction(Request $request)
    {
        return new JsonResponse(['data' => '1']);
    }
}
