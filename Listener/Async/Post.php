<?php 

namespace Aescarcha\AsyncBundle\Listener\Async;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Post extends Base implements AsyncListenerInterface
{
    public function persist( $id )
    {

    }

    public function update( $id )
    {
        $this->process( $id );
    }

    public function remove( $id )
    {

    }

    protected function process($id)
    {
        $entity = $this->getEntity( $id, 'AescarchaAsyncBundle:Post' );
        $url = $this->container->get('router')->generate( 'Async_show', $entity->getRouteParameters(), UrlGeneratorInterface::ABSOLUTE_URL );
        $this->removePersistListenerEvents();
        $entity->setScore( $this->getUrlScore( $url ) );
        $this->getEm()->persist( $entity );
        $this->getEm()->flush();
    }

    public function refresh( $id )
    {
        $this->refreshGeneralData();
        if($id){
            $entity = $this->getEntity( $id, 'AescarchaAsyncBundle:Post' );
            $this->refreshCachedData( $entity );
        }
    }

    protected function refreshCachedData( \Aescarcha\AsyncBundle\Entity\Post $post )
    {
        if( !$this->getEm()->getFilters()->isEnabled('softdeleteable')){
             $this->getEm()->getFilters()->enable('softdeleteable');
        }
        $serializer = $this->container->get('aescarcha.serializer');
        $redis = $this->container->get('snc_redis.default');
        $posts = $this->getEm()->getRepository('AescarchaAsyncBundle:Post');

        $tagManager = $this->container->get('fpn_tag.tag_manager');
        $tagManager->loadTagging($post);
        $tagRepo = $this->getEm()->getRepository('AescarchaTagBundle:Tag');
        foreach($post->getTags() as $tag) {
            $ids = $tagRepo->getResourceIdsForTag($post->getTaggableType(), $tag->getName());
            //doing tag refresh here ATM, if in the future we need to do more stuff, move it to a tag listener
            $redis->hset("AsyncsTag-" . $tag->getName(), 'new', $serializer->serializeJson( $posts->getLast(7, true)
                         ->where('p.parent is null')
                         ->andWhere('p.id IN(:ids)')
                         ->setParameter('ids', $ids)
                         ->getQuery()
                         ->getResult()
                          ));
            $redis->hset("AsyncsTag-" . $tag->getName(), 'todayBest', $serializer->serializeJson( 
                         $posts->getAsyncQueryBuilder()
                         ->andWhere('p.created >= :date')
                         ->andWhere('p.id IN(:ids)')
                         ->setParameter('date', date('Y-m-d'))
                         ->setParameter('ids', $ids)
                         ->getQuery()
                         ->getResult()
                         ));
            $redis->hset("AsyncsTag-" . $tag->getName(), 'weekBest', $serializer->serializeJson( 
                         $posts->getAsyncQueryBuilder()
                         ->andWhere('p.created >= :date')
                         ->andWhere('p.id IN(:ids)')
                         ->setParameter('date', date('Y-m-d', strtotime('-1 week')))
                         ->setParameter('ids', $ids)
                         ->getQuery()
                         ->getResult()
                         ));
        }


    }

   protected function refreshGeneralData()
   {
        if( !$this->getEm()->getFilters()->isEnabled('softdeleteable')){
             $this->getEm()->getFilters()->enable('softdeleteable');
        }
        $serializer = $this->container->get('aescarcha.serializer');
        $redis = $this->container->get('snc_redis.default');
        $posts = $this->getEm()->getRepository('AescarchaAsyncBundle:Post');

        //refresh general data used for layouts etc
        $redis->hset('Asyncs', 'new', $serializer->serializeJson( $posts->getLast(7, true)->where('p.parent is null')->getQuery()->getResult() ));
        $redis->hset('Asyncs', 'todayBest', $serializer->serializeJson( 
                     $posts->getAsyncQueryBuilder()
                     ->andWhere('p.created >= :date')
                     ->setParameter('date', date('Y-m-d'))
                     ->getQuery()
                     ->getResult()
                     ));
        $redis->hset('Asyncs', 'weekBest', $serializer->serializeJson(
                     $posts->getAsyncQueryBuilder()
                     ->andWhere('p.created >= :date')
                     ->setParameter('date', date('Y-m-d', strtotime('-1 week')))
                     ->getQuery()
                     ->getResult()
                     ));
   }

}