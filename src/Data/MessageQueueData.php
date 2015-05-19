<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:26 PM
 */

namespace CmeKernel\Data;

class MessageQueueData extends Data
{
  public $id;
  public $subject;
  public $fromEmail;
  public $fromName;
  public $to;
  public $htmlContent;
  public $textContent;
  public $subscriberId;
  public $listId;
  public $brandId;
  public $campaignId;
  /**
   * @var string $status
   * Allowed values: "Pending", "Sent", "Failed", "Paused"
   */
  public $status;
  public $sendTime;
  public $sentPriority;
  public $lockedBy;
}
