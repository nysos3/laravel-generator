<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;

class APITransformerGenerator extends BaseGenerator
{
  /** @var CommandData */
  private $commandData;

  /** @var string */
  private $path;

  /** @var string */
  private $fileName;

  public function __construct(CommandData $commandData)
  {
    $this->commandData = $commandData;
    $this->path = $commandData->config->pathApiTransformer;
    $this->fileName = $this->commandData->modelName . 'Transformer.php';
  }

  public function generate()
  {
    $templateData = get_template('api.controller.api_transformer', 'laravel-generator');

    $templateData = fill_template($this->commandData->dynamicVars, $templateData);

    $templateData = str_replace('$RELATION_NAMES$',implode(','.infy_nl_tab(1, 2),$this->getRelationNames()),$templateData);

    $templateData = str_replace(
      '$RELATIONS$',
      fill_template($this->commandData->dynamicVars, implode(PHP_EOL.infy_nl_tab(1, 1), $this->generateRelations())),
      $templateData
    );

    FileUtil::createFile($this->path, $this->fileName, $templateData);

    $this->commandData->commandComment("\nAPI Transformer created: ");
    $this->commandData->commandInfo($this->fileName);
  }

  private function generateRelations()
  {
    $relations = [];

    foreach ($this->commandData->relations as $relation) {
      $relationText = $relation->getRelationFunctionText();
      if (!empty($relationText)) {
        $relations[] = $relationText;
      }
    }

    return $relations;
  }

  private function getRelationNames()
  {
    $relations = [];

    foreach ($this->commandData->relations as $relation) {
      $relationText = $relation->getRelationName();
      if (!empty($relationText)) {
        $relations[] = "'$relationText'";
      }
    }

    return $relations;
  }


  public function rollback()
  {
    if ($this->rollbackFile($this->path, $this->fileName)) {
      $this->commandData->commandComment('API Transformer file deleted: ' . $this->fileName);
    }
  }
}
