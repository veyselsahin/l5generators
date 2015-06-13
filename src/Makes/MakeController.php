<?php
namespace veyselsahin\l5generators\Makes;

use Illuminate\Console\AppNamespaceDetectorTrait;
use Illuminate\Filesystem\Filesystem;
use veyselsahin\l5generators\Commands\ScaffoldMakeCommand;
use veyselsahin\l5generators\Migrations\SchemaParser;
use veyselsahin\l5generators\Migrations\SyntaxBuilder;
use Illuminate\Support\Facades\Cache;


class MakeController
{
    use AppNamespaceDetectorTrait, MakerTrait;



    protected $scaffoldCommandObj;

    function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
    {
        $this->files = $files;
        $this->scaffoldCommandObj = $scaffoldCommand;

        $this->start();

    }

    private function start()
    {
        // Cria o nome do arquivo do controller // TweetController


        $name = $this->scaffoldCommandObj->getObjName('Name') . 'Controller';

        // Verifica se o arquivo existe com o mesmo o nome
        if ($this->files->exists($path = $this->getPath($name))) {
            unlink($path);
        }

        // Cria a pasta caso nao exista
        $this->makeDirectory($path);

        // Grava o arquivo
        $this->files->put($path, $this->compileControllerStub());

        $this->scaffoldCommandObj->info('Controller created successfully.');

        //$this->composer->dumpAutoloads();
    }





    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileControllerStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/controller.stub');

        $this->replaceClassName($stub, "controller")
            ->replaceModelPath($stub)
            ->replaceModelName($stub)
            ->replaceSchema($stub, 'controller');


        return $stub;
    }


    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceClassName(&$stub)
    {

        $className = $this->scaffoldCommandObj->getObjName('Name') . 'Controller';
        $stub = str_replace('{{class}}', $className, $stub);

        return $this;
    }


    /**
     * Renomeia o endereço do Model para o controller
     *
     * @param $stub
     * @return $this
     */
    private function replaceModelPath(&$stub)
    {

        $model_name = $this->getAppNamespace(). 'Models\\' . $this->scaffoldCommandObj->getObjName('Name');
        $stub = str_replace('{{model_path}}', $model_name, $stub);

        return $this;

    }


    private function replaceModelName(&$stub)
    {
        $model_name_uc = $this->scaffoldCommandObj->getObjName('Name');
        $model_name = $this->scaffoldCommandObj->getObjName('names');
        $model_names = $this->scaffoldCommandObj->getObjName('name');

        $stub = str_replace('{{model_name_class}}', $model_name_uc, $stub);
        $stub = str_replace('{{model_name_var_sgl}}', $model_name, $stub);
        $stub = str_replace('{{model_name_var}}', $model_names, $stub);

        return $this;
    }


    /**
     * Replace the schema for the stub.
     *
     * @param  string $stub
     * @param string $type
     * @return $this
     */
    protected function replaceSchema(&$stub, $type = 'migration')
    {
        $schema=[];

        if ($schema = $this->scaffoldCommandObj->option('schema')) {
            $schema = (new SchemaParser)->parse($schema);
        }











        $serialize_results = Cache::get("table_serialize_results");

        $m = $this->scaffoldCommandObj->getMeta();

        $table_columns = [];//ilgili tablonun kolonlarını ıcerek


        foreach ($serialize_results as $res)
        {

            if ($res["table3name"] == $m["table"])
            {
                $table_columns = $res;
            }
        }

        unset($table_columns["table1name"]);
        unset($table_columns["table2name"]);
        unset($table_columns["table3name"]);

        $newSchemeArray = [];
        $safe_fields=config("vys.globally_safe_fields");
        foreach ($table_columns as $key => $column)
        {
            for ($i = 0; $i < count($schema); $i++)
            {
                if ($schema[$i]["name"] == $key)
                {
                    if (!in_array($key,$safe_fields))
                    {
                        $newSchemeArray[] = $schema[$i];
                    }
                }
            }
        }

        $schema = $newSchemeArray;







        // Create controllers fields
        $schema = (new SyntaxBuilder)->create($schema, $this->scaffoldCommandObj->getMeta(), 'controller');
        $stub = str_replace('{{model_fields}}', $schema, $stub);


        return $this;
    }
}