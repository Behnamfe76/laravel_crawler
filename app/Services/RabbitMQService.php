<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQService
{
    /**
     * @throws \Exception
     */
    public static function getConnection(): AMQPStreamConnection
    {
        $rabbitMqHost = config('app.rabbitmq.host', 'localhost');
        $rabbitMqPort = config('app.rabbitmq.port', 5672);
        $rabbitMqUser = config('app.rabbitmq.user', 'admin');
        $rabbitMqPassword = config('app.rabbitmq.password', '9102012320');
        return new AMQPStreamConnection($rabbitMqHost, $rabbitMqPort, $rabbitMqUser, $rabbitMqPassword);
    }
}
