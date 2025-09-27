<?php

return [
    'services' => [
        'value' => [
            \Itb\Core\Logger\LoggerFactoryInterface::class => [
                'className' => \Itb\Core\Logger\FileLoggerFactory::class,
                'constructorParams' => [$_SERVER['DOCUMENT_ROOT'] . '/local/logs']
            ]
        ]
    ]
];
