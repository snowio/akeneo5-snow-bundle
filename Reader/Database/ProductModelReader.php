<?php

namespace Snowio\Bundle\CsvConnectorBundle\Reader\Database;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Reader\Database\ProductReader as BaseProductModelReader;

class ProductModelReader extends BaseProductModelReader
{
    protected function getConfiguredFilters()
    {
        return [];
    }
}
