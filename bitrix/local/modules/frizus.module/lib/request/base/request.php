<?php
namespace Frizus\Module\Request\Base;

use Bitrix\Main\Application;
use Frizus\Module\Validation\Validator;

abstract class Request
{
    /**
     * @var array
     */
    public $processing = [];

    /**
     * @var Validator
     */
    public $validator;

    protected $data;

    protected $step;

    protected $validateCalledOnce = false;

    protected $lastStepCalled;

    public function __construct($step = null)
    {
        if (isset($step)) {
            $this->step = $step;
        }
    }

    public function step($step)
    {
        $this->step = $step;
        return $this;
    }

    public function validationData()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }

    protected function messages()
    {
        return [];
    }

    public function input($key = null)
    {
        if (func_num_args() === 0) {
            return $this->data;
        } else {
            return $this->data[$key] ?? null;
        }
    }

    public function processing($key = null, $value = null)
    {
        $n = func_num_args();
        if ($n === 0) {
            return $this->processing;
        } elseif ($n === 1) {
            return $this->processing[$key] ?? null;
        } elseif ($n > 1) {
            $this->processing[$key] = $value;
        }
    }

    protected function requestData($queryKeys = [], $postKeys = [])
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $data = [];
        if (!empty($queryKeys)) {
            $list = $request->getQueryList()->toArray();
            foreach ($queryKeys as $key) {
                if (array_key_exists($key, $list)) {
                    $data[$key] = $list[$key];
                }
            }
        }
        if (!empty($postKeys)) {
            $list = $request->getPostList()->toArray();
            foreach ($postKeys as $key) {
                if (array_key_exists($key, $list)) {
                    $data[$key] = $list[$key];
                }
            }
        }
        return $data;
    }

    protected function getValidatorInstance()
    {
        if (isset($this->validator)) {
            if ($this->validateCalledOnce && $this->lastCalledStep !== $this->step) {
                $this->validator->initRules($this->rules());
            }
            return $this->validator;
        }

        $this->validator = new Validator($this->data, $this->rules(), $this->messages(), $this);
        return $this->validator;
    }

    protected function prepareForValidation()
    {
        $this->data = $this->validationData();
    }

    public function validate()
    {
        if (!$this->validateCalledOnce) {
            $this->prepareForValidation();
        }
        $validator = $this->getValidatorInstance();
        if (!$this->validateCalledOnce) {
            $this->validateCalledOnce = true;
        }
        if ($this->lastCalledStep !== $this->step) {
            $this->lastCalledStep = $this->step;
        }

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $this->passedValidation();
    }

    protected function passedValidation()
    {

    }
}
