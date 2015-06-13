<?php
/**
 * Created by PhpStorm.
 * User: fernandobritofl
 * Date: 4/21/15
 * Time: 4:58 PM
 */

namespace veyselsahin\l5generators\Makes;


use Illuminate\Filesystem\Filesystem;
use veyselsahin\l5generators\Commands\ScaffoldMakeCommand;
use veyselsahin\l5generators\Migrations\SchemaParser;
use veyselsahin\l5generators\Migrations\SyntaxBuilder;
use Illuminate\Support\Facades\Cache;

class MakeView
{
    use MakerTrait;


    protected $scaffoldCommandObj;
    protected $viewName;


    public function __construct(ScaffoldMakeCommand $scaffoldCommand, Filesystem $files, $viewName)
    {
        $this->files = $files;
        $this->scaffoldCommandObj = $scaffoldCommand;
        $this->viewName = $viewName;

        $this->start();
    }

    private function start()
    {
        $this->generateView($this->viewName); // index, show, edit and create
    }


    protected function generateView($nameView = 'index')
    {
        // Get path
        $path = $this->getPath($this->scaffoldCommandObj->getObjName('name'), 'view-' . $nameView);


        // Create directory
        $this->makeDirectory($path);

        if ($this->files->exists($path))
        {
            unlink($path);
        }

        $this->files->put($path, $this->compileViewStub($nameView));





    }


    /**
     * Compile the migration stub.
     *
     * @param $nameView
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function compileViewStub($nameView)
    {
        $stub = $this->files->get(__DIR__ . '/../stubs/html_assets/' . $nameView . '.stub');

        if ($nameView == 'show')
        {
            // show.blade.php
            $this->replaceName($stub)
                ->replaceSchemaShow($stub);

        } elseif ($nameView == 'edit')
        {
            // edit.blade.php
            $this->replaceName($stub)
                ->replaceSchemaEdit($stub);

        } elseif ($nameView == 'create')
        {
            // edit.blade.php
            $this->replaceName($stub)
                ->replaceSchemaCreate($stub);

        } else
        {
            // index.blade.php
            $this->replaceName($stub)
                ->replaceSchemaIndex($stub);
        }


        return $stub;
    }


    /**
     * Replace the class name in the stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceName(&$stub)
    {
        //$this->scaffoldCommandObj->getObjName('names')  tekil almak ıcın kucuk name ile degistirildi
        $stub = str_replace('{{Class}}', $this->scaffoldCommandObj->getObjName('Names'), $stub);
        $stub = str_replace('{{class}}', $this->scaffoldCommandObj->getObjName('name'), $stub);
        $stub = str_replace('{{classSingle}}', $this->scaffoldCommandObj->getObjName('names'), $stub);

        return $this;
    }


    /**
     * Replace the schema for the index.stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceSchemaIndex(&$stub)
    {

        $schemaArray = [];
        if ($schema = $this->scaffoldCommandObj->option('schema'))
        {
            $schemaArray = (new SchemaParser)->parse($schema);
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
        foreach ($table_columns as $key => $column)
        {
            for ($i = 0; $i < count($schemaArray); $i++)
            {
                if ($schemaArray[$i]["name"] == $key)
                {
                    if (isset($column["listeyee"]) && ($column["listeyee"] == true || $column["listeyee"] == "on"))
                    {
                        $newSchemeArray[] = $schemaArray[$i];
                    }
                }
            }
        }

        $schemaArray = $newSchemeArray;
        // Create view index header fields
        $schema = (new SyntaxBuilder)->create($schemaArray, $this->scaffoldCommandObj->getMeta(), 'view-index-header');
        $stub = str_replace('{{header_fields}}', $schema, $stub);


        // Create view index content fields
        $schema = (new SyntaxBuilder)->create($schemaArray, $this->scaffoldCommandObj->getMeta(), 'view-index-content');
        $stub = str_replace('{{content_fields}}', $schema, $stub);

        return $this;
    }


    /**
     * Replace the schema for the show.stub.
     *
     * @param  string $stub
     * @return $this
     */
    protected function replaceSchemaShow(&$stub)
    {
        $schemaArray=[];
        if ($schema = $this->scaffoldCommandObj->option('schema'))
        {
            $schemaArray = (new SchemaParser)->parse($schema);
        }








        // Create view index content fields
        $schema = (new SyntaxBuilder)->create($schemaArray, $this->scaffoldCommandObj->getMeta(), 'view-show-content');
        $stub = str_replace('{{content_fields}}', $schema, $stub);


        return $this;
    }


    /**
     * Replace the schema for the edit.stub.
     *
     * @param  string $stub
     * @return $this
     */
    private function replaceSchemaEdit(&$stub)
    {

        if ($schema = $this->scaffoldCommandObj->option('schema'))
        {
            $schemaArray = (new SchemaParser)->parse($schema);
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
        foreach ($table_columns as $key => $column)
        {
            for ($i = 0; $i < count($schemaArray); $i++)
            {
                if ($schemaArray[$i]["name"] == $key)
                {
                    if (isset($column["formae"]) && ($column["formae"] == true || $column["formae"] == "on"))
                    {
                        $newSchemeArray[] = $schemaArray[$i];
                    }
                }
            }
        }

        $schemaArray = $newSchemeArray;









        // Create view index content fields
        $schema = (new SyntaxBuilder)->create($schemaArray, $this->scaffoldCommandObj->getMeta(), 'view-edit-content');
        $stub = str_replace('{{content_fields}}', $schema, $stub);


        return $this;

    }


    /**
     * Replace the schema for the edit.stub.
     *
     * @param  string $stub
     * @return $this
     */
    private function replaceSchemaCreate(&$stub)
    {

        if ($schema = $this->scaffoldCommandObj->option('schema'))
        {
            $schemaArray = (new SchemaParser)->parse($schema);
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
        foreach ($table_columns as $key => $column)
        {
            for ($i = 0; $i < count($schemaArray); $i++)
            {
                if ($schemaArray[$i]["name"] == $key)
                {
                    if (isset($column["formae"]) && ($column["formae"] == true || $column["formae"] == "on"))
                    {
                        $newSchemeArray[] = $schemaArray[$i];
                    }
                }
            }
        }

        $schemaArray = $newSchemeArray;












        // Create view index content fields
        $schema = (new SyntaxBuilder)->create($schemaArray, $this->scaffoldCommandObj->getMeta(), 'view-create-content');
        $stub = str_replace('{{content_fields}}', $schema, $stub);


        return $this;

    }

}