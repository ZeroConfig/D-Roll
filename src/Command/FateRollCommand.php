<?php
namespace ZeroConfig\D\Command;

use RangeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ZeroConfig\D\RollInterface;

class FateRollCommand extends AbstractInterpreterCommand
{
    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('fate');
        $this->setDescription('Tempt fate and reveal your path.');
        $this->addArgument(
            'numDice',
            InputArgument::REQUIRED,
            'The number of dice to roll. Needs to be <comment>2 or higher</comment>.'
        );
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_OPTIONAL,
            'The configuration for fate translations',
            realpath(__DIR__ . '/../../config/fate.json')
        );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numDice = (int)$input->getArgument('numDice');

        if ($numDice < 2) {
            throw new RangeException(
                'The number of dice needs to be at least 2.'
            );
        }

        $dice = $this
            ->getInterpreter()
            ->interpretDice(sprintf('%dd3', $numDice));

        $rolls = array_map(
            function (RollInterface $roll) : int {
                return $roll->getValue() - 2;
            },
            iterator_to_array($dice->roll())
        );

        $fate = array_sum($rolls);

        sort($rolls);

        foreach ($rolls as $roll) {
            $output->write($this->translateRollValue($roll));
        }
        $output->writeln('');

        $output->writeln(
            $this->translateFate($input, $fate)
        );
    }

    /**
     * Translate the given fate into a visual representation.
     *
     * @param InputInterface $input
     * @param int            $fate
     *
     * @return string
     */
    private function translateFate(InputInterface $input, int $fate): string
    {
        $config = json_decode(
            file_get_contents($input->getOption('config')),
            true
        );

        if (array_key_exists($fate, $config)) {
            $translation = $config[$fate];
        } else {
            $translation = $config[$fate < 0 ? '<' : '>'];
        }

        return sprintf(
            '<fg=%s>%s%d %s</>',
            $translation['color'],
            $fate > 0 ? '+' : '',
            $fate,
            $translation['label']
        );
    }

    /**
     * Translate the given roll value into a visual representation.
     *
     * @param int $value
     *
     * @return string
     */
    private function translateRollValue(int $value) : string
    {
        static $translations = [
            -1 => '<fg=red>[-]</>',
            0  => '<fg=yellow>[ ]</>',
            1  => '<fg=green>[+]</>'
        ];

        return $translations[$value];
    }
}
