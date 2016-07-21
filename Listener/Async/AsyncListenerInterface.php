<?php

namespace Aescarcha\AsyncBundle\Listener\Async;

use Symfony\Component\DependencyInjection\Container;

interface AsyncListenerInterface
{
    public function __construct( Container $container );

    public function persist( $id );

    public function update( $id );

    public function remove( $id );
}
