<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Enums;

/**
 * Class CampaignStatus
 * @package CmeKernel\Enums
 *
 * @method static PENDING
 * @method static QUEUED
 * @method static QUEUING
 * @method static SENDING
 * @method static SENT
 * @method static PAUSED
 * @method static ABORTED
 */
class CampaignStatus extends CmeEnum
{
  const __default    = self::PENDING;
  const PENDING      = "Pending";
  const QUEUING      = "Queuing";
  const QUEUED       = "queued";
  const SENDING      = "Sending";
  const SENT         = "Sent";
  const PAUSED       = "Paused";
  const ABORTED      = "Aborted";
}
