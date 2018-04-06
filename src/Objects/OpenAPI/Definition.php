<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use LaravelRocket\Generator\Objects\Table;
use function ICanBoogie\pluralize;

class Definition
{
    const TYPE_OBJECT = 'object';
    const TYPE_LIST   = 'list';
    const TYPE_MODEL  = 'model';

    /** @var string $name */
    protected $name;

    /** @var \TakaakiMizuno\SwaggerParser\Objects\Base $object */
    protected $object;

    /** @var array $json */
    protected $json;

    /** @var \LaravelRocket\Generator\Objects\Table[] $tables */
    protected $tables;

    /** @var string $type */
    protected $type;

    /** @var \LaravelRocket\Generator\Objects\Table $tablev */
    protected $table;

    /** @var array */
    protected $properties;

    /** @var \TakaakiMizuno\SwaggerParser\Objects\V20\Document */
    protected $osa;

    /** @var string */
    protected $listItemName;

    /**
     * Definition constructor.
     *
     * @param string                                            $name
     * @param \TakaakiMizuno\SwaggerParser\Objects\Base         $object
     * @param \LaravelRocket\Generator\Objects\Definitions      $json
     * @param \TakaakiMizuno\SwaggerParser\Objects\V20\Document $osa
     * @param \LaravelRocket\Generator\Objects\Table[]          $tables
     */
    public function __construct(string $name, $object, $json, $osa, array $tables)
    {
        $this->name   = $name;
        $this->object = $object;
        $this->json   = $json;
        $this->tables = $tables;
        $this->osa    = $osa;

        $this->detectType();
        $this->setProperties();
        if ($this->type === self::TYPE_MODEL) {
            $this->mappingColumns();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        if ($this->getType() !== self::TYPE_MODEL) {
            return '';
        }

        return $this->table->getModelName();
    }

    public function getListItemName(): string
    {
        if ($this->getType() !== self::TYPE_LIST) {
            return '';
        }

        return $this->listItemName;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    protected function detectType()
    {
        $this->type = self::TYPE_OBJECT;

        $allOf = $this->object->allOf;
        if (!empty($allOf)) {
            foreach ($this->object->allOf as $allOf) {
                $check = $allOf->{'$ref'};
                if (!empty($check) && $check === '#/definitions/List') {
                    $this->type = self::TYPE_LIST;
                }

                $properties = $allOf->properties;
                if (!empty($properties)) {
                    foreach ($properties as $key => $property) {
                        if ($key === 'items' && $property->type === 'array') {
                            $reference          = $property->items;
                            $ref                = $reference->{'$ref'};
                            $ref                = str_replace('#/definitions/', '', $ref);
                            $this->listItemName = $ref;
                        }
                    }
                }
            }
        }
        if ($this->type === self::TYPE_OBJECT) {
            $tableCandidateName = pluralize(snake_case($this->name));
            foreach ($this->tables as $table) {
                if ($tableCandidateName === $table->getName()) {
                    $this->type  = self::TYPE_MODEL;
                    $this->table = new Table($table, $this->tables, $this->json);
                }
            }
        }
    }

    protected function setProperties()
    {
        $this->properties = $this->parseObject($this->object);
    }

    protected function parseObject($object)
    {
        $result     = [];
        $allOf      = $object->allOf;
        $properties = $object->properties;
        if (!empty($allOf)) {
            $result = $this->parseAllOf($object->allOf);
        } elseif (!empty($properties)) {
            $result = $this->parseProperties($object->properties);
        }

        return $result;
    }

    protected function parseAllOf($entries)
    {
        $result = [];
        foreach ($entries as $entry) {
            if (!empty($entry->{'$ref'})) {
                $definition = $this->getDefinition($entry->{'$ref'});
                if (!empty($definition)) {
                    $result = array_merge($result, $this->parseObject($definition));
                }
            } elseif (!empty($entry->properties)) {
                $result = array_merge($result, $this->parseProperties($entry->properties));
            }
        }

        return $result;
    }

    protected function parseProperties($properties)
    {
        $result = [];

        if (is_array($properties)) {
            foreach ($properties as $name => $definition) {
                $result[] = [
                    'name'    => $name,
                    'type'    => $definition->type,
                    'default' => $this->getDefaultValue($definition->type),
                ];
            }
        }

        return $result;
    }

    protected function mappingColumns()
    {
        foreach ($this->properties as $index => $property) {
            $column = $this->table->getColumn(snake_case($property['name']));
            if (!empty($column)) {
                $this->properties[$index]['column'] = $column;
            }
        }
    }

    protected function getDefinition($name)
    {
        if (empty($this->osa->definitions)) {
            return null;
        }

        $parts = explode('/', $name);
        $name  = $parts[count($parts) - 1];

        return array_get($this->osa->definitions, $name);
    }

    protected function getDefaultValue($type)
    {
        $defaultValue = 'null';
        switch ($type) {
            case 'string':
                $defaultValue = "''";
                break;
            case 'integer':
                $defaultValue = '0';
                break;
            case 'object':
                $defaultValue = 'null';
                break;
            case 'array':
                $defaultValue = '[]';
                break;
        }

        return $defaultValue;
    }
}
