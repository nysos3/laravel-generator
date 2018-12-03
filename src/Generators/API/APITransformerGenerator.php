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

    FileUtil::createFile($this->path, $this->fileName, $templateData);

    $this->commandData->commandComment("\nAPI Transformer created: ");
    $this->commandData->commandInfo($this->fileName);
  }

  public function rollback()
  {
    if ($this->rollbackFile($this->path, $this->fileName)) {
      $this->commandData->commandComment('API Transformer file deleted: ' . $this->fileName);
    }
  }
}
