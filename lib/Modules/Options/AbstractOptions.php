<?php
namespace Itb\Core\Modules\Options;

abstract class AbstractOptions
{
    private static array $instances = [];

    protected function __construct()
    {
        $moduleId = $this->getModuleId();
        if ($moduleId === '') {
            throw new \InvalidArgumentException('Module ID must be defined.');
        }
        $options = \Bitrix\Main\Config\Option::getForModule($moduleId);
        $this->mapOptions($options);
        $this->validateOptions();
    }

    abstract protected function mapOptions(array $options): void;
    abstract public function getModuleId(): string;

    protected function validateOptions(): void {}

    public static function getInstance(): static
    {
        $class = static::class;
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }
        return self::$instances[$class];
    }
}
