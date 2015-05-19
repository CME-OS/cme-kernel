<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:23 PM
 */

namespace CmeKernel\Data;

class CampaignQueueData extends Data
{
  public $id;
  public $campaignId;
  public $time;
  public $lockedBy = null;
  public $processed = 0;
}
