<?php

/**
 * opAuthLDAPPlugin Form Class.
 *
 * @package    OpenPNE
 * @subpackage lib
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */

class opAuthLoginFormLDAP extends opAuthLoginForm
{
  public function configure()
  {
    $this->setWidgets(array(
      'id' => new sfWidgetFormInput(),
      'password' => new sfWidgetFormInputPassword(),
    ));

    $this->setValidatorSchema(new sfValidatorSchema(array(
      'id' => new sfValidatorString(),
      'password' => new sfValidatorString(),
    )));

    $this->mergePostValidator(
      new opAuthValidatorLDAP()
    );
    parent::configure();
  }
}
