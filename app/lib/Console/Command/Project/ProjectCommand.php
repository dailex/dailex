<?php

namespace Dailex\Console\Command\Project;

use Dailex\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

abstract class ProjectCommand extends Command
{
    protected function generateSettings(OutputInterface $output, array $settings)
    {
        $projectFile = $this->configProvider->get('app.config_dir').'/project.yml';
        $currentSettings = Yaml::parse(file_get_contents($projectFile));
        $mergedSettings = array_replace_recursive($currentSettings, $settings);

        (new Filesystem)->dumpFile(
            $projectFile,
            sprintf($this->getProjectTemplate(), Yaml::dump($mergedSettings, 8, 2))
        );

        $output->writeln('');
        $output->writeln('Project settings have been updated in ' . $projectFile);
        $output->writeln('');
        $output->writeln('    All available console commands are listed here:');
        $output->writeln('');
        $output->writeln('    bin/dailex');
        $output->writeln('');
        $output->writeln('    Please review and modify configuration as required! Happy scaling ;)');
        $output->writeln('');
    }

    protected function getProjectTemplate()
    {
        return <<<SETTINGS
#
# Dailex project settings.
#

%s
SETTINGS;
    }
}
