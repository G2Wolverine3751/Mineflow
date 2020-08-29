<?php

namespace aieuo\mineflow\flowItem\action;

use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\ExampleInput;
use aieuo\mineflow\formAPI\Form;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\Player;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\element\Label;
use aieuo\mineflow\formAPI\CustomForm;
use pocketmine\Server;

class GetPlayerByName extends Action {

    protected $id = self::GET_PLAYER;

    protected $name = "action.getPlayerByName.name";
    protected $detail = "action.getPlayerByName.detail";
    protected $detailDefaultReplace = ["name", "result"];

    protected $category = Category::PLAYER;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;
    protected $returnValueType = self::RETURN_VARIABLE_NAME;

    /** @var string */
    private $playerName;
    /** @var string */
    private $resultName;

    public function __construct(string $name = "", string $result = "player") {
        $this->playerName = $name;
        $this->resultName = $result;
    }

    public function setPlayerName(string $name): self {
        $this->playerName = $name;
        return $this;
    }

    public function getPlayerName(): string {
        return $this->playerName;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getPlayerName() !== "" and !empty($this->getResultName());
    }

    public function getDetail(): string {
        if (!$this->isDataValid()) return $this->getName();
        return Language::get($this->detail, [$this->getPlayerName(), $this->getResultName()]);
    }

    public function execute(Recipe $origin): bool {
        $this->throwIfCannotExecute();

        $name = $origin->replaceVariables($this->getPlayerName());
        $resultName = $origin->replaceVariables($this->getResultName());

        $player = Server::getInstance()->getPlayer($name);
        if (!($player instanceof Player)) {
            throw new \UnexpectedValueException(Language::get("flowItem.error", [$this->getName(), ["action.getPlayerByName.player.notFound"]]));
        }

        $result = new PlayerObjectVariable($player, $resultName, $player->getName());
        $origin->addVariable($result);
        return true;
    }

    public function getEditForm(array $default = [], array $errors = []): Form {
        return (new CustomForm($this->getName()))
            ->setContents([
                new Label($this->getDescription()),
                new ExampleInput("@action.getPlayerByName.form.target", "aieuo", $default[1] ?? $this->getPlayerName(), true),
                new ExampleInput("@flowItem.form.resultVariableName", "player", $default[2] ?? $this->getResultName(), true),
                new CancelToggle()
            ])->addErrors($errors);
    }

    public function parseFromFormData(array $data): array {
        return ["contents" => [$data[1], $data[2]], "cancel" => $data[3], "errors" => []];
    }

    public function loadSaveData(array $content): Action {
        if (!isset($content[1])) throw new \OutOfBoundsException();
        $this->setPlayerName($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getPlayerName(), $this->getResultName()];
    }

    public function getReturnValue(): string {
        return $this->getResultName();
    }
}