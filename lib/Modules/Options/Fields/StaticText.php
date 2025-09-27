<?
namespace Itb\Core\Modules\Options\Fields;

class StaticText extends Field
{
    public function __construct(string $text, string $value)
    {
        $this->setText($text);
        $this->setDefaultValue($value);
    }

    protected function getType() : string
    {
        return 'statictext';
    }
}