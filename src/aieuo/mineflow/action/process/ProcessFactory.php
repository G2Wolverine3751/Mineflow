<?php

namespace aieuo\mineflow\action\process;

class ProcessFactory {
    private static $list = [];

    public static function init(): void {
        self::register(new DoNothing);
        self::register(new EventCancel);
        /* message */
        self::register(new SendMessage);
        self::register(new SendTip);
        self::register(new SendPopup);
        self::register(new SendBroadcastMessage);
        self::register(new SendMessageToOp);
        self::register(new SendTitle);
        /* entity */
        self::register(new GetEntity);
        self::register(new Teleport);
        self::register(new Motion);
        self::register(new SetYaw);
        self::register(new AddDamage);
        self::register(new SetImmobile);
        self::register(new UnsetImmobile);
        /* player */
        self::register(new SetSleeping);
        self::register(new SetSitting);
        /* money */
        self::register(new AddMoney);
        self::register(new TakeMoney);
        self::register(new SetMoney);
        self::register(new GetMoney);
        /* script */
        self::register(new ExecuteRecipe);
        self::register(new ExecuteRecipeWithEntity);
        /* calculation */
        self::register(new FourArithmeticOperations);
        self::register(new Calculate);
        self::register(new GetPi);
        self::register(new GetE);
        /* variable */
        self::register(new AddVariable);
        self::register(new DeleteVariable);
        self::register(new AddListVariable);
        self::register(new AddMapVariable);
    }

    /**
     * @param  string $id
     * @return Process|null
     */
    public static function get(string $id): ?Process {
        if (isset(self::$list[$id])) {
            return clone self::$list[$id];
        }
        return null;
    }

    /**
     * @return Process[]
     */
    public static function getByCategory(int $category): array {
        $processes = [];
        foreach (self::$list as $process) {
            if ($process->getCategory() === $category) $processes[] = $process;
        }
        return $processes;
    }

    /**
     * @return array
     */
    public static function getAll(): array {
        return self::$list;
    }

    /**
     * @param  Process $process
     */
    public static function register(Process $process): void {
        self::$list[$process->getId()] = clone $process;
    }
}