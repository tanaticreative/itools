<?php

namespace Icreative\Tools\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;


class CreatePathCommand extends Command
{

    const ARGUMENT_FILE_PATH = 'path';
    protected $directory;
    protected $io;

    public function __construct(
        DirectoryList $directoryList,
        File $io,
        State $state
    )
    {
        parent::__construct();
        $this->_state = $state;
        $this->directory = $directoryList;
        $this->ioAdapter = $io;
    }

    protected function configure()
    {
        $this->setName('itools:create-path');
        $this->setDescription('Create Path');
        $this->setDefinition([
            new InputArgument(
                self::ARGUMENT_FILE_PATH,
                InputArgument::REQUIRED,
                'Path'
            ),

        ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode('adminhtml');
        $path = $input->getArgument('path');

        if (!empty($path)) {

            //$this->makePath(MAGENTO_BP . '/' . $path);
            $parts = explode('/', $path);
            $last = array_pop($parts);


            if (strpos($last, '.') !== false) {
                $path = implode('/', $parts);
            }

            $filePath = $this->directory->getPath('app') .'/code/'. $path;
            if (!is_dir($filePath)) {
                $this->ioAdapter->mkdir($filePath, 0775);
            }

            if (strpos($last, '.php') !== false) {
                $namespace = implode($parts, '\\');
                $className = explode('.', $last)[0];
                $content = "<?php \nnamespace {$namespace};\nclass {$className}{\n\n}";
                $this->ioAdapter->open(array('path' => $filePath));
                $this->ioAdapter->write($last, $content, 0644);
            }

        }

        return false;

    }


    protected function makePath($path)
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);

        if (is_dir($dir)) {
            return true;
        } else {
            if ($this->makePath($dir)) {
                if (mkdir($dir)) {
                    chmod($dir, 0775);
                    return true;
                }
            }
        }

        return false;
    }
}