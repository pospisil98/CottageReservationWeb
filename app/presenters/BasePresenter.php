<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;


class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    const OWNER_EMAIL = "nejakyemail@domena.cz";
    const DEFAULT_PRICE = 7690;

    public function startup()
    {
        parent::startup();

        $this->template->languages = $this->returnlanguageIconOrder();
    }

    public function returnlanguageIconOrder()
    {
        $languages = array("cs", "en");

        if(($key = array_search($this->locale, $languages)) !== false) {
            unset($languages[$key]);
        }

        array_unshift($languages , $this->locale);

        return $languages;
    }
}
