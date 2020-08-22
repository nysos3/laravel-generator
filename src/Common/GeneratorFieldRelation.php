<?php

namespace InfyOm\Generator\Common;

use Illuminate\Support\Str;

class GeneratorFieldRelation
{
    /** @var string */
    public $type;
    public $inputs;
    public $relationName;

    public static function parseRelation($relationInput)
    {
        $inputs = explode(',', $relationInput);

        $relation = new self();
        $relation->type = array_shift($inputs);
        $modelWithRelation = explode(':', array_shift($inputs)); //e.g ModelName:relationName
        if (count($modelWithRelation) == 2) {
            $relation->relationName = $modelWithRelation[1];
            unset($modelWithRelation[1]);
        }
        $relation->inputs = array_merge($modelWithRelation, $inputs);

        return $relation;
    }

    public function getRelationFunctionText()
    {
        $singularRelation = (!empty($this->relationName)) ? $this->relationName : Str::snake($this->inputs[0]);
        $pluralRelation = (!empty($this->relationName)) ? $this->relationName : Str::snake(Str::plural($this->inputs[0]));

        switch ($this->type) {
            case '1t1':
                $functionName = $singularRelation;
                $relation = 'hasOne';
                $relationClass = 'HasOne';
                break;
            case '1tm':
                $functionName = $pluralRelation;
                $relation = 'hasMany';
                $relationClass = 'HasMany';
                break;
            case 'mt1':
                if (!empty($this->relationName)) {
                    $singularRelation = $this->relationName;
                } elseif (isset($this->inputs[1])) {
                    $singularRelation = Str::snake(str_replace('_id', '', strtolower($this->inputs[1])));
                }
                $functionName = $singularRelation;
                $relation = 'belongsTo';
                $relationClass = 'BelongsTo';
                break;
            case 'mtm':
                $functionName = $pluralRelation;
                $relation = 'belongsToMany';
                $relationClass = 'BelongsToMany';
                break;
            case 'hmt':
                $functionName = $pluralRelation;
                $relation = 'hasManyThrough';
                $relationClass = 'HasManyThrough';
                break;
            default:
                $functionName = '';
                $relation = '';
                $relationClass = '';
                break;
        }

        if (!empty($functionName) and !empty($relation)) {
            return $this->generateRelation($functionName, $relation, $relationClass);
        }

        return '';
    }

    public function getRelationTransformerText($modelName)
    {
        $singularRelation = (!empty($this->relationName)) ? $this->relationName : Str::snake($this->inputs[0]);
        $pluralRelation = (!empty($this->relationName)) ? $this->relationName : Str::snake(Str::plural($this->inputs[0]));

        switch ($this->type) {
            case '1t1':
                $functionName = $singularRelation;
                break;
            case '1tm':
                $functionName = $pluralRelation;
                break;
            case 'mt1':
                if (!empty($this->relationName)) {
                    $singularRelation = $this->relationName;
                } elseif (isset($this->inputs[1])) {
                    $singularRelation = Str::snake(str_replace('_id', '', strtolower($this->inputs[1])));
                }
                $functionName = $singularRelation;
                break;
            case 'mtm':
                $functionName = $pluralRelation;
                break;
            case 'hmt':
                $functionName = $pluralRelation;
                break;
            default:
                $functionName = '';
                break;
        }

        if (!empty($functionName)) {
            return $this->generateTransformerRelation($functionName, $modelName);
        }

        return '';
    }

    public function getRelationName()
    {
        $singularRelation = (!empty($this->relationName)) ? $this->relationName : Str::snake($this->inputs[0]);
        $pluralRelation = (!empty($this->relationName)) ? $this->relationName : Str::snake(Str::plural($this->inputs[0]));

        switch ($this->type) {
            case '1t1':
                $functionName = $singularRelation;
                break;
            case '1tm':
                $functionName = $pluralRelation;
                break;
            case 'mt1':
                if (!empty($this->relationName)) {
                    $singularRelation = $this->relationName;
                } elseif (isset($this->inputs[1])) {
                    $singularRelation = Str::snake(str_replace('_id', '', strtolower($this->inputs[1])));
                }
                $functionName = $singularRelation;
                break;
            case 'mtm':
                $functionName = $pluralRelation;
                break;
            case 'hmt':
                $functionName = $pluralRelation;
                break;
            default:
                $functionName = '';
                $relation = '';
                $relationClass = '';
                break;
        }

        if (!empty($functionName) and !empty($relation)) {
            return $functionName;
        }

        return '';
    }

    public function getRelationFunctionTextVue()
    {
        $modelName = $this->inputs[0];
        switch ($this->type) {
            case '1t1':
                $functionName = Str::snake($modelName);
                $relation = 'hasOne';
                break;
            case '1tm':
                $functionName = Str::snake(Str::plural($modelName));
                $relation = 'hasMany';
                break;
            case 'mt1':
                $functionName = Str::snake($modelName);
                $relation = 'belongsTo';
                break;
            case 'mtm':
                $functionName = Str::snake(Str::plural($modelName));
                $relation = 'belongsTo';
                break;
            case 'hmt':
                $functionName = Str::snake(Str::plural($modelName));
                $relation = 'hasMany';
                break;
            default:
                $functionName = '';
                $relation = '';
                break;
        }

        if (!empty($functionName) and !empty($relation)) {
            $modelPlural = Str::snake(Str::plural($modelName));
            return "{$functionName}: {$relation}('{$modelPlural}')";
        }

        return '';
    }

    private function generateRelation($functionName, $relation, $relationClass)
    {
        $inputs = $this->inputs;
        $modelName = array_shift($inputs);

        $template = get_template('model.relationship', 'laravel-generator');

        $template = str_replace('$RELATIONSHIP_CLASS$', $relationClass, $template);
        $template = str_replace('$FUNCTION_NAME$', $functionName, $template);
        $template = str_replace('$RELATION$', $relation, $template);
        $template = str_replace('$RELATION_MODEL_NAME$', $modelName, $template);

        if (count($inputs) > 0) {
            $inputFields = implode("', '", $inputs);
            $inputFields = ", '".$inputFields."'";
        } else {
            $inputFields = '';
        }

        $template = str_replace('$INPUT_FIELDS$', $inputFields, $template);

        return $template;
    }

    private function generateTransformerRelation($functionName, $modelName)
    {
        $inputs = $this->inputs;
        $relationModelName = array_shift($inputs);

        $template = get_template('model.relationship_transformer', 'laravel-generator');

        $template = str_replace('$FUNCTION_NAME$', 'include'. ucfirst(Str::camel($functionName)), $template);
        $template = str_replace('$RELATION_MODEL_NAME$', ucfirst(Str::camel($relationModelName)), $template);
        $template = str_replace('$RELATION_MODEL_NAME_SNAKE$', Str::snake(lcfirst($relationModelName)), $template);
        $template = str_replace('$RELATION_NAME$', ucfirst(Str::camel($functionName)), $template);
        $template = str_replace('$RELATION_NAME_SNAKE$', Str::snake(lcfirst($functionName)), $template);
        $template = str_replace('$MODEL_NAME$', $modelName, $template);
        $template = str_replace('$MODEL_NAME_SNAKE$', Str::snake($modelName), $template);

        if (count($inputs) > 0) {
            $inputFields = implode("', '", $inputs);
            $inputFields = ", '".$inputFields."'";
        } else {
            $inputFields = '';
        }

        $template = str_replace('$INPUT_FIELDS$', $inputFields, $template);

        return $template;
    }
}
