<?php

/**
 * Created by PhpStorm.
 * User: Okechukwu
 * Date: 5/18/2015
 * Time: 8:46 PM
 */
namespace CmeKernel\Helpers;

use CmeData\BrandData;
use CmeData\CampaignData;
use CmeKernel\Core\CmeCampaign;
use CmeKernel\Core\CmeDatabase;
use CmeKernel\Core\CmeKernel;
use Illuminate\Support\Facades\Log;

class CampaignHelper
{
  private static $_placeHolders;


  public static function buildQueueRanges($campaignId)
  {
    $built        = false;
    $campaignCore = new CmeCampaign();
    $campaign     = $campaignCore->get($campaignId);
    if($campaign)
    {
      if($campaign->tested > 0)
      {
        //get min and max id of campaign list
        $Ids   = ListHelper::getMinMaxIds($campaign->listId);
        $minId = $Ids['minId'];
        $maxId = $Ids['maxId'];

        //build ranges
        for($i = $minId; $i <= $maxId; $i++)
        {
          $start = $i;
          $end   = $i = $i + 1000;
          $range = [
            'list_id'     => $campaign->listId,
            'campaign_id' => $campaignId,
            'start'       => $start,
            'end'         => $end,
            'created'     => time()
          ];
          try
          {
            CmeDatabase::conn()->table('ranges')->insert($range);
          }
          catch(\Exception $e)
          {
            Log::error($e->getMessage());
          }
        }
        $built = true;
      }
      else
      {
        throw new \Exception(
          "You cannot queue a campaign you have not tested. "
          . "Please test campaign before queuing"
        );
      }
    }

    return $built;
  }

  public static function compileMessage(
    CampaignData $campaign, BrandData $brand, array $subscriber
  )
  {
    if(self::$_placeHolders == null)
    {
      $columns = array_keys($subscriber);
      foreach($columns as $c)
      {
        self::$_placeHolders[$c] = "[$c]";
      }

      //add brand attributes as placeholders too
      $columns = $brand->getFields();
      foreach($columns as $c)
      {
        self::$_placeHolders[$c] = "[$c]";
      }
    }

    //parse and compile message (replacing placeholders if any)
    $html = $campaign->htmlContent;
    $text = $campaign->textContent;
    foreach(self::$_placeHolders as $prop => $placeHolder)
    {
      $replace = false;
      if(isset($subscriber[$prop]))
      {
        $replace = $subscriber[$prop];
      }
      elseif(isset($brand->{camel_case($prop)}))
      {
        //do no replace the brand's unsubscribe link as we want to wrap
        //it in a tracking link later
        if($prop != 'brand_unsubscribe_url')
        {
          $replace = $brand->{camel_case($prop)};
        }
      }

      if($replace !== false)
      {
        $html = str_replace($placeHolder, $replace, $html);
        $text = str_replace($placeHolder, $replace, $text);
      }
    }

    $data = [
      'campaignId'            => $campaign->id,
      'listId'                => $campaign->listId,
      'subscriberId'          => $subscriber['id'],
      'brand_unsubscribe_url' => $brand->brandUnsubscribeUrl
    ];
    $html = self::_insertTrackers($html, $data);

    $message       = new \stdClass();
    $message->html = $html;
    $message->text = $text;
    return $message;
  }


  private static function _getUnsubscribeUrl($url, $data)
  {
    $url = self::_generateTrackLink('unsubscribe', $data)
      . "/" . base64_encode($url);
    return $url;
  }

  private static function _insertTrackers($html, $data)
  {
    //wrap html in <cme> tags, so we can extract our original content after
    //messing with it in DOM
    $html = "<cme>" . $html . "</cme>";

    //find all links in campaign and track them
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $clickLink = self::_generateTrackLink('click', $data);
    foreach($dom->getElementsByTagName('a') as $node)
    {
      $oldHref = $node->getAttribute('href');
      if($oldHref != '[brand_unsubscribe_url]')
      {
        $newHref = $clickLink . "/" . base64_encode($oldHref);
        $node->setAttribute('href', $newHref);
      }
    }
    $html = $dom->saveHTML();

    //grab original content between <cme> tags
    $html = strstr($html, '<cme>');
    $html = strstr($html, '</cme>', true);
    $html = trim(str_replace('<cme>', '', $html));

    //track un-subscribe link
    $replace = self::_getUnsubscribeUrl(
      $data['brand_unsubscribe_url'],
      $data
    );
    $html    = str_replace('%5Bbrand_unsubscribe_url%5D', $replace, $html);

    //append pixel to html content, so we can track opens
    $pixelUrl = self::_generateTrackLink('open', $data);
    $html .= '<img src="' . $pixelUrl
      . '" style="display:none;" height="1" width="1" />';

    return $html;
  }

  private static function _generateTrackLink($type, $data)
  {
    $domain = CmeKernel::Config()->cmeHost;
    return "http://" . $domain . "/track/" . $type . "/" . $data['campaignId']
    . "_" . $data['listId'] . "_" . $data['subscriberId'];
  }

  public static function getPriority($priority)
  {
    switch($priority)
    {
      case 1:
        $name = "Low";
        break;
      case 2:
        $name = "Normal";
        break;
      case 3:
        $name = "Medium";
        break;
      case 4:
        $name = "High";
        break;
      default:
        $name = "Unknowm";
    }

    return $name;
  }

  public static function labelSender($email, $label)
  {
    $domain = strstr($email, '@');
    $user   = strstr($email, '@', true);
    return $user . '+' . $label . $domain;
  }
}
