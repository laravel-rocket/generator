<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use function ICanBoogie\pluralize;

class PathElement
{
    /** @var string */
    protected $element;

    protected $isVariable = false;

    protected $variableName = '';

    protected $isPlural = false;

    /**
     * @param string $path
     * @param string $httpMetod
     *
     * @return static[]
     */
    public static function parsePath($path, $httpMetod)
    {
        $elements       = [];
        $elementStrings = explode('/', $path);
        foreach ($elementStrings as $elementString) {
            if (empty($elementString)) {
                continue;
            }
            $elements[] = new static($elementString, $httpMetod);
        }

        return $elements;
    }

    /**
     * @return bool
     */
    public function isPlural()
    {
        return $this->isPlural;
    }

    /**
     * @return bool
     */
    public function isVariable()
    {
        return $this->isVariable;
    }

    /**
     * @return string
     */
    public function variableName()
    {
        return $this->variableName;
    }

    public function elementName()
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
        if (preg_match('/^{([^}]+])}$/', $this->element, $matches)) {
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
}
