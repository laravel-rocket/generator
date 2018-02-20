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

class InterfaceValidator extends BaseCommand
{
    protected $name = 'rocket:validate:interface-consistency';

    protected $signature = 'rocket:validate:interface-consistency {--name=}';
    // protected $signature = 'rocket:validate:interface-consistency {--name=} {--json=}';

    protected $description = 'Validate Interface Consistency';

    /** @var \TakaakiMizuno\MWBParser\Elements\Table[] $tables */
    protected $tables;

    /** @var \LaravelRocket\Generator\Objects\Definitions */
    protected $json;
    
    protected $checkingpaths = array();
    protected $folderpaths = [
        'services' => [
            'implement' => '/services/production/',
            'interface' => '/services/',
        ],
        'repositories' => [
            'implement' => '/repositories/Eloquent/',
            'interface' => '/repositories/',
        ],
        'helpers' => [
            'implement' => '/helpers/Production/',
            'interface' => '/helpers/',
        ],
    ];
    protected $fileVersion = ['implement', 'interface'];
    protected $interface_subfix = 'interface';
    

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->updateFileList();
        $this->setFileToBeChecked();
        $check_result = $this->checkFiles();
        if ( ! $check_result ) {
            return false;
        }
        return true;
    }

    protected function updateFileList()
    {
        $return = true;
        $this->fileList = array();
        foreach ($this->folderpaths as $type => $paths) {
            // if ( ! array_key_exists('implement', $paths) ) {
            //     continue;
            // }
            // if ( ! array_key_exists('interface', $paths) ) {
            //     continue;
            // }
            foreach($paths as $version => $path) {
                $f_path = app_path().$path;
                $files_folders = scandir($f_path);
                foreach($files_folders as $f) {
                    // if ($f == '.' || $f == '..') continue;
                    if ( is_dir($f) || strtolower(substr($f,-4)) !== '.php' ) {
                        // not a .php file
                        continue;
                    }
                    $name = strtolower(substr($f,0,-4));
                    if ($version == 'interface') {
                        if (substr($name,-strlen($this->interface_subfix)) !== $this->interface_subfix) {
                            // this interface filename does not end with the "interface" subfix
                            continue;
                        }
                        // remove the subfix to get the name
                        $name = substr($name,0,-strlen($this->interface_subfix));
                    }
                    // check if this file already exists in the list
                    if (array_key_exists($name,$this->fileList)) {
                        // does exist
                        if ($this->fileList[$name]['type'] !== $type) {
                            // type mismatch
                            $this->output('This name ['.$name.'] exists in 2 different types of files: ['.$this->fileList[$name]['type'].'] & ['.$type.']. The one in type ['.$type.'] is ignored');
                            $return = false;
                            continue;
                        }
                    } else {
                        // create a new entry in the file list
                        $this->fileList[$name] = [
                            'type' => $type,
                            'implement' => [
                                'exists' => false,
                                'path' => ''
                            ],
                            'interface' => [
                                'exists' => false,
                                'path' => ''
                            ],
                            'checking' => false
                        ];
                    }
                    // add new info to the entry
                    $this->fileList[$name][$version]['exists'] = true;
                    $this->fileList[$name][$version]['path'] = $f_path.$f;
                }
            }
        }
        // SECTION_NOT_NEEDED
        // check for missing interface files
        // foreach ($this->fileList as $name => $file) {
        //     if ( $file['interface']['exists'] == false ) {
        //         $this->output('The interface file for ['.$name.'] does not exist.', 'error');
        //         // $file['checking'] = false;
        //         $return = false;
        //     }
        // }
        return $return;
    }

    protected function setFileToBeChecked()
    {
        if (empty($this->option('name'))) {
            // set all files
            foreach ($this->fileList as $name => $fileEntry) {
                $fileEntry['checking'] = true;
            }
        } else {
            $name = $this->option('name');
            if ( ! array_key_exists($name,$this->fileList)) {
                $this->output('The filename ['.$name.'] does not exist anywhere.', 'error');
                return false;
            }
            // file exists => set that one
            $this->fileList[$name]['checking'] = true;
        }
        return true;
    }

    protected function checkFiles()
    {
        $no_mismatch = true;
        foreach($this->fileList as $name => $fileEntry) {
            if ( ! $fileEntry['checking'] ) {
                continue;
            }
            if ( ! $fileEntry['implement']['exists'] ) {
                continue;
            }
            if ( ! $fileEntry['interface']['exists'] ) {
                $this->output('The file ['.$name.'] does not have the interface counterpart.', 'error');
                $no_mismatch = false;
                continue;
            }
            $compare_result = $this->compareFiles(
                $fileEntry['implement']['path'], 
                $fileEntry['interface']['path']
            );
            if ( $compare_result === false ) {
                // mismatch found
                // $this->output('Mismatch between this file and its interface found: ['.$name.']', 'error');
                // return false;
                $no_mismatch = false;
            }
        }
        return $no_mismatch;
    }

    protected function compareFiles($implement_path, $interface_path)
    {
        // $lexer = new Lexer([
        //     'usedAttributes' => [
        //         'comments', 'startLine', 'endLine',
        //     ],
        // ]);
        // $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        
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
        return compareStatements($imp_statements, $int_statements);
    }

    protected function compareStatements($implement_statements, $interface_statements)
    {
        $pass = true;
        $imp_method_all = getMethods($implement_statements);
        $int_method_all = getMethods($interface_statements);
        foreach ($imp_method_all as $imp_method) {
            $name = $imp_method->name;
            $int_method = searchMethodByName($int_method_all, $name);
            if ( empty($int_method) ) {
                // method exists in IMP, not found in INT
                $this->output('Method ['.$name.'], exists in the implementation file, is not defined in the interface file.', 'error');
                $pass = false;
                continue;
            }
            $imp_param_count = count($imp_method->params);
            $int_param_count = count($int_method->params);
            if ( $imp_param_count !== $int_param_count ) {
                // different number of params
                $this->output('Method ['.$name.'] has different numbers of parameters in the interface & implementation files.', 'error');
                $pass = false;
                continue;
            }
            for ($i = 0; $i < $imp_param_count; $i++) {
                $imp_param_name = $imp_method->params[$i]->name;
                $int_param_name = $int_method->params[$i]->name;
                if ( $imp_param_name !== $int_param_name ) {
                    $this->output('Method ['.$name.'] has different names for parameter #'.$i.' in the interface & implementation files.', 'error');
                    $pass = false;
                }
            }
        }
        return $pass;
    }

    protected function getMethods($statements)
    {
        $methods = array();
        foreach ($statements as $statement) {
            if (get_class($statement) == ClassMethod::class) {
                if ($statement->flags == 1) {
                    array_push($methods, $statement);
                }
                continue;
            }
            if (property_exists($statement, 'stmts')) {
                $methods_sub = getMethods($statement->stmts);
                if ( count($methods_sub) >= 1 ) {
                    $methods = array_merge($methods, $methods_sub);
                }
            }
        }
        return $methods;
    }

    protected function searchMethodByName($methods, $name)
    {
        foreach ($methods as $method) {
            if ($method->name == $name) {
                return $method;
            }
        }
        return null;
    }

    // NOT_IN_USE / WORK_IN_PROGRESS
    protected function getPath($type='',$version='implement',$filename='')
    {
        if (array_key_exists($type,$this->folderpaths)) {
            // type specified
            if ($version !== 'interface') {
                $version = 'implement';
            }
            if (array_key_exists($version,$this->folderpaths[$type])) {
                $path = app_path().$this->folderpaths[$type][$version];
                return $path;
            }
        } else {
            // no type specified => look for the file with filename
            foreach ($this->folderpaths as $f_path) {
                
            }
        }
        return false;
    }

    // NOT_IN_USE
    protected function getFiles()
    {
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

    // NOT_IN_USE
    protected function addPathToCheck($type='Services',$name='')
    {
        if ( array_key_exists($type, $this->folderpaths) ) {
        if ( strlen($name) > 0 ) {
            $implement_path = $this->folderpaths[$type]['implement'].$name.'.php';
            $interface_path = $this->folderpaths[$type]['interface'].$name.$this->interface_subfix.'.php';
            if ( file_exists($implement_path) && file_exists($interface_path) ) {
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
