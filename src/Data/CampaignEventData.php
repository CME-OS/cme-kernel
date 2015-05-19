<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:21 PM
 */

namespace CmeKernel\Data;

class CampaignEventData extends Data
{
  public $eventId;
  public $campaignId;
  public $listId;
  public $subscriberId;
  public $eventType; //enum
  public $reference;
  public $time;
}
