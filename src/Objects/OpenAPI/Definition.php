<?php
namespace LaravelRocket\Generator\Objects\OpenAPI;

use function ICanBoogie\pluralize;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelRocket\Generator\Objects\Table;

class Definition
{
    const TYPE_OBJECT = 'object';
    const TYPE_LIST   = 'list';
    const TYPE_MODEL  = 'model';

    protected const TABLE_ALIASES = [
        'Me'    => 'users',
        'Image' => 'files',
    ];

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

    /** @var \LaravelRocket\Generator\Objects\OpenAPI\Definition */
    protected $listItem;

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

    /**
     * @return string
     */
    public function getListItemName(): string
    {
        if ($this->getType() !== self::TYPE_LIST) {
            return '';
        }

        return $this->listItemName;
    }

    /**
     * @return \LaravelRocket\Generator\Objects\OpenAPI\Definition|null
     */
    public function getListItem()
    {
        if ($this->getType() !== self::TYPE_LIST) {
            return null;
        }

        return $this->listItem;
    }

    /**
     * @param \LaravelRocket\Generator\Objects\OpenAPI\OpenAPISpec $spec
     */
    public function setListResponseItem($spec)
    {
        if ($this->getType() !== self::TYPE_LIST) {
            return;
        }
        $this->listItem = $spec->findDefinition($this->listItemName);
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
            $tableCandidateName = pluralize(Str::snake($this->name));
            if (array_key_exists($this->name, self::TABLE_ALIASES)) {
                $tableCandidateName = self::TABLE_ALIASES[$this->name];
            }
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
            $ref        = $entry->{'$ref'};
            $properties = $entry->properties;
            if (!empty($ref)) {
                $definition = $this->getDefinition($ref);
                if (!empty($definition)) {
                    $result = array_merge($result, $this->parseObject($definition));
                }
            } elseif (!empty($properties)) {
                $result = array_merge($result, $this->parseProperties($properties));
            }
        }

        return $result;
    }

    protected function parseProperties($properties)
    {
        $result = [];

        if (is_array($properties)) {
            foreach ($properties as $name => $definition) {
                $reference = null;
                if ($definition->type === 'object') {
                    $reference = $definition->{'$ref'};
                } elseif ($definition->type === 'array') {
                    $reference = $definition->items->{'$ref'};
                }

                if (!empty($reference)) {
                    $parts     = explode('/', $reference);
                    $reference = $parts[count($parts) - 1];
                }

                $result[] = [
                    'name'       => $name,
                    'type'       => $definition->type,
                    'default'    => $this->getDefaultValue($definition->type),
                    'cast'       => $this->getCast($definition->type),
                    'definition' => $reference,
                ];
            }
        }

        return $result;
    }

    protected function mappingColumns()
    {
        foreach ($this->properties as $index => $property) {
            $column = $this->table->getColumn(Str::snake($property['name']));
            if (!empty($column)) {
                $this->properties[$index]['column'] = $column;
                continue;
            }

            $relation = $this->table->getRelation(Str::camel($property['name']));
            if (!empty($relation)) {
                $this->properties[$index]['relation'] = $relation;
                continue;
            }

            $relation = $this->table->getRelation(Str::camel($property['definition']));
            if (!empty($relation)) {
                $this->properties[$index]['relation'] = $relation;
                continue;
            }

            $relation = $this->table->getRelation(Str::camel(pluralize($property['definition'])));
            if (!empty($relation)) {
                $this->properties[$index]['relation'] = $relation;
                continue;
            }
        }
    }

    protected function getDefinition($name)
    {
        $definition = $this->osa->definitions;
        if (empty($definition)) {
            return null;
        }

        $parts = explode('/', $name);
        $name  = $parts[count($parts) - 1];

        return Arr::get($definition, $name);
    }

    protected function getDefaultValue($type)
    {
        $defaultValue = 'null';
        switch ($type) {
            case 'string':
                $defaultValue = "''";
                break;
            case 'integer':
            case 'number':
                $defaultValue = '0';
                break;
            case 'boolean':
                $defaultValue = 'false';
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

    protected function getCast($type)
    {
        $cast = '';
        switch ($type) {
            case 'string':
                $cast = '(string)';
                break;
            case 'integer':
                $cast = '(int)';
                break;
            case 'number':
                $cast = '(float)';
                break;
            case 'boolean':
                $cast = '(boolean)';
                break;
        }

        return $cast;
    }
}
