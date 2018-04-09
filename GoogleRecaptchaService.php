<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class GoogleRecaptchaService
{
    const RECAPTCHA_FORM_FIELD_NAME = 'g-recaptcha-response';
    const RECAPTCHA_VALIDATION_ROOT_URL = 'https://www.google.com/recaptcha/api/siteverify?';

    /** @var Request $request */
    protected $request;

    /**
     * GoogleRecaptchaService constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return string
     */
    public function displayRecaptchaFormField()
    {
        return '<div class="g-recaptcha" data-sitekey="' . config('services.google.recaptcha.site_key') . '"></div>';
    }

    /**
     * @return bool
     */
    public function solveRecaptcha()
    {
        if (!$this->hasFormFieldInputData()) {
            return false;
        }

        try {

            // Http Client Connection
            $client   = new Client();
            $response = json_decode($client->get($this->getRecaptchaValidationUrl())->getBody());
            return $response->success;
            
        } catch (Exception $exception) {
            logger()->error($exception);
            return false;
        }

    }

    /**
     * @return bool
     */
    protected function hasFormFieldInputData()
    {
        return $this->getFormFieldInputData() ? true : false;
    }

    /**
     * @return string|null
     */
    public function getFormFieldInputData()
    {
        return $this->request->get(self::RECAPTCHA_FORM_FIELD_NAME);
    }

    /**
     * @return string
     */
    public function getRecaptchaValidationUrl()
    {
        $configParams['secret']   = config('services.google.recaptcha.secret_key');
        $configParams['response'] = $this->getFormFieldInputData();
        $urlParams                = http_build_query($configParams);

        return self::RECAPTCHA_VALIDATION_ROOT_URL . $urlParams;
    }
}