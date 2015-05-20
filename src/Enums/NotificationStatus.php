<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Enums;

/**
 * Class NotificationStatus
 * @package CmeKernel\Enums
 *
 * @method static READ
 * @method static UNREAD
 */
class NotificationStatus extends CmeEnum
{
  const __default = self::UNREAD;
  const READ      = "Read";
  const UNREAD    = "Unread";
}
