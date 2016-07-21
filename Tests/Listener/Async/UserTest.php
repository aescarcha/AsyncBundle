<?php

namespace Aescarcha\AsyncBundle\Tests\Listener;

use Aescarcha\AsyncBundle\Tests\BaseKernelTest;
use Aescarcha\AsyncBundle\Listener\Async\User;

/**
 * This class is just to test that the event is triggered and the job is pushed and consumed.
 * More specific test in each of the async listeners
 */

class UserListenerTest extends BaseKernelTest 
{

    public function testRefresh()
    {   
        $redis = static::$kernel->getContainer()->get('snc_redis.default');
        $serializer = static::$kernel->getContainer()->get('aescarcha.serializer');
        $redis->del('UserDetails-1');

        $listener = new User( static::$kernel->getContainer() );
        $listener->refresh( 1 );

        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userAcceptedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userRejectedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userCompletedNominations') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'userScore') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'followingCount') );
        $this->assertSame( '0', $redis->hget('UserDetails-1', 'followerCount') );
        $this->assertSame( '[]', $redis->hget('UserDetails-1', 'followerIds') );
        $this->assertSame( '[]', $redis->hget('UserDetails-1', 'followingIds') );

        $this->assertCount( 1,  $serializer->deserializeJson($redis->hget('UserDetails-1', 'topUserAsyncs') ));
        $this->assertCount( 1,  $serializer->deserializeJson($redis->hget('UserDetails-1', 'topUserResponses') ));

        $dbUser = $this->Users->find(1);
        $this->assertEquals( 0, $dbUser->getScore());
    }
    
}