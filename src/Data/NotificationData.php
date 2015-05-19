<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:30 PM
 */

namespace CmeKernel\Data;

class NotificationData extends Data
{
  public $id;
  public $subject;
  public $message;
  public $recipient;
  /**
   * @var string $status
   * Allowed values: "Read", "Unread"
   */
  public $status;
  public $time;
}
