<?php 

namespace Aescarcha\AsyncBundle\Listener\Async;

class User extends Base implements AsyncListenerInterface
{
    public function persist( $id )
    {

    }

    public function update( $id )
    {

    }

    public function remove( $id )
    {

    }

    public function refresh( $id )
    {
        $this->refreshGeneralData();
        if($id){
            $entity = $this->getEntity( $id, 'AescarchaUserBundle:User' );
            $this->refreshCachedData( $entity );
        }
    }

    protected function refreshCachedData( \FOS\UserBundle\Model\User $user )
    {
        if( !$this->getEm()->getFilters()->isEnabled('softdeleteable')){
             $this->getEm()->getFilters()->enable('softdeleteable');
        }
        $redis = $this->container->get('snc_redis.default');
        $nominationRepository = $this->container->get('aescarcha.notification')->getRepository('Nomination');
        $subscription = $this->container->get('aescarcha.subscription');
        $postsRepository = $this->getEm()->getRepository('AescarchaAsyncBundle:Post');
        $serializer = $this->container->get('aescarcha.serializer');

        $key = 'UserDetails-' . $user->getId();
        $redis->hset($key, 'userAcceptedNominations', $nominationRepository->getForUserByStatus( $user, 1 ));
        $redis->hset($key, 'userCompletedNominations', $nominationRepository->getForUserByStatus( $user, 2 ));
        $redis->hset($key, 'userRejectedNominations', $nominationRepository->getForUserByStatus( $user, -1 ));
        //@TODO: change this to following and followers
        $redis->hset($key, 'followingCount', count($subscription->getUserSubscriptionsToType( $user, 'Aescarcha\UserBundle\Entity\User' )));
        $redis->hset($key, 'followerCount', count($subscription->getSubscriptionsTo( $user )));

        $redis->hset($key, 'followerIds', json_encode(array_column($subscription->getSubscriptionsTo( $user, 's.userId' ), 'userId')));
        $redis->hset($key, 'followingIds', json_encode(array_column($subscription->getUserSubscriptionsToType( $user, 'Aescarcha\UserBundle\Entity\User', 's.targetId' ), 'targetId')));

        $redis->hset($key, 'topUserAsyncs', $serializer->serializeJson( $postsRepository->findBy(['type' => 1, 'user' => $user], ['replyCount' => 'desc'], 5)));
        $redis->hset($key, 'topUserResponses', $serializer->serializeJson( $postsRepository->findBy(['type' => 2, 'user' => $user], ['replyCount' => 'desc'], 5)));

        $userScore = $this->getEm()
        ->createQuery("SELECT SUM(p.score) AS score FROM AescarchaAsyncBundle:Post p WHERE p.user = ?1")
        ->setParameter(1, $user)
        ->getSingleScalarResult();
        $redis->hset($key, 'userScore', $userScore);

        $this->removePersistListenerEvents();
        $user->setScore($userScore);
        $this->getEm()->persist( $user );
        $this->getEm()->flush();
    }


    protected function refreshGeneralData()
    {
        return true;
    }

}