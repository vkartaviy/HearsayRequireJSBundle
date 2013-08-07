<?php

namespace Hearsay\RequireJSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command that places bundle web assets into a given directory.
 */
class AssetsInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('requirejs:assets:install')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ))
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->setDescription('Installs requirejs assets under a public web directory')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs requirejs assets into a given
directory (e.g. the web directory).

<info>php %command.full_name% web</info>

To create a symlink instead of copying its assets, use the <info>--symlink</info> option:

<info>php %command.full_name% web --symlink</info>

EOT
            );
    }

    /**
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        /* @var $container \Symfony\Component\DependencyInjection\ContainerInterface */
        $container = $this->getContainer();

        $webDir = $targetArg . '/' . $container->getParameter('hearsay_require_js.web_dir');

        $filesystem = $container->get('filesystem');
        $filesystem->mkdir($webDir, 0777);

        $output->writeln(sprintf("Installing assets using the <comment>%s</comment> option", $input->getOption('symlink') ? 'symlink' : 'hard copy'));

        $mapping = $container->get('hearsay_require_js.namespace_mapping');

        foreach ($mapping->getNamespaces() as $filepath => $info) {
            $namespace = $info['namespace'];
            $isDir = $info['is_dir'];

            $target = $webDir;

            if (strlen($namespace) > 0) {
                $target .= '/' . $namespace;
            }

            if (!$isDir) {
                $target .= '.js';
            }

            $output->writeln(sprintf('Installing assets for <comment>%s</comment> into <comment>%s</comment>', $filepath, $target));

            $filesystem->remove($target);

            if ($input->getOption('symlink')) {
                $filesystem->symlink($filepath, $target);
            } else {
                if ($isDir) {
                    $filesystem->mkdir($target, 0777);
                    $filesystem->mirror($filepath, $target, Finder::create()->in($filepath), array('delete' => true, 'override' => true));
                } else {
                    $filesystem->copy($filepath, $target, true);
                }
            }
        }
    }
}
