<?
namespace Itb\Core\Modules\Options\Fields;

abstract class Field
{
    protected $name;
    protected $label;
    protected $text;
    protected $isDisabled = false;
    protected $defaultValue;
    protected $extraOptions = [];

    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    public final function getOptions()
    {
        return [$this->name, $this->text, $this->defaultValue, $this->getTypeAndExtraOptionsArray(), $this->getDisabled()];
    }

    public function setDefaultValue(string|array $defaultValue)
    {
        if(is_array($defaultValue)){
            $defaultValue = implode(',', $defaultValue);
        }
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function setText(string $text)
    {
        $this->text = $text;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    public function isDisabled()
    {
        $this->isDisabled = true;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    private function getDisabled()
    {
        return $this->isDisabled ? 'Y' : 'N';
    }

    protected function getTypeAndExtraOptionsArray() : array
    {
        return [$this->getType(), $this->getExtraOptions()];
    }

    protected function getExtraOptions() : string|array
    {
        return $this->extraOptions;
    }

    abstract protected function getType() : string; // bitrix/modules/main/admin/settings.php функция renderInput отрисовывает поля по типам
}
