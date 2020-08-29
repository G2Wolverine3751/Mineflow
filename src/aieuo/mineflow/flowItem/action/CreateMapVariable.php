<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\Variable;
use aieuo\mineflow\variable\MapVariable;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\Main;
use aieuo\mineflow\formAPI\element\Toggle;

class CreateMapVariable extends Action {

    protected $id = self::CREATE_MAP_VARIABLE;

    protected $name = "action.createMapVariable.name";
    protected $detail = "action.createMapVariable.detail";
    protected $detailDefaultReplace = ["name", "scope", "key", "value"];

    protected $category = Category::VARIABLE;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    /** @var string */
    private $variableName;
    /** @var array */
    private $variableKey;
    /** @var array */
    private $variableValue;
    /** @var bool */
    private $isLocal;

    public function __construct(string $name = "", string $key = "", string $value = "", bool $local = true) {
        $this->variableName = $name;
        $this->variableKey = array_map("trim", explode(",", $key));
        $this->variableValue = array_map("trim", explode(",", $value));
        $this->isLocal = $local;
    }

    public function setVariableName(string $variableName) {
        $this->variableName = $variableName;
    }

    public function getVariableName(): string {
        return $this->variableName;
    }

    public function setKey(array $variableKey) {
        $this->variableKey = $variableKey;
    }

    public function getKey(): array {
        return $this->variableKey;
    }

    public function setVariableValue(array $variableValue) {
        $this->variableValue = $variableValue;
    }

    public function getVariableValue(): array {
        return $this->variableValue;
    }

    public function isDataValid(): bool {
        return $this->variableName !== "" and ($this->variableKey !== "");
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getVariableName(), $this->isLocal ? "local" : "global", implode(",", $this->getKey()), implode(",", $this->getVariableValue())]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $helper = Main::getVariableHelper();
        $name = $origin->replaceVariables($this->getVariableName());
        $keys = $this->getKey();
        $values = $this->getVariableValue();

        $variable = new MapVariable([], $name);
        for ($i=0; $i<count($keys); $i++) {
            $key = $keys[$i];
            $value = $values[$i] ?? "";
            if ($key === "") continue;

            if (!$helper->isVariableString($value)) {
                $value = $helper->replaceVariables($value, $origin->getVariables());
                $addVariable = Variable::create($helper->currentType($value), $key, $helper->getType($value));
            } else {
                $addVariable = $origin->getVariable(substr($value, 1, -1)) ?? $helper->get(substr($value, 1, -1));
                if ($addVariable === null) {
                    $value = $helper->replaceVariables($value, $origin->getVariables());
                    $addVariable = Variable::create($helper->currentType($value), $key, $helper->getType($value));
                } else {
                    $addVariable->setName($key);
                }
            }

            $variable->addValue($addVariable);
        }

        if ($this->isLocal) {
            $origin->addVariable($variable);
        } else {
            $helper->add($variable);
        }
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.variable.form.name", "aieuo", $default[1] ?? $this->getVariableName(), true),
                new ExampleInput("@action.variable.form.key", "auieo", $default[2] ?? implode(",", $this->getKey()), false),
                new ExampleInput("@action.variable.form.value", "aeiuo", $default[3] ?? implode(",", $this->getVariableValue()), false),
                new Toggle("@action.variable.form.global", $default[4] ?? !$this->isLocal),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        $errors = [];
        $name = $data[1];
        $key = array_map("trim", explode(",", $data[2]));
        $value = array_map("trim", explode(",", $data[3]));
        return ["contents" => [$name, $key, $value, !$data[4]], "cancel" => $data[5], "errors" => $errors];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[3])) throw new \OutOfBoundsException();
        $this->setVariableName($content[0]);
        $this->setKey($content[1]);
        $this->setVariableValue($content[2]);
        $this->isLocal = $content[3];
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getVariableName(), $this->getKey(), $this->getVariableValue(), $this->isLocal];
    }
}