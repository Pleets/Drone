<?php

namespace Drone\Form\Validator;

use Zend\Validator\NotEmpty;
use Zend\Validator\Digits;
use Zend\Validator\LessThan;
use Zend\Validator\GreaterThan;
use Zend\Validator\EmailAddress;
use Zend\Validator\Date;
use \Exception as Exception;

class QuickValidator
{
    /**
     * @var array
     */
	private $rules;

    /**
     * @var boolean
     */
	private $valid;

    /**
     * @var array
     */
	private $messages = array();

    /**
     * Get all failure messages
     *
     * @return array
     */
	public function getMessages()
	{
		return $this->messages;
	}

    /**
     * Get valid attribute after validation
     *
     * @return array
     */
	public function isValid()
	{
		return $this->valid;
	}

    /**
     * Set valid atribute after each validation
     *
     * @return array
     */
	public function setValid($valid)
	{
		return $this->valid && $valid;
	}

    /**
     * Constructor
     *
     * @param array $rules
     */
	public function __construct($rules)
	{
		$this->rules = $rules;
	}

	public function validateWith($arrayForm)
	{

		foreach ($this->rules as $key => $attributes)
		{
			if (!array_key_exists($key, $arrayForm))
				throw new Exception("El campo <strong>$key</strong> no existe!", 300);

			$label = (array_key_exists('label', array_keys($attributes))) ? $attributes["label"] : $key;

			foreach ($attributes as $name => $value)
			{
				$form_value = $arrayForm[$key];

				switch ($name)
				{
					case 'required':

						$validator = new NotEmpty();
						break;

					case 'minlength':

						$validator = new GreaterThan(['min' => $value, 'inclusive' => true]);
						$form_value = strlen($form_value);
						break;

					case 'maxlength':

						$validator = new LessThan(['max' => $value, 'inclusive' => true]);
						$form_value = strlen((string) $form_value);
						break;

					case 'type':

						switch ($value)
						{
							case 'number':

								$validator = new Digits();
								break;

							case 'email':

								$validator = new EmailAddress();
								break;

							case 'date':

								$validator = new Date();
								break;
						}
						break;

					case 'min':

						if (in_array('type', $attributes) && $attributes['type'] == "number")
							$validator = new GreaterThan(['min' => $value, 'inclusive' => true]);

						break;

					case 'max':

						if (in_array('type', $attributes) && $attributes['type'] == "number")
							$validator = new LessThan(['max' => $value, 'inclusive' => true]);

						break;
				}

				if (in_array($name, ['required', 'digits', 'minlength', 'maxlength', 'type', 'min', 'max', 'date']))
				{
					$valid = $validator->isValid($form_value);
					$this->setValid($valid);

					if (!$valid)
					{
						if (!in_array($key, array_keys($this->messages)))
							$this->messages[$key] = array();

						$this->messages[$key] = array_merge($this->messages[$key], $validator->getOption("messages"));
					}
				}
			}
		}
	}
}