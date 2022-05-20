<?php

namespace Snowio\Bundle\CsvConnectorBundle\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Tool\Component\Connector\Job\JobParameters\DefaultValuesProvider\SimpleCsvExport as BaseSimpleCsvExport;

class SimpleDefaultValues extends BaseSimpleCsvExport implements DefaultValuesProviderInterface
{
    use DefaultValuesTrait;

    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
