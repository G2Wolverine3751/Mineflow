<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\event\Event;

class EventObjectVariable extends ObjectVariable {

    public function __construct(Event $value, string $name = "", ?string $str = null) {
        $names = explode("\\", $value->getEventName());
        parent::__construct($value, $name, $str ?? end($names));
    }

    public function getValueFromIndex(string $index): ?Variable {
        $event = $this->getEvent();
        switch ($index) {
            case "name":
                $names = explode("\\", $event->getEventName());
                $variable = new StringVariable(end($names), "name");
                break;
            case "isCanceled":
                $variable = new StringVariable($event->isCancelled() ? "true" : "false", "isCanceled");
                break;
            default:
                return null;
        }
        return $variable;
    }

    public function getEvent(): Event {
        /** @var Event $value */
        $value = $this->getValue();
        return $value;
    }
}
