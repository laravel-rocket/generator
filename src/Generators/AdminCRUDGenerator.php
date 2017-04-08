<?php

namespace LaravelRocket\Generator\Generators;

class AdminCRUDGenerator extends Generator
{

    public function generate($name, $overwrite = false, $baseDirectory = null)
    {
        $modelName = $this->getModelName($name);
        $this->generateController($modelName);
        $this->generateRequest($modelName);
        $this->generateView($modelName, 'index');
        $this->generateView($modelName, 'edit');
        $this->generateUnittest($modelName);
        $this->addItemToSubMenu($modelName);
        $this->addItemToLanguageFile($modelName);
        $this->addItemToRoute($modelName);
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getModelName($name)
    {
        $className = $this->getClassName($name);
        $modelName = str_replace('Controller', '', $className);

        return $modelName;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getControllerClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Http\\Controllers\\Admin\\'.$modelName.'Controller';
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function getRequestClass($name)
    {
        $modelName = $this->getModelName($name);

        return '\\App\\Http\\Requests\\Admin\\'.$modelName.'Request';
    }

    /**
     * @param string $name
     * @param string $type
     *
     * @return string
     */
    protected function getViewPath($name, $type)
    {
        $directoryName = \StringHelper::camel2Spinal(\StringHelper::pluralize($name));

        return base_path('/resources/views/pages/admin/'.$directoryName.'/'.$type.'.blade.php');
    }

    /**
     * @return string
     */
    protected function getSideBarViewPath()
    {
        return base_path('/resources/views/layouts/admin/side_menu.blade.php');
    }

    /**
     * @return string
     */
    protected function getLanguageFilePath()
    {
        return base_path('/resources/lang/en/admin.php');
    }

    /**
     * @return string
     */
    protected function getRoutesPath()
    {
        return base_path('/routes/admin.php');
    }

    /**
     * @param  string
     *
     * @return string
     */
    protected function getStubForView($type)
    {
        return $this->getStubPath('/admin-crud/view-'.$type.'.stub');
    }

    /**
     * @param  string $modelName
     * @param  string $classPath
     * @param  string $stubFilePath
     * @return bool
     */
    protected function saveFile($modelName, $classPath, $stubFilePath)
    {
        $list = $this->generateParams($modelName);
        $updates = $this->generateUpdate($modelName);
        $tableHeader = $this->generateListHeader($modelName);
        $tableContent = $this->generateListRow($modelName);
        $formContent = $this->generateEditForm($modelName);
        $testColumn = $this->generateTestColumn($modelName);

        return $this->generateFile($modelName, $classPath, $stubFilePath, [
            'models-spinal'  => \StringHelper::camel2Spinal(\StringHelper::pluralize($modelName)),
            'models'         => lcfirst(\StringHelper::pluralize($modelName)),
            'MODELS'         => \StringHelper::pluralize($modelName),
            'COLUMN_UPDATES' => $updates,
            'COLUMNS'        => $list,
            'TABLE_HEADER'   => $tableHeader,
            'TABLE_CONTENT'  => $tableContent,
            'FORM'           => $formContent,
            'test_column'    => $testColumn,
        ]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function generateController($name)
    {
        $modelName = $this->getModelName($name);
        $className = $this->getControllerClass($modelName);
        $classPath = $this->convertClassToPath($className);

        $stubFilePath = $this->getStubPath('/admin-crud/controller.stub');

        return $this->saveFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function generateRequest($name)
    {
        $modelName = $this->getModelName($name);
        $className = $this->getRequestClass($modelName);
        $classPath = $this->convertClassToPath($className);

        $stubFilePath = $this->getStubPath('/admin-crud/request.stub');

        return $this->saveFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function generateView($name, $type)
    {
        $modelName = $this->getModelName($name);
        $path = $this->getViewPath($name, $type);
        $stubPath = $this->getStubForView($type);

        return $this->saveFile($modelName, $path, $stubPath);
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function generateUnitTest($name)
    {
        $modelName = $this->getModelName($name);
        $classPath = base_path('/tests/Controllers/Admin/'.$modelName.'ControllerTest.php');
        $stubFilePath = $this->getStubPath('/admin-crud/unittest.stub');

        return $this->saveFile($modelName, $classPath, $stubFilePath);
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function addItemToSubMenu($name)
    {
        $modelName = $this->getModelName($name);
        $sideMenuPath = $this->getSideBarViewPath();

        $key = '<!-- %%SIDEMENU%% -->';
        $bind = '<li class="c-admin__sidemenuitem @if( $menu==\''.\StringHelper::camel2Snake($modelName).'\') c-admin__sidemenu-item--is-active @endif "><a href="{!! action(\'Admin\\'.$modelName.'Controller@index\') !!}"><i class="fa fa-users"></i> <span>'.\StringHelper::pluralize($modelName).'</span></a></li>'.PHP_EOL.'            ';

        return $this->replaceFile([
            $key => $bind,
        ], $sideMenuPath);
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function addItemToLanguageFile($name)
    {
        $modelName = $this->getModelName($name);
        $languageFilePath = $this->getLanguageFilePath();
        $directoryName = \StringHelper::camel2Spinal(\StringHelper::pluralize($modelName));

        $key = '/* NEW PAGE STRINGS */';

        $columns = $this->getFillableColumns($modelName);
        $bind = "'".$directoryName."'   => [".PHP_EOL."            'columns'  => [".PHP_EOL;
        foreach ($columns as $column) {
            $name = $column->getName();
            $bind .= "                '".$name."' => '".ucfirst($name)."',".PHP_EOL;
        }
        $bind .= '            ],'.PHP_EOL.'        ],'.PHP_EOL.'        ';

        return $this->replaceFile([
            $key => $bind,
        ], $languageFilePath);
    }

    /**
     * @param  string $name
     * @return bool
     */
    protected function addItemToRoute($name)
    {
        $modelName = $this->getModelName($name);
        $routePath = $this->getRoutesPath();

        $directoryName = \StringHelper::camel2Spinal(\StringHelper::pluralize($modelName));

        $key = '/* NEW ADMIN RESOURCE ROUTE */';
        $bind = '\\Route::resource(\''.$directoryName.'\', \'Admin\\'.$modelName.'Controller\');'.PHP_EOL.'        ';

        return $this->replaceFile([
            $key => $bind,
        ], $routePath);
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function generateEditForm($name)
    {
        $modelName = $this->getModelName($name);
        $columns = $this->getFillableColumns($modelName);
        $result = '';
        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType()->getName();

            if ($name == 'id') {
                continue;
            }
            $stubPath = $this->getStubPath('/admin-crud/form/text.stub');
            if (\StringHelper::endsWith($name, 'image_id')) {
                $stubPath = $this->getStubPath('/admin-crud/form/image.stub');
            } else {
                if (\StringHelper::endsWith($name, '_id')) {
                    $stubPath = $this->getStubPath('/admin-crud/form/select.stub');
                    continue;
                } else {
                    switch ($type) {
                        case 'boolean':
                            $stubPath = $this->getStubPath('/admin-crud/form/checkbox.stub');
                            break;
                        case 'datetime':
                            $stubPath = $this->getStubPath('/admin-crud/form/datetime.stub');
                            break;
                        case 'text':
                            $stubPath = $this->getStubPath('/admin-crud/form/textarea.stub');
                            break;
                        case 'string':
                        case 'integer':
                        case "bigint":
                        case "smallint":
                            switch ( $name ) {
                                case "password":
                                    $stubPath = $this->getStubPath('/admin-crud/form/password.stub');
                                    break;
                                case "email":
                                    $stubPath = $this->getStubPath('/admin-crud/form/email.stub');
                                    break;
                                default:
                                    $stubPath = $this->getStubPath('/admin-crud/form/text.stub');
                            }
                            break;
                    }
                }
            }

            $fieldName = strlen($name) > 3 ? substr($name, 0, strlen($name) - 3) : $name;
            $relationName = lcfirst(\StringHelper::snake2Camel($fieldName));

            $result .= $this->replace([
                    'column'        => $name,
                    'models-spinal' => \StringHelper::camel2Spinal(\StringHelper::pluralize($modelName)),
                    'models'        => \StringHelper::pluralize(lcfirst($modelName)),
                    'MODEL'         => $modelName,
                    'model'         => lcfirst($modelName),
                    'relation'      => $relationName,
                ], $stubPath).PHP_EOL;
        }

        return $result;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function generateListHeader($name)
    {
        $modelName = $this->getModelName($name);
        $spinalName = \StringHelper::camel2Spinal(\StringHelper::pluralize($modelName));
        $columns = $this->getFillableColumns($modelName);
        $result = '';

        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType()->getName();

            if (\StringHelper::endsWith($name, 'image_id')) {
                continue;
            } elseif (\StringHelper::endsWith($name, '_id')) {
                continue;
            } else {
                switch ($type) {
                    case 'text':
                        break;
                    case 'datetime':
                    case 'string':
                    case 'integer':
                    case 'boolean':
                        $result .= '                <th>@lang(\'admin.pages.'.$spinalName.'.columns.'.$name.'\')</th>'.PHP_EOL;
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function generateListRow($name)
    {
        $modelName = $this->getModelName($name);
        $lcName = lcfirst($modelName);

        $columns = $this->getFillableColumns($modelName);
        $result = '';
        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType()->getName();

            if (\StringHelper::endsWith($name, 'image_id')) {
                continue;
            } else {
                if (\StringHelper::endsWith($name, '_id')) {
                    continue;
                } else {
                    switch ($type) {
                        case 'text':
                            break;
                        case 'boolean':
                            $result .= '                <td>{{ ($'.$lcName.'->'.$name.') ?  \'ON\' : \'OFF\' }}<\/td>'.PHP_EOL;
                            break;
                        case 'datetime':
                        case 'string':
                        case 'integer':
                            $result .= '                <td>{{ $'.$lcName.'->'.$name.' }}</td>'.PHP_EOL;
                            break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function generateUpdate($name)
    {
        $modelName = $this->getModelName($name);
        $columns = $this->getFillableColumns($modelName);
        $result = '';
        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType()->getName();

            if (\StringHelper::endsWith($name, 'image_id')) {
                continue;
            } else {
                if (\StringHelper::endsWith($name, '_id')) {
                    continue;
                } else {
                    switch ($type) {
                        case 'text':
                            break;
                        case 'boolean':
                            $result .= '        $input[\''.$name.'\'] = $request->get(\''.$name.'\', false);'.PHP_EOL;
                            break;
                        case 'datetime':
                        case 'string':
                        case 'integer':
                            break;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function generateParams($name)
    {
        $modelName = $this->getModelName($name);
        $columns = $this->getFillableColumns($modelName);
        $params = [];
        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType()->getName();

            if (\StringHelper::endsWith($name, 'image_id')) {
                continue;
            } else {
                if (\StringHelper::endsWith($name, '_id')) {
                    continue;
                } else {
                    switch ($type) {
                        case 'boolean':
                            break;
                        case 'text':
                        case 'datetime':
                        case 'string':
                        case 'integer':
                            $params[] = $name;
                            break;
                    }
                }
            }
        }
        $result = implode(',', array_map(function($name) {
            return "'".$name."'";
        }, $params));

        return $result;
    }

    protected function generateTestColumn($name)
    {
        $modelName = $this->getModelName($name);
        $columns = $this->getFillableColumns($modelName);
        $candidate = "NONAME";
        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType()->getName();
            if ($type == 'string' || $type == 'text') {
                return $name;
            }
            if ($type == 'integer') {
                $candidate = $name;
            }
        }

        return $candidate;
    }
}
