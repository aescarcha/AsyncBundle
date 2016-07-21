<?php

namespace Aescarcha\AsyncBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class AsyncController extends Controller
{
    protected $repositories = [
        'Post' => 'AescarchaAsyncBundle:Post',
        'User' => 'AescarchaUserBundle:User',
        'Nomination' => 'AescarchaNotificationBundle:Nomination',
        'Notification' => 'AescarchaNotificationBundle:Notification',
        'Subscription' => 'AescarchaUserSubscriptionBundle:Subscription',
    ];

    public function actionPerformedAction($entity, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository($this->repositories[$entity])->find( $id );
        //yup, update nothing, see if it tirggers the event
        $entity->setUpdated( new \Datetime );
        $em->persist($entity);
        $em->flush();
        $response = new JsonResponse();
        return $response->setData(['status' => 'success']);
    }
}
