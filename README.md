# Aescarcha AsyncBundle
## Introduction

This bundle handles events on Symfony entities, like persist, delete... It pushes a job to RMQ so heavy stuff can be handled in an asynchronous way.

## Install

    composer require aescarcha/async

#### Config

##### Configure RMQ in your config.yml, IE:

    old_sound_rabbit_mq:
        connections:
            default:
                host:     'localhost'
                port:     5672
                user:     'guest'
                password: 'guest'
                vhost:    '/'
                lazy:     false
                connection_timeout: 3
                read_write_timeout: 3

                # requires php-amqplib v2.4.1+ and PHP5.4+
                keepalive: false

                # requires php-amqplib v2.4.1+
                heartbeat: 0
        producers: #the process sending messages to the broker is called producer
            async_refresher:
                connection:       default
                exchange_options: {name: 'async-refresher', type: direct}

        consumers: #the process receiving those messages is called consumer
            async_refresher:
                connection:       default
                exchange_options: {name: 'async-refresher', type: direct}
                queue_options:    {name: 'async-refresher'}
                callback:         aescarcha.persist_listener

#### services.yml

    #Listener to trigger async regeneration, this service is also a rabbit consumer
    aescarcha.persist_listener:
        class: Aescarcha\ChallengeBundle\Listener\PersistListener
        arguments: [ @service_container ]
        tags:
            - { name: doctrine.event_listener, event: postPersist }
            - { name: doctrine.event_listener, event: postUpdate }
            - { name: doctrine.event_listener, event: postRemove }
            - { name: doctrine.event_listener, event: postSoftDelete }

##### AppKernel.php

    $bundles = array(
        new Aescarcha\SerializerBundle\AescarchaSerializerBundle(),
    );



## Tests
Tests are provided on the repo, but they're not working because the test requires some Entities and Repositories to work, making them work in a clean symfony install is also a TODO

## SERIUS TODO:
Dynamic classes called on listeners, move those classes outside the repo