<?php

namespace Aescarcha\AsyncBundle\Listener\Async;

use Aescarcha\AsyncBundle\Listener\PersistListener;

abstract class Base
{
    protected $container;

    public function __construct( \Symfony\Component\DependencyInjection\Container $container )
    {
        $this->container = $container;
    }

    protected function getEntity( $id, $repository )
    {
        if( $this->getEm()->getFilters()->isEnabled('softdeleteable')){
             $this->getEm()->getFilters()->disable('softdeleteable');
        }
        $entity = $this->getEm()->getRepository($repository)->find( $id );
        $this->getEm()->getFilters()->enable('softdeleteable');
        return $entity;

    }

    protected function getEm()
    {
        return $this->container->get('doctrine')->getManager();
    }

    protected function getUrlScore( $url )
    {
        $score = 0;
        $fb = $this->container->get('aescarcha.facebook');
        $score += $fb->getLikes($url);
        $score += $fb->getShares($url) * 2;
        return $score;
    }

    
    protected function refreshUser( $id )
    {
        $listener = new User($this->container);
        return $listener->refresh($id);
    }


    /**
     * Remove persistlistener events, so it will no requeue stuff
     */
    protected function removePersistListenerEvents(  array $events = array('postPersist', 'postUpdate') )
    {
        foreach ($this->getEm()->getEventManager()->getListeners() as $event => $listeners) {
            foreach ($listeners as $hash => $listener) {
                if ($listener instanceof PersistListener) {
                    $this->getEm()->getEventManager()->removeEventListener( $events, $listener );
                }
            }
        }
    }

}