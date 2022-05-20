<?php

namespace spec\Snowio\Bundle\CsvConnectorBundle\Job\JobParameters\DefaultValuesProvider;

use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Tool\Component\Batch\Job\JobInterface;

class ProductDefaultValuesSpec extends ObjectBehavior
{
    public function let(
        DefaultValuesProviderInterface $decoratedProvider,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->beConstructedWith($decoratedProvider, $channelRepository, $localeRepository, ['my_supported_job_name']);
    }

    // @codingStandardsIgnoreLine
    function it_is_a_default_values()
    {
        $this->shouldImplement('Akeneo\Tool\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface');
    }

    // @codingStandardsIgnoreLine
    function it_supports_a_job(JobInterface $job)
    {
        $job->getName()->willReturn('my_supported_job_name');
        $this->supports($job)->shouldReturn(true);
    }

    // @codingStandardsIgnoreLine
    function it_provides_default_values(
        $decoratedProvider,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $channel->getCode()->willReturn('channel_code');
        $channelRepository->getFullChannels()->willReturn([$channel]);

        $locale->getCode()->willReturn('locale_code');
        $localeRepository->getActivatedLocaleCodes()->willReturn([$locale]);

        $decoratedProvider->getDefaultValues()->willReturn(['decoratedParam' => true]);
        $this->getDefaultValues()->shouldReturnWellFormedDefaultValues();
    }

    public function getMatchers()
    {
        return [
            'returnWellFormedDefaultValues' => function ($parameters) {
                return true === $parameters['decoratedParam'] &&
                    '.' === $parameters['decimalSeparator'] &&
                    '' === $parameters['endpoint'] &&
                    '' === $parameters['secretKey'] &&
                    '' === $parameters['applicationId'] &&
                    '' === $parameters['exportDir'];
            }
        ];
    }
}
