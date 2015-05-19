<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:25 PM
 */

namespace CmeKernel\Data;

class ListImportQueueData extends Data
{
  public $id;
  public $listId;
  /**
   * @var string $type
   * Allowed Values: "api", "csv", "file"
   */
  public $type;
  public $source;
  public $lockedBy = null;
}
