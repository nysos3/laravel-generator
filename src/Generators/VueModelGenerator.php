<?php

namespace InfyOm\Generator\Generators;

use Illuminate\Support\Str;
use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Common\GeneratorFieldRelation;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;


class VueModelGenerator extends BaseGenerator
{
    /**
     * Fields not included in the generator by default.
     *
     * @var array
     */
    protected $excluded_fields = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;
    private $fileName;
    private $table;

    /**
     * ModelGenerator constructor.
     *
     * @param \InfyOm\Generator\Common\CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathVueModel;
        $this->fileName = $this->commandData->modelName.'.js';
        $this->table = $this->commandData->dynamicVars['$TABLE_NAME$'];
    }

    public function generate()
    {
        $templateData = get_template('model.vue', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nModel created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);


        $fields = [];

        foreach ($this->commandData->fields as $field) {
            $camel = Str::camel($field->name);
            $options = [];
            if(in_array($field->name,$this->excluded_fields)) {
              $options['persist'] = false;
            }
            if(empty($options)) {
              $fields[] = "{$camel}: attr()";
            } else {
              $options = json_encode($options);
              $fields[] = "{$camel}: attr({$options})";
            }
        }

        $relations = $this->generateRelations();
        foreach($relations as $relation)
          $fields[] = $relation;

        $templateData = str_replace('$FIELDS$', implode(','.PHP_EOL.'      ', $fields), $templateData);

        return $templateData;
    }

    /**
     * @param $db_type
     * @param GeneratorFieldRelation|null $relation
     *
     * @return string
     */
    private function getPHPDocType($db_type, $relation = null)
    {
        switch ($db_type) {
            case 'datetime':
            case 'date':
            case 'time':
                return 'string|\Carbon\Carbon';
            case 'text':
                return 'string';
            case '1t1':
            case 'mt1':
                return '\\'.$this->commandData->config->nsModel.'\\'.$relation->inputs[0].' '.camel_case($relation->inputs[0]);
            case '1tm':
                return '\Illuminate\Database\Eloquent\Collection'.' '.$relation->inputs[0];
            case 'mtm':
            case 'hmt':
                return '\Illuminate\Database\Eloquent\Collection'.' '.camel_case($relation->inputs[1]);
            default:
                return $db_type;
        }
    }

    private function generateRelations()
    {
        $relations = [];

        foreach ($this->commandData->relations as $relation) {
            $relationText = $relation->getRelationFunctionTextVue();

            if (!empty($relationText)) {
                $relations[] = $relationText;
            }
        }

        return $relations;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Model file deleted: '.$this->fileName);
        }
    }
}
