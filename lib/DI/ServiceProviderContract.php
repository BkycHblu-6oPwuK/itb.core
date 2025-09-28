<?php

namespace Itb\Core\DI;

interface ServiceProviderContract
{
    /**
     * Зарегистрировать зависимости в сервис-локаторе
     */
    public function register(): void;
}
