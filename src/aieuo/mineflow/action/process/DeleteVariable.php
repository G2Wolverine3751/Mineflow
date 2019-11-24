<?php

namespace aieuo\mineflow\action\process;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Logger;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\element\Input;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\Main;
use aieuo\mineflow\FormAPI\element\Toggle;

class DeleteVariable extends Process {

    protected $id = self::DELETE_VARIABLE;

    protected $name = "@action.deleteVariable.name";
    protected $description = "@action.deleteVariable.description";
    protected $detail = "action.deleteVariable.detail";

    protected $category = Categories::CATEGORY_ACTION_VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var bool */
    private $isLocal = true;

    public function __construct(string $name = "", bool $local = true) {
        $this->variableName = $name;
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function isDataValid(): bool {
        return !empty($this->variableName);
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global"]);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        if (!$this->isDataValid()) {
            Logger::warning(Language::get("invalid.contents", [$this->getName()]), $target);
            return null;
        }

        $name = $this->getVariableName();
        if ($origin instanceof Recipe) {
            $name = $origin->replaceVariables($name);
        }

        if (!$this->isLocal) {
            Main::getInstance()->getVariableHelper()->delete($name);
            return true;
        }
        if (!($origin instanceof Recipe)) {
            Logger::warning(Language::get("action.error", [$this->getName(), Language::get("action.error.recipe")]), $target);
            return null;
        }
        $origin->removeVariable($name);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []) {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new Input("@action.variable.form.name", Language::get("form.example", ["aieuo"]), $default[1] ?? $this->getVariableName()),
                new Toggle("@action.variable.form.global", !$this->isLocal),
                new Toggle("@form.cancelAndBack")
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        if ($name === "") {
            $errors[] = ["@form.insufficient", 1];
        }
        return ["status" => empty($errors), "contents" => [$name, !$data[2]], "cancel" => $data[3], "errors" => $errors];
    }

    public function parseFromSaveData(array $content): ?Process {
        if (!isset($content[1])) return null;
        $this->setVariableName($content[0]);
        $this->isLocal = $content[1];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->isLocal];
    }
}