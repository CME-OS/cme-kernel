<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeData\CampaignEventData;
use CmeData\UnsubscribeData;
use CmeKernel\Enums\EventType;
use CmeKernel\Exceptions\InvalidDataException;

class CmeCampaignEvent
{
  private $_tableName = "campaign_events";

  /**
   * @param int $id
   *
   * @return bool|CampaignEventData
   * @throws \Exception
   */
  public function get($id)
  {
    if((int)$id > 0)
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
    else
    {
      throw new \Exception("Invalid Event ID");
    }
  }

  /**
   * @param int $campaignId
   *
   * @return int
   * @throws \Exception
   */
  public function getSentMessages($campaignId)
  {
    if((int)$campaignId)
    {
      return CmeDatabase::conn()->table($this->_tableName)->where(
        ['campaign_id' => $campaignId, 'event_type' => 'Sent']
      )->count();
    }
    else
    {
      throw new \Exception("Invalid Campaign ID");
    }
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool
   * @throws InvalidDataException
   * @throws \Exception
   */
  public function update(CampaignEventData $data)
  {
    if($data->validate())
    {
      CmeDatabase::conn()->table($this->_tableName)
        ->where('id', '=', $data->eventId)
        ->update($data->toArray());

      return true;
    }
    else
    {
      throw new InvalidDataException();
    }
  }

  /**
   * @param $id
   *
   * @return bool
   * @throws \Exception
   */
  public function delete($id)
  {
    if((int)$id > 0)
    {
      CmeDatabase::conn()->table($this->_tableName)->delete($id);
      return true;
    }
    else
    {
      throw new \Exception("Invalid Event ID");
    }
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackQueue(CampaignEventData $data)
  {
    $data->eventType = EventType::QUEUED;
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackOpen(CampaignEventData $data)
  {
    $data->eventType = EventType::OPENED;
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackUnsubscribe(CampaignEventData $data)
  {
    $success         = false;
    $data->eventType = EventType::UNSUBSCRIBED;
    $this->_create($data);
    $subscriber = CmeKernel::EmailList()->getSubscriber(
      $data->subscriberId,
      $data->listId
    );
    if($subscriber)
    {
      $campaign     = CmeKernel::Campaign()->get($data->campaignId);
      $unsubscribed = CmeKernel::EmailList()->isUnsubscribed(
        $subscriber->email
      );
      if(!$unsubscribed && $subscriber->id > 0)
      {
        $udata             = new UnsubscribeData();
        $udata->email      = $subscriber->email;
        $udata->branId     = $campaign->brandId;
        $udata->campaignId = $campaign->id;
        $udata->listId     = $data->listId;
        $udata->time       = time();
        $success           = CmeKernel::EmailList()->unsubscribe($udata);
      }
    }
    return $success;
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function create(CampaignEventData $data)
  {
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackClick(CampaignEventData $data)
  {
    $data->eventType = EventType::CLICKED;
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackSend(CampaignEventData $data)
  {
    $data->eventType = EventType::SENT;
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackBounce(CampaignEventData $data)
  {
    $data->eventType = EventType::BOUNCED;
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return bool|int
   * @throws InvalidDataException
   */
  public function trackFail(CampaignEventData $data)
  {
    $data->eventType = EventType::FAILED;
    return $this->_create($data);
  }

  /**
   * @param CampaignEventData $data
   *
   * @return int
   * @throws InvalidDataException
   * @throws \Exception
   */
  private function _create(CampaignEventData $data)
  {
    $data->eventId = null;
    $data->time    = time();
    if($data->validate())
    {
      $id            = CmeDatabase::conn()
        ->table($this->_tableName)
        ->insertGetId(
          $data->toArray()
        );

      return $id;
    }
    else
    {
      throw new InvalidDataException();
    }
  }
}
