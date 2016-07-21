<?php

namespace Aescarcha\AsyncBundle\Tests\Listener;

use Aescarcha\AsyncBundle\Tests\BaseKernelTest;
use Aescarcha\AsyncBundle\Listener\Async\User;
use Aescarcha\AsyncBundle\Listener\Async\Subscription;

/**
 * This class is just to test that the event is triggered and the job is pushed and consumed.
 * More specific test in each of the async listeners
 */

class SubscriptionListenerTest extends BaseKernelTest 
{

    public function testRefreshOnSubscribe()
    {   
        $redis = static::$kernel->getContainer()->get('snc_redis.default');
        $redis->del('UserDetails-2');
        $redis->del('UserDetails-1');

        $consumer = static::$kernel->getContainer()->get('old_sound_rabbit_mq.async_refresher_consumer');
        $this->assertInstanceOf('OldSound\RabbitMqBundle\RabbitMq\Consumer', $consumer);
        $consumer->purge();

        $service = static::$kernel->getContainer()->get('aescarcha.subscription');
        $user = $this->Users->find(1);
        $object = $this->Users->find(2);
        $service->subscribe( $user, $object );

        //If it isn't stuck, its that it was pushed
        $consumer->consume(1);

        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userAcceptedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userRejectedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userCompletedNominations') );
        $this->assertSame( '1', $redis->hget('UserDetails-1', 'followingCount') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'followerCount') );
        $this->assertSame( '1', $redis->hget('UserDetails-2', 'followerCount') );
        $this->assertSame( '[]', $redis->hget('UserDetails-1', 'followerIds') );
        $this->assertSame( '[2]', $redis->hget('UserDetails-1', 'followingIds') );
        $this->assertSame( '[1]', $redis->hget('UserDetails-2', 'followerIds') );
    }


    public function testRefreshOnUnSubscribe()
    {   
        $this->testRefreshOnSubscribe();
        $redis = static::$kernel->getContainer()->get('snc_redis.default');
        $redis->del('UserDetails-1');
        $redis->del('UserDetails-2');

        $consumer = static::$kernel->getContainer()->get('old_sound_rabbit_mq.async_refresher_consumer');
        $this->assertInstanceOf('OldSound\RabbitMqBundle\RabbitMq\Consumer', $consumer);
        $consumer->purge();

        $service = static::$kernel->getContainer()->get('aescarcha.subscription');
        $user = $this->Users->find(1);
        $object = $this->Users->find(2);
        $service->unSubscribe( $user, $object );
        //If it isn't stuck, its that it was pushed
        //it must consume 2, because the first one is the subscription processor
        $consumer->consume(2);

        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userAcceptedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userRejectedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userCompletedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'followingCount') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'followerCount') );
        $this->assertSame( '0', $redis->hget('UserDetails-2', 'followerCount') );
        $this->assertSame( '[]', $redis->hget('UserDetails-1', 'followerIds') );
        $this->assertSame( '[]', $redis->hget('UserDetails-1', 'followingIds') );
        $this->assertSame( '[]', $redis->hget('UserDetails-2', 'followerIds') );
    }

    
}