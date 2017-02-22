<?php
namespace ZeroConfig\D\Command;

use Symfony\Component\Console\Command\Command;
use ZeroConfig\D\DiceInterpreterInterface;

abstract class AbstractInterpreterCommand extends Command
{
    /** @var DiceInterpreterInterface */
    private $interpreter;

    /**
     * Constructor.
     *
     * @param DiceInterpreterInterface $interpreter
     */
    final public function __construct(DiceInterpreterInterface $interpreter)
    {
        parent::__construct('interpret');
        $this->interpreter = $interpreter;
    }

    /**
     * Get the dice interpreter.
     *
     * @return DiceInterpreterInterface
     */
    final protected function getInterpreter(): DiceInterpreterInterface
    {
        return $this->interpreter;
    }
}
