<?php

class Model_Produit extends Model_App_Lim {

    public $_fields = array(
        'prix' => array(
            'title' => 'Prix',
            'type' => 'numeric'
        ),
        'contrat' => array(
            'title' => 'Contrat',
            '_type' => 'select',
            '_options' => array('Model_Contrat', 'Minimal_Liste')
        ),
        'annonceur' => array(
            'title' => 'Annonceur',
            '_type' => 'select',
            '_options' => array('Model_Annonceur', 'Minimal_Liste')
        )
    );

    protected $_request_retaled_data = array('contrat', 'annonceur');

	public function __construct($id=NULL) {
		$this->fields = get_option("imcompare_produit", array());
        $this->fields = array_merge($this->fields, $this->_fields);

        Model_Field::option_adapter_walker($this->fields, true);

		parent::__construct($id);

        if(!is_null($id)){
            $this->set_related();
        }
	}

    public function __get($key){
        $get = parent::__get($key);
        if(is_null($get)) $get = $this->{$key};
        return $get;
    }
}
