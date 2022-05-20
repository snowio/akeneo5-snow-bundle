<?php

namespace Snowio\Bundle\CsvConnectorBundle\Writer\File\Csv;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Writer\File\Csv\ProductModelWriter as BaseProductModelWriter;

class ProductModelWriter extends BaseProductModelWriter
{
    use WriterOverriderTrait;
}
