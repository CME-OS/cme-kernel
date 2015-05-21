<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\BrandData;
use CmeData\CampaignEventData;

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
      $data = CampaignEventData::hydrate(head($event));
    }
    return $data;
  }

  /**
   * @param $id - Campaign ID
   *
   * @return int
   */
  public function getSentMessages($id)
  {
    return CmeDatabase::conn()->table($this->_tableName)->where(
      ['campaign_id' => $id, 'event_type' => 'Sent']
    )->count();
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
      ->update($data->toArray());

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

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
  public function trackQueue(CampaignEventData $data)
  {
    $data->eventType = 'queued';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
  public function trackOpen(CampaignEventData $data)
  {
    $data->eventType = 'opened';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
  public function trackUnsubscribe(CampaignEventData $data)
  {
    $data->eventType = 'unsubscribed';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
  public function trackClick(CampaignEventData $data)
  {
    $data->eventType = 'clicked';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
  public function trackSend(CampaignEventData $data)
  {
    $data->eventType = 'sent';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
  public function trackBounce(CampaignEventData $data)
  {
    $data->eventType = 'bounced';
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   */
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
        $data->toArray()
      );

    return $id;
  }
}
