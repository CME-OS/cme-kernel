<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Enums\EventType;
use CmeKernel\Helpers\ListHelper;

class CmeAnalytics
{

  /**
   * @param EventType $eventType
   * @param int       $campaignId
   * @param int       $limit
   *
   * @return array
   * @throws \Exception
   */
  public function getLastXOfEvent(
    EventType $eventType, $campaignId, $limit = 10
  )
  {
    $campaign  = CmeKernel::Campaign()->get($campaignId);
    $listTable = ListHelper::getTable($campaign->listId);

    $eventType   = $eventType->getValue();
    $subscribers = CmeDatabase::conn()->select(
      "SELECT subscriber_id, time FROM campaign_events
      WHERE campaign_id = $campaignId
      AND subscriber_id > 0
      AND event_type='$eventType'
      GROUP BY subscriber_id
      ORDER BY event_id DESC LIMIT $limit"
    );

    $subscriber_ids = [];
    $times          = [];
    foreach($subscribers as $subscriber)
    {
      $subscriber_ids[]                    = $subscriber['subscriber_id'];
      $times[$subscriber['subscriber_id']] = $subscriber['time'];
    }

    $result = [];
    if($subscriber_ids)
    {
      $result = CmeDatabase::conn()->select(
        "SELECT id, email FROM $listTable
         WHERE id IN (" . implode(',', $subscriber_ids) . ")"
      );

      foreach($result as $i => $row)
      {
        $result[$i]['time'] = $times[$row['id']];
      }
    }

    //sort results
    usort(
      $result,
      function ($a, $b)
      {
        if($a['time'] == $b['time'])
        {
          return 0;
        }
        return ($a['time'] > $b['time']) ? -1 : 1;
      }
    );

    return $result;
  }

  /**
   * @param int $campaignId
   *
   * @return array
   */
  public function getEventCounts($campaignId)
  {
    $campaignId = (int)$campaignId;
    $eventTypes = EventType::getPossibleValues();
    $stats      = [];
    $counted    = [];
    foreach($eventTypes as $type)
    {
      $stats[$type]['unique'] = 0;
      $stats[$type]['total']  = 0;
    }

    $lastId = 0;
    do
    {
      $events = CmeDatabase::conn()->select(
        "SELECT event_id, event_type, subscriber_id FROM campaign_events
         WHERE event_id > $lastId
         AND campaign_id = $campaignId
         AND subscriber_id > 0
         ORDER BY event_id ASC LIMIT 20000"
      );
      foreach($events as $event)
      {
        if(isset($stats[$event['event_type']]))
        {
          if(!isset($counted[$event['event_type']][$event['subscriber_id']]))
          {
            $counted[$event['event_type']][$event['subscriber_id']] = 1;
            $stats[$event['event_type']]['unique']++;
          }
          $stats[$event['event_type']]['total']++;
        }
        $lastId = $event['event_id'];
      }
    }
    while($events);

    return $stats;
  }

  public function getLinkActivity($campaignId)
  {
    $campaignId = (int)$campaignId;
    $clicks     = CmeDatabase::conn()->select(
      "SELECT count(*) as total, subscriber_id, reference FROM campaign_events
      WHERE campaign_id = $campaignId
      AND subscriber_id > 0
      AND event_type='" . EventType::CLICKED . "'
      GROUP BY reference, subscriber_id"
    );

    $stats = [];
    foreach($clicks as $c)
    {
      if(!isset($stats[$c['reference']]))
      {
        $stats[$c['reference']]['unique'] = 0;
        $stats[$c['reference']]['total']  = 0;
      }
      $stats[$c['reference']]['unique']++;
      $stats[$c['reference']]['total'] += $c['total'];
    }

    return $stats;
  }
}
