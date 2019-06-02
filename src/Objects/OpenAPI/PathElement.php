<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use function ICanBoogie\pluralize;
use function ICanBoogie\singularize;
use Illuminate\Support\Str;

class PathElement
{
    /** @var string */
    protected $element;

    /** @var bool */
    protected $isVariable = false;

    /** @var string */
    protected $variableName = '';

    /** @var bool */
    protected $isPlural = false;

    /**
     * @param string $path
     * @param string $httpMethod
     *
     * @return static[]
     */
    public static function parsePath($path, $httpMethod)
    {
        $elements       = [];
        $elementStrings = explode('/', $path);
        foreach ($elementStrings as $elementString) {
            if (empty($elementString)) {
                continue;
            }
            $elements[] = new static($elementString, $httpMethod);
        }

        return $elements;
    }

    /**
     * @return bool
     */
    public function isPlural(): bool
    {
        return $this->isPlural;
    }

    /**
     * @return bool
     */
    public function isVariable(): bool
    {
        return $this->isVariable;
    }

    /**
     * @return string
     */
    public function variableName(): string
    {
        return $this->variableName;
    }

    /**
     * @return bool
     */
    public function isPrimaryKeyVariable(): bool
    {
        return ($this->isVariable && $this->variableName === 'id') ? true : false;
    }

    /**
     * @return string
     */
    public function elementName(): string
    {
        return $this->element;
    }

    /**
     * PathElement constructor.
     *
     * @param string $element
     * @param string $httpMethod
     */
    public function __construct($element, $httpMethod)
    {
        $this->element = $element;
        $this->detectVariable();
        $this->detectPlural();
    }

    protected function detectVariable()
    {
        if (preg_match('/^\{([^}]+)\}$/', $this->element, $matches)) {
            $this->isVariable   = true;
            $this->variableName = $matches[1];
        }
    }

    protected function detectPlural()
    {
        if (pluralize($this->element) === $this->element) {
            $this->isPlural = true;
        }
        if ($this->element === 'me') {
            $this->isPlural = true;
        }
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return ucfirst(Str::camel(singularize($this->element)));
    }
}
