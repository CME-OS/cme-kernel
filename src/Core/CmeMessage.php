<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\MessageQueueData;
use CmeKernel\Exceptions\InvalidDataException;

class CmeMessage
{
  private $_tableName = "message_queue";

  /**
   * @param MessageQueueData $data
   *
   * @return int $messageId
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function create(MessageQueueData $data)
  {
    $data->id = null;
    if($data->validate())
    {
      $id = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId($data->toArray());
      return $id;
    }
    else
    {
      throw new InvalidDataException();
    }
  }
}
