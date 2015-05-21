<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\MessageQueueData;

class CmeMessage
{
  private $_tableName = "message_queue";

  public function create(MessageQueueData $data)
  {
    $data->id = null;
    $id       = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId($data->toArray());
    return $id;
  }
}
