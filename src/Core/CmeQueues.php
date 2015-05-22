<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

class CmeQueues
{
  public function getMessageQueueSize()
  {
    return CmeDatabase::conn()->table('message_queue')->count();
  }
}
