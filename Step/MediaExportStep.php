<?php

namespace Snowio\Bundle\CsvConnectorBundle\Step;

use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use Akeneo\Tool\Component\FileStorage\Exception\FileTransferException;
use Snowio\Bundle\CsvConnectorBundle\MediaExport\ExportLocation;
use Snowio\Bundle\CsvConnectorBundle\MediaExport\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * This class rsyncs media files to a configurable location and logs some info a separate log file
 * Forcing reconnect to MySQL was looked into as part of this, which is not necessary:
 * @see \Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository::updateStepExecution
 */
class MediaExportStep extends AbstractStep
{
    /** @var ExportLocation */
    protected $exportLocation;

    /** @var Logger */
    private $logger;

    /** @var string */
    private $assetStorageDir;

    /**
     * MediaExportStep constructor.
     * @param string $name
     * @param EventDispatcherInterface $eventDispatcher
     * @param JobRepositoryInterface $jobRepository
     * @param ExportLocation $exportLocation
     * @param Logger $logger
     * @param string $assetStorageDir
     */
    public function __construct(
        $name,
        EventDispatcherInterface $eventDispatcher,
        JobRepositoryInterface $jobRepository,
        ExportLocation $exportLocation,
        Logger $logger,
        $assetStorageDir = ''
    ) {
        parent::__construct($name, $eventDispatcher, $jobRepository);
        $this->exportLocation = $exportLocation;
        $this->logger = $logger;
        $this->assetStorageDir = rtrim($assetStorageDir, "/");
    }

    /**
     * Extension point for subclasses to execute business logic. Subclasses should set the {@link ExitStatus} on the
     * {@link StepExecution} before returning.
     *
     * Do not catch exception here. It will be correctly handled by the execute() method.
     *
     * @param StepExecution $stepExecution the current step context
     *
     * @throws \Exception
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        try {
            $currentExportDir = rtrim($stepExecution->getJobParameters()->get('exportDir'), '/');

            $this->exportLocation->setUser($stepExecution->getJobParameters()->get('rsyncUser'));
            $this->exportLocation->setHost($stepExecution->getJobParameters()->get('rsyncHost'));
            $this->exportLocation->setDirectory($stepExecution->getJobParameters()->get('rsyncDirectory'));

            $newExportDir = rtrim($this->exportLocation->toString(), '/');

            $stepExecution->addSummaryInfo('export_location', $newExportDir);

            $stepExecution->addSummaryInfo(
                'log_file',
                $this->logger->getLogFileNameForJob($stepExecution->getJobExecution()->getId())
            );

            $output = $this->syncMedia($currentExportDir, $newExportDir, $stepExecution->getJobParameters()->get('rsyncOptions'));

            $this->writeLog(
                $this->getModifiedOutputForLog($output, $stepExecution),
                $stepExecution->getJobExecution()
            );

        } catch (FileTransferException $e) {
            $this->writeLog(
                ['Error - something went wrong during rsync.', $e->getMessage()],
                $stepExecution->getJobExecution()
            );

            //Do not rethrow the exception we want execution to proceed
        } catch(\Exception $e) {
            $this->writeLog(
                ['Error - something went wrong during media export.', $e->getMessage()],
                $stepExecution->getJobExecution()
            );
            throw $e;
        }
    }

    /**
     * @param $currentExportDir
     * @param $newExportDir
     * @param $options
     * @return array
     * @throws FileTransferException
     * @author James Pollard <jp@amp.co>
     */
    protected function syncMedia($currentExportDir, $newExportDir, $options = '')
    {
        /**
         * Intentionally copy the export csv files - often we'll need additional data in Magento, and this
         * simplifies getting it.
         * for instance, we need attributes definitions for definition providers.
         */
        exec("rsync -aK $options $currentExportDir/ $newExportDir/", $output, $status);

        if ($status !== 0) {
            throw new FileTransferException('Error - rsync failure during media export.' . implode(" : ", $output));
        }

        if (is_dir($this->assetStorageDir)) {
            exec("rsync -aK $options $this->assetStorageDir/ $newExportDir/", $assetRsyncOutput, $status);
            if ($status !== 0) {
                throw new FileTransferException('Error - rsync failure during asset export.' . implode(" : ", $assetRsyncOutput));
            }

            $output = array_merge($output, $assetRsyncOutput);
        }

        return $output;
    }

    /**
     * @param array $content
     * @author James Pollard <jp@amp.co>
     */
    protected function writeLog(array $content, JobExecution $job)
    {
        $this->logger->writeLog($content, $job->getId());
    }

    /**
     * @param array $output
     * @param StepExecution $stepExecution
     * @return array
     * @author James Pollard <jp@amp.co>
     */
    protected function getModifiedOutputForLog(array $output, StepExecution $stepExecution)
    {
        $jobParameters = $stepExecution->getJobParameters();
        $jobExecution = $stepExecution->getJobExecution();

        array_unshift(
            $output,
            '------------------------------',
            sprintf('Export Profile: %s (%s)', $jobExecution->getLabel(), $jobParameters->get('applicationId')),
            sprintf('Execution ID: %s', $jobExecution->getId()),
            date('d/m/Y H:i:s')
        );

        return $output;
    }
}
