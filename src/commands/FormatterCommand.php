<?php
namespace gossi\formatter\commands;

use gossi\formatter\Formatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FormatterCommand extends Command {

	/* (non-PHPdoc)
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure() {
		$this
			->setName('format')
			->setDescription('Beautify PHP Code')
			->addArgument(
				'input',
				InputArgument::REQUIRED,
				'The input'
			)
// 			->addArgument(
// 				'output',
// 				InputArgument::OPTIONAL,
// 				'The output'
// 			)
			->addOption(
				'profile',
				null,
				InputOption::VALUE_OPTIONAL,
				'The profile with the formatting options'
			)
		;
	}

	protected function execute(InputInterface $in, OutputInterface $output) {
		$in = $in->getArgument('input');
// 		$out = $in->getArgument('output');

		$code = file_exists($in) ? file_get_contents($in) : null;

		if ($code !== null) {
			$formatter = new Formatter();
			$beauty = $formatter->format($code);

			echo $output->writeln($beauty);
		}
	}

}
