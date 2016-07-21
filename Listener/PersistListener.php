<?php
namespace Aescarcha\AsyncBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Product;

//Rabbit stuff
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;  
use PhpAmqpLib\Message\AMQPMessage;  

class PersistListener implements ConsumerInterface
{
    protected $container;

    public function __construct( \Symfony\Component\DependencyInjection\Container $container )
    {
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $msg['class'] = get_class($entity);
        $msg['id'] = $entity->getId();
        $msg['action'] = 'persist';
        $this->container->get('old_sound_rabbit_mq.async_refresher_producer')->publish(serialize($msg));
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $msg['class'] = get_class($entity);
        $msg['id'] = $entity->getId();
        $msg['action'] = 'update';
        $this->container->get('old_sound_rabbit_mq.async_refresher_producer')->publish(serialize($msg));
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $msg['class'] = get_class($entity);
        $msg['id'] = $entity->getId();
        $msg['action'] = 'remove';
        $this->container->get('old_sound_rabbit_mq.async_refresher_producer')->publish(serialize($msg));
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        return $this->postRemove( $args );
    }

    /**
     * Get the async listener based on the class
     * @param  string $class [description]
     * @return string the listener classname
     */
    protected function getListenerClass( $class )
    {
        $class = str_replace('\Entity\\', '\Listener\Async\\', $class);
        $class = preg_replace('/\\\[a-zA-Z]+Bundle\\\/', '\AsyncBundle\\', $class);
        return $class;
    }

    /**
     * Receive the published message, then instantiate the async listener
     * @param  AMQPMessage $msg [description]
     * @return [type]           [description]
     */
    public function execute(AMQPMessage $msg)  
    {  
        $message = unserialize($msg->body);  
        // try {
        $asyncListenerClass = $this->getListenerClass( $message['class'] );
        if(class_exists($asyncListenerClass)){
            $listener = new $asyncListenerClass( $this->container );
            //call lib update, persist or remove
            $listener->$message['action']($message['id']);
        }
        // } catch (\Exception $e) {
        //     dump($e->getMessage());
        //     return false;
        // }

        return true;

    } 
}