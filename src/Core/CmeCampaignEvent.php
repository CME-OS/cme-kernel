<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\BrandData;
use CmeKernel\Data\CampaignData;
use CmeKernel\Data\CampaignEventData;

class CmeCampaignEvent
{
  private $_tableName = "campaign_events";

  /**
   * @param $id
   *
   * @return bool| BrandData
   */
  public function get($id)
  {
    $event = CmeDatabase::conn()
      ->table($this->_tableName)
      ->where(['event_id' => $id])
      ->get();

    $data = false;
    if($event)
    {
      $data = CmeDatabase::hydrate(new CampaignEventData(), head($event));
    }
    return $data;
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool
   */
  public function update(CampaignEventData $data)
  {
    CmeDatabase::conn()->table($this->_tableName)
      ->where('id', '=', $data->eventId)
      ->update(CmeDatabase::dataToArray($data));

    return true;
  }

  /**
   * @param int $id
   *
   * @return bool
   */
  public function delete($id)
  {
    CmeDatabase::conn()->table($this->_tableName)->delete($id);
    return true;
  }

  public function trackQueue(CampaignEventData $data)
  {
    $data->eventType = 'queued';
    return $this->_create($data);
  }

  public function trackOpen(CampaignEventData $data)
  {
    $data->eventType = 'opened';
    return $this->_create($data);
  }

  public function trackUnsubscribe(CampaignEventData $data)
  {
    $data->eventType = 'unsubscribed';
    return $this->_create($data);
  }

  public function trackClick(CampaignEventData $data)
  {
    $data->eventType = 'clicked';
    return $this->_create($data);
  }

  public function trackSend(CampaignEventData $data)
  {
    $data->eventType = 'sent';
    return $this->_create($data);
  }

  public function trackBounce(CampaignEventData $data)
  {
    $data->eventType = 'bounced';
    return $this->_create($data);
  }

  public function trackFail(CampaignEventData $data)
  {
    $data->eventType = 'failed';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int $id
   */
  private function _create(CampaignEventData $data)
  {
    $data->eventId = null;
    $data->time    = time();
    $id            = CmeDatabase::conn()
      ->table($this->_tableName)
      ->insertGetId(
        CmeDatabase::dataToArray($data)
      );

    return $id;
  }
}
