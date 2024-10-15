<? 
namespace Itb\Core\Modules\Options\Fields;

class MultiSelect extends Field
{

    public function __construct(string $name, string $text, array $values)
    {
        parent::__construct($name, $text);
        $this->extraOptions = $values;
    }

    protected function getType() : string
    {
        return 'multiselectbox';
    }
}
