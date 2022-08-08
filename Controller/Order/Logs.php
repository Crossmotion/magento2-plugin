<?php

namespace Paynl\Payment\Controller\Order;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;

class Logs extends \Magento\Framework\App\Action\Action
{
    protected $fileFactory;
    protected $directoryList;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        return parent::__construct($context);
    }


    private function downloadPayLog()
    {
        # Just download the PAY. logs
        $content['type'] = 'filename';
        $content['value'] = 'log/pay.log';
        $content['rm'] = 0;
        $dir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $filePath = $dir . '/var/log/pay.log';

        if (file_exists($filePath)) {
            $this->fileFactory->create('pay.log', $content, DirectoryList::VAR_DIR);
        }
    }

    public function execute()
    {
        if (!class_exists('\ZipArchive')) {
            # Zipping is not possible, so trying to download only pay.log
            $this->downloadPayLog();
        }

        $dir = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $rootPath = $dir . '/var/log';

        try {
            $bDirChange = chdir($rootPath);
        } catch (\Exception $e) {
            $bDirChange = false;
        }

        if ($bDirChange) {
            $zip = new \ZipArchive();
            $zip->open('logs.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootPath), \RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();

            $content['type'] = 'filename';
            $content['value'] = 'log/logs.zip';
            $content['rm'] = 1;
            $this->fileFactory->create('logs-' . date("Y-m-d") . '.zip', $content, DirectoryList::VAR_DIR);
        } else {
            $this->downloadPayLog();
        }
    }
}
