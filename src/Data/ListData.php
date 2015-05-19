<?php
/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:24 PM
 */

namespace CmeKernel\Data;

class ListData extends Data
{
  public $id;
  public $name;
  public $description;
  public $endpoint;
  public $refreshInterval;
  public $lastRefreshTime;
  public $lockedBy;
  public $deletedAt;
  /**
   * Size of list - number of subscribers in list
   * @var int $_size
   */
  private $_size;

  /**
   * @param int $size
   */
  public function setSize($size)
  {
    $this->_size = $size;
  }

  /**
   * @return int $_size;
   */
  public function getSize()
  {
    return $this->_size;
  }
}
