<?php

/**
 * A custom contact search
 */
class CRM_Findmissingmembershippayments_Form_Search_findMissingMembershipPayments extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  function __construct(&$formValues) {
    parent::__construct($formValues);
  }

  
  /**
   * Prepare a set of search fields
   *
   * @param CRM_Core_Form $form modifiable
   * @return void
   */
  function buildForm(&$form) {
    CRM_Utils_System::setTitle(ts('Find Missing Membership Payments'));
    
    $afinancialTypes = CRM_Contribute_PseudoConstant::financialType();
    foreach ($afinancialTypes as $key => $value) {
      $financialTypes[$key] = $value;
    }
    $form->addElement('select', 'financial_type_id', ts('Financial Type'), $financialTypes, TRUE);

    // Optionally define default search values
    $form->setDefaults(array(
      'financial_type_id' => 2,
    ));

    /**
     * if you are using the standard template, this array tells the template what elements
     * are part of the search criteria
     */
    $form->assign('elements', array('financial_type_id'));
  }
  /**
   * Get a list of summary data points
   *
   * @return mixed; NULL or array with keys:
   *  - summary: string
   *  - total: numeric
   */
  function summary() {
    return NULL;
    // return array(
    //   'summary' => 'This is a summary',
    //   'total' => 50.0,
    // );
  }

  /**
   * Get a list of displayable columns
   *
   * @return array, keys are printable column headers and values are SQL column names
   */
  function &columns() {
    // return by reference
    $columns = array(
      ts('Name')      => 'display_name',
      ts('Amount')    => 'total_amount',
      ts('Type')      => 'name',
      ts('Received')  => 'receive_date',
      ts('Status')    => 'status',
    );
    return $columns;
  }

  /**
   * Construct a full SQL query which returns one page worth of results
   *
   * @return string, sql
   */
  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    // delegate to $this->sql(), $this->select(), $this->from(), $this->where(), etc.
    //print_r($this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL)  );exit;
    return $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, NULL);
  }

  /**
   * Construct a SQL SELECT clause
   *
   * @return string, sql fragment with SELECT arguments
   */
  function select() {
    return "
      contact_a.id                  as contact_id  ,
      contact_a.display_name        as display_name  ,
      contribution_b.total_amount   as total_amount,
      financial_type.name           as name,
      contribution_b.receive_date   as receive_date,
      option_value.name             as status
    ";
  }

  /**
   * Construct a SQL FROM clause
   *
   * @return string, sql fragment with FROM and JOIN clauses
   */
  
  function from() {
      return "
      FROM      civicrm_contact contact_a
      LEFT JOIN civicrm_contribution contribution_b ON ( contribution_b.contact_id = contact_a.id )
      LEFT JOIN civicrm_financial_type financial_type ON ( contribution_b.financial_type_id = financial_type.id )
      LEFT JOIN civicrm_option_group option_group ON (option_group.name = 'contribution_status')
      LEFT JOIN civicrm_option_value option_value ON (contribution_b.contribution_status_id = option_value.value
                               AND option_group.id = option_value.option_group_id )
      LEFT JOIN civicrm_membership_payment membership_payment  ON ( membership_payment.contribution_id = contribution_b.id)
      ";
  }
  /**
   * Construct a SQL WHERE clause
   *
   * @return string, sql fragment with conditional expressions
   */
  function where($includeContactIDs = FALSE) {
    //`$params = array();
    $financialTypeId = $_POST['financial_type_id'];
    return " contribution_b.financial_type_id = $financialTypeId AND membership_payment.contribution_id IS NULL";
  }

  /**
   * Determine the Smarty template for the search screen
   *
   * @return string, template path (findable through Smarty template path)
   */
  function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

  /**
   * Modify the content of each row
   *
   * @param array $row modifiable SQL result row
   * @return void
   */
  function alterRow(&$row) {
    //$row['sort_name'] .= ' ( altered )';
  }
}
