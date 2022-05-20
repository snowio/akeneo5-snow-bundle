Akeneo 5 Snow Bundle
=====================================

This is a custom Akeneo 5 bundle.

## Purpose

This bundle provides a new connector called Snowio Connector that allows us to export all the data from just one job. It will then zip them and send to a defined snowio endpoint.

- Full Job
Export (products, product models, categories, attributes, attribute options, families and attribute groups)
- Partial Job
Export (categories, attributes, attribute options, families and attribute groups)

Both jobs have the following final steps: Generate metadata, Zip files, send to Snowio using Guzzle.

## Installation 

To install this bundle on a Akeneo 5 instance, run the following:

`composer require snowio/akeneo5-snow-bundle`

### Configure threshold check step

`Snowio\Bundle\CsvConnectorBundle\Step\CheckThresholdsStep` has an injectable export threshold, and checks this against the read count of the previous step.

Define the class as a parameter:
```
parameters:
   ...
   snowio_connector.step.check_thresholds.class: Snowio\Bundle\CsvConnectorBundle\Step\CheckThresholdsStep
```

Create services for this class:
```
services:
   ...
   snowio_connector.step.check_threshold.products:
      class: '%snowio_connector.step.check_thresholds.class%'
      arguments:
         - 'check_thresholds'
         - '@event_dispatcher'
         - '@akeneo_batch.job_repository'
         - '%minimum_products_export%'

   snowio_connector.step.check_threshold.attributes:
      class: '%snowio_connector.step.check_thresholds.class%'
      arguments:
         - 'check_thresholds'
         - '@event_dispatcher'
         - '@akeneo_batch.job_repository'
         - '%minimum_attributes_export%'
```

You need to inject the thresholds (bottom parameter) - these should be referenced by variables in `parameters.yml`.

Add your services after the steps you want to check, e.g.:

<pre><code>
services:
   ...
   snowio_connector.job.full_export:
       class: '%pim_connector.job.simple_job.class%'
       arguments:
           - '%snowio_connector.job_name.full_export%'
           - '@event_dispatcher'
           - '@akeneo_batch.job_repository'
           -
               - '@snowio_connector.step.csv_product.export'
               <b>- '@snowio_connector.step.check_threshold.products'</b>
               - '@snowio_connector.step.csv_variant_group.export'
               - '@snowio_connector.step.csv_category.export'
               - '@snowio_connector.step.csv_attribute.export'
               <b>- '@snowio_connector.step.check_threshold.attributes'</b>
               - '@snowio_connector.step.csv_attribute_option.export'
               - '@snowio_connector.step.csv_family.export'
               - '@snowio_connector.step.csv_attribute_group.export'
               - '@snowio_connector.step.metadata'
               - '@snowio_connector.step.archive'
               - '@snowio_connector.step.media_export'
               - '@snowio_connector.step.post'
<pre><code>

Version ^1.4 introduce new parameters in `config/services/services.yml` which need to be added on deployment.
```
   media_export_directory: media_export/
   media_export_host: false
   media_export_user: false
   minimum_products_export: 0
   minimum_attributes_export: 0
```
