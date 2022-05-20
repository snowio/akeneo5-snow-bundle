<?php

namespace Snowio\Bundle\CsvConnectorBundle\Job\JobParameters\DefaultValuesProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\DefaultValueProvider\ProductCsvExport as BaseProductCsvExport;

class ProductDefaultValues extends BaseProductCsvExport implements DefaultValuesProviderInterface
{
    use DefaultValuesTrait;

    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
