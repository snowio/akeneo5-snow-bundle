<?php

namespace Snowio\Bundle\CsvConnectorBundle\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider\ProductCsvExport as BaseProductCsvExport;

class ProductConstraint extends BaseProductCsvExport implements ConstraintCollectionProviderInterface
{
    use ConstraintTrait;

    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
