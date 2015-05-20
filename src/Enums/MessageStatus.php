<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Enums;

/**
 * Class MessageStatus
 * @package CmeKernel\Enums
 *
 * @method static PENDING
 * @method static SENT
 * @method static PAUSED
 * @method static FAILED
 */
class MessageStatus extends CmeEnum
{
  const __default    = self::PENDING;
  const PENDING      = "Pending";
  const SENT         = "Sent";
  const PAUSED       = "Paused";
  const FAILED       = "Failed";
}
