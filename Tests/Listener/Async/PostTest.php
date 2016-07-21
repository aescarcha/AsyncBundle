<?php

namespace Aescarcha\AsyncBundle\Tests\Listener;

use Aescarcha\AsyncBundle\Tests\BaseKernelTest;
use Aescarcha\AsyncBundle\Listener\Async\Post;

/**
 * This class is just to test that the event is triggered and the job is pushed and consumed.
 * More specific test in each of the async listeners
 */

class PostListenerTest extends BaseKernelTest 
{

    public function testRefresh()
    {   
        $redis = static::$kernel->getContainer()->get('snc_redis.default');
        $serializer = static::$kernel->getContainer()->get('aescarcha.serializer');
        $redis->del('Posts');

        $listener = new Post( static::$kernel->getContainer() );
        $listener->refresh( 1 );

        $collection = $redis->hget('Asyncs', 'new');
        $collectionDecoded = $serializer->deserializeJson( $collection );
        $this->assertCount( 6, $collectionDecoded );
        foreach ($collectionDecoded as $key => $entity) {
            $this->assertInstanceOf( 'Aescarcha\AsyncBundle\Entity\Post', $entity );
            $this->assertNull( $entity->getParent() );
        }

        $collection = $redis->hget('Asyncs', 'todayBest');
        $collectionDecoded = $serializer->deserializeJson( $collection );
        $this->assertCount( 6, $collectionDecoded );
        foreach ($collectionDecoded as $key => $entity) {
            $this->assertInstanceOf( 'Aescarcha\AsyncBundle\Entity\Post', $entity );
            $this->assertNull( $entity->getParent() );
        }

        $collection = $redis->hget('Asyncs', 'weekBest');
        $collectionDecoded = $serializer->deserializeJson( $collection );
        $this->assertCount( 6, $collectionDecoded );
        foreach ($collectionDecoded as $key => $entity) {
            $this->assertInstanceOf( 'Aescarcha\AsyncBundle\Entity\Post', $entity );
            $this->assertNull( $entity->getParent() );
        }

        $collection = $redis->hget('AsyncsTag-risky', 'weekBest');
        $collectionDecoded = $serializer->deserializeJson( $collection );
        $this->assertCount( 1, $collectionDecoded );
        foreach ($collectionDecoded as $key => $entity) {
            $this->assertInstanceOf( 'Aescarcha\AsyncBundle\Entity\Post', $entity );
            $this->assertNull( $entity->getParent() );
        }

        $collection = $redis->hget('AsyncsTag-hard', 'new');
        $collectionDecoded = $serializer->deserializeJson( $collection );
        $this->assertCount( 5, $collectionDecoded );
        foreach ($collectionDecoded as $key => $entity) {
            $this->assertInstanceOf( 'Aescarcha\AsyncBundle\Entity\Post', $entity );
            $this->assertNull( $entity->getParent() );
        }
    }
    
}