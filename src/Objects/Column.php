<?php
namespace LaravelRocket\Generator\Objects;

class Column
{
    protected $uneditables     = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
    protected $unlistables     = ['id', 'remember_token', 'created_at', 'updated_at', 'password', 'deleted_at'];
    protected $unlistableTypes = ['text', 'mediumtext', 'longtext'];
    protected $unshowables     = ['deleted_at', 'remember_token', 'password'];

    /** @var \TakaakiMizuno\MWBParser\Elements\Column|\Doctrine\DBAL\Schema\Column */
    protected $column;

    /** @var bool */
    protected $hasRelation = false;

    /**
     * @var string
     */
    protected $editFieldType = 'string';

    /**
     * @var array
     */
    protected $editFieldOptions = [];

    /**
     * @var array
     */
    protected $definition = [];

    /**
     * Column constructor.
     *
     * @param \TakaakiMizuno\MWBParser\Elements\Column|\Doctrine\DBAL\Schema\Column $column
     * @param \TakaakiMizuno\MWBParser\Elements\Table|null                          $table
     * @param array                                                                 $definition
     */
    public function __construct($column, $table = null, $definition = [])
    {
        $this->column     = $column;
        $this->definition = $definition;

        if (!empty($table)) {
            foreach ($table->getForeignKey() as $foreignKey) {
                $columns          = $foreignKey->getColumns();
                $referenceColumns = $foreignKey->getReferenceColumns();
                if (count($columns) == 0) {
                    continue;
                }
                if (count($referenceColumns) == 0) {
                    continue;
                }
                if ($columns[0]->getName() === $column->getName()) {
                    $this->hasRelation = true;
                    break;
                }
            }
        }

        $this->setEditFieldType();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->column->getName();
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return title_case(str_replace('_', ' ', $this->column->getName()));
    }

    /**
     * @return string
     */
    public function getAPIName()
    {
        return camel_case($this->getName());
    }

    /**
     * @return string
     */
    public function getQueryName()
    {
        return snake_case($this->getName());
    }

    /**
     * @return string
     */
    public function getType()
    {
        $type = $this->column->getType();

        if (get_class($this->column) == \Doctrine\DBAL\Schema\Column::class || !is_string($type)) {
            return $type->getName();
        }

        return $type;
    }

    /**
     * @return string
     */
    public function getEditFieldType()
    {
        return $this->editFieldType;
    }

    /**
     * @return array
     */
    public function getEditFieldOptions()
    {
        return $this->editFieldOptions;
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        return !in_array($this->column->getName(), $this->uneditables);
    }

    /**
     * @return bool
     */
    public function isListable(): bool
    {
        if (in_array($this->getName(), $this->unlistables)) {
            return false;
        }

        if (in_array($this->getType(), $this->unlistableTypes)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isShowable(): bool
    {
        return !in_array($this->column->getName(), $this->unshowables);
    }

    /**
     * @return bool
     */
    public function isAPIReturnable(): bool
    {
        if (in_array($this->getName(), $this->unshowables)) {
            return false;
        }

        return $this->isEditable() || $this->isShowable() || $this->isListable();
    }

    /**
     * @return bool
     */
    public function hasRelation(): bool
    {
        return $this->hasRelation;
    }

    /**
     * @return bool
     */
    public function isQueryable(): bool
    {
        if (ends_with($this->getName(), 'name')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isBoolean(): bool
    {
        switch ($this->getType()) {
            case 'tinyint':
                return true;
            case 'bigint':
            case 'int':
                if (starts_with($this->getName(), 'is_') || starts_with($this->getName(), 'has_')) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isTimestamp(): bool
    {
        switch ($this->getType()) {
            case 'timestamp':
            case 'timestamp_f':
                return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isUnixTimestamp(): bool
    {
        switch ($this->getType()) {
            case 'int':
                if (ends_with($this->getName(), '_at')) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isNumber(): bool
    {
        if ($this->isBoolean() || $this->isUnixTimestamp()) {
            return false;
        }
        switch ($this->getType()) {
            case 'bigint':
            case 'int':
            case 'decimal':
                return true;
                break;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isString(): bool
    {
        switch ($this->getType()) {
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return true;
                break;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        if (get_class($this->column) == \Doctrine\DBAL\Schema\Column::class) {
            return !$this->column->getNotnull();
        }

        return $this->column->isNullable();
    }

    /**
     * @return mixed|null|string
     */
    public function getDefaultValue()
    {
        if (get_class($this->column) == \Doctrine\DBAL\Schema\Column::class) {
            return $this->column->getDefault();
        }

        return $this->column->getDefaultValue();
    }

    /**
     * @return string
     */
    public function getDefaultAPIResponse()
    {
        $defaultValue = ''.$this->getDefaultValue();
        if (empty($defaultValue)) {
            if ($this->isNullable()) {
                $defaultValue = 'null';
            } elseif ($this->isBoolean()) {
                $defaultValue = 'false';
            } elseif ($this->isUnixTimestamp() || $this->isNumber()) {
                $defaultValue = '0';
            } elseif ($this->isString()) {
                $defaultValue = "''";
            } else {
                $defaultValue = 'null';
            }
        }

        return $defaultValue;
    }

    protected function setEditFieldType()
    {
        $name = $this->getName();
        $type = empty($this->definition) ? '' : strtolower(array_get($this->definition, 'type', $this->getType()));

        if (starts_with($type, 'bool') || (starts_with($name, 'is_') ||
                starts_with($name, 'has_')) && ($type === 'int' || $type === 'tinyint')) {
            $this->editFieldType    = 'boolean';
            $this->editFieldOptions = [];

            return;
        }

        if (ends_with($name, 'image_id') && ($type === 'int' || $type === 'bigint')) {
            $this->editFieldType    = 'image';
            $this->editFieldOptions = [];

            return;
        }

        if (ends_with($name, 'file_id') && ($type === 'int' || $type === 'bigint')) {
            $this->editFieldType    = 'file';
            $this->editFieldOptions = [];

            return;
        }

        if (ends_with($name, 'type') || $type === 'type') {
            $this->editFieldType    = 'select';
            $this->editFieldOptions = array_get($definitions, 'options', []);

            return;
        }

        if ($name === 'password') {
            $this->editFieldType    = 'password';
            $this->editFieldOptions = [];

            return;
        }

        if (ends_with($name, 'country_code') && $type === 'varchar') {
            $this->editFieldType    = 'country';
            $this->editFieldOptions = [];

            return;
        }

        if (ends_with($name, 'currency_code') && $type === 'varchar') {
            $this->editFieldType    = 'currency';
            $this->editFieldOptions = [];

            return;
        }

        if ($type === 'date') {
            $this->editFieldType    = 'date';
            $this->editFieldOptions = [];

            return;
        }

        if (ends_with($name, 'gender') && $type === 'varchar') {
            $this->editFieldType    = 'select';
            $this->editFieldOptions = array_get($definitions, 'options', [[
                'name'  => 'Male',
                'value' => 'male',
            ], [
                'name'  => 'Female',
                'value' => 'female',
            ]]);

            return;
        }

        if (in_array($type, ['text', 'mediumtext', 'longtext', 'smalltext', 'tinytext'])) {
            $this->editFieldType    = 'textarea';
            $this->editFieldOptions = [];

            return;
        }

        $this->editFieldType    = 'text';
        $this->editFieldOptions = [];

        return;
    }

    /**
     * @param string|null $previousColumnName
     *
     * @return string
     */
    public function generateAddMigration($previousColumnName = null)
    {
        $column  = $this->column;
        $postfix = '';
        switch ($this->getType()) {
            case 'tinyint':
                $type = 'boolean';
                break;
            case 'bigint':
                $type = $column->isUnsigned() ? 'unsignedBigInteger' : 'bigInteger';
                break;
            case 'int':
                $type = $column->isUnsigned() ? 'unsignedInteger' : 'integer';
                break;
            case 'timestamp':
            case 'timestamp_f':
                $type = 'timestamp';
                break;
            case 'date':
                $type = 'date';
                break;
            case 'varchar':
            case 'string':
                $type = 'string';
                if ($column->getLength() != 255) {
                    $postfix = ', '.$column->getLength();
                }
                break;
            case 'text':
                $type = 'text';
                break;
            case 'mediumtext':
                $type = 'mediumtext';
                break;
            case 'longtext':
                $type = 'longtext';
                break;
            case 'decimal':
                $type    = 'decimal';
                $postfix = ', '.$column->getPrecision().', '.$column->getScale();
                break;
            default:
                $type = 'unknown';
        }
        $line = '$table->'.$type.'(\''.$this->getName().'\''.$postfix.')';

        if ($this->isNullable()) {
            $line .= '->nullable()';
        }
        if (!is_null($this->getDefaultValue()) && $this->getDefaultValue() !== '') {
            $defaultValue = $this->getDefaultValue();
            if (starts_with($defaultValue, "'") && ends_with($defaultValue, "'")) {
                $defaultValue = substr($defaultValue, 1, strlen($defaultValue) - 2);
            }
            switch ($this->getType()) {
                case 'tinyint':
                    $defaultValue = (int) $defaultValue == 1 ? 'true' : 'false';
                    $line .= '->default('.$defaultValue.')';
                    break;
                case 'bigint':
                case 'int':
                    $line .= '->default('.((int) $defaultValue).')';
                    break;
                case 'timestamp':
                case 'timestamp_f':
                case 'date':
                case 'varchar':
                case 'text':
                case 'mediumtext':
                case 'longtext':
                    $line .= '->default(\''.$defaultValue.'\')';
                    break;
                case 'decimal':
                    $line .= '->default('.((float) $defaultValue).')';
                    break;
                default:
                    $line .= '->default(\''.$defaultValue.'\')';
                    break;
            }
        }
        if (!empty($previousColumnName)) {
            $line .= '->after(\''.$previousColumnName.'\')';
        }

        return $line;
    }

    /**
     * @return string
     */
    public function generateDropMigration()
    {
        $line = '$table->dropColumn(\''.$this->column->getName().'\')';

        return $line;
    }
}
