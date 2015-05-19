<?php

/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:07 PM
 */

namespace CmeKernel\Data;

class CampaignData extends Data
{
  /**
   * Campaign ID
   * @var int id
   */
  public $id;
  /**
   * Name of campaign
   * @var string $name
   */
  public $name;
  /**
   * Subject of campaign
   * @var string subject
   */
  public $subject;
  /**
   * From address of campaign
   * @var string from
   */
  public $from;
  /**
   * HTML content of the campaign
   * @var string $htmlContent
   */
  public $htmlContent;
  /**
   * Plain text content of the campaign
   * @var string $textContent
   */
  public $textContent;
  /**
   * List ID of the list this campaign should be sent to
   * @var int $listId
   */
  public $listId;
  /**
   * Brand ID of the brand this campaign should be sent to
   * @var int $brandId
   */
  public $brandId;
  /**
   * Timestamp of when campaign should be sent
   * @var int sentTime
   */
  public $sendTime;
  /**
   * Send Priority of the campaign
   * @var int $sendPriority
   */
  public $sendPriority = 0;
  /**
   * Status of the campaign
   * Allowed Values: "Pending", "Queuing", "Queued", "Sending", "Sent", "Paused", "Aborted"
   * @var string $status
   */
  public $status = 'Pending';
  /**
   * Type of Campaign
   * Allowed Values: "Default", "Rolling"
   * @var string $type
   */
  public $type = 'Default';
  /**
   * How frequent you want to send this campaign
   * @var int $frequency
   */
  public $frequency;
  /**
   * JSON encoded string represent filter that must be applied to list before
   * sending this campaign
   * @var string $filters ;
   */
  public $filters;
  /**
   * Flag to track if campaign has been tested or not
   * @var int $tested
   */
  public $tested = 0;
  /**
   * Flag to track if campaign has been previewed or not
   * @var int $previewed
   */
  public $previewed = 0;
  /**
   * ID of SMTP provider this campaign will be sent through
   * @var int $smtpProviderId
   */
  public $smtpProviderId;
  /**
   * The timestamp of when this campaign was created
   * @var int $created
   */
  public $created;
  /**
   * The timestamp of when this campaign was deleted
   * @var int $deletedAt
   */
  public $deletedAt;
}
