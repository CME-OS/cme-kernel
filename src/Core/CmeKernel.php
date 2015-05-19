<?php
/**
 * @author  oke.ugwu
 */

namespace CmeKernel\Core;

use CmeKernel\Data\InitData;

class CmeKernel
{
  public static function init(InitData $data)
  {
    CmeDatabase::init($data);
  }

  public static function Campaign()
  {
    return new CmeCampaign();
  }

  public static function Brand()
  {
    return new CmeBrand();
  }

  public static function CampaignEvent()
  {
    return new CmeCampaignEvent();
  }

  public static function SmtpProvider()
  {
    return new CmeSmtpProvider();
  }

  public static function Template()
  {
    return new CmeTemplate();
  }

  public static function User()
  {
    return new CmeUser();
  }

  public static function EmailList()
  {
    return new CmeList();
  }

  public static function Analytics()
  {
    return new CmeAnalytics();
  }
}
