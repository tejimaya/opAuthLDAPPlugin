<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthLDAPPluginConfigForm Class
 *
 * @package    OpenPNE
 * @subpackage opAuthLDAPPlugin
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */

class opAuthLDAPPluginConfigForm extends sfForm
{
  protected $configs = array();
 
  public function configure()
  {
    $widgets = array();
    $validators = array();
    $helps = array();

    $serverNum = sfConfig::get('app_auth_ldap_server_num', 3);
    for ($i = 1; $i <= $serverNum; ++$i)
    {
      $host = 'server'.$i.'_ldap_host';
      $port = 'server'.$i.'_ldap_port';
      $baseDn = 'server'.$i.'_ldap_basedn';

      $this->configs = array_merge($this->configs, array(
        $host          => 'opauthldapplugin_'.$host,
        $port          => 'opauthldapplugin_'.$port,
        $baseDn        => 'opauthldapplugin_'.$baseDn,
      ));

      $widgets = array_merge($widgets, array(
        $host         => new sfWidgetFormInput(),
        $port         => new sfWidgetFormInput(),
        $baseDn       => new sfWidgetFormInput(),
      ));

      $validators = array_merge($validators, array(
         $host         => new sfValidatorString(array('required' => $i == 1)),
         $port         => new sfValidatorString(array('required' => false)),
         $baseDn       => new sfValidatorString(array('required' => $i == 1)),
      ));

      $helps = array_merge($helps, array(
         $host         => 'LDAPサーバーのホスト名を入力します。',
         $port         => 'LDAPサーバーのポートを入力します。',
         $baseDn       => 'LDAPディレクトリツリーの最上位のDNを入力します。',
      ));
    }

    $this->configs = array_merge($this->configs, array(
      'ldap_mail'                => 'opauthldapplugin_ldap_mail',
      'ldap_rejectattrtype'         => 'opauthldapplugin_ldap_rejectattrtype',
      'ldap_rejectattr'         => 'opauthldapplugin_ldap_rejectattr',
    ));

    // set form widget.
    $widgets = array_merge($widgets, array(
      'ldap_mail'                => new sfWidgetFormInput(),
      'ldap_rejectattrtype'         => new sfWidgetFormInput(),
      'ldap_rejectattr'         => new sfWidgetFormInput(),
    ));
    $this->setWidgets($widgets);

    // set Validators.
    $validators = array_merge($validators, array(
      'ldap_mail'                => new sfValidatorString(array('required' => false)),
      'ldap_rejectattrtype'         => new sfValidatorString(array('required' => false)),
      'ldap_rejectattr'         => new sfValidatorString(array('required' => false)),
    ));
    $this->setValidators($validators);

    // set Help.
    $helps = array_merge($helps, array(
      'ldap_mail'=>        'ユーザー情報の中でメールアドレスが保存されている属性型を指定します。',
      'ldap_rejectattrtype'=> 'ユーザー情報でログイン停止をする為の値が保存されている属性型を指定します。',
      'ldap_rejectattr'=> 'ユーザー情報でログイン停止をする為の属性値を指定します。コンマ（,）区切りで複数入力できます。',
    ));

    foreach ($helps as $key => $value)
    {
      $this->widgetSchema->setHelp($key, $value);
    }

    foreach ($this->configs as $k => $v)
    {
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($v);
      if($config)
      {
        $this->getWidgetSchema()->setDefault($k,$config->getValue());
      }
    }
    $this->getWidgetSchema()->setNameFormat('ldap[%s]');
  }

  public function save()
  {
    foreach($this->getValues() as $k => $v)
    {
      if(!isset($this->configs[$k]))
      {
        continue;
      }
      $config = Doctrine::getTable('SnsConfig')->retrieveByName($this->configs[$k]);
      if(!$config)
      {
        $config = new SnsConfig();
        $config->setName($this->configs[$k]);
      }
      $config->setValue($v);
      $config->save();
    }
  }

}
