<?php
namespace LaravelRocket\Generator\Objects;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LaravelRocket\Generator\Helpers\StringHelper;

class Column
{
    protected $uneditables     = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
    protected $unlistables     = ['id', 'remember_token', 'created_at', 'updated_at', 'password', 'deleted_at'];
    protected $unlistableTypes = ['text', 'mediumtext', 'longtext'];
    protected $unshowables     = ['deleted_at', 'remember_token', 'password'];

    /** @var \TakaakiMizuno\MWBParser\Elements\Column|\Doctrine\DBAL\Schema\Column */
    protected $column;

    /** @var bool */
    protected $relation = false;

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
                    $this->relation = new Relation(
                        Relation::TYPE_BELONGS_TO,
                        $table->getName(),
                        $column,
                        $foreignKey->getReferenceTableName(),
                        $referenceColumns[0]
                    );
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
    public function getKeyName()
    {
        return $this->getAPIName();
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return Str::title(str_replace('_', ' ', $this->column->getName()));
    }

    /**
     * @return string
     */
    public function getAPIName()
    {
        $name = $this->getName();
        //        if ($this->hasRelation() && $this->relation->getType() === Relation::TYPE_BELONGS_TO && Str::endsWith($name, '_id')) {
        //            $name = substr($name, 0, strlen($name) - 3);
        //        }

        return Str::camel($name);
    }

    /**
     * @return string
     */
    public function getQueryName()
    {
        $name = $this->getName();
        //        if ($this->hasRelation() && $this->relation->getType() === Relation::TYPE_BELONGS_TO && Str::endsWith($name, '_id')) {
        if ($this->hasFileRelation() || $this->hasImageRelation()) {
            $name = substr($name, 0, strlen($name) - 3);
        }

        return Str::snake($name);
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
     * @return bool
     */
    public function hasFileRelation()
    {
        return $this->editFieldType === 'image' || $this->editFieldType === 'file';
    }

    /**
     * @return bool
     */
    public function hasImageRelation()
    {
        return $this->editFieldType === 'image';
    }

    /**
     * @return bool|\LaravelRocket\Generator\Objects\Relation
     */
    public function getRelation()
    {
        return $this->relation;
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
     * @return string
     */
    public function getPresentation()
    {
        if ($this->isStatus() || $this->isType()) {
            return 'badge';
        }

        return 'normal';
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        if ($this->hasImageRelation() || $this->hasFileRelation()) {
            return false;
        }

        return !in_array($this->column->getName(), $this->uneditables);
    }

    /**
     * @return bool
     */
    public function isListable(): bool
    {
        if ($this->isPrimaryKey()) {
            return true;
        }

        if (in_array($this->getName(), $this->unlistables)) {
            return false;
        }

        if (in_array($this->getType(), $this->unlistableTypes)) {
            return false;
        }

        if ($this->hasRelation()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isShowable(): bool
    {
        if ($this->isPrimaryKey()) {
            return true;
        }

        if (in_array($this->column->getName(), $this->unshowables)) {
            return false;
        }

        if ($this->hasRelation()) {
            return false;
        }

        return true;
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
        return !empty($this->relation);
    }

    /**
     * @return bool
     */
    public function isQueryable(): bool
    {
        if (Str::endsWith($this->getName(), 'name')) {
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
                if (Str::startsWith($this->getName(), 'is_') || Str::startsWith($this->getName(), 'has_')) {
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
                if (Str::endsWith($this->getName(), '_at')) {
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
    public function isJson(): bool
    {
        switch ($this->getType()) {
            case 'text':
            case 'mediumtext':
            case 'longtext':
                if (in_array($this->getName(), [
                    'json',
                    'data',
                ])) {
                    return true;
                }
                break;
            case 'json':
                return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasOptionConfiguration(): bool
    {
        return StringHelper::hasPostFix($this->getName(), [
            'role',
            'status',
        ]);
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
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->isString() && StringHelper::hasPostFix($this->getName(), 'status');
    }

    /**
     * @return bool
     */
    public function isType(): bool
    {
        return $this->isString() && StringHelper::hasPostFix($this->getName(), 'type');
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
        $type = empty($this->definition) ? $this->getType() : strtolower(Arr::get($this->definition, 'type', $this->getType()));

        $this->editFieldType    = 'text';
        $this->editFieldOptions = [];

        if (Str::startsWith($type, 'bool') || (Str::startsWith($name, 'is_') ||
                Str::startsWith($name, 'has_')) && ($type === 'int' || $type === 'tinyint')) {
            $this->editFieldType = 'boolean';

            return;
        }

        if (Str::endsWith($name, 'image_id') && ($type === 'int' || $type === 'bigint')) {
            $this->editFieldType = 'image';

            return;
        }

        if (Str::endsWith($name, 'file_id') && ($type === 'int' || $type === 'bigint')) {
            $this->editFieldType = 'file';

            return;
        }

        if (Str::endsWith($name, 'type') || $type === 'type') {
            $this->editFieldType    = 'select_single';
            $this->editFieldOptions = Arr::get($this->definition, 'options', []);

            return;
        }

        if ($this->isUnixTimestamp() || $this->isTimestamp()) {
            $this->editFieldType = 'datetime';

            return;
        }

        if ($name === 'password') {
            $this->editFieldType = 'password';

            return;
        }

        if ($name === 'email' || Str::endsWith($name, '_email')) {
            $this->editFieldType = 'email';

            return;
        }

        if (Str::endsWith($name, 'country_code') && $type === 'varchar') {
            if (Str::contains($name, 'phone')) {
                $this->editFieldType = 'phone_country';
            } else {
                $this->editFieldType = 'country';
            }

            return;
        }

        if ($type === 'date') {
            $this->editFieldType = 'date';

            return;
        }

        if (Str::endsWith($name, 'gender') && $type === 'varchar') {
            $this->editFieldType    = 'select_single';
            $this->editFieldOptions = Arr::get($this->definition, 'options', [[
                'name'  => 'Male',
                'value' => 'male',
            ], [
                'name'  => 'Female',
                'value' => 'female',
            ]]);

            return;
        }

        if (in_array($type, ['text', 'mediumtext', 'longtext', 'smalltext', 'tinytext'])) {
            $this->editFieldType = 'textarea';

            return;
        }

        if (in_array($type, ['json'])) {
            $this->editFieldType = 'json';

            return;
        }

        if ($this->hasRelation()) {
            if ($this->relation->getType() === Relation::TYPE_BELONGS_TO && Str::endsWith($name, '_id')) {
                $this->editFieldType = 'select_single';
            }
        }

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
                $isUnsigned = false;
                if (method_exists($column, 'isUnsigned')) {
                    $isUnsigned = $column->isUnsigned();
                } elseif (method_exists($column, 'getUnsigned')) {
                    $isUnsigned = $column->getUnsigned();
                }
                $type = $isUnsigned ? 'unsignedBigInteger' : 'bigInteger';
                break;
            case 'int':
                $isUnsigned = false;
                if (method_exists($column, 'isUnsigned')) {
                    $isUnsigned = $column->isUnsigned();
                } elseif (method_exists($column, 'getUnsigned')) {
                    $isUnsigned = $column->getUnsigned();
                }
                $type = $isUnsigned ? 'unsignedInteger' : 'integer';
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
            case 'json':
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
            if (Str::startsWith($defaultValue, "'") && Str::endsWith($defaultValue, "'")) {
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

    /**
     * @param string       $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    protected function hasPostFix($haystack, $needles)
    {
        if (!is_array($needles)) {
            $needles = [$needles];
        }

        foreach ($needles as $needle) {
            if ($haystack === $needle || Str::endsWith($haystack, '_'.$needle)) {
                return true;
            }
        }

        return false;
    }

    public function isPrimaryKey()
    {
        if ($this->column->getAutoincrement() && $this->getName() == 'id') {
            return true;
        }

        return false;
    }
}
