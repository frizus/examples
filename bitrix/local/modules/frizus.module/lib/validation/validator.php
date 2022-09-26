<?php
namespace Frizus\Module\Validation;

use Frizus\Module\Helper\MessageBag;
use Frizus\Module\Request\Base\Request;
use Frizus\Module\Rule\Base\DataAwareRule;
use Frizus\Module\Rule\Base\ValidatorAwareRule;
use Frizus\Module\Rule\Base\Rule as RuleInterface;
use Frizus\Module\Validation\Traits\FormatsMessages;
use Frizus\Module\Validation\Traits\ValidateAttributes;

class Validator
{
    use ValidateAttributes;
    use FormatsMessages;

    public $data;

    public $rules;

    public $customMessages;

    protected $request;

    protected $messages;

    protected $stopOnFirstFailure = false;

    protected $bailAttributes;

    public function __construct($data, $rules, $messages = [], $request = null)
    {
        $this->data = $data;
        $this->customMessages = $messages;
        $this->request = $request;
        $this->initRules($rules);
    }

    public function initRules($rules)
    {
        $this->bailAttributes = [];
        foreach ($rules as $attribute => $rules2) {
            foreach ($rules2 as $i => $rule) {
                if ($rule === 'bail') {
                    unset($rules[$attribute][$i]);
                    if (!array_key_exists($attribute, $this->bailAttributes)) {
                        $this->bailAttributes[$attribute] = true;
                    }
                }
            }
            if (empty($rules2)) {
                unset($rules[$attribute]);
            }
        }
        $this->rules = $rules;
    }

    public function passes()
    {
        $this->messages = new MessageBag();

        foreach ($this->rules as $attribute => $rules) {
            if ($this->stopOnFirstFailure && $this->messages->isNotEmpty()) {
                return false;
            }

            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);
                if ($this->shouldStopValidating($attribute)) {
                    break;
                }
            }
        }

        return $this->messages->isEmpty();
    }

    public function fails()
    {
        return !$this->passes();
    }

    protected function shouldStopValidating($attribute)
    {
        return array_key_exists($attribute, $this->bailAttributes) &&
            $this->messages->has($attribute);
    }

    public function validateAttribute($attribute, $rule)
    {
        $keyExists = array_key_exists($attribute, $this->data);
        $value = $keyExists ? $this->data[$attribute] : null;

        if (is_callable($rule)) {
            $fail = false;
            $rule($attribute, $value, $keyExists, function($message) use ($attribute, $fail) {
                if (!$fail) {
                    $fail = true;
                }
                $this->messages->add(
                    $attribute,
                    $this->makeReplacements($message, $attribute)
                );
            });
            return !$fail;
        }

        if ($rule instanceof RuleInterface) {
            return $this->validateUsingCustomRule($attribute, $value, $keyExists, $rule);
        }

        $method = "validate$rule";
        if (!$this->$method($attribute, $value, $keyExists, $this)) {
            $this->messages->add(
                $attribute,
                $this->makeReplacements($this->getMessage($attribute, $rule), $attribute)
            );
            return false;
        }
        return true;
    }

    protected function validateUsingCustomRule($attribute, $value, $keyExists, $rule)
    {
        if ($rule instanceof ValidatorAwareRule) {
            $rule->setValidator($this);
        }

        if ($rule instanceof DataAwareRule) {
            $rule->setData($this->data);
        }

        if (!$rule->passes($attribute, $value, $keyExists)) {
            $this->messages->add(
                $attribute,
                $this->makeReplacements($attribute, $rule->message())
            );
            return false;
        }
        return true;
    }

    public function stopOnFirstFailure($stopOnFirstFailure = true)
    {
        $this->stopOnFirstFailure = $stopOnFirstFailure;
        return $this;
    }

    public function messages()
    {
        if (!$this->messages) {
            $this->passes();
        }

        return $this->messages;
    }

    /**
     * @return Request|null
     */
    public function request()
    {
        return $this->request;
    }
}
