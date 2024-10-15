<?
namespace Itb\Core\Modules\Options\Fields;

class Checkbox extends Field
{
    protected function getType() : string
    {
        return 'checkbox';
    }

    public function setDefaultValue($defaultValue)
    {
        return $this;
    }

    public function isChecked()
    {
        $this->defaultValue = 'Y';
        return $this;
    }
}
