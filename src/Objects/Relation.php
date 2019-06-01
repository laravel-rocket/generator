<?php
namespace LaravelRocket\Generator\Objects;

use Illuminate\Support\Str;
use LaravelRocket\Generator\Helpers\StringHelper;
use function ICanBoogie\singularize;

class Relation
{
    protected $uneditables = ['passwordResets', 'notifications'];
    protected $unlistables = ['passwordResets', 'notifications'];
    protected $unshowables = ['passwordResets', 'notifications'];

    protected $name = '';
    protected $type = '';

    /**
     * @var null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    protected $column = null;
    /**
     * @var null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    protected $referenceColumn = null;

    protected $tableName             = '';
    protected $referenceTableName    = '';
    protected $intermediateTableName = '';

    /**
     * @var string
     */
    protected $editFieldType = 'string';

    /**
     * @var array
     */
    protected $editFieldOptions = [];

    const TYPE_BELONGS_TO      = 'belongsTo';
    const TYPE_HAS_MANY        = 'hasMany';
    const TYPE_HAS_ONE         = 'hasOne';
    const TYPE_BELONGS_TO_MANY = 'belongsToMany';

    const EDIT_TYPE_CHECKBOX      = 'checkbox';
    const EDIT_TYPE_RADIO_BUTTON  = 'radio_button';
    const EDIT_TYPE_SELECT_SINGLE = 'select_single';
    const EDIT_TYPE_SELECT_MULTI  = 'select_multi';
    const EDIT_TYPE_IMAGE         = 'image';
    const EDIT_TYPE_FILE          = 'file';

    /**
     * Relation constructor.
     *
     * @param string                                   $type
     * @param string                                   $tableName
     * @param \TakaakiMizuno\MWBParser\Elements\Column $column
     * @param string                                   $referenceTableName
     * @param \TakaakiMizuno\MWBParser\Elements\Column $referenceColumn
     * @param string                                   $intermediateTableName
     */
    public function __construct($type, $tableName, $column, $referenceTableName, $referenceColumn, $intermediateTableName = '')
    {
        $this->type                  = $type;
        $this->tableName             = $tableName;
        $this->column                = $column;
        $this->referenceTableName    = $referenceTableName;
        $this->referenceColumn       = $referenceColumn;
        $this->intermediateTableName = $intermediateTableName;

        $this->setName();
        $this->editFieldType = $this->detectEditType();
    }

    protected function setName()
    {
        switch ($this->type) {
            case self::TYPE_BELONGS_TO:
                $this->name = Str::camel(preg_replace('/_id$/', '', $this->column->getName()));

                return;
            case self::TYPE_HAS_MANY:
                $this->name = Str::camel($this->referenceTableName);

                return;
            case self::TYPE_HAS_ONE:
                $this->name = Str::camel(singularize($this->referenceTableName));

                return;
            case self::TYPE_BELONGS_TO_MANY:
                $this->name = Str::camel($this->referenceTableName);

                return;
        }

        $this->name = Str::camel($this->referenceTableName);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * @return string
     */
    public function getReferenceTableName(): string
    {
        return $this->referenceTableName;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return Str::title(str_replace('_', ' ', Str::snake($this->getName())));
    }

    /**
     * @return null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @return null|\TakaakiMizuno\MWBParser\Elements\Column
     */
    public function getReferenceColumn()
    {
        return $this->referenceColumn;
    }

    /**
     * @return bool
     */
    public function isRoles(): bool
    {
        return $this->getType() === self::TYPE_HAS_MANY && StringHelper::hasPostFix($this->getName(), 'roles');
    }

    /**
     * @return bool
     */
    public function isTypes(): bool
    {
        return $this->getType() === self::TYPE_HAS_MANY && StringHelper::hasPostFix($this->getName(), 'types');
    }

    /**
     * @return bool
     */
    public function isStatues(): bool
    {
        return $this->getType() === self::TYPE_HAS_MANY && StringHelper::hasPostFix($this->getName(), 'statuses');
    }

    /**
     * @return bool
     */
    public function isEditable(): bool
    {
        if (StringHelper::hasPostFix($this->getName(), $this->uneditables)) {
            return false;
        }

        if ($this->getType() === self::TYPE_BELONGS_TO) {
            return false;
        }

        return $this->isRoles() || $this->isTypes();
    }

    /**
     * @return bool
     */
    public function isListable(): bool
    {
        return !StringHelper::hasPostFix($this->getName(), $this->unlistables)
            && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
    }

    /**
     * @return bool
     */
    public function isShowable(): bool
    {
        return !StringHelper::hasPostFix($this->getName(), $this->unshowables)
            && ($this->getType() === self::TYPE_BELONGS_TO || $this->isRoles() || $this->isTypes());
    }

    /**
     * @return bool
     */
    public function shouldIncludeInAPI(): bool
    {
        if (StringHelper::hasPostFix($this->getName(), $this->unshowables)) {
            return false;
        }

        return $this->isEditable() || $this->isShowable() || $this->isListable();
    }

    /**
     * @return bool
     */
    public function isImage(): bool
    {
        return StringHelper::hasPostFix($this->getName(), 'image');
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return StringHelper::hasPostFix($this->getName(), ['file', 'image']);
    }

    /**
     * @return string
     */
    public function getViewName(): string
    {
        return Str::title(str_replace('_', ' ', Str::snake($this->name)));
    }

    /**
     * @return string
     */
    public function getReferenceModel(): string
    {
        return ucfirst(Str::camel(singularize($this->referenceTableName)));
    }

    /**
     * @return string
     */
    public function getIntermediateTableName(): string
    {
        return $this->intermediateTableName;
    }

    /**
     * @return string
     */
    public function getIntermediateTableModel(): string
    {
        return ucfirst(Str::camel(singularize($this->intermediateTableName)));
    }

    /**
     * @return bool
     */
    public function isMultipleSelection(): bool
    {
        return $this->type === self::TYPE_HAS_MANY || $this->type === self::TYPE_BELONGS_TO_MANY;
    }

    /**
     * @return string
     */
    public function getEditFieldType(): string
    {
        return $this->editFieldType;
    }

    /**
     * @return array
     */
    public function getEditFieldOptions(): array
    {
        return $this->editFieldOptions;
    }

    /**
     * @return string
     */
    public function detectEditType(): string
    {
        if ($this->isMultipleSelection()) {
            if (Str::endsWith($this->name, '_type') || Str::endsWith($this->name, 'role')) {
                return self::EDIT_TYPE_CHECKBOX;
            }

            return self::EDIT_TYPE_SELECT_MULTI;
        }

        if (Str::endsWith($this->name, '_type') || Str::endsWith($this->name, 'role')) {
            return self::EDIT_TYPE_RADIO_BUTTON;
        }

        if ($this->isImage()) {
            return self::EDIT_TYPE_IMAGE;
        }

        if ($this->isFile()) {
            return self::EDIT_TYPE_FILE;
        }

        return self::EDIT_TYPE_SELECT_SINGLE;
    }

    /**
     * @return string
     */
    public function getAPIName(): string
    {
        return Str::camel($this->getName());
    }

    /**
     * @return string
     */
    public function getQueryName(): string
    {
        return Str::snake($this->getName());
    }

    /**
     * @return string
     */
    public function getPresentation(): string
    {
        if ($this->isTypes() || $this->isRoles() || $this->isStatues()) {
            return 'badge';
        }

        return 'normal';
    }

    /**
     * @return string
     */
    public function getInterestedColumnName(): string
    {
        if ($this->isRoles()) {
            return 'role';
        }

        if ($this->isTypes()) {
            return 'type';
        }

        return 'id';
    }
}
