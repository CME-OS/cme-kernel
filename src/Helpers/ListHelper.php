<?php
/**
 * @author  oke.ugwu
 */
namespace CmeKernel\Helpers;

use CmeKernel\Core\CmeCampaign;
use CmeKernel\Core\CmeDatabase;
use Illuminate\Support\Str;

class ListHelper
{
  public static function getTable($listId)
  {
    return 'list_' . $listId;
  }

  public static function tableExists($listId)
  {
    return CmeDatabase::schema()->hasTable(self::getTable($listId));
  }

  public static function createListTable($listId, $columns)
  {
    if(in_array('email', $columns))
    {
      $tableName = self::getTable($listId);
      CmeDatabase::schema()->create(
        $tableName,
        function ($table) use ($columns)
        {
          //add other additional columns needed
          $table->increments('id');
          $table->unique('email');
          foreach($columns as $column)
          {
            $table->string(Str::slug($column, '_'), 225);
          }
          $table->integer('bounced', 0);
          $table->integer('unsubscribed', 0);
          $table->integer('test_subscriber', 0);
          $table->timestamp('date_created');
        }
      );
    }
    else
    {
      throw new \Exception("List must have a field called email");
    }
  }

  public static function addSubscribers($listId, array $subscribers)
  {
    if(!empty($subscribers))
    {
      $tableName = self::getTable($listId);
      if(self::tableExists($listId))
      {
        $batch = [];
        foreach($subscribers as $subscriber)
        {
          $values = array_values($subscriber);
          foreach($values as $k => $v)
          {
            $values[$k] = CmeDatabase::conn()->getPdo()->quote($v);
          }
          $batch[] = "(" . implode(",", $values) . ", '" . date(
              'Y-m-d H:i:s'
            ) . "')";
        }

        CmeDatabase::conn()->insert(
          sprintf(
            "INSERT IGNORE INTO %s (%s) VALUES %s",
            $tableName,
            implode(',', array_keys($subscribers[0])) . ', date_created',
            implode(',', $batch)
          )
        );
      }
    }
  }

  public static function inBuiltFields()
  {
    return [
      'id',
      'bounced',
      'unsubscribed',
      'test_subscriber',
      'date_created',
    ];
  }

  public static function getMinMaxIds($listId)
  {
    return head(
      CmeDatabase::conn()->select(
        sprintf(
          "SELECT min(id) as minId, max(id) as maxId FROM %s",
          self::getTable($listId)
        )
      )
    );
  }

  public static function getRandomSubscriber($listId)
  {
    return head(
      CmeDatabase::conn()->select(
        sprintf(
          "SELECT * FROM %s WHERE bounced=0 AND unsubscribed=0 LIMIT 1",
          self::getTable($listId)
        )
      )
    );
  }

  public static function count($listId, $campaignId = null)
  {
    $filterSql = "";
    if($campaignId !== null)
    {
      $campaign = (new CmeCampaign())->get($campaignId);
      if($campaign->filters)
      {
        $sql = FilterHelper::buildSql(json_decode($campaign->filters));
        $filterSql = 'WHERE '.$sql;
      }
    }

    $count = head(
      CmeDatabase::conn()->select(
        sprintf(
          "SELECT count(*) as count FROM %s %s",
          self::getTable($listId),
          $filterSql
        )
      )
    );

    return $count['count'];
  }
}
