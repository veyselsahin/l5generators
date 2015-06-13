<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/22/15
 * Time: 10:34 PM
 */

namespace veyselsahin\l5generators\Makes;


use veyselsahin\l5generators\Migrations\SchemaParser;
use veyselsahin\l5generators\Migrations\SyntaxBuilder;
use Illuminate\Filesystem\Filesystem;
use veyselsahin\l5generators\Commands\ScaffoldMakeCommand;

class MakeModel
{
    use MakerTrait;
    protected $scaffoldCommandObj;

    public function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files)
    {
        $this->files = $files;
        $this->scaffoldCommandObj = $scaffoldCommand;

        $this->start();
    }


    protected function start()
    {

        $name = config("vys.model_namespace") . $this->scaffoldCommandObj->getObjName('Name');
        $path = $this->getPath($name, 'model');


        $this->makeDirectory($path);

        $this->files->put($path, $this->compileModelStub());

        /* if (! $this->files->exists($modelPath)) {
             $this->scaffoldCommandObj->call('make:model', [
                 'name' => $name,
                 '--no-migration' => true
             ]);
         }*/

    }


    /**
     * Compile the migration stub.
     *
     * @return string
     */
    protected function compileModelStub()
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/model.stub');

        $this->replaceNamespace($stub)
            ->replaceClassName($stub)
            ->replaceSchema($stub, 'model');


        return $stub;
    }


    protected function replaceNamespace(&$stub)
    {
        $interface_model_namespace = config("vys.interface_model_namespace");
        $stub = str_replace('{{interface_model_namespace}}', $interface_model_namespace, $stub);
        return $this;
    }


    protected function replaceClassName(&$stub)
    {
        $interface_model_class = config("vys.interface_model_class");
        $model_class = $this->scaffoldCommandObj->getObjName('Name');
        $stub = str_replace('{{interface_model_class}}', $interface_model_class, $stub);
        $stub = str_replace('{{model_class}}', $model_class, $stub);
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

        if ($schema = $this->scaffoldCommandObj->option('schema'))
        {
            $schema = (new SchemaParser())->parse($schema);
        }


        // Create controllers fields
        $schema = (new SyntaxBuilder())->create($schema, $this->scaffoldCommandObj->getMeta(), 'model');
        $stub = str_replace('{{model_fields}}', $schema, $stub);


        return $this;
    }
}
