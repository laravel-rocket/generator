<?php
namespace LaravelRocket\Generator\FileUpdaters\React\CRUD\Admin;

use Illuminate\Support\Arr;

class SideBarFileUpdater extends ReactCRUDAdminFileUpdater
{
    /**
     * @param \TakaakiMizuno\MWBParser\Elements\Table      $table
     * @param \TakaakiMizuno\MWBParser\Elements\Table[]    $tables
     * @param \LaravelRocket\Generator\Objects\Definitions $json
     *
     * @return bool
     */
    public function insert($table, $tables, $json): bool
    {
        $this->json = $json;

        $this->setTargetTable($table, $tables);

        if (!$this->needGenerate()) {
            return false;
        }

        $filePath = $this->getTargetFilePath();

        $json = $this->parseJson($filePath);

        $exist = $this->checkItAlreadyExists($json);
        if ($exist) {
            return false;
        }

        $json = $this->insertItem($json);

        return $this->outputJson($json, $filePath);
    }

    /**
     * @param array $json
     *
     * @return bool
     */
    protected function checkItAlreadyExists($json): bool
    {
        $pathName = $this->tableObject->getPathName();
        foreach (Arr::get($json, 'items', []) as $item) {
            if (Arr::get($item, 'url', '') == '/'.$pathName) {
                return true;
            }
        }

        return false;
    }

    protected function getTargetFilePath(): string
    {
        return resource_path('assets/admin/src/components/Sidebar/_nav.js');
    }

    protected function parseJson($path)
    {
        $data = null;
        if (file_exists($path)) {
            $file = file_get_contents($path);
            $file = preg_replace('/^\s*export\s+default\s*/m', '', $file);
            $file = preg_replace('/;\s*$/', '', $file);

            $data = json_decode($file, true);
        }
        if (empty($data)) {
            $data = [
                'items' => [],
            ];
        }

        return $data;
    }

    /**
     * @param array $json
     *
     * @return array
     */
    protected function insertItem($json): array
    {
        $json['items'][] = [
            'name'  => $this->tableObject->getDisplayName(),
            'url'   => '/'.$this->tableObject->getPathName(),
            'icon'  => $this->tableObject->getIconClass(),
            'roles' => $this->tableObject->getAccessRoles(),
        ];

        return $json;
    }

    protected function outputJson($json, $filePath)
    {
        $output = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        $output = 'export default '.$output.';'.PHP_EOL;
        $this->fileService->save($filePath, $output);

        return true;
    }
}
