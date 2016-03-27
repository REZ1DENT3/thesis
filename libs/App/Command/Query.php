<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Query extends Command
{

    protected function configure()
    {
        $start = 0;
        $stop = 100;

        $this->setName("app:query")
            ->setDescription("Display the fibonacci numbers between 2 given numbers")
            ->setDefinition(array(
                new InputOption('start', 's', InputOption::VALUE_OPTIONAL, 'Start number of the range of Fibonacci number', $start),
                new InputOption('stop', 'e', InputOption::VALUE_OPTIONAL, 'stop number of the range of Fibonacci number', $stop)
            ))
            ->setHelp(<<<EOT
Display the fibonacci numbers between a range of numbers given as parameters

Usage:

<info>php console.php phpmaster:fibonacci 2 18</info>

You can also specify just a number and by default the start number will be 0
<info>php console.php phpmaster:fibonacci 18</info>

If you don't specify a start and a stop number it will set by default [0,100]
<info>php console.php phpmaster:fibonacci</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $header_style = new OutputFormatterStyle('yellow', null, array('bold'));
        $output->getFormatter()->setStyle('header', $header_style);

        $start = intval($input->getOption('start'));
        $stop = intval($input->getOption('stop'));

        if (($start >= $stop) || ($start < 0)) {
            throw new \InvalidArgumentException('Stop number should be greater than start number');
        }

        $output->writeln('<header>Fibonacci numbers between ' . $start . ' - ' . $stop . '</header>');

        $xnM2 = 0;
        $xnM1 = 1;
        $totalFiboNr = 0;
        while ($xnM2 <= $stop) {
            if ($xnM2 >= $start) {
                $output->writeln('<header>' . $xnM2 . '</header>');
                $totalFiboNr++;
            }
            $xn = $xnM1 + $xnM2;
            $xnM2 = $xnM1;
            $xnM1 = $xn;

        }
        $output->writeln('<header>Total of Fibonacci numbers found = ' . $totalFiboNr . ' </header>');
    }

}