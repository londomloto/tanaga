<?php
namespace Micro\Translator;

use Phalcon\Translate\Adapter\NativeArray;

class LanguageProvider extends \Micro\Component {

    protected $_translator;

    public function __construct() {
        $translations = $this->getTranslations();
        $this->_translator = new NativeArray(array(
            'content' => $translations
        ));
    }

    public function _($key, $placeholder = NULL) {
        return $this->_translator->query($key, $placeholder);
    }

    public function getLanguage() {
        $app = $this->getApp();
        $request = $app->request;
        $language = $request->getQuery('lang');

        if (empty($language)) {
            $language = $request->getHeader('X-Language');
        }

        if (empty($language)) {
            $language = $app->config->app->language;
        }

        if (empty($language)) {
            $language = 'en';
        }

        return $language;
    }

    public function getTranslations($language = NULL) {
        if (is_null($language)) {
            $language = $this->getLanguage();
        }

        $resource = APPPATH.'languages/'.$language.'.php';

        if (file_exists($resource)) {
            $translations = require($resource);
        } else {
            $translations = array();
        }

        return $translations;
    }

}