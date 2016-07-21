<?php 

namespace Aescarcha\AsyncBundle\Listener\Async;

class Subscription extends Base implements AsyncListenerInterface
{
    public function persist( $id )
    {
        $entity = $this->process( $id );
        if($entity){
            $this->refreshUser( $entity->getUserId() );
            if( 'Aescarcha\UserBundle\Entity\User' === $entity->getTargetType()){
                $this->refreshUser( $entity->getTargetId() );
            }
        }
    }

    public function update( $id )
    {
        $entity = $this->process( $id );
        if($entity){
            $this->refreshUser( $entity->getUserId() );
            if( 'Aescarcha\UserBundle\Entity\User' === $entity->getTargetType()){
                $this->refreshUser( $entity->getTargetId() );
            }
        }
    }

    public function remove( $id )
    {
        $entity = $this->process( $id );
        if($entity){
            $this->refreshUser( $entity->getUserId() );
            if( 'Aescarcha\UserBundle\Entity\User' === $entity->getTargetType()){
                $this->refreshUser( $entity->getTargetId() );
            }
        }
    }

    protected function process($id)
    {
        $entity = $this->getEntity( $id, 'AescarchaUserSubscriptionBundle:Subscription' );
        return $entity;
    }

}