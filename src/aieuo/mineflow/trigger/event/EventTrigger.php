<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\trigger\Trigger;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class EventTrigger extends Trigger {

    public static function create(string $eventName, string $subKey = ""): EventTrigger {
        return EventTriggerList::get($eventName) ?? new EventTrigger($eventName, $subKey);
    }

    public function __construct(string $key, string $subKey = "") {
        parent::__construct(Trigger::TYPE_EVENT, $key, $subKey);
    }

    /**
     * @param Event $event
     * @return Player|Entity|null
     */
    public function getTargetEntity(Event $event): ?Entity {
        if ($event instanceof PlayerEvent or $event instanceof CraftItemEvent) {
            $target = $event->getPlayer();
        } elseif ($event instanceof EntityDamageByEntityEvent) {
            $target = $event->getDamager();
        } elseif ($event instanceof EntityEvent) {
            $target = $event->getEntity();
        } else {
            $target = null;
        }
        return $target;
    }

    /**
     * @param Event $event
     * @return array<string, Variable>
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getVariables($event): array {
        $target = $this->getTargetEntity($event);
        if ($target === null) return [];
        return DefaultVariables::getEntityVariables($this->getTargetEntity($event));
    }
}