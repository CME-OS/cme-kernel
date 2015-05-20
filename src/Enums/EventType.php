<?php

/**
 * @author  oke.ugwu
 */
namespace CmeKernel\Enums;

/**
 * Class EventType
 * @package CmeKernel\Enums
 *
 * @method static QUEUED
 * @method static SENT
 * @method static OPENED
 * @method static CLICKED
 * @method static FAILED
 * @method static BOUNCED
 * @method static UNSUBSCRIBED
 * @method static TEST
 * @method static UNKNOWN
 */
class EventType extends CmeEnum
{
  const __default    = self::UNKNOWN;
  const QUEUED       = "queued";
  const SENT         = "sent";
  const OPENED       = "opened";
  const CLICKED      = "clicked";
  const FAILED       = "failed";
  const BOUNCED      = "bounced";
  const UNSUBSCRIBED = "unsubscribed";
  const TEST         = "test";
  const UNKNOWN      = "Unknown";
}
