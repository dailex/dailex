<?php

namespace Dailex\Console\Command\Crate;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCrates extends CrateCommand
{
    protected function configure()
    {
        $this
            ->setName('crate:ls')
            ->setDescription('Lists currently installed crates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->crateMap as $name => $crate) {
            $output->writeln(sprintf('Summary for crate <options=bold>%s</>', $name));
            $output->writeln('  Location: '.$crate->getLocation());
        }
    }
}
