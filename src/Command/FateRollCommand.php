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
            'skillBonus',
            InputArgument::OPTIONAL,
            'The skill bonus to add to the fate roll. Must be 0 or higher.',
            '0'
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
        $skill = (int)$input->getArgument('skillBonus');

        if ($skill < 0) {
            throw new RangeException(
                'The skill bonus cannot be a negative number.'
            );
        }

        $dice = $this
            ->getInterpreter()
            ->interpretDice(sprintf('4d3+%d', $skill));

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
        $output->writeln(sprintf(' +%d', $dice->getModifier()));

        $output->writeln(
            $this->translateFate($input, $fate + $dice->getModifier())
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
