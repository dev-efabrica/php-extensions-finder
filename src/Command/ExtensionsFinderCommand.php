<?php

namespace ExtensionsFinder\Command;

use Exception;
use ExtensionsFinder\ExtensionsFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExtensionsFinderCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this->setName('check')
            ->setDescription('Checks if your composer.json contains all PHP extensions used in your code')
            ->addArgument('dirs', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'List of dirs to check')
            ->addOption('composer', null, InputOption::VALUE_REQUIRED, 'Path to composer.json', 'composer.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');

        $requiredExtensions = (new ExtensionsFinder())->find($input->getArgument('dirs'));
        $composerJsonPath = $input->getOption('composer');
        if (!file_exists($composerJsonPath)) {
            throw new Exception('composer.json not found on path "' . $composerJsonPath . '"');
        }
        $composerContent = file_get_contents($composerJsonPath) ?: '';
        $composer = json_decode($composerContent, true);
        $requiredList = $composer['require'] ?? [];
        $alreadyRequiredExtensions = [];
        foreach ($requiredList as $required => $version) {
            $required = strtolower($required);
            if (strpos($required, 'ext-') === 0) {
                $alreadyRequiredExtensions[] = $required;
            }
        }

        $missingExtensions = array_diff(array_keys($requiredExtensions), $alreadyRequiredExtensions);
        if (!$missingExtensions) {
            $output->writeln('No missing PHP extensions');
            $output->writeln('');
            return 0;
        }
        $output->writeln('Missing extensions usage:', OutputInterface::VERBOSITY_VERY_VERBOSE);
        $output->writeln("=========================\n", OutputInterface::VERBOSITY_VERY_VERBOSE);
        foreach ($requiredExtensions as $requiredExtension => $usagesInFile) {
            if (!in_array($requiredExtension, $missingExtensions)) {
                continue;
            };
            $output->writeln($requiredExtension, OutputInterface::VERBOSITY_VERY_VERBOSE);
            $output->writeln(str_repeat('-', strlen($requiredExtension)), OutputInterface::VERBOSITY_VERY_VERBOSE);
            foreach ($usagesInFile as $fileName => $usages) {
                foreach ($usages as $usage) {
                    $output->writeln($fileName . ':' . $usage['line'] . ' ' . $usage['token'], OutputInterface::VERBOSITY_VERY_VERBOSE);
                }
            }
            $output->writeln('', OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $composerPatch = ['require' => array_combine($missingExtensions, array_fill(0, count($missingExtensions), '*'))];
        $output->writeln('Please, add these lines to your composer.json:');
        $output->writeln("==============================================\n");
        $output->writeln(json_encode($composerPatch, JSON_PRETTY_PRINT));
        $output->writeln('');
        return count($missingExtensions);
    }
}
