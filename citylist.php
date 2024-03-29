<?php

class Citylist extends Module
{
    public function __construct()
    {
        $this->name = 'citylist';
        $this->version = '1.0.0';
        $this->author = 'Elxphaz';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->getTranslator()->trans(
            'City List',
            [],
            'Modules.Citylist.Admin'
        );

        $this->description =
            $this->getTranslator()->trans(
                'Add and display city list to address section',
                [],
                'Modules.Citylist.Admin'
            );

        $this->ps_versions_compliancy = [
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_,
        ];

        // $tabNames = [];
        // foreach (Language::getLanguages(true) as $lang) {
        //     $tabNames[$lang['locale']] = $this->trans('City List', array(), 'Modules.Citylist.Admin', $lang['locale']);
        //     $tabNames[$lang['locale']] = $this->trans('City Shipping', array(), 'Modules.Citylist.Admin', $lang['locale']);
        // }

        $this->tabs = [
            [
                    'name' => [
                        'en' => 'City List', // Fallback value
                        'fr' => 'City List',
                ],
                'route_name' => 'city_list',
                'class_name' => 'AdminCityList',
                'visible' => true,
                'parent_class_name' => 'IMPROVE',
                'wording' => 'City List',
                'wording_domain' => 'Modules.Citylist.Admin'
            ],
            [
                    'name' => [
                        'en' => 'City Shipping', // Fallback value
                        'fr' => 'City Shipping',
                ],
                'route_name' => 'city_shipping_list',
                'class_name' => 'AdminCityListShipping',
                'visible' => true,
                'parent_class_name' => 'IMPROVE',
                'wording' => 'City Shipping',
                'wording_domain' => 'Modules.Citylist.Admin'
            ],
        ];
    }




    /**
     * This function is required in order to make module compatible with new translation system.
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem()
    {
        return true;
    }



    /**
     * Install module and register hooks to allow grid modification.
     *
     * @see https://devdocs.prestashop.com/1.7/modules/concepts/hooks/use-hooks-on-modern-pages/
     *
     * @return bool
     */
    public function install()
    {
        return parent::install() &&
            $this->installSql() &&
            $this->registerHook('additionalCustomerAddressFields') &&
            $this->registerHook('actionAfterCreateAddressFormHandler') &&
            $this->registerHook('actionAfterUpdateAddressFormHandler') &&
            $this->registerHook('actionValidateCustomerAddressForm') &&
            $this->registerHook('actionObjectAddressAddBefore') &&
            $this->registerHook('actionObjectAddressAddAfter') &&
            $this->registerHook('actionObjectAddressUpdateAfter') &&
            $this->registerHook('actionObjectAddressDeleteAfter') &&
            $this->registerHook('displayPDFInvoice') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayBeforeBodyClosingTag') &&
            $this->registerHook('actionGetIDZoneByAddressID') &&
            $this->registerHook('actionFrontControllerSetMedia');
    }


    public function uninstall()
    {
        return parent::uninstall()
        && $this->uninstallSql();
    }

    private function installSql()
    {

        $sqlCitylist = '
            CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . 'city_list` (
            `id_citylist` INT AUTO_INCREMENT NOT NULL,
            `id_country` INT NOT NULL,
            `city_name` VARCHAR(64) NOT NULL,
            `active` TINYINT(1) NOT NULL,
            PRIMARY KEY(id_citylist))
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE =' . pSQL(_MYSQL_ENGINE_) . ';
            ';

        // $sql[] = '
        //     CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . 'city_list_customer_address` (
        //     `id_citylist_customer_address` INT AUTO_INCREMENT NOT NULL,
        //     `id_address` INT DEFAULT NULL,
        //     `id_citylist` INT DEFAULT NULL,
        //     PRIMARY KEY(id_citylist_customer_address))
        //     DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE =' . pSQL(_MYSQL_ENGINE_) . ';
        //     ';

        //Create a table for save shipping zone and citylist id

        $sqlShipping = '
            CREATE TABLE IF NOT EXISTS `' . pSQL(_DB_PREFIX_) . 'city_list_shipping` (
            `id_city_list_shipping` INT AUTO_INCREMENT NOT NULL,
            `id_citylist` INT NOT NULL,
            `id_zone` INT NOT NULL,
            `active` TINYINT(1) NOT NULL,
            PRIMARY KEY(id_city_list_shipping))
            DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE =' . pSQL(_MYSQL_ENGINE_) . ';
            ';

            return (
                DB::getInstance()->execute($sqlCitylist) 
            && DB::getInstance()->execute($sqlShipping));
    }


    private function uninstallSql()
    {
        $sql = array();

        $sqlCitylist = 'DROP TABLE IF EXISTS `' . pSQL(_DB_PREFIX_) . 'city_list`';
        $sqlShipping = 'DROP TABLE IF EXISTS `' . pSQL(_DB_PREFIX_) . 'city_list_shipping`';

            return (Db::getInstance()->execute($sqlCitylist) && Db::getInstance()->execute($sqlShipping) );
    }





    //Hook Section

    // public function hookAdditionalCustomerAddressFields($params)
    // {
    //     //Get city from database
    //     $cities = \Db::getInstance()->executeS('
    //         SELECT * FROM `' . pSQL(_DB_PREFIX_) . 'city_list` WHERE `active` = 1
    //     ');

    //     $cityKey = array();
    //     $cityValue = array();

    //     //get all city for displaying
    //     foreach ($cities as $key => $value) {
    //         $cityKey[] = $value['id_citylist'];
    //         $cityValue[] = $value['city_name'];
    //     }


    //     //Combine city list information on one array
    //     $cityList = array_combine($cityKey, $cityValue);


    //     //create formfield city
    //     $formField = (new FormField)
    //         ->setName('id_citylist')
    //         ->setType('select')
    //         ->setAvailableValues($cityList)
    //         ->setLabel($this->getTranslator()->trans('City', [], 'Modules.Citylist.Front'));

    //     //if a city already choosed selected by default when user want update
    //     if (Tools::getIsset('id_address')) {
    //         $address = new Address(Tools::getValue('id_address'));

    //         if (!empty($cities)) {
    //             foreach ($cities as $city) {
    //                 $formField->addAvailableValue(
    //                     $city['id_citylist'],
    //                     $city['city_name']
    //                 );
    //             }
    //             if (!empty($address->id)) {
    //                 $id_citylist =  \Db::getInstance()->executeS('SELECT `id_citylist` FROM `' . _DB_PREFIX_ . 'city_list_customer_address` WHERE `id_address` = ' . $address->id);
    //                 $formField->setValue($id_citylist[0]['id_citylist']);
    //             }
    //         }
    //     }

    //     return array(
    //         $formField
    //     );
    // }


    // public function hookActionAfterUpdateAddressFormHandler($params)
    // {
    // }


    //Initialise and add asset ressource for the module
    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerJavascript(
            'citylist-javascript',
            $this->_path . 'views/js/citylist.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
        $this->context->controller->registerJavascript(
            'citylist-ajax-javascript',
            $this->_path . 'views/js/ajaxImportCitylist.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }

    // public function hookActionObjectAddressAddAfter($params)
    // {

    //     if ($params['object']->id_citylist != null) {
    //         $db = \Db::getInstance();
    //         $result = $db->insert('city_list_customer_address', [
    //             'id_address' => (int) $params['object']->id,
    //             'id_citylist' => (int)$params['object']->id_citylist,
    //         ]);

    //         return $result;
    //     }
    // }

    // public function hookActionObjectAddressUpdateAfter($params)
    // {
    //     // dump($params);
    //     // die();
    //     if ($params['object']->id_citylist != null) {
    //         $db = \Db::getInstance();
    //         $result = $db->update('city_list_customer_address', [
    //             'id_citylist' => (int)$params['object']->id_citylist,
    //         ], 'id_address =' . (int) $params['object']->id, 1);

    //         return $result;
    //     }
    // }

    // public function hookActionObjectAddressDeleteAfter($params)
    // {
    //     if ($params['object']->id_citylist != null) {
    //         $db = \Db::getInstance();
    //         $result = $db->delete('city_list_customer_address', 'id_address =' . (int) $params['object']->id);

    //         return $result;
    //     }
    // }


    // public function hookActionValidateCustomerAddressForm($params)
    // {
    // }


    //This hook is used for add information of city list and shipping to the pdf invoice
    public function hookDisplayPDFInvoice($params)
    {
        $id_order = $params['object']->id_order;
        $city_name = $this->cityName($id_order);
        if ($city_name) {
            $this->context->smarty->assign(array(
                'city_name' => $city_name
            ));
            return $this->display(__FILE__, 'views/templates/hook/invoice.tpl');
        }
    }

    public function hookDisplayAdminOrder($params)
    {
        $id_order = $params['id_order'];
        $city_name = $this->cityName($id_order);
        if ($city_name) {
            $this->context->smarty->assign(array(
                'city_name' => $city_name
            ));
            return $this->display(__FILE__, 'views/templates/admin/order.tpl');
        }
    }

    private function cityName($id_order)
    {

        $order = new Order($id_order);
        $city_address = new Address($order->id_address_delivery);

        //Get city id
        $sql = new DbQuery();
        $sql->select('id_citylist');
        $sql->from('city_list');
        $sql->where("city_name = '$city_address->city'");
        $id_citylist = Db::getInstance()->getValue($sql);

        //Get city name
        if ($id_citylist) {
            $sql = new DbQuery();
            $sql->select('city_name');
            $sql->from('city_list', 'cl');
            $sql->where('cl.id_citylist = ' . $id_citylist);
            return Db::getInstance()->getValue($sql);
        }
    }


    public function hookDisplayBeforeBodyClosingTag()
    {
        $link_to_fo_ajax = Context::getContext()->link->getModuleLink($this->name, 'cities') ;

        $this->context->smarty->assign(
            array(
                'ajax_link' => $link_to_fo_ajax
            ));
        return $this->display(__FILE__, 'views/templates/front/footer.tpl');
    }


    public function hookActionGetIDZoneByAddressID($params)
    {
        //Chargement de l'objet adresse à partir de son identifiant
	    $address = new Address($params['id_address']);

         //Identifiant de la zone géographique clic and collect
        
        $repository = $this->get('prestarchitect.citylist.repository.city_list_shipping_repository');
        $cities = $repository->getShippings();
         $id_zone = 9;

         
         foreach($cities as $city) {
            //  dump($address->city);
            //  dump($city->getCityList()->getCityName());
             if ($address->city == $city->getCityList()->getCityName()) {
             	 return $city->getZoneId(); //L'important est de retourner la zone ici
              }
        }

    }
}