<?php

namespace LaravelRocket\Generator\Generators\React\CRUD\Admin;

use LaravelRocket\Generator\Generators\BaseGenerator;

class RoleConfigGenerator extends BaseGenerator
{
    /**
     * @var \LaravelRocket\Generator\Objects\Definitions
     */
    protected $json;

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return config_path('admin_user.php');
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return 'config.admin_user';
    }

    protected function getVariables(): array
    {
        $roles = $this->json->get('admin.roles', []);

        return [
            'roles' => $roles,
        ];
    }

    /**
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return bool
     */
    public function generate($json): bool
    {
        $this->json = $json;

        $view      = $this->getView();
        $variables = $this->getVariables();

        $path = $this->getPath();
        if (file_exists($path)) {
            unlink($path);
        }
        $this->fileService->render($view, $path, $variables);

        return true;
    }
}
