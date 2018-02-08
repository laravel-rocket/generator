<?php
namespace LaravelRocket\Generator\Generators\Models;

use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\ParserFactory;

class ModelGenerator extends ModelBaseGenerator
{
    /**
     * @return string
     */
    protected function getPath(): string
    {
        $modelName = $this->getModelName();

        return app_path('Models/'.$modelName.'.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'model.model';
    }

    /**
     * @return array
     */
    protected function getVariables(): array
    {
        $modelName                  = $this->getModelName();
        $variables                  = $this->getFillableColumns();
        $variables['className']     = $modelName;
        $variables['tableName']     = $this->table->getName();
        $variables['relationTable'] = $this->detectRelationTable($this->table);
        $variables['relations']     = $this->getRelations();
        $variables['constants']     = $this->getConstants();

        return $variables;
    }

    protected function getConstants(): array
    {
        $constants = [];
        $filePath  = $this->getPath();
        if (!file_exists($filePath)) {
            return $constants;
        }

        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine',
            ],
        ]);

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        try {
            $statements = $parser->parse(file_get_contents($filePath));
        } catch (Error $e) {
            return [];
        }

        $this->getAllConstants($statements, $constants);

        $columns = $this->json->get(['tables', $this->table->getName().'.columns'], []);
        foreach ($columns as $name => $column) {
            $type = array_get($column, 'type');
            if ($type === 'type') {
                $options = array_get($column, 'options', []);
                foreach ($options as $option) {
                    $value            = array_get($option, 'value');
                    $name             = $this->generateConstantName($name, $value);
                    $constants[$name] = "$name = '$value'";
                }
            }
        }

        asort($constants);

        return $constants;
    }

    protected function getAllConstants($statements, &$result)
    {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;
        foreach ($statements as $statement) {
            if (get_class($statement) === \PhpParser\Node\Stmt\ClassConst::class) {
                foreach ($statement->consts as $constant) {
                    $result[$constant->name] = ltrim($prettyPrinter->prettyPrint([$constant]));
                }
            } elseif (property_exists($statement, 'stmts')) {
                $return = $this->getAllConstants($statement->stmts, $result);
                if (!empty($return)) {
                    return $return;
                }
            }
        }
    }

    protected function getFillableColumns()
    {
        $columnInfo = [
            'timestamps'      => [],
            'softDelete'      => false,
            'fillables'       => [],
            'authenticatable' => false,
        ];

        $excludes          = ['id', 'remember_token', 'created_at', 'deleted_at', 'updated_at'];
        $timestampExcludes = ['created_at', 'updated_at'];

        foreach ($this->table->getColumns() as $column) {
            $name = $column->getName();
            $type = $column->getType();

            if (!in_array($name, $excludes)) {
                $columnInfo['fillables'][] = $name;
            }
            if ($name == 'deleted_at') {
                $columnInfo['softDelete'] = true;
            }
            if ($name == 'remember_token') {
                $columnInfo['authenticatable'] = true;
            }
            if (($type == 'timestamp' || $type == 'timestamp_f') && !in_array($name, $timestampExcludes)) {
                $columnInfo['fillables'][] = $name;
            }
        }

        return $columnInfo;
    }
}
