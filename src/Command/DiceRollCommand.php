<?php
namespace ZeroConfig\D\Command;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ZeroConfig\D\RollInterface;

class DiceRollCommand extends AbstractInterpreterCommand
{
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('dice');
        $this->setDescription('Roll dice and see if lady luck smiles upon you.');
        $this->addArgument(
            'dice',
            InputArgument::IS_ARRAY |  InputArgument::REQUIRED,
            'List of dice configurations.'
        );
        $this->setHelp(<<<HELP
Roll dice using the given dice configurations. Dice configurations are a really
loose notation of human lingo for dice numbers.

<fg=red>3</><fg=yellow>d6</><fg=green>+10</> translates into <fg=red>3</> dice with <fg=yellow>6</> eyes, using a <fg=green>+10</> modifier.
HELP
);
    }

    /**
     * Execute the current command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $diceList = $this
            ->getInterpreter()
            ->interpretList(...$input->getArgument('dice'));

        $maximum       = 0;
        $total         = 0;
        $totalModifier = 0;
        $table         = new Table($output);
        $right         = new TableStyle();

        $right->setPadType(STR_PAD_LEFT);

        $table->setHeaders([
            'Dice',
            'Rolls',
            new TableCell(
                'Roll total',
                ['colspan' => 2]
            ),
            'Maximum'
        ]);
        $table->setColumnStyle(1, $right);
        $table->setColumnStyle(2, $right);
        $table->setColumnStyle(3, $right);
        $table->setColumnStyle(4, $right);

        foreach ($diceList as $numEyes => $dice) {
            $rolls = array_map(
                function (RollInterface $roll) : int {
                    return $roll->getValue();
                },
                iterator_to_array($dice->roll())
            );

            $diceMaximum    = $numEyes * count($rolls) + $dice->getModifier();
            $subTotal       = array_sum($rolls);
            $total         += $subTotal;
            $totalModifier += $dice->getModifier();
            $maximum       += $diceMaximum;

            $table->addRow([
                sprintf('%dx d%s +%d', count($rolls), $numEyes, $dice->getModifier()),
                implode(', ', $rolls),
                $subTotal,
                sprintf('+%d', $dice->getModifier()),
                $diceMaximum
            ]);
        }

        $table->addRows([
            new TableSeparator(),
            [
                '<info>Total</info>',
                new TableCell(
                    sprintf(
                        '<fg=white;options=bold,underscore>%d</>',
                        $total + $totalModifier
                    ),
                    ['colspan' => 3]
                ),
                $maximum
            ]
        ]);

        $table->render();

        if ($total === $maximum) {
            $output->write('<fg=green;options=bold>CRIT!</> ');
        }

        $output->writeln(
            sprintf(
                'Rolled a <comment>%d</comment> out of <comment>%d</comment>',
                $total + $totalModifier,
                $maximum
            )
        );
    }
}
