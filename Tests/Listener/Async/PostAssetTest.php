<?php

namespace Aescarcha\AsyncBundle\Tests\Listener;

use Aescarcha\AsyncBundle\Tests\BaseKernelTest;
use Aescarcha\AsyncBundle\Listener\Async\PostAsset;

/**
 * This class is just to test that the event is triggered and the job is pushed and consumed.
 * More specific test in each of the async listeners
 */

class PostAssetListenerTest extends BaseKernelTest 
{

    public function testDeleteRemovesFromFb()
    {   
        $entity = $this->modifyRegistryToAvoidRealDelete(1);
        $listener = new PostAsset( static::$kernel->getContainer() );
        $listener->remove(1);

        $facebook = static::$kernel->getContainer()->get('aescarcha.facebook');
        $this->setExpectedException('Facebook\Exceptions\FacebookResponseException');
        //as it is already deleted, it should throw an exception
        $response = $facebook->delete( $entity->getForeignId() );
    }

    protected function modifyRegistryToAvoidRealDelete( $id = 1 )
    {
        $facebook = static::$kernel->getContainer()->get('aescarcha.facebook');
        $response = $facebook->upload( array(
                                      'src' => 'http://www.iteramos.com/packages/aescarcha/q2astack/img/plusplus-white.png',
                                      'type' => 1,
                                      'message' => 'My test message.'
                                      ));

        $data = json_decode($response->getContent(), true);
        $entity = $this->em->getRepository('AescarchaAsyncBundle:PostAsset')->find($id);
        $entity->setForeignId( $data['id'] );
        $entity->setForeignOriginalId( $data['id'] );
        $this->em->persist($entity);
        $this->em->flush();
        return $entity;
    }

    //This test is for async check of width, this is now done in the front, if it's done in the future use this test
    // public function testRefresh()
    // {
    //     $id = 1;
    //     $listener = new PostAsset( static::$kernel->getContainer() );
    //     $listener->persist($id);
    //     $entity = $this->em->getRepository('AescarchaAsyncBundle:PostAsset')->find($id);
    //     $this->assertEquals( '480', $entity->getWidth() );
    //     $this->assertEquals( '300', $entity->getHeight() );
    // }
    
}