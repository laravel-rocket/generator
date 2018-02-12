<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\Objects\Definitions;
// use LaravelRocket\Generator\Validators\Error as ValidatorError;
// use LaravelRocket\Generator\Validators\TableSchemaValidator;
// use TakaakiMizuno\MWBParser\Parser;
use PhpParser\Error;
use PhpParser\Lexer;
use PhpParser\ParserFactory;
use PhpParser\Node\Stmt\ClassMethod;

class ValidatorFromMWB extends BaseCommand
{
    protected $name = 'rocket:validate:interface-consistency';

    protected $signature = 'rocket:validate:interface-consistency {--name=}';
    // protected $signature = 'rocket:validate:interface-consistency {--name=} {--json=}';

    protected $description = 'Validate Interface Consistency';

    /** @var \TakaakiMizuno\MWBParser\Elements\Table[] $tables */
    protected $tables;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;

    protected $implement_paths = array();
    protected $interface_paths = array();
    protected $checkingpaths = array();
    protected $folderpaths = [
        'services' => [
            'implement' => 'app/services/production',
            'interface' => 'app/services',
        ],
        'repositories' => [
            'implement' => 'app/',
            'interface' => 'app/',
        ],
        'helpers' => [
            'implement' => 'app/',
            'interface' => 'app/',
        ],
    ];
    protected $interface_subfix = 'Interface';
    

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        // $this->tables = $this->getTablesFromMWBFile();
        // if ($this->tables === false) {
        //     return false;
        // }
        // $this->getAppJson();

        // $success = $this->validateTableSchema();
        // if (!$success) {
        //     return false;
        // }
        // return true;

        
    }

    protected function getFiles()
    {
        // $this->implement_paths = array();
        // $this->interface_paths = array();
        $this->checkingpaths = array();
        $name = $this->option('name');
        $checkAll = false;
        if ( empty($name) ) {
            $checkAll = true;
            foreach ($this->folderpaths as $type => $path) {
                if ( ! array_key_exists('implement', $path) ) {
                    continue;
                }
                if ( ! array_key_exists('interface', $path) ) {
                    continue;
                }
                $files_folders = scandir($path['implement']);
                foreach($files_folders as $f) {
                    if ( ! is_dir($f) && substr($f,-4) == '.php' ) {
                        $f_name = substr($f,0,-4);
                        $addpath_success = $this->addPathToCheck($type, $name);
                        if ( $addpath_success === false ) {
                            // return false;
                        }
                    }
                }
            }
        } else {
            // check to see which folder/type this file belongs to
            $fileFound = false;
            foreach ($this->folderpaths as $type => $path) {
                if ( ! array_key_exists('implement', $path) ) {
                    continue;
                }
                if ( ! array_key_exists('interface', $path) ) {
                    continue;
                }
                $files_folders = scandir($path['implement']);
                foreach($files_folders as $f) {
                    if ( ! is_dir($f) && $f === $name.'.php' ) {
                        $fileFound = true;
                        $addpath_success = $this->addPathToCheck($type, $name);
                        if ( $addpath_success === false ) {
                            // return false;
                        }
                        break;
                    }
                }
                if ( $fileFound ) {
                    break;
                }
            }
        }
        return true;
    }

    protected function checkAllFiles()
    {
        if ( count($this->checkingpaths) >= 1 ) {
            foreach($this->checkingpaths as $path) {
                $result = $this->compareFiles($path['implement'], $path['interface']);
                if ( $result === false ) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function compareFiles($implement_path,$interface_path)
    {
        $lexer = new Lexer([
            'usedAttributes' => [
                'comments', 'startLine', 'endLine',
            ],
        ]);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
        
        try {
            $imp_statements = $parser->parse(file_get_contents($implement_path));
        } catch (Error $e) {
            return false;
        }
        try {
            $int_statements = $parser->parse(file_get_contents($interface_path));
        } catch (Error $e) {
            return false;
        }
        // compare $imp_statements and $int_statements
        return true;
    }

    protected function addPathToCheck($type='Services',$name='')
    {
        if ( array_key_exists($type, $this->folderpaths) ) {
        if ( strlen($name) > 0 ) {
            $implement_path = $this->folderpaths[$type]['implement'].$name.'.php';
            $interface_path = $this->folderpaths[$type]['interface'].$name.$this->interface_subfix.'.php';
            if ( file_exists($implement_path) && file_exists($interface_path) ) {
                // array_push($this->implement_paths, $implement_path);
                // array_push($this->interface_paths, $interface_path);
                array_push($this->checkingpaths,[
                    'implement' => $implement_path,
                    'interface' => $interface_path
                ]);
                return true;
            }
        }}
        return false;
    }
}
