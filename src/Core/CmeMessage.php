<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\MessageQueueData;

class CmeMessage
{
  private $_tableName = "message_queue";

  public function create(MessageQueueData $data)
  {
    $data->id = null;
    $id       = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId(
        CmeDatabase::dataToArray($data)
      );

    return $id;
  }
}
