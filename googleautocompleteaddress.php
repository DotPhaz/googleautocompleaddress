<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Googleautocompleteaddress extends Module
{
    public function __construct()
    {
        $this->name = 'googleautocompleteaddress';
        $this->version = '1.0.0';
        $this->author = 'Eliphaz';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->getTranslator()->trans(
            'Google autocomplete address',
            [],
            'Modules.Googleautocompleteaddress.Admin'
        );

        $this->description =
            $this->getTranslator()->trans(
                'Google autocomplete address',
                [],
                'Modules.Googleautocompleteaddress.Admin'
            );

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps_versions_compliancy = [
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_,
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

    public function install()
    {
        return (parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && Configuration::updateValue('API_KEY', ''));
    }

    public function uninstall()
    {
        return (parent::uninstall()
            && Configuration::deleteByName('API_KEY'));
    }


    /**
     * This method handles the module's configuration page
     * @return string The page's HTML content 
     */
    public function getContent()
    {
        $output = '';

        //this part is executed only when the form is submited
        if (Tools::isSubmit('submit' . $this->name)) {

            //retreive value set by the user
            $configValue = (string) Tools::getValue('API_TOKEN');

            //check that value is valid
            if (empty($configValue) || !Validate::isGenericName($configValue)) {
                //invalid value, show an error
                $output = $this->displayError($this->l('Invalid Configuration value'));
            } else {
                //value is ok, update it and display a configuration messsage
                Configuration::updateValue('API_KEY', $configValue);
                $output = $this->displayConfirmation($this->l('Setting updated'));
            }
        }

        //display any message, then the form
        return $output . $this->displayForm();
    }


    public function displayForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Google autocomplete api key'),
                        'name' => 'API_KEY',
                        'size' => 20,
                        'required' => true,
                    ]
                ],
                'submit' => [
                    'title' => $this->l('save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();


        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->fields_value['API_KEY'] = Tools::getValue('API_KEY', Configuration::get('API_KEY'));

        return $helper->generateForm([$form]);
    }



    public function hookActionFrontControllerSetMedia()
    {
        $this->context->controller->registerJavascript(
            'googleautocompletaddress-javascript',
            $this->_path . 'views/js/googleautocompleteaddress.js',
            [
                'media' => 'bottom',
                'priority' => 1000,
            ]
        );
    }
}