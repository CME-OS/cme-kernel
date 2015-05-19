<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:32 PM
 */

namespace CmeKernel\Data;

class QueueRangesData extends Data
{
  public $listId;
  public $campaignId;
  public $start;
  public $end;
  public $lockedBy = null;
  public $created;
}
