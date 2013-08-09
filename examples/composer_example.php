<?php

require __DIR__.'/../vendor/autoload.php';

// An object of this type will receive events
class EventHandler implements DataSift_IStreamConsumerEventHandler
{
	public function onInteraction($consumer, $interaction, $hash)
	{
		echo $interaction['interaction']['content']."\n";
	}

	// Ignore the other events for the purposes of this example.
	public function onConnect($consumer)                      { }
		public function onDeleted($consumer, $interaction, $hash) { }
		public function onStatus($consumer, $type, $info)         { }
		public function onWarning($consumer, $message)            { }
		public function onError($consumer, $message)              { }
		public function onDisconnect($consumer)                   { }
		public function onStopped($consumer, $reason)             { }
}

// Create the user
$user = new DataSift_User('username', 'api_key');
// Create a definition looking for the word "datasift"
$def = $user->createDefinition('interaction.content contains "datasift"');
// Get an HTTP stream consumer for that definition
$consumer = $def->getConsumer(DataSift_StreamConsumer::TYPE_HTTP, new EventHandler());
// Consume it - this will not return unless the stream gets disconnected
$consumer->consume();
