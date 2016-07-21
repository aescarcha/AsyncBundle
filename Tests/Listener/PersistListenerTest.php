<?php

namespace Aescarcha\AsyncBundle\Tests\Listener;

use Aescarcha\AsyncBundle\Tests\BaseKernelTest;

/**
 * This class is just to test that the event is triggered and the job is pushed and consumed.
 * More specific test in each of the async listeners
 */

class PersistListenerTest extends BaseKernelTest 
{

    public function testUpdate()
    {   
        $consumer = static::$kernel->getContainer()->get('old_sound_rabbit_mq.async_refresher_consumer');
        $this->assertInstanceOf('OldSound\RabbitMqBundle\RabbitMq\Consumer', $consumer);
        $consumer->purge();

        $object = $this->Posts->find(1);
        $object->setContent('culooo a das da hsd oash doia doad');
        $object->setScore(99999999);
        $this->em->persist($object);
        $this->em->flush();
        //If it isn't stuck, its that it was pushed
        $consumer->consume(1);

        $object = $this->Posts->find(1);
        //score back to 0 because because no likes
        $this->assertEquals( 0, $object->getScore());

    }

    public function testDelete()
    {   
        $consumer = static::$kernel->getContainer()->get('old_sound_rabbit_mq.async_refresher_consumer');
        $consumer->purge();

        $object = $this->Posts->find(1);
        $this->assertNull( $object->getDeletedAt() );
        $this->em->remove($object);
        $this->em->flush();
        $object = $this->Posts->find(1);
        $this->assertNotNull( $object->getDeletedAt() );

        //If it isn't stuck, its that it was pushed
        $consumer->consume(1);
    }
    
    
}