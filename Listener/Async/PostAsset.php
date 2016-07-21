<?php 

namespace Aescarcha\AsyncBundle\Listener\Async;

class PostAsset extends Base implements AsyncListenerInterface
{
    public function persist( $id )
    {
        $this->refresh( $id );
    }

    public function update( $id )
    {
        $this->refresh( $id );
    }

    public function remove( $id )
    {
        $this->removeFbAsset( $id );
    }   

    protected function removeFbAsset( $id )
    {
        $entity = $this->getEntity( $id, 'AescarchaAsyncBundle:PostAsset' );
        $facebook = $this->container->get('aescarcha.facebook');
        return $facebook->delete( $entity->getForeignId() );
    }

    public function refresh($id)
    {
        $entity = $this->getEntity( $id, 'AescarchaAsyncBundle:PostAsset' );
    }

}