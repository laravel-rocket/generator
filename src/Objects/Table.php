<?php
namespace LaravelRocket\Generator\Objects;

use function ICanBoogie\singularize;

class Table
{
    /** @var \TakaakiMizuno\MWBParser\Elements\Table */
    protected $table = null;

    /** @var bool */
    protected $isRelationTable = false;

    /** @var \LaravelRocket\Generator\Objects\Column[] */
    protected $columns = [];

    /** @var \LaravelRocket\Generator\Objects\Column[] */
    protected $columnHash = [];

    /** @var \LaravelRocket\Generator\Objects\Relation[] */
    protected $relations = [];

    /** @var \LaravelRocket\Generator\Objects\Relation[] */
    protected $relationHash = [];

    /** @var \LaravelRocket\Generator\Objects\Definitions|null */
    protected $json;

    /**
     * Table constructor.
     *
     * @param \TakaakiMizuno\MWBParser\Elements\Table      $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]    $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     */
    public function __construct($table, $tables, $json = null)
    {
        $this->table = $table;
        $this->json  = $json;
        $columns     = $table->getColumns();
        foreach ($columns as $column) {
            $columnDefinition                           = empty($this->json) ? [] : $this->json->getColumnDefinition($table->getName(), $column->getName());
            $columnObject                               = new Column($column, $table, $columnDefinition);
            $this->columns[]                            = $columnObject;
            $this->columnHash[$columnObject->getName()] = $columnObject;
        }
        $this->isRelationTable = $this->detectRelationTable($table);
        $this->setRelations($tables);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->table->getName();
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return ucfirst(camel_case(singularize($this->table->getName())));
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return title_case(str_replace('_', ' ', $this->table->getName()));
    }

    public function getPathName()
    {
        return kebab_case($this->table->getName());
    }

    /**
     * @return \LaravelRocket\Generator\Objects\Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn(string $name): bool
    {
        return array_key_exists($name, $this->columnHash);
    }

    /**
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\Column|null
     */
    public function getColumn($name)
    {
        return array_get($this->columnHash, $name);
    }

    /**
     * @return \LaravelRocket\Generator\Objects\Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRelation(string $name): bool
    {
        return array_key_exists($name, $this->relationHash);
    }

    /**
     * @param string $name
     *
     * @return \LaravelRocket\Generator\Objects\Relation|null
     */
    public function getRelation($name)
    {
        return array_get($this->relationHash, $name);
    }

    /**
     * @return bool
     */
    public function isRelationTable(): bool
    {
        return $this->isRelationTable;
    }

    /**
     * @return bool
     */
    public function isAuthTable(): bool
    {
        return $this->hasColumn('remember_token');
    }

    public function getTestColumn()
    {
        $variables = [
            'testColumnName' => '',
            'testData'       => '',
        ];

        $found = false;
        foreach ($this->table->getColumns() as $column) {
            if (in_array($column->getName(), ['remember_token', 'id', 'deleted_at', 'created_at', 'updated_at'])) {
                continue;
            }
            if (in_array($column->getType(), ['varchar', 'text', 'mediumtext', 'longtext'])) {
                $variables['testColumnName'] = $column->getName();
                $variables['testData']       = 'str_random(10)';
                $found                       = true;
                break;
            }
        }

        if (!$found) {
            foreach ($this->table->getColumns() as $column) {
                if (in_array($column->getName(), ['remember_token', 'id', 'deleted_at', 'created_at', 'updated_at'])) {
                    continue;
                }
                if (in_array($column->getType(), ['int', 'bigint', 'decimal'])) {
                    $variables['testColumnName'] = $column->getName();
                    $variables['testData']       = 'rand(50,100)';
                    break;
                }
            }
        }

        if (!$found) {
            foreach ($this->table->getColumns() as $column) {
                if (in_array($column->getName(), ['remember_token', 'id', 'deleted_at', 'created_at', 'updated_at'])) {
                    continue;
                }
                $variables['testColumnName'] = $column->getName();
                $variables['testData']       = 'rand(50,100)';
                break;
            }
        }

        return $variables;
    }

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table[] $tables
     */
    protected function setRelations($tables)
    {
        $relations = [];
        $names     = [];

        // Extract BelongsTo Relationship
        foreach ($this->table->getForeignKey() as $foreignKey) {
            $columns          = $foreignKey->getColumns();
            $referenceColumns = $foreignKey->getReferenceColumns();
            if (count($columns) == 0) {
                continue;
            }
            if (count($referenceColumns) == 0) {
                continue;
            }

            $column          = $columns[0];
            $referenceColumn = $referenceColumns[0];

            $relation = new Relation(
                Relation::TYPE_BELONGS_TO,
                $this->table->getName(),
                $column,
                $foreignKey->getReferenceTableName(),
                $referenceColumn
            );

            if (in_array($relation->getName(), $names)) {
                continue;
            }

            $relations[] = $relation;
            $names[]     = $relation->getName();
        }

        foreach ($tables as $table) {
            if ($this->table->getName() === $table->getName()) {
                continue;
            }
            $relationTableColumns = ['', ''];
            $relationTableNames   = ['', ''];

            $hasRelation = false;

            foreach ($table->getForeignKey() as $foreignKey) {
                $columns          = $foreignKey->getColumns();
                $referenceColumns = $foreignKey->getReferenceColumns();
                if (count($columns) == 0) {
                    continue;
                }
                if (count($referenceColumns) == 0) {
                    continue;
                }
                $column          = $columns[0];
                $referenceColumn = $referenceColumns[0];

                $relation = new Relation(
                    $foreignKey->hasMany() ? Relation::TYPE_HAS_MANY : Relation::TYPE_HAS_ONE,
                    $this->table->getName(),
                    $referenceColumn,
                    $table->getName(),
                    $column
                );

                if ($this->table->getName() === $foreignKey->getReferenceTableName() && !in_array($relation->getName(), $names)) {
                    $relations[]             = $relation;
                    $relationTableColumns[0] = $column;
                    $relationTableNames[0]   = $foreignKey->getReferenceTableName();
                    $hasRelation             = true;
                    $names[]                 = $relation->getName();
                } else {
                    // Store for BelongsToMany Relations
                    $relationTableColumns[1] = $column;
                    $relationTableNames[1]   = $foreignKey->getReferenceTableName();
                }
            }

            $relation = new Relation(
                Relation::TYPE_BELONGS_TO_MANY,
                $this->table->getName(),
                $relationTableColumns[0],
                $relationTableNames[1],
                $relationTableColumns[1],
                $table->getName()
            );
            if ($hasRelation && $this->detectRelationTable($table) && !in_array($relation->getName(), $names)) {
                $relations[] = $relation;
                $names[]     = $relation->getName();
            }
        }

        $this->relations = $relations;

        foreach ($relations as $relation) {
            $this->relationHash[$relation->getName()] = $relation;
        }
    }

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table $table
     *
     * @return bool
     */
    protected function detectRelationTable($table): bool
    {
        $foreignKeys = $table->getForeignKey();
        if (count($foreignKeys) != 2) {
            return false;
        }
        $tables = [];
        foreach ($foreignKeys as $foreignKey) {
            if (!$foreignKey->hasMany()) {
                return false;
            }
            $tables[] = $foreignKey->getReferenceTableName();
        }
        if ($table->getName() === implode('_', [singularize($tables[0]), $tables[1]])
            || $table->getName() === implode('_', [singularize($tables[1]), $tables[0]])) {
            return true;
        }

        return false;
    }

    public function getIconClass()
    {
        $mappings = [
            'admin_users'   => 'fa fa-user-secret',
            'users'         => 'fa fa-users',
            'images'        => 'fa fa-images',
            'companies'     => 'fa fa-building',
            'schedules'     => 'fa fa-calendar',
            'locations'     => 'fa fa-map-marker',
            'notifications' => 'fa fa-bell',
            'tags'          => 'fa fa-tags',
            'articles'      => 'fa fa-pencil',
        ];

        $name = $this->getName();
        foreach ($mappings as $mapping => $iconClass) {
            if (ends_with($name, $mapping)) {
                return $iconClass;
            }
        }

        return 'fa fa-file';
    }
}
