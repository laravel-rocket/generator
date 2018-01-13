<?php
namespace LaravelRocket\Generator\Generators\CRUD;

class TemplateGenerator extends CRUDBaseGenerator
{
    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table   $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[] $tables
     *
     * @return bool
     */
    public function generate($table, $tables): bool
    {
        $this->table  = $table;
        $this->tables = $tables;

        if (!$this->canGenerate()) {
            return false;
        }

        $variables = $this->getVariables();

        foreach (['index', 'edit'] as $action) {
            $path = $this->getActionPath($action);
            if (file_exists($path)) {
                unlink($path);
            }
            $view = $this->getActionView($action);

            if (file_exists($path)) {
                unlink($path);
            }

            $this->fileService->render($view, $path, $variables, true, true);
        }

        return true;
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionPath(string $action): string
    {
        return '';
    }

    /**
     * @param string $action
     *
     * @return string
     */
    protected function getActionView(string $action): string
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                 = $this->getModelName();
        $variables                 = $this->getColumns();
        $variables['modelName']    = $modelName;
        $variables['variableName'] = camel_case($modelName);
        $variables['viewName']     = kebab_case($modelName);
        $variables['className']    = $modelName.'Repository';
        $variables['tableName']    = $this->table->getName();

        return $variables;
    }

    protected function getColumns()
    {
        $columnInfo = [
            'editableColumns' => [],
            'listColumns'     => [],
        ];

        $uneditables     = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
        $unlistables     = ['id', 'created_at', 'updated_at'];
        $unlistableTypes = ['text', 'mediumtext', 'longtext'];

        $relations    = $this->getRelations();
        $relationHash = [];
        foreach ($relations as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $relationHash[$relation['column']] = $relation;
            }
        }

        foreach ($this->table->getColumns() as $column) {
            $name     = $column->getName();
            $type     = $column->getType();
            $relation = '';
            if (array_key_exists($name, $relationHash)) {
                $type     = 'relation';
                $relation = camel_case($relationHash[$name]['referenceModel']);
            }
            if ((starts_with($name, 'is_') || starts_with($name, 'has_')) && $type === 'int') {
                $type = 'boolean';
            }

            if (!in_array($name, $unlistables) && !in_array($type, $unlistableTypes)) {
                $columnInfo['listColumns'][] = [
                    'name'     => $name,
                    'type'     => $type,
                    'relation' => $relation,
                ];
            }

            if (!in_array($name, $uneditables)) {
                $columnInfo['editableColumns'][] = [
                    'name'     => $name,
                    'type'     => $this->detectColumnEditorType($column, $relations),
                    'relation' => $relation,
                ];
            }
        }

        return $columnInfo;
    }

    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Column $column
     * @param array                                    $relations
     *
     * @return string
     */
    protected function detectColumnEditorType($column, $relations): string
    {
        $name = $column->getName();
        $type = $column->getType();

        switch ($column->getType()) {
            case 'int':
                if (starts_with($name, 'is_') || starts_with($name, 'has_')) {
                    return 'bool';
                }

                return $type;
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return 'text';
            case 'varchar':
                return 'string';
            default:
                return $type;
        }
    }
}
