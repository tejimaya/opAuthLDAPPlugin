<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opAuthValidatorLDAP
 *
 * @package    OpenPNE
 * @subpackage validator
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */

class opAuthValidatorLDAP extends sfValidatorSchema
{
  /**
   * Constructor.
   *
   * @param array  $options   An array of options
   * @param array  $messages  An array of error messages
   *
   * @see sfValidatorSchema
   */
  public function __construct($options = array(), $messages = array())
  {
    parent::__construct(null, $options, $messages);
  }

  /**
   * Configures this validator.
   *
   * Available options:
   *
   *  * config_name: The configuration name of MemberConfig
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->setMessage('invalid', 'ID is not a valid.');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($values)
  {
    $uid = $values['id'];
    $password = $values['password'];

    $options = array();
    $serverNum = sfConfig::get('app_auth_ldap_server_num', 3);
    for ($i = 1; $i <= $serverNum; ++$i)
    {
      $accountDomainName = opConfig::get('opauthldapplugin_server'.$i.'_ldap_adn', '');
      $option = array(
        'host' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_host', null),
        'port' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_port', 0),
        'bindRequiresDn' => '' === $accountDomainName,
        'baseDn' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_basedn', null),
        'useStartTls' => opConfig::get('opauthldapplugin_server'.$i.'_ldap_useSsl', false)
      );
      if (!$option['bindRequiresDn'])
      {
        $option['accountDomainName'] = $accountDomainName;
      }
      $options[] = $option;
    }
    $adapter = new Zend_Auth_Adapter_Ldap($options, $uid, $password);
    $result = $adapter->authenticate();

    sfContext::getInstance()->getEventDispatcher()->notify(
      new sfEvent($this, 'application.log', array(implode(' ', $result->getMessages()), 'priority' => sfLogger::INFO))
    );
    if (!$result->isValid())
    {
      throw new sfValidatorError($this, 'invalid');
    }

    $ldap = $adapter->getLdap();
    $entry = $ldap->getEntry($ldap->getBoundUser());

    // reject member
    $rejectAttrType = opConfig::get('opauthldapplugin_ldap_rejectattrtype', '');
    if ('' !== $rejectAttrType)
    {
      $attrs = $entry[$rejectAttrType];
      $rejectAttrs = preg_split('/,/', opConfig::get('opauthldapplugin_ldap_rejectattr', ''));
      if (count(array_intersect($attrs, $rejectAttrs)))
      {
        throw new sfValidatorError($this, 'invalid');
      }
    }

    $memberConfig = Doctrine::getTable('MemberConfig')->retrieveByNameAndValue('ldap', $entry['uid'][0]);
    if ($memberConfig)
    {
      $values['member'] = $memberConfig->getMember();
    }
    $values['ldap_entry'] = $entry;

    return $values;
  }
}
