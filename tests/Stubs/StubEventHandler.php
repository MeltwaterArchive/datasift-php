<?php

namespace DataSift\Tests\Stubs;

use DataSift_IStreamConsumerEventHandler;

class StubEventHandler implements DataSift_IStreamConsumerEventHandler
{
    public function onConnect($consumer)
    {
    }

    public function onInteraction($consumer, $interaction, $hash)
    {
    }

    public function onDeleted($consumer, $interaction, $hash)
    {
    }

    public function onStatus($consumer, $type, $info)
    {
    }

    public function onWarning($consumer, $message)
    {
    }

    public function onError($consumer, $message)
    {
    }

    public function onDisconnect($consumer)
    {
    }

    public function onStopped($consumer, $reason)
    {
    }
}
