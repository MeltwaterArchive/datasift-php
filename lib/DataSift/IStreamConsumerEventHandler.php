<?php
/**
 * DataSift client
 *
 * This software is the intellectual property of MediaSift Ltd., and is covered
 * by retained intellectual property rights, including copyright.
 * Distribution of this software is strictly forbidden under the terms of this license.
 *
 * @category  DataSift
 * @package   PHP-client
 * @author    Stuart Dallas <stuart@3ft9.com>
 * @copyright 2012 MediaSift Ltd.
 * @license   http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link      http://www.mediasift.com
 */

/**
 * The DataSift_IStreamConsumerEvents interface defines the set of methods a
 * consumer event handler must implement.
 *
 * @category DataSift
 * @package  PHP-client
 * @author   Stuart Dallas <stuart@3ft9.com>
 * @license  http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 * @link     http://www.mediasift.com
 */
interface DataSift_IStreamConsumerEventHandler
{
	/**
	 * Called when the stream is connected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 *
	 * @return void
	 */
	public function onConnect($consumer);

	/**
	 * Called for each interaction consumed.
	 *
	 * @param DataSift_StreamConsumer $consumer    The consumer sending the
	 *                                             event.
	 * @param array                   $interaction The interaction data.
	 * @param string                  $hash        The hash of the stream that
	 *                                             matched this interaction.
	 *
	 * @return void
	 */
	public function onInteraction($consumer, $interaction, $hash);

	/**
	 * Called for each deletion notification consumed.
	 *
	 * @param DataSift_StreamConsumer $consumer    The consumer sending the
	 *                                             event.
	 * @param array                   $interaction The interaction data.
	 * @param string                  $hash        The hash of the stream that
	 *                                             matched this interaction.
	 *
	 * @return void
	 */
	public function onDeleted($consumer, $interaction, $hash);

	/**
	 * Called for each status message received.
	 *
	 * @param DataSift_StreamConsumer $consumer    The consumer sending the
	 *                                             event.
	 * @param string                  $type        The status type.
	 * @param array                   $info        The data sent with the
	 *                                             status message.
	 *
	 * @return void
	 */
	public function onStatus($consumer, $type, $info);

	/**
	 * Called when a warning occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 * @param string $message The warning message.
	 *
	 * @return void
	 */
	public function onWarning($consumer, $message);

	/**
	 * Called when an error occurs or is received down the stream.
	 *
	 * @param DataSift_StreamConsumer consumer The consumer sending the event.
	 * @param string $message The error message.
	 *
	 * @return void
	 */
	public function onError($consumer, $message);

	/**
	 * Called when the stream is disconnected.
	 *
	 * @param DataSift_StreamConsumer $consumer The consumer sending the event.
	 *
	 * @return void
	 */
	public function onDisconnect($consumer);

	/**
	 * Called when the consumer stops for some reason.
	 *
	 * @param DataSift_StreamConsumer consumer The consumer sending the event.
	 * @param string $reason The reason the consumer stopped.
	 *
	 * @return void
	 */
	public function onStopped($consumer, $reason);
}
