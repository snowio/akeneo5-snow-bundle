<?php

namespace Snowio\Bundle\CsvConnectorBundle\Writer\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductWriter as BaseProductWriter;

class ProductWriter extends BaseProductWriter
{
    use WriterOverriderTrait;
}
