<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Enums;

/**
 * Class CampaignPriority
 * @package CmeKernel\Enums
 *
 * @method static LOW
 * @method static NORMAL
 * @method static MEDIUM
 * @method static HIGH
 */
class CampaignPriority extends CmeEnum
{
  const __default = self::LOW;
  const LOW       = 1;
  const NORMAL    = 2;
  const MEDIUM    = 3;
  const HIGH      = 4;
}
