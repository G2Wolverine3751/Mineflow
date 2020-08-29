<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Server;

class CommandConsole extends Action {

    protected $id = self::COMMAND_CONSOLE;

    protected $name = "action.commandConsole.name";
    protected $detail = "action.commandConsole.detail";
    protected $detailDefaultReplace = ["command"];

    protected $category = Category::COMMAND;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    protected $permission = self::PERMISSION_LEVEL_1;

    /** @var string */
    private $command;

    public function __construct(string $command = "") {
        $this->command = $command;
    }

    public function setCommand(string $health) {
        $this->command = $health;
    }

    public function getCommand(): string {
        return $this->command;
    }

    public function isDataValid(): bool {
        return $this->command !== "";
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getCommand()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $command = $origin->replaceVariables($this->getCommand());

        Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $command);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.command.form.command", "mineflow", $default[1] ?? $this->getCommand(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1]], "cancel" => $data[2], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[0])) throw new \OutOfBoundsException();
        $this->setCommand($content[0]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getCommand()];
    }
}