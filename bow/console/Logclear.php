<?php
namespace Bow\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remove log dir runtime files
 * name: log:remove
 */
class Logclear extends Command
{

	protected function configure()
    {
        $this
            ->setName('log:clear')
            ->setDescription('Remove log dir runtime files')
            // ->addArgument(
            //     'dir',
            //     InputArgument::OPTIONAL,
            //     'Where do you want to remove?'
            // )
            // ->addOption(
            //    'yell',
            //    null,
            //    InputOption::VALUE_NONE,
            //    'If set, the task will yell in uppercase letters'
            // )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $dir = $input->getArgument('dir');
        // if ($dir) {
        //     $rm =  __DIR__ '/../../logs'. $dir;
        // }

        // if ($input->getOption('yell')) {
        //     $text = strtoupper($text);
        // }

        $dir =  __DIR__ . '/../../logs/*';

		if (strtoupper(substr(PHP_OS, 0 , 3)) == 'WIN') {
			$code = "rmdir /s/q " . $dir;
		}else {
			$code = "rm -Rf " . $dir;
		}

		exec($code);

        $output->writeln('remove success.' . $code);
    }
}
